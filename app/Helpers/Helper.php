<?php
namespace App\Helpers;

use Error;
use ErrorException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Helper{

    /**
     * Save user log
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public static function updateUserLog($input=array()){
        if(env('CUSTOM_LOG'))
        {
            try{
                $log = array(
                    'user_id'       => $input['user_id']??0,
                    'user_name'     => $input['user_name']??NULL,
                    'parent'        => $input['parent']??'',
                    'parent_id'     => $input['parent_id']??'',
                    'action'        => $input['action']??'',
                    'status'        => $input['response']?$input['response']['status']:[],
                    'ip_address'    => $input['ip_address']??'',
                    'browser'       => $input['browser']??''
                );

                DB::table('user_logs')->insert($log);
            }
            catch(ErrorException | Error $e){
                $status = false;
                $statusCode = 500;
                $error['code'] = '';
                $error['title'] = 'INTERNAL_ERROR';
                $error['description'] = 'Syntax Error.';
                $response = [];

                return response()->json(["status"=>$status,"statusCode"=>$statusCode,"error"=>$error,"response"=>$response],$statusCode);
            }
            catch(QueryException $e){
                $status = false;
                $statusCode = 502;
                $error['code'] = '';
                $error['title'] = 'DB_ERROR';
                $error['description'] = 'SQL Syntax Error.';
                $error['message'] = $e->getMessage();
                $response = [];

                return response()->json(["status"=>$status,"statusCode"=>$statusCode,"error"=>$error,"response"=>$response],$statusCode);
            }
        }
    }
}
