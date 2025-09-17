<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferensiMobilejknBpjsTaskid extends Model
{
    use HasFactory;

    protected $table = 'referensi_mobilejkn_bpjs_taskid';

    // Composite primary key: no_rawat and taskid
    // Laravel doesn't natively support composite keys, so we'll handle it manually
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'no_rawat',
        'taskid',
        'waktu',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
        ];
    }

    /**
     * Get the reg_periksa that owns the ReferensiMobilejknBpjsTaskid.
     */
    public function regPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }
}
