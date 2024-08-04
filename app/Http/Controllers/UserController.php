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
}
