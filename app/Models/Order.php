<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 *
 * @property int $id
 * @property string $customer
 * @property Carbon $created_at
 * @property Carbon|null $completed_at
 * @property int $warehouse_id
 * @property string $status
 *
 * @property Warehouse $warehouse
 * @property Collection|OrderItem[] $order_items
 *
 * @package App\Models
 */
class Order extends Model
{
	protected $table = 'orders';
	public $timestamps = false;

	protected $casts = [
		'completed_at' => 'datetime',
		'warehouse_id' => 'int'
	];

	protected $fillable = [
		'customer',
		'completed_at',
		'warehouse_id',
		'status'
	];

	public function warehouse()
	{
		return $this->belongsTo(Warehouse::class);
	}

	public function items()
	{
		return $this->hasMany(OrderItem::class);
	}
}
