<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Warehouse
 * 
 * @property int $id
 * @property string $name
 * 
 * @property Collection|Order[] $orders
 * @property Collection|Stock[] $stocks
 *
 * @package App\Models
 */
class Warehouse extends Model
{
	protected $table = 'warehouses';
	public $timestamps = false;

	protected $fillable = [
		'name'
	];

	public function orders()
	{
		return $this->hasMany(Order::class);
	}

	public function stocks()
	{
		return $this->hasMany(Stock::class);
	}
}
