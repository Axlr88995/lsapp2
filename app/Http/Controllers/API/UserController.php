<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;
class UserController extends Controller 
{
public $successStatus = 200;

    public function login(Request $request){
        $param = NULL;
        $validator = Validator::make($request->all(), [ 
            'emailOrTel' => 'required',
            'password' => 'required' 
        ]);

        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        if(strlen(request('emailOrTel')) == 10 && is_numeric(request('emailOrTel'))){
            $param = 'tel';
        }else if(filter_var(request('emailOrTel'), FILTER_VALIDATE_EMAIL)){
            $param = 'email';
        }
        else{
            return response()->json(['error'=>'InvalidMobile Number OR password !!!'], 401);  
        } 

        if(Auth::attempt([$param => request('emailOrTel'), 'password' => request('password')])){
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            return response()->json(['success' => $success], $this-> successStatus); 
        }
        else
        {
            return response()->json(['error'=>'Unauthorized'], 401); 
        } 
    }

    public function register(Request $request) 
    { 
        $validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email|unique:users',
            'tel' => 'required|digits:10|unique:users',
            'password' => 'required|string|min:6', 
            'c_password' => 'required|same:password', 
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        $input = $request->all(); 
        $input['password'] = bcrypt($input['password']); 
        $user = User::create($input); 
        $success['token'] =  $user->createToken('MyApp')-> accessToken; 
        $success['name'] =  $user->name;
        return response()->json(['success'=>$success], $this-> successStatus); 
    }

    public function details() 
    { 
        $user = Auth::user(); 
        return response()->json(['success' => $user], $this-> successStatus); 
    }
    
    public function delete(Request $request) 
    { 
        $validator = Validator::make($request->all(), [ 
            'email' => 'required|email' 
        ]);
        $input = $request->all(); 
        $email = $input['email'];    
        $user = User::whereEmail($email)->first();
        if($user==NULL){
            return response()->json(['error'=>'User Not Found!'], 404); 
        }
        $user->delete();
        return response()->json(['Success'=>'User deleted Successfully'], 200); 
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [ 
            'name' => 'string|max:255',
            'email' => 'required|email',
            'tel' => 'digits:10|unique:users',
            'password' => 'string|min:6',
            'c_password' => 'same:password', 
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        $input = $request->all(); 
        $email = $input['email'];
        $user = User::whereEmail($email)->firstOrFail(); 
        if(isset($input['name'])){
            $user->name = $input['name'];
        }
        if(isset($input['tel'])){
            $user->tel = $input['tel'];
        }
        if(isset($input['password'])){
            $user->password = bcrypt($input['password']);;
        }
        $user->save();
        return response()->json(['Success'=>'User updated Successfully'], 200);          
    }
}