<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(){

        return view('index');
    
    }
    public function create(){
        return view('todos.create');
    }
    public function details(){
    
        return view('details');
    
    }
    
    public function edit(){
    
        return view('edit');
    
    }
    public function update(){
    
        //we will write codes for updating a todo here
    
    }
    public function delete(){
    
        //we will write codes for deleting a todo here
    
    }
}
