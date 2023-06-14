<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\user_extras;
use App\Models\user_courses;
use App\Models\Course;
use App\Models\course_types;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
    //

    public function index(Request $request) {

        $limit = $request->input("limit");
        $offset = $request->input("start");

        $users = User::limit($limit)->offset($offset)->get();
        $total = User::count();

        foreach($users as $user) {
            $user->roles = $user->getRoleNames();
            $user->skills = user_extras::where("user_id", $user->id)->where("type", "skill")->get();
            $user->socials = user_extras::where("user_id", $user->id)->where("type", "social")->get();
        }
        return response()->json(["data" => $users, "found" => $total]);

    }

    public function store(Request $request){
        $params = $request->validate([
            "name" => "required",
            "email" => "required|unique:users|email",
            "password" => "required|confirmed",
            "address" => "required",
            "zip" => "required",
            "city" => "required",
            "phone" => "required",
            "company" => "nullable",
            "why" => "nullable",
            "socials" => "nullable",
            "skills" => "nullable",
            "role" => "nullable"
        ]);

        if(!$request->socials) $params["socials"] = null;
        if(!$request->skills) $params["skills"] = null;

        $user = User::create([
            "name" => $params["name"],
            "email" => $params["email"],
            "password" => Hash::make($params["password"]),
            "address" => $params["address"],
            "zip" => $params["zip"],
            "city" => $params["city"],
            "phone" => $params["phone"],
            "why" => $params["why"] ?? null,
        ]);



        if($params["socials"] != null){
            foreach ($params["socials"] as $social) {
                user_extras::create([
                    "user_id" => $user->id,
                    "type" => "social",
                    "value" => $social["value"],
                    "extra" => $social["extra"],
                ]);
            }
        }
        if($params["skills"] != null){
            foreach ($params["skills"] as $skill) {
                user_extras::create([
                    "user_id" => $user->id,
                    "type" => "skill",
                    "value" => $skill["value"],
                    "extra" => $skill["extra"],
                ]);
            }
        }

        if($params["role"] != null){
            $user->assignRole($params["role"]);
        }


        return response()->json($user);

    }

    public function show($id){
        $user = User::findOrFail($id);
        $user->roles = $user->getRoleNames();
        $user->socials = user_extras::where("user_id", $user->id)->where("type", "social")->get();
        $user->skills = user_extras::where("user_id", $user->id)->where("type", "skill")->get();
        return response()->json($user);
    }


    public function destroy($id){
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json($user);
    }

    public function update($id, Request $request){
        $params = $request->validate([
            "name" => "nullable",
            "email" => "nullable|email",
            "address" => "nullable",
            "zip" => "nullable",
            "city" => "nullable",
            "phone" => "nullable",
            "why" => "nullable",
            "socials" => "nullable",
            "skills" => "nullable",
            "role" => "nullable",
            "company" => "nullable",
        ]);

        $user = User::findOrFail($id);
        $user->update($params);

        user_extras::where("user_id", $user->id)->delete();

        // foreach ($params["socials"] as $social) {
        //     user_extras::create([
        //         "user_id" => $user->id,
        //         "type" => "social",
        //         "value" => $social["value"],
        //         "extra" => $social["extra"],
        //     ]);
        // }

        // foreach ($params["skills"] as $skill) {
        //     user_extras::create([
        //         "user_id" => $user->id,
        //         "type" => "skill",
        //         "value" => $skill["value"],
        //         "extra" => $skill["extra"],
        //     ]);
        // }
        if($params["role"] != null){
            // remove all roles from user
            $user->roles()->detach();
            // assign new role
            $user->assignRole($params["role"]);
        }

        return response()->json($user);
    }




    public function me(Request $request){
        if(!$user = auth()->user()){
            return response()->json(['error' => 'notLoggedIn'], 404);
        }
        $skills = user_extras::where("user_id", $user->id)->where("type", "skill")->get();
        $socials = user_extras::where("user_id", $user->id)->where("type", "social")->get();

        $user->skills = $skills;
        $user->socials = $socials;
        return response()->json($user);
    }




    public function profile(Request $request, $id){
        $user = User::findOrFail($id);
        $skills = user_extras::where("user_id", $user->id)->where("type", "skill")->get();
        $socials = user_extras::where("user_id", $user->id)->where("type", "social")->get();

        $user_courses = user_courses::where("user_id", $user->id)->get();
        $finished_courses = [];
        $out_skills = [];
        $open_courses = [];
        foreach($user_courses as $course){
            $course = Course::findOrFail($course->course_id);
            if($course->start_date < date("Y-m-d h:i:s")){
                $finished_courses[] = $course;
            }else{
                $open_courses[] = $course;
            }
        }

        foreach($skills as $skill){
            $out_skills[] = $skill->value;
        }

        // $avatar = 'https://gravatar.com/' . md5(strtolower(trim($user->email))) . '.jpg?s=200&d=mm';
        // // check if gravatar exists
        // $file_headers = @get_headers($avatar);
        // if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
        //     $avatar = null;
        // }

        return response()->json([
            "id" => $user->id,
            "name" => $user->name,
            "registered_since" => $user->created_at,
            "skills" => $out_skills,
            "socials" => $socials,
            "finished_courses" => sizeof($finished_courses),
            "open_courses" => sizeof($open_courses),
            "all_courses" => sizeof($user_courses),
            "level" => User::getUserLevel(sizeof($finished_courses)),
        ]);



    }

    public function getDashboardData(Request $request){
        if(!$user = auth()->user()){
            return response()->json(['error' => 'notLoggedIn'], 404);
        }

        $user_courses = user_courses::where("user_id", $user->id)->get();
        $type_distribution = [];
        $finished_courses = [];
        $open_courses = [];
        $upcoming_courses = [];
        foreach($user_courses as $ucourse){
            $course = Course::findOrFail($ucourse->course_id);
            $course->type = course_types::findOrFail($course->course_type_id);
            $ucourse->course = $course;

            $course->course_type = course_types::find($course->course_type_id);
            $course->name = ($course->name ? $course->name : $course->course_type->name);
            $course->duration = ($course->duration ? $course->duration : $course->course_type->duration);
            $course->image = ($course->image ? $course->image : $course->course_type->image);
            $course->user_has_course = true;

            if($course->start_date < date("Y-m-d h:i:s")){
                $finished_courses[] = $ucourse;
                if(!isset($type_distribution[$ucourse->course->type->name])){
                    $type_distribution[$ucourse->course->type->name] = 0;
                }
                $type_distribution[$ucourse->course->type->name]++;
                $course->is_finished = true;
            }else{
                $open_courses[] = $course;
                $course->is_finished = false;
            }

        }



        // get six closest courses where date is in the future


        $six_closest_courses = Course::where("start_date", ">", date("Y-m-d h:i:s"))->orderBy("start_date", "asc")->limit(6)->get();

        foreach($six_closest_courses as $course){
            $course->course_type = course_types::find($course->course_type_id);
            $course->name = ($course->name ? $course->name : $course->course_type->name);
            $course->duration = ($course->duration ? $course->duration : $course->course_type->duration);
            $course->image = ($course->image ? $course->image : $course->course_type->image);

            $users_have_course = sizeof(user_courses::where("course_id", $course->id)->get());
            $course->user_amount = $users_have_course;

            $i_enrolled = user_courses::where("course_id", $course->id)->where("user_id", $user->id)->first();
            $course->user_has_course = $i_enrolled ? true : false;


            $upcoming_courses[] = $course;
        }

        return response()->json([
            "user_courses" => $user_courses,
            "type_distribution" => $type_distribution,
            "finished_courses" => sizeof($finished_courses),
            "upcoming_courses" => $upcoming_courses,
            "open_courses" => $open_courses,
            "all_courses" => sizeof($user_courses),
            "level" => User::getUserLevel(sizeof($finished_courses)),
        ]);
    }


    public function update_self(Request $request){
        $user = auth()->user();

        $params = $request->validate([
            "name" => "nullable",
            "email" => "nullable|email|unique:users,email,".$user->id,
            "password" => "nullable|confirmed|min:8",
            "address" => "nullable",
            "zip" => "nullable",
            "city" => "nullable",
            "phone" => "nullable",
            "why" => "nullable",
            "socials" => "nullable",
            "skills" => "nullable",
            "custom_skill" => "nullable",
            "company" => "nullable",
        ]);

        $user->update($params);

        $user->roles = $user->getRoleNames();


        if(isset($params["skills"])){
            user_extras::where("user_id", $user->id)->where("type", "skill")->delete();
            foreach($params["skills"] as $skill){
                user_extras::create([
                    "user_id" => $user->id,
                    "type" => "skill",
                    "value" => $skill,
                ]);
            }
        }
        if(isset($params["socials"])){
            user_extras::where("user_id", $user->id)->where("type", "social")->delete();
            foreach($params["socials"] as $social){
                user_extras::create([
                    "user_id" => $user->id,
                    "type" => "social",
                    "value" => $social,
                ]);
            }
        }
        if(isset($params["custom_skill"])){
            $already_exist = user_extras::where("user_id", $user->id)->where("type", "skill")->where("value", $params["custom_skill"])->first();
            if(!$already_exist){
                user_extras::create([
                    "user_id" => $user->id,
                    "type" => "skill",
                    "value" => $params["custom_skill"],
                ]);
            }
        }

        $user->skills = user_extras::where("user_id", $user->id)->where("type", "skill")->get();
        $user->socials = user_extras::where("user_id", $user->id)->where("type", "social")->get();


        return response()->json(["user" => $user, "message" => "success"]);


    }


    public function generateCertificate(){
        $user = auth()->user();

        $user_courses = user_courses::where("user_id", $user->id)->get();

        $finished_courses = [];

        foreach($user_courses as $ucourse){
            $ucourse->course = Course::findOrFail($ucourse->course_id);
            if($ucourse->course->start_date < date("Y-m-d h:i:s")){
                $finished_courses[] = $ucourse;
            }
        }


        $pdf = Pdf::loadView('pdf.certificate', [
            "user" => $user,
            "finished_courses" => sizeof($finished_courses),
            "date" => date("d.m.Y"),
            "level" => User::getUserLevel(sizeof($finished_courses)),
        ]);
        return $pdf->download('certificate.pdf');
    }


    public function deleteSelf(){
        if(!$user = auth()->user()){
            return response()->json(["message" => "user not found"], 404);
        }

        foreach(user_courses::where("user_id", $user->id)->get() as $ucourse){
            $ucourse->delete();
        }
        foreach(user_extras::where("user_id", $user->id)->get() as $uextra){
            $uextra->delete();
        }
        // delete user account
        // $auser = User::findOrFail($user->id);
        // return $auser;
        $user->delete();


        return response()->json(["message" => "success"], 200);
    }

}
