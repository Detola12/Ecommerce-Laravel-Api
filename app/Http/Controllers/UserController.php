<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use HttpResponse;
    public function register(CreateUserRequest $request){
        $newUser = DB::transaction(function () use ($request){
            return User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => 1
            ]);
        });

        Auth::login($newUser);
        $token = $newUser->createToken('Token - ')->plainTextToken;
        return $this->dataSuccess($token, "User registered");
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'email|required',
            'password' => 'string|required'
        ]);

        $user = User::where('email', $request->email)->first();
        if(Auth::attempt(['email' => $request->email,'password' => $request->password])){
            $token = $user->createToken('Token - ' . $user->name . now())->plainTextToken;
        }
        else{
            return $this->error("Invalid Credentials", 400);
        }
        return $this->dataSuccess($token, "Logged In");
    }

    public function logout(Request $request){

        $request->user()->currentAccessToken()->delete();

        return $this->success("Logged Out");
    }
}
