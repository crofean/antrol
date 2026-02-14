<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferensiMobilejknBpjs extends Model
{
    use HasFactory;

    protected $table = 'referensi_mobilejkn_bpjs';

    protected $primaryKey = 'nobooking';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nobooking',
        'no_rawat',
        'nomorkartu',
        'nik',
        'nohp',
        'kodepoli',
        'pasienbaru',
        'norm',
        'tanggalperiksa',
        'kodedokter',
        'jampraktek',
        'jeniskunjungan',
        'nomorreferensi',
        'nomorantrean',
        'angkaantrean',
        'estimasidilayani',
        'sisakuotajkn',
        'kuotajkn',
        'sisakuotanonjkn',
        'kuotanonjkn',
        'status',
        'validasi',
        'statuskirim',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggalperiksa' => 'date',
            'validasi' => 'datetime',
            'sisakuotajkn' => 'integer',
            'kuotajkn' => 'integer',
            'sisakuotanonjkn' => 'integer',
            'kuotanonjkn' => 'integer',
        ];
    }

    // /**
    //  * Get the pasien that owns the ReferensiMobilejknBpjs.
    //  */
    // public function pasien()
    // {
    //     return $this->belongsTo(Pasien::class, 'norm', 'no_rkm_medis');
    // }

    /**
     * Get the reg_periksa that owns the ReferensiMobilejknBpjs.
     */
    public function regPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }

    /**
     * Get the referensi_mobilejkn_bpjs_taskid for the ReferensiMobilejknBpjs.
     */
    public function referensiMobilejknBpjsTaskid()
    {
        return $this->hasMany(ReferensiMobilejknBpjsTaskid::class, 'no_rawat', 'no_rawat');
    }
}
