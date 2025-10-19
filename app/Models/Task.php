<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'description',
        'due_date'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function getDueDateAttribute($value)
    {
        return ($value) ? Carbon::parse($value)->format('d-m-Y') : null;
    }
}
