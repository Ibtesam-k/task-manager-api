<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['title','description'];



    // Tell Laravel to generate UUID for this column
    public function uniqueIds()
    {
        return ['uuid'];
    }

    // 
    // public const COL_UUID = 'uuid';
    
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function tasks() : HasMany 
    {
          return $this->hasMany(Task::class);
    }
}
