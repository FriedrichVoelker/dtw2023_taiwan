<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\course_types;
use App\Models\user_courses;

class CourseTypesController extends Controller
{
    public function dropdown(){
        $course_types = course_types::all();
        return $course_types;
    }

    public function index(Request $request){

        $limit = $request->input("limit");
        $offset = $request->input("start");


        $course_types = course_types::limit($limit)->offset($offset)->get();
        $total = course_types::count();


        return response()->json(["data" => $course_types, "found" => $total]);
    }

    public function destroy($id){


        $courses = Course::where("course_type_id", $id)->get();
        foreach($courses as $course){
            $course->delete();
        }

        $course_type = course_types::find($id);
        $course_type->delete();
        return response()->json(["success" => true]);
    }


    public function store(Request $request){
        $params = $request->validate([
            "name" => "required",
            "duration" => "numeric|nullable",
            "image" => "nullable",
        ]);

        $course_type = course_types::create($params);
        return response()->json(["success" => true, "course_type" => $course_type]);
    }

    public function update($id, Request $request){
        $params = $request->validate([
            "name" => "nullable",
            "duration" => "numeric|nullable",
            "image" => "nullable",
        ]);

        $course_type = course_types::find($id);
        $course_type->update($params);
        return response()->json(["success" => true, "course_type" => $course_type]);
    }

    public function show($id){
        $course_type = course_types::find($id);
        return response()->json(["success" => true, "course_type" => $course_type]);
    }


}
