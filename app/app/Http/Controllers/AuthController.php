<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\RegisterMail;
use Illuminate\Support\Facades\Mail;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

use App\Models\User;
use App\Models\user_extras;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request){

        $params = $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|confirmed|min:8",
            "address" => "required",
            "zip" => "required",
            "city" => "required",
            "phone" => "required",
            "why" => "nullable",
            "socials" => "nullable",
            "skills" => "nullable",
            "company" => "nullable",
        ]);
        $password = $params["password"];

        $params["password"] = Hash::make($params["password"]);


        $user = User::create($params);

        $user->assignRole("inspirer");

        foreach ($params["socials"] as $social) {
            user_extras::create([
                "user_id" => $user->id,
                "type" => "social",
                "value" => $social["value"],
                "extra" => $social["extra"],
            ]);
        }

        foreach ($params["skills"] as $skill) {
            user_extras::create([
                "user_id" => $user->id,
                "type" => "skill",
                "value" => $skill,
            ]);
        }

        $token = Auth::attempt([
            "email" => $params["email"],
            "password" => $password,
        ]);

        Mail::to($user->email)->send(new RegisterMail($user));

        return $this->respondWithToken($token, $user);

    }

    public function login(Request $request){
        $credentials = $request->only(['email', 'password']);
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout(){
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }


        /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $user = null)
    {

        if($user == null){
            $user = auth()->user();
        }
        $user->roles = $user->getRoleNames();

        $skills = user_extras::where("user_id", $user->id)->where("type", "skill")->get();
        $socials = user_extras::where("user_id", $user->id)->where("type", "social")->get();

        $user->skills = $skills;
        $user->socials = $socials;



        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
