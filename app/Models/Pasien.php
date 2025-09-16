<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasien';

    protected $primaryKey = 'no_rkm_medis';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'no_rkm_medis',
        'nm_pasien',
        'no_ktp',
        'jk',
        'tmp_lahir',
        'tgl_lahir',
        'nm_ibu',
        'alamat',
        'gol_darah',
        'pekerjaan',
        'stts_nikah',
        'agama',
        'tgl_daftar',
        'no_tlp',
        'umur',
        'pnd',
        'keluarga',
        'namakeluarga',
        'kd_pj',
        'no_peserta',
        'kd_kel',
        'kd_kec',
        'kd_kab',
        'pekerjaanpj',
        'alamatpj',
        'kelurahanpj',
        'kecamatanpj',
        'kabupatenpj',
        'perusahaan_pasien',
        'suku_bangsa',
        'bahasa_pasien',
        'cacat_fisik',
        'email',
        'nip',
        'kd_prop',
        'propinsipj',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
            'tgl_daftar' => 'date',
            'jk' => 'string',
            'gol_darah' => 'string',
            'stts_nikah' => 'string',
            'pnd' => 'string',
            'keluarga' => 'string',
        ];
    }

    /**
     * Get the penjab that owns the Pasien.
     */
    public function penjab()
    {
        return $this->belongsTo(Penjab::class, 'kd_pj', 'kd_pj');
    }

    /**
     * Get the kelurahan that owns the Pasien.
     */
    // public function kelurahan()
    // {
    //     return $this->belongsTo(Kelurahan::class, 'kd_kel', 'kd_kel');
    // }

    /**
     * Get the kecamatan that owns the Pasien.
     */
    // public function kecamatan()
    // {
    //     return $this->belongsTo(Kecamatan::class, 'kd_kec', 'kd_kec');
    // }

    /**
     * Get the kabupaten that owns the Pasien.
     */
    // public function kabupaten()
    // {
    //     return $this->belongsTo(Kabupaten::class, 'kd_kab', 'kd_kab');
    // }

    /**
     * Get the perusahaan_pasien that owns the Pasien.
     */
    // public function perusahaanPasien()
    // {
    //     return $this->belongsTo(PerusahaanPasien::class, 'perusahaan_pasien', 'kode_perusahaan');
    // }

    /**
     * Get the suku_bangsa that owns the Pasien.
     */
    // public function sukuBangsa()
    // {
    //     return $this->belongsTo(SukuBangsa::class, 'suku_bangsa', 'id');
    // }

    /**
     * Get the bahasa_pasien that owns the Pasien.
     */
    // public function bahasaPasien()
    // {
    //     return $this->belongsTo(BahasaPasien::class, 'bahasa_pasien', 'id');
    // }

    /**
     * Get the cacat_fisik that owns the Pasien.
     */
    // public function cacatFisik()
    // {
    //     return $this->belongsTo(CacatFisik::class, 'cacat_fisik', 'id');
    // }

    /**
     * Get the propinsi that owns the Pasien.
     */
    // public function propinsi()
    // {
    //     return $this->belongsTo(Propinsi::class, 'kd_prop', 'kd_prop');
    // }

    /**
     * Get the reg_periksa for the Pasien.
     */
    public function regPeriksa()
    {
        return $this->hasMany(RegPeriksa::class, 'no_rkm_medis', 'no_rkm_medis');
    }
}
