<?php namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('home', ['title' => 'Inicio']);
    }

    public function contacto()
    {
        return view('contacto', ['title' => 'Contacto']);
    }
}
