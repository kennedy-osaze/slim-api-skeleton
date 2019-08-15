<?php

namespace App\Controllers;

use App\Models\User;
use Slim\Http\Request;
use Slim\Http\Response;

class HomeController extends Controller
{
    public function index(Request $request, Response $response)
    {
        return $response->withJson([
            'status' => 'success',
            'message' => 'I made it...'
        ], 200);
    }

    public function me(Request $request, Response $response, User $user)
    {
        return $response->withJson(['status' => 'success', 'user' => $user, 'request' => $request->getAttribute('user')], 200);
    }
}
