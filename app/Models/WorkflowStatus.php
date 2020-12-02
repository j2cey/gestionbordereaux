<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class WorkflowStatus
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
 * @property string $code
 *
 * @property integer|null $workflow_action_type_id
 * @property integer|null $workflow_step_id
 * @property integer|null $workflow_object_field_id
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class WorkflowStatus extends BaseModel implements Auditable
{
    use HasFactory, LogsActivity, \OwenIt\Auditing\Auditable;
    protected $guarded = [];

    #region Spatie LogsActivity

    protected static $logAttributes = ['*'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Action sur [Statut de Workflow]: {$eventName}";
    }

    #endregion

    #region Scopes

    public function scopeCoded($query, $code) {
        return $query
            ->where('code', $code)
            ;
    }

    #endregion
}
