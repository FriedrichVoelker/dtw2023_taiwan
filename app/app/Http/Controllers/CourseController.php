<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\course_types;
use App\Models\user_courses;

class CourseController extends Controller
{
    public function index() {

        $user = auth()->user();


        $courses = Course::all();
        // $courses = $courses->sortBy("start_date");
        $outcourses = [];
        foreach($courses as $course) {

            if($course->start_date < date("Y-m-d h:i:s")){
                continue;
            }



            $course->course_type = course_types::find($course->course_type_id);

            $user_with_course_amount = user_courses::where("course_id", $course->id)->count();
            $course->user_amount = $user_with_course_amount;
            $course->user_is_enrolled = user_courses::where("course_id", $course->id)->where("user_id", $user->id)->exists();
            if($user_with_course_amount >= $course->needed_inspirers){
                $course->has_all = true;
            }else{
                $course->has_all = false;
            }
            $outcourses[] = $course;
        }


        return response()->json(["courses" => $outcourses]);
    }


    public function adminIndex(Request $request){

        $limit = $request->input("limit");
        $offset = $request->input("start");


        $courses = Course::limit($limit)->offset($offset)->get();
        $size = Course::count();


        $outcourses = [];
        foreach($courses as $course) {
            $course->is_finished = $course->start_date < date("Y-m-d h:i:s");
            // if($course->start_date < date("Y-m-d h:i:s")){
            //     $course->is_finished = true;
            // }else{
            //     $course->is_finished = false;
            // }


            $course->course_type = course_types::find($course->course_type_id);


            if($course->name == null){
                $course->name = $course->course_type->name;
                $course->use_custom_name = false;
            }else{
                $course->use_custom_name = true;
            }

            if($course->duration == null){
                $course->duration = $course->course_type->duration;
                $course->use_custom_duration = false;
            }else{
                $course->use_custom_duration = true;
            }

            if($course->image == null){
                $course->image = $course->course_type->image;
                $course->use_custom_image = false;
            }else{
                $course->use_custom_image = true;
            }


            $user_with_course_amount = user_courses::where("course_id", $course->id)->count();
            $course->user_amount = $user_with_course_amount;
            if($user_with_course_amount >= $course->needed_inspirers){
                $course->has_all = true;
            }else{
                $course->has_all = false;
            }
            $outcourses[] = $course;
        }

        // sort by start date
        usort($outcourses, function($a, $b) {
            return $a->start_date <=> $b->start_date;
        });


        return response()->json(["data" => $outcourses, "found" => $size]);



    }



    public function store(Request $request){
        $params = $request->validate([
            "name" => "nullable",
            "description" => "required",
            "course_type_id" => "required|exists:course_types,id",
            "duration" => "nullable",
            "image" => "nullable",
            "start_date" => "required",
            "use_custom_image" => "nullable",
            "use_custom_name" => "nullable",
            "use_custom_duration" => "nullable",
        ]);


        if(!$params["use_custom_image"]){
            $params["image"] = null;
        }
        if(!$params["use_custom_name"]){
            $params["name"] = null;
        }
        if(!$params["use_custom_duration"]){
            $params["duration"] = null;
        }

        $course_type = course_types::findOrFail($params["course_type_id"]);
        // return  $course_type->id;
        // dd($course_type->id);
        // return response()->json(["message"=>"error"],500);
        $course = Course::create([
            "name" => $params["name"],
            "description" => $request->description,
            "course_type_id" => $course_type->id,
            "duration" => $params["duration"],
            "image" => $params["image"],
            "start_date" => $request->start_date,
        ]);

        $course->course_type_id = $course_type->id;
        $course->save();


        $course->type = $course_type;
        return $course;
    }

    public function show($id) {
        $course = Course::findOrFail($id);
        $course->type = course_types::find($course->course_type_id);
        return $course;
    }

    public function update(Request $request, $id) {
        $params = $request->validate([
            "name" => "nullable",
            "description" => "required",
            "course_type_id" => "required|exists:course_types,id",
            "duration" => "nullable",
            "image" => "nullable",
            "start_date" => "required",
            "use_custom_image" => "nullable",
            "use_custom_name" => "nullable",
            "use_custom_duration" => "nullable",
        ]);


        if($params["use_custom_image"] == false){
            $params["image"] = null;
        }
        if($params["use_custom_name"] == false){
            $params["name"] = null;
        }
        if($params["use_custom_duration"] == false){
            $params["duration"] = null;
        }

        $course = Course::find($id);
        $course->name = $params["name"];
        $course->description = $params["description"];
        $course->course_type_id = $params["course_type_id"];
        $course->duration = $params["duration"];
        $course->image = $params["image"];
        $course->start_date = $params["start_date"];
        $course->save();

        $course->type = course_types::find($course->type);
        return $course;
    }

    public function destroy($id) {
        $course = Course::find($id);
        $course->delete();
        return $course;
    }

    public function enroll(Request $request, $id) {
        $user = auth()->user();

        $course = Course::find($id);

        $user_course = user_courses::create([
            "user_id" => $user->id,
            "course_id" => $course->id,
        ]);

        return $user_course;
    }

    public function unenroll(Request $request, $id) {
        $user = auth()->user();

        $course = Course::find($id);

        $user_course = user_courses::where("user_id", $user->id)->where("course_id", $course->id)->first();
        $user_course->delete();

        return $user_course;
    }

    public function enrolled($id) {
        $course = Course::find($id);
        $enrolled = user_courses::where("course_id", $course->id)->get();
        foreach($enrolled as $user_course) {
            $user_course->user = $user_course->user;
        }
        return $enrolled;
    }

    public function my_courses() {
        $user = auth()->user();
        $enrolled = user_courses::where("user_id", $user->id)->get();
        $courses = [];
        foreach($enrolled as $enroll) {
            $course = Course::find($enroll->course_id);

            if($course->start_date < date("Y-m-d h:i:s")){
                $course->is_finished = true;
            }else {
                $course->is_finished = false;
            }



            $course->course_type = course_types::find($course->course_type_id);

            $course->name = ($course->name ? $course->name : $course->course_type->name);
            $course->duration = ($course->duration ? $course->duration : $course->course_type->duration);
            $course->image = ($course->image ? $course->image : $course->course_type->image);
            $course->enrolled_at = $enroll->created_at;
            $courses[] = $course;
        }

        // sort courses by start date
        usort($courses, function($a, $b) {
            return strtotime($a->start_date) - strtotime($b->start_date);
        });


        return response()->json(["data" => $courses]);
    }


}
