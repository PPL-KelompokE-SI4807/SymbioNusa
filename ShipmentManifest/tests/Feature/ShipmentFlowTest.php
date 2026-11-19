<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\PurchaseOrder;
use App\Models\ShipmentManifest;
use App\Models\User;
use Database\Seeders\Pbi11StandaloneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentFlowTest extends TestCase
{
    use RefreshDatabase;

    private function seller(): User
    {
        return User::where('email', 'seller1@symbionusa.test')->firstOrFail();
    }

    private function payload(int $orderId, float $kg, array $extra = []): array
    {
        return array_merge([
            'purchase_order_id' => $orderId,
            'armada' => 'Hino Dutro',
            'plat' => 'D 1234 ABC',
            'sopir' => 'Ujang',
            'jadwal_berangkat' => now()->addDay()->format('Y-m-d\TH:i'),
            'estimasi_tiba' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'rute_asal' => 'Bandung',
            'rute_tujuan' => 'Cikarang',
            'berat_dikirim_kg' => $kg,
        ], $extra);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(Pbi11StandaloneSeeder::class);
    }

    public function test_seller_membuat_draf_lalu_memulai_pengiriman(): void
    {
        $seller = $this->seller();
        $po = PurchaseOrder::where('nomor_po', 'PO-2026-0001')->firstOrFail();

        $this->withSession(['demo_user_id' => $seller->id])
            ->post('/shipments', $this->payload($po->id, 4900))
            ->assertRedirect();

        $shipment = ShipmentManifest::where('purchase_order_id', $po->id)->firstOrFail();
        $this->assertSame(ShipmentStatus::Draf, $shipment->status);
        $this->assertNull($shipment->no_manifes);

        $this->withSession(['demo_user_id' => $seller->id])
            ->post("/shipments/{$shipment->id}/start")
            ->assertRedirect();

        $shipment->refresh();
        $this->assertSame(ShipmentStatus::DalamPengiriman, $shipment->status);
        $this->assertMatchesRegularExpression('/^MNF-\d{6}-\d{4}$/', $shipment->no_manifes);
        $this->assertSame(OrderStatus::DalamPengiriman, $po->fresh()->status);
    }

    public function test_berat_melebihi_batas_atas_ditolak(): void
    {
        $po = PurchaseOrder::where('nomor_po', 'PO-2026-0001')->firstOrFail();

        $this->withSession(['demo_user_id' => $this->seller()->id])
            ->post('/shipments', $this->payload($po->id, 5300)) // Bmax = 5.250
            ->assertSessionHasErrors('berat_dikirim_kg');

        $this->assertSame(0, ShipmentManifest::where('purchase_order_id', $po->id)->count());
    }

    public function test_berat_di_bawah_batas_bawah_wajib_alasan(): void
    {
        $po = PurchaseOrder::where('nomor_po', 'PO-2026-0001')->firstOrFail();

        $this->withSession(['demo_user_id' => $this->seller()->id])
            ->post('/shipments', $this->payload($po->id, 4000))
            ->assertSessionHasErrors('alasan_di_bawah_batas');

        $this->withSession(['demo_user_id' => $this->seller()->id])
            ->post('/shipments', $this->payload($po->id, 4000, ['alasan_di_bawah_batas' => 'Sebagian belum selesai dikemas']))
            ->assertSessionHasNoErrors();
    }

    public function test_order_dengan_verifikasi_dokumen_belum_disetujui_ditolak(): void
    {
        $po = PurchaseOrder::where('nomor_po', 'PO-2026-0002')->firstOrFail();

        $this->withSession(['demo_user_id' => $this->seller()->id])
            ->post('/shipments', $this->payload($po->id, 3000))
            ->assertSessionHasErrors('purchase_order_id');
    }

    public function test_buyer_tidak_bisa_membuat_pengiriman(): void
    {
        $buyer = User::where('role', User::ROLE_BUYER)->firstOrFail();

        $this->withSession(['demo_user_id' => $buyer->id])
            ->get('/shipments/create')
            ->assertForbidden();
    }
}
