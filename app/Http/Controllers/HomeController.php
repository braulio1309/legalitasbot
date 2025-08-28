<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Pasar CSS y JS como variables para incluirlos en el layout
        $css = file_get_contents(public_path('css/app.css'));
        $js = file_get_contents(public_path('js/app.js'));
        
        return view('home', compact('css', 'js'));
    }
}