<?php

namespace App\Models;

use PHPUnit\Util\Json;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class WorkflowExecAction
 * @package App\Models
 *
 * @property integer $id
 *
 * @property string $uuid
 * @property bool $is_default
 * @property string|null $tags
 * @property integer|null $status_id
 *
 * @property integer|null $workflow_exec_id
 * @property integer|null $workflow_action_id
 * @property string|null $model_type
 * @property integer|null $model_id
 *
 * @property string|null $motif_rejet
 * @property Json $report
 * @property integer|null $workflow_status_id
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class WorkflowExecAction extends BaseModel
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    #region Spatie LogsActivity

    protected static $logAttributes = ['*'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Action sur [Execution Action de Workflow]: {$eventName}";
    }

    #endregion

    #region Eloquent Relationships

    public function exec() {
        return $this->belongsTo(WorkflowExec::class, 'workflow_exec_id');
    }

    public function action() {
        return $this->belongsTo(WorkflowAction::class, 'workflow_action_id');
    }

    public function execstatus() {
        return $this->belongsTo(WorkflowStatus::class, 'workflow_status_id');
    }

    #endregion
}
