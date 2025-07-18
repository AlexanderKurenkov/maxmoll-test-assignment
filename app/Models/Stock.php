<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Stock
 * 
 * @property int $product_id
 * @property int $warehouse_id
 * @property int $stock
 * 
 * @property Product $product
 * @property Warehouse $warehouse
 *
 * @package App\Models
 */
class Stock extends Model
{
	protected $table = 'stocks';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'product_id' => 'int',
		'warehouse_id' => 'int',
		'stock' => 'int'
	];

	protected $fillable = [
		'stock'
	];

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function warehouse()
	{
		return $this->belongsTo(Warehouse::class);
	}
}
