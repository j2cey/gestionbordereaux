<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Product
 * @package App\Models
 *
 * @property integer $id
 *
 * @property string $uuid
 * @property bool $is_default
 * @property string|null $tags
 * @property integer|null $status_id
 *
 * @property string $name
 * @property float $price
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Product extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'price',
    ];

    /**
     * The attributes that should be cast to native types
     *
     * @return array
     */
    protected $casts = [
        'price' => 'float',
    ];

    #region Spatie LogsActivity

    protected static $logAttributes = ['*'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Action sur [Product]: {$eventName}";
    }

    #endregion
}
