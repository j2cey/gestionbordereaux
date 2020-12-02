<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class BordereauremiseType
 * @package App\Models
 *
 * @property integer $id
 *
 * @property string $uuid
 * @property bool $is_default
 * @property string|null $tags
 * @property integer|null $status_id
 *
 * @property string $code
 * @property string $titre
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BordereauremiseType extends BaseModel
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    #region Spatie LogsActivity

    protected static $logAttributes = ['*'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Action sur [Type de Bordereau de Remise]: {$eventName}";
    }

    #endregion
}
