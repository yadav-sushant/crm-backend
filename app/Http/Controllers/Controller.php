<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public $user_id = 0;
    public $browser = '';
    public $ip_address = '';
    public $current_date = '';

    public $action = '';
    public $status = true;
    public $statusCode = 200;
    public $error = array();
    public $response = array();

    public function __construct(){
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $this->user_id = ($user)?$user->id:0;
            $this->current_date = date('Y-m-d H:i:s');
            
            return $next($request);
        });
    }


}
