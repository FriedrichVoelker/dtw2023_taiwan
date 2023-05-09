<?php

namespace App\Helper;

use Illuminate\Http\Request;

class ApiResponse {

    public static function notImplemented() {
        return response()->json(['success' => false, 'message' => 'not implemented'], 501);
    }

    public static function notFound() {
        return response()->json(['success' => false, 'message' => 'not found'], 404);
    }

    public static function unauthorized() {
        return response()->json(['success' => false, 'message' => 'unauthorized'], 401);
    }

    public static function forbidden() {
        return response()->json(['success' => false, 'message' => 'forbidden'], 403);
    }

    public static function badRequest() {
        return response()->json(['success' => false, 'message' => 'bad request'], 400);
    }

    public static function internalServerError() {
        return response()->json(['success' => false, 'message' => 'internal server error'], 500);
    }

    public static function response($status = 200, $data = null) {
        return response()->json(['success' => true, 'data' => $data], $status);
    }

}
