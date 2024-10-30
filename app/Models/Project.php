<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'status',
        'progress'
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'projects_id')->orderBy('bobot', 'DESC')->orderBy('id', 'ASC');
    }
}
