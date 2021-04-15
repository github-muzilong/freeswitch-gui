<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class IndexController extends Controller
{
    //后台布局
    public function layout()
    {
        return View::make('backend.layout');
    }

    /**
     * 后台主页
     */
    public function index()
    {
        return View::make('backend.index.index');
    }



}
