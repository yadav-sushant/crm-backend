<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Error;
use ErrorException;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public $parent = '';
    public function __construct(){
        parent::__construct();
        $this->parent = 'users';
    }

    function login(Request $req){
        $this->action = 'login';
        $this->browser = $req->header('User-Agent');
        $this->ip_address = $req->ip();
        try{
            $validator = Validator::make($req->all(), [
                'user_name' => 'required|string|exists:users,contact_no',
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                $this->status = false;
                $this->statusCode = 403;
                $this->error['code'] = 'UC0001';
                $this->error['title'] = 'VALIDATION_ERROR';
                $this->error['description'] = 'These fields are required.';
                $this->error['fields'] = $validator->errors();
            }
            else{
                if(Auth::attempt(['contact_no' => $req->user_name, 'password' => $req->password])){
                    $user = Auth::user();
                    $token =  $user->createToken('CRM')->accessToken;

                    $this->response['data'] = [
                            'id'                => $user->id,
                            'name'              => $user->name,
                            'token'             => $token,
                            'access_list'       => []
                        ];
                    $this->user_id = $user->id;

                    $this->response['description'] = 'User logged in successfully.';
                }
                else{
                    $this->status = false;
                    $this->statusCode = 401;
                    $this->error['code'] = 'UC0002';
                    $this->error['title'] = 'UNAUTHORIZED';
                    $this->error['description'] = 'You have entered invalid credentials.';
                }
            }
        }
        catch(ErrorException | Error $e){
            $this->status = false;
            $this->statusCode = 500;
            $this->error['code'] = 'UC0003';
            $this->error['title'] = 'INTERNAL_ERROR';
            $this->error['description'] = 'Syntax Error.';
            $this->error['details'] = ['message' => $e->getMessage()];
        }
        catch(QueryException $e){
            $this->status = false;
            $this->statusCode = 502;
            $this->error['code'] = 'UC0004';
            $this->error['title'] = 'DB_ERROR';
            $this->error['description'] = 'SQL Syntax Error.';
            $this->error['details'] = ['message' => $e->getMessage()];
        }

        $response = ["status"=>$this->status,"statusCode"=>$this->statusCode,"error"=>$this->error,"response"=>$this->response];

        Helper::updateUserLog(["user_id"=>$this->user_id, "user_name"=>$req->user_name??"", "parent"=>"authentication", "parent_id"=> "", "action"=>$this->action, "response"=>$response, "browser"=>$this->browser, "ip_address"=>$this->ip_address]);

        // unset($response["error"]["details"]);
        return response()->json($response,$this->statusCode);

    }
}
