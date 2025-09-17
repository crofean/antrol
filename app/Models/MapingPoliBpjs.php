<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapingPoliBpjs extends Model
{
    use HasFactory;

    protected $table = 'maping_poli_bpjs';

    // Primary key is kd_poli_rs (varchar)
    protected $primaryKey = 'kd_poli_rs';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'kd_poli_rs',
        'kd_poli_bpjs',
        'nm_poli_bpjs',
    ];

    /**
     * Relation to Poliklinik
     */
    public function poliklinik()
    {
        return $this->belongsTo(Poliklinik::class, 'kd_poli_rs', 'kd_poli');
    }
}
