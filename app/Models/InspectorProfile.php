<?php

namespace App\Models;

use App\Enums\InspectorStatus;
use Carbon\CarbonInterface;
use Database\Factories\InspectorProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Profil kompetensi Inspector (PBI-01 / SYM-5), tabel `inspector_profiles` di DDL.
 *
 * @property int $id_inspector
 * @property array<int, int|string>|null $bidang_keahlian id_categories yang dikuasai (JSON)
 * @property string|null $wilayah_tugas
 * @property string|null $no_sertifikat
 * @property string|null $sertifikat_url path berkas di disk "local" (privat)
 * @property Carbon|null $berlaku_sampai
 * @property bool|null $status_aktif
 * @property int $id_user
 * @property-read User $user
 */
class InspectorProfile extends Model
{
    /** @use HasFactory<InspectorProfileFactory> */
    use HasFactory;

    protected $table = 'inspector_profiles';

    protected $primaryKey = 'id_inspector';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'bidang_keahlian',
        'wilayah_tugas',
        'no_sertifikat',
        'sertifikat_url',
        'berlaku_sampai',
        'status_aktif',
        'id_user',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bidang_keahlian' => 'array',
            'berlaku_sampai' => 'date',
            'status_aktif' => 'boolean',
        ];
    }

    /** Masa berlaku sertifikat dihitung per tanggal WIB (AGENTS.md §7), walau app.timezone masih UTC. */
    public const TIMEZONE = 'Asia/Jakarta';

    /**
     * Tanggal hari ini menurut WIB, sebagai tengah malam agar sebanding dengan kolom DATE.
     */
    public static function today(): Carbon
    {
        return Carbon::parse(Carbon::now(self::TIMEZONE)->toDateString());
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Inspector yang boleh ditugaskan (PBI-18): status aktif dan sertifikat belum lewat.
     *
     * @param  Builder<InspectorProfile>  $query
     */
    public function scopeAssignable(Builder $query, ?CarbonInterface $today = null): void
    {
        $query->where('status_aktif', 1)
            ->whereDate('berlaku_sampai', '>=', ($today ?? self::today())->toDateString());
    }

    /**
     * Saring menurut status turunan (lihat InspectorStatus).
     *
     * @param  Builder<InspectorProfile>  $query
     */
    public function scopeWithStatus(Builder $query, InspectorStatus $status, ?CarbonInterface $today = null): void
    {
        $today = ($today ?? self::today())->copy()->startOfDay();
        $limit = $today->copy()->addDays(InspectorStatus::EXPIRING_WITHIN_DAYS)->toDateString();

        match ($status) {
            InspectorStatus::AKTIF => $query->where('status_aktif', 1)
                ->whereDate('berlaku_sampai', '>', $limit),
            InspectorStatus::SEGERA_BERAKHIR => $query->where('status_aktif', 1)
                ->whereDate('berlaku_sampai', '>=', $today->toDateString())
                ->whereDate('berlaku_sampai', '<=', $limit),
            InspectorStatus::NONAKTIF => $query->where(function (Builder $q) use ($today) {
                $q->where('status_aktif', 0)
                    ->orWhereNull('status_aktif')
                    ->orWhereNull('berlaku_sampai')
                    ->orWhereDate('berlaku_sampai', '<', $today->toDateString());
            }),
        };
    }

    /**
     * Inspector yang menguasai kategori tertentu.
     *
     * @param  Builder<InspectorProfile>  $query
     */
    public function scopeWithExpertise(Builder $query, int $categoryId): void
    {
        $query->whereJsonContains('bidang_keahlian', $categoryId);
    }

    public function status(?CarbonInterface $today = null): InspectorStatus
    {
        return InspectorStatus::resolve((bool) $this->status_aktif, $this->berlaku_sampai, $today ?? self::today());
    }

    /**
     * Sisa hari masa berlaku sertifikat (negatif bila sudah lewat).
     */
    public function daysUntilExpiry(?CarbonInterface $today = null): ?int
    {
        if ($this->berlaku_sampai === null) {
            return null;
        }

        return (int) ($today ?? self::today())->copy()->startOfDay()
            ->diffInDays($this->berlaku_sampai->copy()->startOfDay(), false);
    }

    /**
     * @return list<int>
     */
    public function expertiseIds(): array
    {
        return array_values(array_map('intval', $this->bidang_keahlian ?? []));
    }

    /**
     * Kategori bidang keahlian, diurutkan menurut nama.
     *
     * @param  Collection<int, Category>|null  $categories  koleksi siap pakai agar daftar tidak N+1
     * @return Collection<int, Category>
     */
    public function expertiseCategories(?Collection $categories = null): Collection
    {
        $ids = $this->expertiseIds();

        if ($categories !== null) {
            return $categories->whereIn('id_categories', $ids)->sortBy('nama')->values();
        }

        return Category::whereIn('id_categories', $ids)->orderBy('nama')->get();
    }
}
