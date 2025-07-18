<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property int $id
 * @property string $name
 * @property float $price
 * 
 * @property Collection|OrderItem[] $order_items
 * @property Collection|Stock[] $stocks
 *
 * @package App\Models
 */
class Product extends Model
{
	protected $table = 'products';
	public $timestamps = false;

	protected $casts = [
		'price' => 'float'
	];

	protected $fillable = [
		'name',
		'price'
	];

	public function order_items()
	{
		return $this->hasMany(OrderItem::class);
	}

	public function stocks()
	{
		return $this->hasMany(Stock::class);
	}
}
