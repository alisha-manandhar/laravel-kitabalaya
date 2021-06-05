<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Storage;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $user['token'] = $user->createToken('MyApp')-> accessToken; 
            
            return response(['data' => $user,"message" => "Logged in"],200);
        } 

            return response(['error'=>'Incorrect Password or Email'],422);
        
    }


     
    public function register(Request $request)
    {
        $data = $request->all();


        $validator = Validator::make($data, [
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'contact' => 'required|size:10|unique:users',
            'email' => 'required|unique:users|email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ]);
        
        if($validator->fails()){
            return response(['error' => $validator->errors()],422);     
        }
        
        $data['password'] = bcrypt($request['password']);

        $user = User::create($data);
 
        return response(['data' => 'User Created',"message" => "New Account Created"],201);
   
       
    }

    public function logout (Request $request) {
        
        $token = $request->user()->token()->revoke();
        return response(['message'=> 'Logged out successfully'],200);
    }

    public function show()
    {
        if (Auth::check())
        {
            return response(['data'=>Auth::user()],200);
        }
        
    }

    public function updateContact(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'contact' => 'required|size:10|unique:users',
        ]);
        
        if($validator->fails()){
            return response(['error' => $validator->errors()],422);     
        }
        
        $user = Auth::user();
        $user->update($data);
       
        return response(['data'=> $user, 'message' => 'Updated Contact'], 201);
    }

    public function updateEmail(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'email' => 'required|email|unique:users',
        ]);
        
        if($validator->fails()){
            return response(['error' => $validator->errors()],422);     
        }
        
        $user = Auth::user();
        $user->update($data);
       
        return response(['data'=> $user, 'message' => 'Email updated'], 201);
    }
   
    public function changePassword(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'oldPassword' => 'required',
            'password' => 'required|min:8',
            'rePassword' => 'required|same:password',
        ]);
        
        if($validator->fails()){
            return response(['error' => $validator->errors()],422);     
        }
        $user = Auth::user();
        if (Hash::check($data['oldPassword'], $user['password']))
        {   
            $user->update(['password'=> bcrypt($data['password'])]);
        return response([ 'message' => 'Password Updated'], 201);
    }
    return response(['error' => ["oldPassword"=>'Invalid Old Password']],422);
        
        
    }

    public function updateProfilePicture(Request $request)
    {

        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'avatar' => 'required|file|image|mimes:jpeg,png,gif,webp|max:9048'
        ]);
        if($validator->fails()){
            return response(['error' => $validator->errors()],422);     
        }
        
        $name = $request->file('avatar')->getClientOriginalName();
        $name = date('Ymd_') .time(). $name;
        
        if ($user->avatar != "false")
        {
            $oldFilename = $user->avatar;
            Storage::delete('public/'.$oldFilename);
        }
        $photo = $request->file('avatar')->storeAs('public',$name); 
        $user->update(['avatar'=> $name]);
        

        return response(['data'=> $name, 'message' => ' Profile Updated '], 201);
       
    }

    
}
   
   
