<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $fillable = ['name', 'description', 'order', 'required_role', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
