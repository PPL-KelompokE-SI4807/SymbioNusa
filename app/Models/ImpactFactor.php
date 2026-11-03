<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpactFactor extends Model
{
    use HasFactory;

    protected $table = 'impact_factors';

    protected $primaryKey = 'id_impact_factor';

    public $timestamps = false;

    protected $fillable = [
        'id_impact_factor',
        'ef_buang',
        'ef_virgin',
        'ef_proses',
        'ef_trans',
        'substitution_rate',
        'harga_virgin_ref',
        'biaya_buang_ref',
        'versi',
        'berlaku_dari',
        'aktif',
        'categories_id_categories',
    ];

    protected function casts(): array
    {
        return [
            'ef_buang' => 'decimal:4',
            'ef_virgin' => 'decimal:4',
            'ef_proses' => 'decimal:4',
            'ef_trans' => 'decimal:4',
            'substitution_rate' => 'decimal:2',
            'harga_virgin_ref' => 'decimal:2',
            'biaya_buang_ref' => 'decimal:2',
            'berlaku_dari' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id_categories', 'id_categories');
    }
}
