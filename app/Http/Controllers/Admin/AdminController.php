<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function authorizeAdmin()
    {
        return response()->json([
            'success' => true
        ]);
    }


    public function getAdminMenuCategories()
    {
        // todo: maybe one day it'll be good to move categories to database, but for now let's make it static XD
        $menu = [
            [
                'href' => '/admin',
                'title' => 'Main Site',
                'icon' => [
                    'element' => 'font-awesome-icon',
                    'attributes' => [
                        'icon' => 'jedi'
                    ]
                ]
            ], [
                'href' => '/admin/users',
                'title' => 'Users',
                'icon' => [
                    'element' => 'font-awesome-icon',
                    'attributes' => [
                        'icon' => 'user-secret'
                    ]
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'menu' => $menu
        ]);
    }

}
