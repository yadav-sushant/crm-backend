<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'contact_no',
        'email',
        'password',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password'
    ];

    public static function getRecords($input = []){
        $searchValues = $input['searchValues']??'';
        $filterVal =  $input['filter']??'';
        $columns = $input['columns']??[];
        $order = $input['order']??[];
        $offset = $input['offset']??0;
        $limit = $input['limit']??0;

        $orderBy = ['name' => 'asc']; $sortBy = [];

        $data = User::select('id', 'name', 'contact_no', 'email', 'status')->where('status', '!=', 0);

        if($searchValues){
            $data = $data->where(function($query) use ($searchValues){
                $query->where('id', 'LIKE',"%{$searchValues}%")
                ->orWhere('name', 'LIKE',"%{$searchValues}%")
                ->orWhere('contact_no', 'LIKE',"%{$searchValues}%")
                ->orWhere('email', 'LIKE',"%{$searchValues}%");
            });
        }
       

        if($offset) $data = $data->skip($offset);
        if($limit) $data = $data->take($limit);

        if(!empty($order)){
            $orderBy = [];
            for($i=0; $i<count($order); $i++){
                $col = $columns[$order[$i]['column']]['data'];
                $strArr = explode('.', $col);
                if(count($strArr)==1) $orderBy[$col] = $order[$i]['dir'];
                else if(count($strArr)>1) $sortBy[$col] = $order[$i]['dir'];
            }
        }

        foreach($orderBy as $col => $dir){
            $data = $data->orderBy($col, $dir);
        }

        $data = $data->get();

        foreach($sortBy as $col => $dir){
            if($dir=='desc') $data = $data->sortByDesc($col)->values();
            else $data = $data->sortBy($col)->values();
        }

        $data = $data->toArray();

        return $data;
    }

    public static function getRecordsFiltered($input = []){
        if($input['searchValues']) 
            return count(User::getRecords($input));
        else 
            return User::getRecordsTotal($input);
    }

    public static function getRecordsTotal($input = []){
        $offset = $input['offset']??0;
        $limit = $input['limit']??0;

        $data = User::where('status', '!=', 0);
        
        if($offset) $data = $data->skip($offset);
        if($limit) $data = $data->take($limit);

        $data = $data->get()->count();

        return $data;
    }


}
