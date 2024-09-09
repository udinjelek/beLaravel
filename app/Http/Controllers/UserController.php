<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class UserController extends Controller
{
    public function getAllUsers()
    {
        $query = Db::select("select * from users");
        return response()->json($query);
    }

    public function helloworld()
    {
        return "hello world";
    }

    public function hi()
    {
        $dataOut = [    'id' => 01,
                        'name' => 'monster',];
        return response()->json($dataOut);
    }
}
