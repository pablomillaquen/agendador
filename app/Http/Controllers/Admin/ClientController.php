<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Clients/Index', [
            'clients' => \App\Models\Client::latest()->paginate(10),
        ]);
    }
}
