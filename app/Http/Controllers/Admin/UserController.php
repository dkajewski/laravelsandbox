<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Http\Resources\UserAdmin as UserResource;

class UserController extends Controller
{

    /**
     * Display a listing of the resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $users = User::orderBy('id')->paginate(20);
        return UserResource::collection($users);
    }
}