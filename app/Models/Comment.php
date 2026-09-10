<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = ['task_id','created_by','body'];

    public function uniqueIds() : array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function task() : BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
