<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Company;
use App\Models\DocumentVerification;
use App\Models\OrderStatusHistory;
use App\Models\PurchaseOrder;
use App\Models\ShipmentDocument;
use App\Models\ShipmentManifest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/** PBI-11 (SYM-15): Pengiriman & Manifes Angkut. PO factory: volume 2.000 kg, toleransi 5% → 1.900–2.100 kg. */
#[Group('SYM-15')]
class ShipmentManifestTest extends TestCase
{
    use DatabaseTransactions;

    private Company $sellerCompany;

    private Company $buyerCompany;

    private User $seller;

    private User $buyer;

    private PurchaseOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->sellerCompany = Company::factory()->seller()->create();
        $this->buyerCompany = Company::factory()->buyer()->create();
        $this->seller = User::factory()->seller($this->sellerCompany)->create();
        $this->buyer = User::factory()->buyer($this->buyerCompany)->create();
        $this->order = $this->readyOrder();
    }

    private function readyOrder(bool $verified = true, OrderStatus $status = OrderStatus::DIBAYAR): PurchaseOrder
    {
        $order = PurchaseOrder::factory()->create([
            'companies_id_seller' => $this->sellerCompany->id_company,
            'companies_id_buyer' => $this->buyerCompany->id_company,
            'status' => $status,
        ]);

        DocumentVerification::factory()
            ->{$verified ? 'setuju' : 'ditolak'}()
            ->create(['purchase_orders_id_purchase_order' => $order->id_purchase_order]);

        return $order;
    }

    /** @return array<string, mixed> */
    private function payload(float $kg, array $extra = []): array
    {
        return array_merge([
            'purchase_orders_id_purchase_order' => $this->order->id_purchase_order,
            'armada' => 'Hino Dutro',
            'plat' => 'd 1234  abc',
            'sopir' => 'Ujang',
            'jadwal' => now()->addDay()->format('Y-m-d\TH:i'),
            'rute' => 'Gudang Cimahi → Pabrik Cikarang',
            'berat_dikirim_kg' => $kg,
            'aksi' => 'draf',
        ], $extra);
    }

    private function shipmentFor(PurchaseOrder $order): ShipmentManifest
    {
        return ShipmentManifest::where('purchase_orders_id_purchase_order', $order->id_purchase_order)->firstOrFail();
    }

    public function test_seller_menyimpan_draf_dengan_surat_jalan(): void
    {
        $this->actingAs($this->seller)
            ->post(route('shipments.store'), $this->payload(2000, [
                'dokumen' => [UploadedFile::fake()->create('surat jalan.pdf', 120, 'application/pdf')],
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $shipment = $this->shipmentFor($this->order);
        $this->assertSame(ShipmentStatus::DRAF, $shipment->status);
        $this->assertNull($shipment->no_manifes);
        $this->assertSame('D 1234 ABC', $shipment->plat);
        $this->assertSame(OrderStatus::DIBAYAR, $this->order->fresh()->status);

        $document = $shipment->documents()->firstOrFail();
        $this->assertSame(ShipmentDocument::JENIS_SURAT_JALAN, $document->jenis);
        Storage::disk('local')->assertExists($document->file_url);
        $this->assertSame('surat-jalan.pdf', $document->fileName());
    }

    public function test_menerbitkan_manifes_mengubah_status_order_dan_mencatat_riwayat(): void
    {
        $shipment = ShipmentManifest::factory()->create(['purchase_orders_id_purchase_order' => $this->order->id_purchase_order]);

        $this->actingAs($this->seller)
            ->post(route('shipments.issue', $shipment))
            ->assertRedirect(route('shipments.show', $shipment));

        $shipment->refresh();
        $this->assertSame(ShipmentStatus::DALAM_PERJALANAN, $shipment->status);
        $this->assertMatchesRegularExpression('/^MNF-\d{6}-\d{4}$/', (string) $shipment->no_manifes);
        $this->assertSame(route('shipments.manifest', $shipment, false), $shipment->manifes_url);
        $this->assertSame(OrderStatus::DALAM_PENGIRIMAN, $this->order->fresh()->status);

        $history = OrderStatusHistory::where('purchase_orders_id_purchase_order', $this->order->id_purchase_order)->latest('id_order_status_history')->firstOrFail();
        $this->assertSame('DIBAYAR', $history->dari);
        $this->assertSame('DALAM_PENGIRIMAN', $history->ke);
        $this->assertStringContainsString((string) $shipment->no_manifes, (string) $history->alasan);
        $this->assertSame($this->seller->id_user, $history->users_id_user);
    }

    public function test_simpan_dan_terbitkan_sekaligus(): void
    {
        $this->actingAs($this->seller)
            ->post(route('shipments.store'), $this->payload(2050, ['aksi' => 'terbitkan']))
            ->assertSessionHasNoErrors();

        $this->assertSame(ShipmentStatus::DALAM_PERJALANAN, $this->shipmentFor($this->order)->status);
        $this->assertSame(OrderStatus::DALAM_PENGIRIMAN, $this->order->fresh()->status);
    }

    public function test_berat_melebihi_batas_atas_ditolak(): void
    {
        $this->actingAs($this->seller)
            ->post(route('shipments.store'), $this->payload(2100.01))
            ->assertSessionHasErrors('berat_dikirim_kg');

        $this->assertSame(0, ShipmentManifest::where('purchase_orders_id_purchase_order', $this->order->id_purchase_order)->count());
    }

    public function test_berat_di_bawah_batas_bawah_butuh_alasan_saat_terbit(): void
    {
        $this->actingAs($this->seller)
            ->post(route('shipments.store'), $this->payload(1800))
            ->assertSessionHasNoErrors();

        $shipment = $this->shipmentFor($this->order);

        $this->actingAs($this->seller)
            ->post(route('shipments.issue', $shipment))
            ->assertSessionHasErrors('alasan_berat');
        $this->assertTrue($shipment->fresh()->isDraft());

        $this->actingAs($this->seller)
            ->post(route('shipments.issue', $shipment), ['alasan_berat' => 'Sebagian belum selesai dikemas'])
            ->assertSessionHasNoErrors();

        $this->assertSame(ShipmentStatus::DALAM_PERJALANAN, $shipment->fresh()->status);
        $history = OrderStatusHistory::where('purchase_orders_id_purchase_order', $this->order->id_purchase_order)->latest('id_order_status_history')->firstOrFail();
        $this->assertStringContainsString('Sebagian belum selesai dikemas', (string) $history->alasan);
    }

    public function test_order_tanpa_verifikasi_dokumen_disetujui_ditolak(): void
    {
        $this->order = $this->readyOrder(verified: false);

        $this->actingAs($this->seller)
            ->post(route('shipments.store'), $this->payload(2000))
            ->assertSessionHasErrors('purchase_orders_id_purchase_order');
    }

    public function test_order_yang_belum_dibayar_ditolak(): void
    {
        $this->order = $this->readyOrder(status: OrderStatus::INVOICED);

        $this->actingAs($this->seller)
            ->post(route('shipments.store'), $this->payload(2000))
            ->assertSessionHasErrors('purchase_orders_id_purchase_order');
    }

    public function test_satu_order_hanya_boleh_punya_satu_pengiriman_aktif(): void
    {
        ShipmentManifest::factory()->create(['purchase_orders_id_purchase_order' => $this->order->id_purchase_order]);

        $this->actingAs($this->seller)
            ->post(route('shipments.store'), $this->payload(2000))
            ->assertSessionHasErrors('purchase_orders_id_purchase_order');
    }

    public function test_draf_yang_dibatalkan_membebaskan_order(): void
    {
        $shipment = ShipmentManifest::factory()->create(['purchase_orders_id_purchase_order' => $this->order->id_purchase_order]);

        $this->actingAs($this->seller)->post(route('shipments.cancel', $shipment))->assertRedirect(route('shipments.index'));
        $this->assertSame(ShipmentStatus::DIBATALKAN, $shipment->fresh()->status);

        $this->actingAs($this->seller)
            ->post(route('shipments.store'), $this->payload(2000))
            ->assertSessionHasNoErrors();
    }

    public function test_seller_lain_tidak_bisa_memakai_order_ini(): void
    {
        $other = User::factory()->seller()->create();

        $this->actingAs($other)
            ->post(route('shipments.store'), $this->payload(2000))
            ->assertSessionHasErrors('purchase_orders_id_purchase_order');

        $shipment = ShipmentManifest::factory()->create(['purchase_orders_id_purchase_order' => $this->order->id_purchase_order]);
        $this->actingAs($other)->get(route('shipments.show', $shipment))->assertForbidden();
    }

    public function test_buyer_tidak_bisa_membuat_dan_tidak_melihat_draf(): void
    {
        $this->actingAs($this->buyer)->get(route('shipments.create'))->assertForbidden();

        $draft = ShipmentManifest::factory()->create(['purchase_orders_id_purchase_order' => $this->order->id_purchase_order]);
        $this->actingAs($this->buyer)->get(route('shipments.show', $draft))->assertForbidden();
        $this->actingAs($this->buyer)->get(route('shipments.index'))->assertOk()->assertDontSee($draft->displayNumber());
    }

    public function test_buyer_melihat_pengiriman_terbit_dan_manifesnya(): void
    {
        $shipment = ShipmentManifest::factory()->dalamPerjalanan()->create(['purchase_orders_id_purchase_order' => $this->order->id_purchase_order]);

        $this->actingAs($this->buyer)->get(route('shipments.index'))->assertOk()->assertSee((string) $shipment->no_manifes);
        $this->actingAs($this->buyer)->get(route('shipments.show', $shipment))->assertOk();
        $this->actingAs($this->buyer)->get(route('shipments.manifest', $shipment))
            ->assertOk()
            ->assertSee('Manifes Angkut')
            ->assertSee((string) $shipment->no_manifes);
    }

    public function test_manifes_belum_tersedia_untuk_draf(): void
    {
        $draft = ShipmentManifest::factory()->create(['purchase_orders_id_purchase_order' => $this->order->id_purchase_order]);

        $this->actingAs($this->seller)->get(route('shipments.manifest', $draft))->assertForbidden();
    }

    public function test_seller_menandai_pengiriman_tiba(): void
    {
        $shipment = ShipmentManifest::factory()->dalamPerjalanan()->create(['purchase_orders_id_purchase_order' => $this->order->id_purchase_order]);

        $this->actingAs($this->buyer)->post(route('shipments.arrive', $shipment))->assertForbidden();
        $this->actingAs($this->seller)->post(route('shipments.arrive', $shipment))->assertRedirect(route('shipments.show', $shipment));

        $this->assertSame(ShipmentStatus::TIBA, $shipment->fresh()->status);
    }

    public function test_pengiriman_terbit_tidak_bisa_diubah(): void
    {
        $shipment = ShipmentManifest::factory()->dalamPerjalanan()->create(['purchase_orders_id_purchase_order' => $this->order->id_purchase_order]);

        $this->actingAs($this->seller)->get(route('shipments.edit', $shipment))->assertForbidden();
        $this->actingAs($this->seller)->put(route('shipments.update', $shipment), $this->payload(2000))->assertForbidden();
    }

    public function test_surat_jalan_bisa_diunduh_dan_dihapus_selama_draf(): void
    {
        $this->actingAs($this->seller)->post(route('shipments.store'), $this->payload(2000, [
            'dokumen' => [UploadedFile::fake()->create('sj.pdf', 50, 'application/pdf')],
        ]));
        $shipment = $this->shipmentFor($this->order);
        $document = $shipment->documents()->firstOrFail();

        $this->actingAs($this->seller)->get(route('shipments.documents.download', [$shipment, $document]))->assertOk();

        $this->actingAs($this->seller)->delete(route('shipments.documents.destroy', [$shipment, $document]))->assertRedirect();
        $this->assertDatabaseMissing('shipment_documents', ['id_shipment_document' => $document->id_shipment_document]);
        Storage::disk('local')->assertMissing((string) $document->file_url);
    }

    public function test_halaman_memakai_kaca_dan_menampilkan_order_siap_kirim(): void
    {
        $this->actingAs($this->seller)->get(route('shipments.index'))
            ->assertOk()
            ->assertSee('dash-kaca', false)
            ->assertSee('kaca-backdrop', false)
            ->assertSeeInOrder(['Pengiriman &amp; Manifes', 'Siap dikirim', $this->order->formattedNumber(), 'Daftar pengiriman'], false);

        $this->actingAs($this->seller)->get(route('shipments.create', ['order' => $this->order->id_purchase_order]))
            ->assertOk()
            ->assertSee('dash-kaca', false)
            ->assertSeeInOrder(['Data pengiriman', 'Prasyarat', 'Detail pesanan']);
    }
}
