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
use Illuminate\Support\Facades\DB;

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
                'user_name' => 'required|string|exists:users,contact_no,status,1',
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

        unset($response["error"]["details"]);
        return response()->json($response,$this->statusCode);
    }
    function logout(Request $req){
        $this->action = 'logout';
        $this->browser = $req->header('User-Agent');
        $this->ip_address = $req->ip();
        try{
            $user = Auth::user();
            $this->user_id = $user_id??0;
            $token = $user->token();
            $token->revoke();
            $this->response['description'] = 'User logged out successfully.';
        }
        catch(ErrorException | Error $e){
            $this->status = false;
            $this->statusCode = 500;
            $this->error['code'] = 'UC0005';
            $this->error['title'] = 'INTERNAL_ERROR';
            $this->error['description'] = 'Syntax Error.';
            $this->error['details'] = ['message' => $e->getMessage()];
        }
        $response = ["status"=>$this->status,"statusCode"=>$this->statusCode,"error"=>$this->error,"response"=>$this->response];

        Helper::updateUserLog(["user_id"=>$this->user_id, "parent"=>"authentication", "parent_id"=> "", "action"=>$this->action, "browser"=>$this->browser, "ip_address"=>$this->ip_address, "request"=>$req->all(), "response"=>$response]);

        unset($response["error"]["details"]);

        return response()->json($response,$this->statusCode);
    }

    function getUserTable(Request $req){
        try{
            $validator = Validator::make($req->all(), [
                'start'    => 'nullable|numeric',
                'length'     => 'required|numeric'
            ]);
            if ($validator->fails()) {
                $this->status = false;
                $this->statusCode = 403;
                $this->error['code'] = 'UC0006';
                $this->error['title'] = 'VALIDATION_ERROR';
                $this->error['description'] = 'These fields are required.';
                $this->error['fields'] = $validator->errors();
            }
            else{

                $input = array(
                    'searchValues'  => ($req->search)?$req->search['value']:'',
                    'columns'       => ($req->columns)?$req->columns:[],
                    'order'         => ($req->order)?$req->order:[],
                    'offset'        => $req->start??0,
                    'limit'         => $req->length??0
                );
                $data = User::getRecords($input);

                $input = array(
                    'searchValues'  => ($req->search)?$req->search['value']:'',
                    'offset'        => $req->start??0,
                    'limit'         => $req->length??0,
                );
                $recordsFiltered = User::getRecordsFiltered($input);

                $recordsTotal = User::getRecordsTotal();

                $this->response['recordsTotal'] = $recordsTotal;
                $this->response['recordsFiltered'] = $recordsFiltered;
                $this->response['data'] = $data;
            }
        }
        catch(ErrorException | Error $e){
            $this->status = false;
            $this->statusCode = 500;
            $this->error['code'] = 'UC0007';
            $this->error['title'] = 'INTERNAL_ERROR';
            $this->error['description'] = 'Syntax Error.';
            $this->error['details'] = ['message' => $e->getMessage()];
        }
        catch(RelationNotFoundException | QueryException $e){
            $this->status = false;
            $this->statusCode = 502;
            $this->error['code'] = 'UC0008';
            $this->error['title'] = 'DB_ERROR';
            $this->error['description'] = 'SQL Syntax Error.';
            $this->error['details'] = ['message' => $e->getMessage()];
        }
        $response = ["status"=>$this->status,"statusCode"=>$this->statusCode,"error"=>$this->error,"response"=>$this->response];

        unset($response["error"]["details"]);
        return response()->json($response,$this->statusCode);
    }

    function getUserList(){
        try{
            $this->response = User::select('id','name')
                                ->where('status',1)
                                ->orderBy('name')
                                ->get();
        }
        catch(ErrorException | Error $e){
            $this->status = false;
            $this->statusCode = 500;
            $this->error['code'] = 'UC0009';
            $this->error['title'] = 'INTERNAL_ERROR';
            $this->error['description'] = 'Syntax Error.';
            $this->error['details'] = ['message' => $e->getMessage()];
        }
        catch(QueryException $e){
            $this->status = false;
            $this->statusCode = 502;
            $this->error['code'] = 'UC0010';
            $this->error['title'] = 'DB_ERROR';
            $this->error['description'] = 'SQL Syntax Error.';
            $this->error['details'] = ['message' => $e->getMessage()];
        }
        $response = ["status"=>$this->status,"statusCode"=>$this->statusCode,"error"=>$this->error,"response"=>$this->response];

        unset($response["error"]["details"]);
        return response()->json($response,$this->statusCode);
    }

    function addUpdateUser(Request $req, $action=''){
        if(!in_array($action,array('add','update')))
            throw new NotFoundHttpException();
        else{
            $id=$req->id??0;
            $this->action = $action;
            $this->browser = $req->header('User-Agent');
            $this->ip_address = $req->ip();
            $data = $req->all();
            try{
                $validator = Validator::make($req->all(), [
                    'id'                => ($this->action=='add')?'in:0':'required|not_in:0|numeric|exists:users,id,status,!0',
                    'name'              => 'required|string',
                    'contact_no'        => 'required|min:10|max:10|unique:users,contact_no,'.$id,
                    'email'             => 'required|string|email|unique:users,email,'.$id,
                ]);
                if ($validator->fails()) {
                    $this->status = false;
                    $this->statusCode = 403;
                    $this->error['code'] = 'UC0011';
                    $this->error['title'] = 'VALIDATION_ERROR';
                    $this->error['description'] = 'These fields are required.';
                    $this->error['fields'] = $validator->errors();
                }
                else{
                    DB::beginTransaction();

                    unset($data['id']);
                    if($id) $data['updated_by'] = $this->user_id;
                    else $data['created_by'] = $this->user_id;
                    
                    $output = User::updateOrCreate(['id'=>$id],$data);
                    $id = $output->id;

                    $this->response['description'] = 'User details '.rtrim($this->action,'e').'ed successfully.';
                    $this->response['data'] = ["id"=>$id];

                    DB::commit();
                }
            }
            catch(ErrorException | Error $e){
                DB::rollBack();
                $this->status = false;
                $this->statusCode = 500;
                $this->error['code'] = 'UC0012';
                $this->error['title'] = 'INTERNAL_ERROR';
                $this->error['description'] = 'Syntax Error.';
                $this->error['details'] = ['message' => $e->getMessage()];
            }
            catch(QueryException $e){
                DB::rollBack();
                $this->status = false;
                $this->statusCode = 502;
                $this->error['code'] = 'UC0013';
                $this->error['title'] = 'DB_ERROR';
                $this->error['description'] = 'SQL Syntax Error.';
                $this->error['details'] = ['message' => $e->getMessage()];
            }

            $response = ["status"=>$this->status,"statusCode"=>$this->statusCode,"error"=>$this->error,"response"=>$this->response];

            Helper::updateUserLog(["user_id"=>$this->user_id, "parent"=>$this->parent, "parent_id"=> $id, "action"=>$this->action, "browser"=>$this->browser, "ip_address"=>$this->ip_address, "request"=>$data, "response"=>$response]);

            unset($response["error"]["details"]);
            return response()->json($response,$this->statusCode);
        }
    }

    function deleteUser(Request $req){
        $this->action = 'delete';
        $this->browser = $req->header('User-Agent');
        $this->ip_address = $req->ip();

        $id = $req->id??0;
        try{
            $validator = Validator::make($req->all(), [
                'id'          => 'required|not_in:0|numeric|exists:users,id,status,!0',
                'remark'      => 'required|string'
            ]);
            if ($validator->fails()) {
                $this->status = false;
                $this->statusCode = 403;
                $this->error['code'] = 'UC0014';
                $this->error['title'] = 'VALIDATION_ERROR';
                $this->error['description'] = 'These fields are required.';
                $this->error['fields'] = $validator->errors();
            }
            else{
                DB::beginTransaction();

                $data['status'] = 0;
                $data['updated_by'] = $this->user_id;
                User::where('status',1)->where('id',$id)->update($data);

                $this->response['description'] = 'User details deleted successfully.';
                $this->response['data'] = ["id"=>$id];

                DB::commit();
            }
        }
        catch(ErrorException | Error $e){
            DB::rollBack();
            $this->status = false;
            $this->statusCode = 500;
            $this->error['code'] = 'UC0015';
            $this->error['title'] = 'INTERNAL_ERROR';
            $this->error['description'] = 'Syntax Error.';
            $this->error['details'] = ['message' => $e->getMessage()];
        }
        catch(QueryException $e){
            DB::rollBack();
            $this->status = false;
            $this->statusCode = 502;
            $this->error['code'] = 'UC0016';
            $this->error['title'] = 'DB_ERROR';
            $this->error['description'] = 'SQL Syntax Error.';
            $this->error['details'] = ['message' => $e->getMessage()];
        }

        $response = ["status"=>$this->status,"statusCode"=>$this->statusCode,"error"=>$this->error,"response"=>$this->response];

        Helper::updateUserLog(["user_id"=>$this->user_id, "parent"=>$this->parent, "parent_id"=> $id, "action"=>$this->action, "browser"=>$this->browser, "ip_address"=>$this->ip_address, "request"=>$req->all(), "response"=>$response]);

        unset($response["error"]["details"]);
        return response()->json($response,$this->statusCode);
    }

    function getUser(Request $req){
        $id = $req->id??0;
        return $id;
        // try{
        //     $validator = Validator::make($req->all(), [
        //         'id'    => 'required|not_in:0|numeric|exists:users,id,status,!0'
        //     ]);
        //     if ($validator->fails()) {
        //         $this->status = false;
        //         $this->statusCode = 403;
        //         $this->error['code'] = 'UC0017';
        //         $this->error['title'] = 'VALIDATION_ERROR';
        //         $this->error['description'] = 'These fields are required.';
        //         $this->error['fields'] = $validator->errors();
        //     }
        //     else{
        //         $data = User::where('status', '!=', 0)->where('id',$id)->get()->first();

        //         $this->response['data'] = $data;
        //     }
        // }
        // catch(ErrorException | Error $e){
        //     $this->status = false;
        //     $this->statusCode = 500;
        //     $this->error['code'] = 'UC0018';
        //     $this->error['title'] = 'INTERNAL_ERROR';
        //     $this->error['description'] = 'Syntax Error.';
        //     $this->error['details'] = ['message' => $e->getMessage()];
        // }
        // catch(QueryException $e){
        //     $this->status = false;
        //     $this->statusCode = 502;
        //     $this->error['code'] = 'UC0019';
        //     $this->error['title'] = 'DB_ERROR';
        //     $this->error['description'] = 'SQL Syntax Error.';
        //     $this->error['details'] = ['message' => $e->getMessage()];
        // }

        // $response = ["status"=>$this->status,"statusCode"=>$this->statusCode,"error"=>$this->error,"response"=>$this->response];

        // unset($response["error"]["details"]);
        // return response()->json($response,$this->statusCode);
    }

}
