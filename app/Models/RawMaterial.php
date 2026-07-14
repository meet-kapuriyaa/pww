<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_name',
        'unit',
        'current_stock',
        'safety_threshold',
        'average_purchase_price',
    ];

    protected $casts = [
        'current_stock' => 'decimal:4',
        'safety_threshold' => 'decimal:4',
        'average_purchase_price' => 'decimal:2',
    ];

    /**
     * Get the bill of materials referencing this raw material.
     */
    public function billOfMaterials()
    {
        return $this->hasMany(BillOfMaterial::class, 'raw_material_id');
    }

    /**
     * Get the finished goods that consume this raw material.
     */
    public function finishedGoods()
    {
        return $this->belongsToMany(FinishedGood::class, 'bill_of_materials', 'raw_material_id', 'finished_good_id')
                    ->withPivot('required_quantity', 'waste_percentage')
                    ->withTimestamps();
    }
}
