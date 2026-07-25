<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\RegPeriksa;
use App\Models\ReferensiMobilejknBpjs;
use App\Models\ReferensiMobilejknBpjsBatal;
use App\Models\ReferensiMobilejknBpjsTaskid;
use App\Services\MobileJknService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;

class CancellationReconciliationTest extends TestCase
{
    use DatabaseTransactions;

    protected $mockService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create Mock for MobileJknService
        $this->mockService = Mockery::mock(MobileJknService::class);
        $this->app->instance(MobileJknService::class, $this->mockService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_reconcile_failed_cancellations_successfully()
    {
        // Disable timestamps for RegPeriksa legacy model and use valid DB keys
        $patient = new RegPeriksa([
            'no_reg' => '999',
            'no_rawat' => 'TEST/RAWAT/999',
            'tgl_registrasi' => now()->format('Y-m-d'),
            'jam_reg' => now()->format('H:i:s'),
            'kd_dokter' => '12345678901',
            'no_rkm_medis' => '000002',
            'kd_poli' => 'ANA',
            'p_jawab' => 'Test PJ',
            'almt_pj' => 'Test Alamat',
            'hubunganpj' => 'Diri Sendiri',
            'biaya_reg' => 0.00,
            'stts' => 'Batal',
            'stts_daftar' => 'Lama',
            'status_lanjut' => 'Ralan',
            'kd_pj' => 'BPJ',
            'umurdaftar' => 30,
            'sttsumur' => 'Th',
            'status_bayar' => 'Belum Bayar',
            'status_poli' => 'Baru',
        ]);
        $patient->timestamps = false;
        $patient->save();

        // Create a dummy cancelled booking with statuskirim = Belum
        $ref = new ReferensiMobilejknBpjs([
            'nobooking' => 'TESTBOOKING999',
            'no_rawat' => 'TEST/RAWAT/999',
            'nomorkartu' => '123456789',
            'nik' => '1234567890123456',
            'status' => 'Batal',
            'statuskirim' => 'Belum',
            'validasi' => now()->toDateTimeString(),
            'nohp' => '08123456789',
            'norm' => '000002',
            'kodepoli' => 'ANA',
            'kodedokter' => '12345678901',
            'jampraktek' => '08:00-12:00',
            'jeniskunjungan' => 1,
            'nomorreferensi' => '1234567',
            'nomorantrean' => 'A-1',
            'angkaantrean' => 1,
            'estimasidilayani' => now()->timestamp * 1000,
            'sisakuotajkn' => 10,
            'kuotajkn' => 30,
            'sisakuotanonjkn' => 10,
            'kuotanonjkn' => 30,
        ]);
        $ref->timestamps = false;
        $ref->save();

        // Create a dummy cancelled booking detail
        $batal = new ReferensiMobilejknBpjsBatal([
            'nobooking' => 'TESTBOOKING999',
            'no_rkm_medis' => '000002',
            'no_rawat_batal' => 'TEST/RAWAT/999',
            'nomorreferensi' => 'REF999',
            'tanggalbatal' => now()->toDateTimeString(),
            'keterangan' => 'Test Batal',
            'statuskirim' => 'Belum',
        ]);
        $batal->timestamps = false;
        $batal->save();

        // Mock service calls for any arguments because real DB records from last 7 days might also get reconciled
        $this->mockService->shouldReceive('updateTaskId')
            ->andReturn(['success' => true]);

        $this->mockService->shouldReceive('batalAntrean')
            ->andReturn(['success' => true]);

        // Run reconciliation via controller endpoint
        $response = $this->postJson('/api/mobilejkn/reconcile-cancellations');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Manual reconciliation of cancellations completed.',
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('stats.task_cancelled'));

        // Verify statuskirim is now 'Sudah'
        $this->assertEquals('Sudah', $ref->fresh()->statuskirim);
        $this->assertEquals('Sudah', $batal->fresh()->statuskirim);
    }
}
