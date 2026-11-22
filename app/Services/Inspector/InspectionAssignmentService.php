<?php

namespace App\Services\Inspector;

use App\Enums\OrderStatus;
use App\Models\DocumentVerification;
use App\Models\Inspection;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\OrderStateMachine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * InspectionAssignmentService (PBI-18 / SYM-22)
 *
 * Mengelola alur penugasan inspeksi, integrasi dengan OrderStateMachine dan
 * DocumentVerification, serta pembatalan dan penugasan ulang inspeksi.
 */
class InspectionAssignmentService
{
    public function __construct(
        protected InspectorMatchingService $matchingService,
        protected InspectionModeAdvisor $modeAdvisor
    ) {}

    /**
     * Mengambil daftar Purchase Order yang siap ditugaskan inspeksi.
     */
    public function getOrdersAwaitingAssignment(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = PurchaseOrder::query()
            ->with(['seller', 'buyer', 'rfqContract.listing.category'])
            ->where('status', OrderStatus::DIKONFIRMASI)
            ->whereDoesntHave('inspections', function ($q) {
                $q->whereIn('status', [Inspection::STATUS_DITUGASKAN, Inspection::STATUS_DIPROSES]);
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id_purchase_order', 'like', "%{$search}%")
                    ->orWhereHas('seller', fn ($sq) => $sq->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('buyer', fn ($bq) => $bq->where('nama', 'like', "%{$search}%"));
            });
        }

        return $query->orderByDesc('id_purchase_order')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil riwayat penugasan inspeksi platform.
     */
    public function getRecentInspections(?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Inspection::query()
            ->with(['purchaseOrder.seller', 'purchaseOrder.buyer', 'inspector.inspectorProfile']);

        if ($status && in_array($status, [
            Inspection::STATUS_DITUGASKAN,
            Inspection::STATUS_DIPROSES,
            Inspection::STATUS_SELESAI,
            Inspection::STATUS_DIBATALKAN,
        ], true)) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('id_inspection')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil daftar penugasan untuk Inspector tertentu.
     */
    public function getInspectionsForInspector(User $inspector, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Inspection::query()
            ->with(['purchaseOrder.seller', 'purchaseOrder.buyer', 'purchaseOrder.rfqContract.listing.category'])
            ->where('users_id_user', $inspector->id_user);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('id_inspection')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Menugaskan Inspector untuk Purchase Order tertentu.
     *
     * @param  array{
     *     users_id_user: int|string,
     *     mode_verifikasi: string,
     *     alasan_mode?: string|null,
     *     jadwal: string|Carbon
     * }  $data
     *
     * @throws ValidationException
     */
    public function assignInspection(PurchaseOrder $po, array $data, User $actor): Inspection
    {
        $inspectorId = (int) $data['users_id_user'];
        $schedule = Carbon::parse($data['jadwal']);

        // Cek bentrok jadwal
        if ($this->matchingService->checkScheduleConflict($inspectorId, $schedule)) {
            throw ValidationException::withMessages([
                'jadwal' => ['Inspector yang dipilih telah memiliki jadwal inspeksi lain pada tanggal yang sama.'],
            ]);
        }

        return DB::transaction(function () use ($po, $data, $inspectorId, $schedule, $actor) {
            // 1. Buat record penugasan inspeksi
            $inspection = Inspection::create([
                'mode_verifikasi' => $data['mode_verifikasi'],
                'alasan_mode' => $data['alasan_mode'] ?? null,
                'jadwal' => $schedule,
                'status' => Inspection::STATUS_DITUGASKAN,
                'purchase_orders_id_purchase_order' => $po->id_purchase_order,
                'users_id_user' => $inspectorId,
            ]);

            // 2. Inisialisasi record document_verification untuk Inspector terkait
            DocumentVerification::firstOrCreate(
                [
                    'purchase_orders_id_purchase_order' => $po->id_purchase_order,
                    'users_id_user' => $inspectorId,
                ],
                [
                    'verifiable_type' => PurchaseOrder::class,
                    'verifiable_id' => $po->id_purchase_order,
                    'checklist' => [
                        'nama_perusahaan_sesuai' => false,
                        'nomor_dokumen_valid' => false,
                        'masa_berlaku_valid' => false,
                        'bukti_listing_sesuai' => false,
                        'dokumen_terbaca_jelas' => false,
                    ],
                    'status' => DocumentVerification::STATUS_MENUNGGU,
                ]
            );

            // 3. Transisi status Purchase Order menjadi INSPEKSI
            if ($po->status === OrderStatus::DIKONFIRMASI) {
                OrderStateMachine::transition(
                    po: $po,
                    target: OrderStatus::INSPEKSI,
                    actor: $actor,
                    reason: 'Penugasan inspeksi oleh Admin (PBI-18)'
                );
            }

            return $inspection;
        });
    }

    /**
     * Membatalkan penugasan inspeksi.
     */
    public function cancelInspection(Inspection $inspection, ?string $reason, User $actor): Inspection
    {
        return DB::transaction(function () use ($inspection, $reason) {
            $inspection->update([
                'status' => Inspection::STATUS_DIBATALKAN,
                'alasan_mode' => $reason ? ($inspection->alasan_mode ? "{$inspection->alasan_mode}. Dibatalkan: {$reason}" : "Dibatalkan: {$reason}") : $inspection->alasan_mode,
            ]);

            // Batalkan juga verifikasi dokumen yang masih menunggu jika ada
            DocumentVerification::query()
                ->where('purchase_orders_id_purchase_order', $inspection->purchase_orders_id_purchase_order)
                ->where('users_id_user', $inspection->users_id_user)
                ->where('status', DocumentVerification::STATUS_MENUNGGU)
                ->update(['status' => DocumentVerification::STATUS_DIBATALKAN]);

            return $inspection->fresh();
        });
    }

    /**
     * Menugaskan ulang (reassign) inspeksi ke Inspector lain.
     *
     * @param  array{users_id_user: int|string, jadwal: string|Carbon, alasan_reassign?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function reassignInspection(Inspection $inspection, array $data, User $actor): Inspection
    {
        $newInspectorId = (int) $data['users_id_user'];
        $newSchedule = Carbon::parse($data['jadwal']);

        if ($this->matchingService->checkScheduleConflict($newInspectorId, $newSchedule, $inspection->id_inspection)) {
            throw ValidationException::withMessages([
                'jadwal' => ['Inspector baru yang dipilih telah memiliki jadwal inspeksi lain pada tanggal yang sama.'],
            ]);
        }

        return DB::transaction(function () use ($inspection, $data, $newInspectorId, $newSchedule) {
            $oldInspectorId = $inspection->users_id_user;

            $inspection->update([
                'users_id_user' => $newInspectorId,
                'jadwal' => $newSchedule,
                'alasan_mode' => $data['alasan_reassign'] ?? $inspection->alasan_mode,
                'status' => Inspection::STATUS_DITUGASKAN,
            ]);

            // Update verifikasi dokumen terkait
            DocumentVerification::query()
                ->where('purchase_orders_id_purchase_order', $inspection->purchase_orders_id_purchase_order)
                ->where('users_id_user', $oldInspectorId)
                ->where('status', DocumentVerification::STATUS_MENUNGGU)
                ->update(['users_id_user' => $newInspectorId]);

            return $inspection->fresh();
        });
    }
}
