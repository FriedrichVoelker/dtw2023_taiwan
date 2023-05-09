<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Models\User;

class UserController extends Controller
{
    //

    function index() {
        return ApiResponse::response(User::all());
    }



}
