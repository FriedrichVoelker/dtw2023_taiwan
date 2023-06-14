<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "location",
        "needed_inspirers",
        "description",
        "duration",
        "image",
        "start_date",
        "course_type_id",
    ];
}
