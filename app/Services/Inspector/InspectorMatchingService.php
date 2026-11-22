<?php

namespace App\Services\Inspector;

use App\Models\Category;
use App\Models\Inspection;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * InspectorMatchingService (PBI-18 / SYM-22)
 *
 * Menyaring Inspector aktif berdasarkan keahlian kategori material,
 * wilayah tugas (untuk mode Lapangan), beban kerja, dan pencegahan bentrok jadwal.
 */
class InspectorMatchingService
{
    /**
     * Mengambil dan memberi skor kecocokan seluruh Inspector aktif untuk order tertentu.
     *
     * @return Collection<int, User>
     */
    public function findEligibleInspectors(PurchaseOrder $po, string $mode = Inspection::MODE_DESK): Collection
    {
        $po->loadMissing(['rfqContract.listing.category', 'seller']);

        /** @var Category|null $category */
        $category = $po->rfqContract?->listing?->category;
        $categoryId = $category?->id_categories;
        $sellerWilayah = trim((string) ($po->seller?->alamat ?? ''));

        // Ambil seluruh user ber-role INSPECTOR yang aktif dan sertifikatnya belum kedaluwarsa
        $inspectors = User::query()
            ->where('role', 'INSPECTOR')
            ->where('status', 'AKTIF')
            ->whereHas('inspectorProfile', function ($query) {
                $query->where('status_aktif', 1)
                    ->where(function ($q) {
                        $q->whereNull('berlaku_sampai')
                            ->orWhereDate('berlaku_sampai', '>=', now()->toDateString());
                    });
            })
            ->with('inspectorProfile')
            ->get();

        foreach ($inspectors as $inspector) {
            $profile = $inspector->inspectorProfile;
            $skills = (array) ($profile?->bidang_keahlian ?? []);
            $wilayah = trim((string) ($profile?->wilayah_tugas ?? ''));

            // 1. Kecocokan Keahlian Kategori (Bobot 50 poin)
            $categoryMatch = false;
            if ($categoryId) {
                $categoryMatch = in_array($categoryId, $skills, false)
                    || in_array((string) $categoryId, $skills, true);
            }
            $scoreKeahlian = $categoryMatch ? 50 : 10;

            // 2. Kecocokan Wilayah (Bobot 30 poin)
            $regionMatch = false;
            if ($wilayah !== '' && $sellerWilayah !== '') {
                $regionMatch = str_contains(mb_strtolower($sellerWilayah), mb_strtolower($wilayah))
                    || str_contains(mb_strtolower($wilayah), mb_strtolower($sellerWilayah));
            }

            if ($mode === Inspection::MODE_LAPANGAN) {
                $scoreWilayah = $regionMatch ? 30 : 5;
            } else {
                // Untuk mode Desk, wilayah tidak mutlak sehingga diberikan nilai dasar memadai
                $scoreWilayah = $regionMatch ? 30 : 25;
            }

            // 3. Beban Kerja Aktif (Bobot 20 poin)
            $activeCount = Inspection::query()
                ->where('users_id_user', $inspector->id_user)
                ->whereIn('status', [Inspection::STATUS_DITUGASKAN, Inspection::STATUS_DIPROSES])
                ->count();

            $scoreBeban = match (true) {
                $activeCount === 0 => 20,
                $activeCount <= 2 => 15,
                $activeCount <= 4 => 10,
                default => 5,
            };

            $totalScore = min(100, $scoreKeahlian + $scoreWilayah + $scoreBeban);

            $inspector->match_score = $totalScore;
            $inspector->active_workload = $activeCount;
            $inspector->has_category_match = $categoryMatch;
            $inspector->has_region_match = $regionMatch;
        }

        return $inspectors->sortBy([
            ['match_score', 'desc'],
            ['active_workload', 'asc'],
        ])->values();
    }

    /**
     * Memeriksa apakah Inspector memiliki jadwal inspeksi lain yang bertabrakan pada tanggal yang sama.
     */
    public function checkScheduleConflict(
        int $inspectorUserId,
        Carbon|string $schedule,
        ?int $ignoreInspectionId = null
    ): bool {
        $date = Carbon::parse($schedule)->toDateString();

        $query = Inspection::query()
            ->where('users_id_user', $inspectorUserId)
            ->whereIn('status', [Inspection::STATUS_DITUGASKAN, Inspection::STATUS_DIPROSES])
            ->whereDate('jadwal', $date);

        if ($ignoreInspectionId) {
            $query->where('id_inspection', '!=', $ignoreInspectionId);
        }

        return $query->exists();
    }

    /**
     * Mengambil kalender beban kerja Inspector selama n hari ke depan.
     *
     * @return array<int, array{date: string, day_name: string, count: int, level: string}>
     */
    public function getWorkloadCalendar(int $inspectorUserId, Carbon|string|null $startDate = null, int $days = 7): array
    {
        $start = $startDate ? Carbon::parse($startDate) : now();
        $calendar = [];

        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $dateStr = $day->toDateString();

            $count = Inspection::query()
                ->where('users_id_user', $inspectorUserId)
                ->whereIn('status', [Inspection::STATUS_DITUGASKAN, Inspection::STATUS_DIPROSES])
                ->whereDate('jadwal', $dateStr)
                ->count();

            $level = match (true) {
                $count === 0 => 'ringan',
                $count === 1 => 'sedang',
                default => 'padat',
            };

            $calendar[] = [
                'date' => $dateStr,
                'day_name' => $day->translatedFormat('l, d M'),
                'count' => $count,
                'level' => $level,
            ];
        }

        return $calendar;
    }
}
