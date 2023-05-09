<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_courses extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "course_id",
    ];
}
