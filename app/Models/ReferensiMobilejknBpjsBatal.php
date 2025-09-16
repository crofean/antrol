<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferensiMobilejknBpjsBatal extends Model
{
    use HasFactory;

    protected $table = 'referensi_mobilejkn_bpjs_batal';

    protected $primaryKey = 'nobooking';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'no_rkm_medis',
        'no_rawat_batal',
        'nomorreferensi',
        'tanggalbatal',
        'keterangan',
        'statuskirim',
        'nobooking',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggalbatal' => 'datetime',
        ];
    }

    // /**
    //  * Get the pasien that owns the ReferensiMobilejknBpjsBatal.
    //  */
    // public function pasien()
    // {
    //     return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    // }

    // /**
    //  * Get the referensiMobilejknBpjs that owns the ReferensiMobilejknBpjsBatal.
    //  */
    // public function referensiMobilejknBpjs()
    // {
    //     return $this->belongsTo(ReferensiMobilejknBpjs::class, 'nobooking', 'nobooking');
    // }
}
