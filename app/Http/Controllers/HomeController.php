<?php

namespace App\Http\Controllers;

use App\Exceptions\APIException;
use App\User;
use Illuminate\Support\Facades\Session;
use Mockery\CountValidator\Exception;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\WealthbetterClient as WealthbetterClient;
use App\Extensions\WealthbetterAPIClient as WealthbetterAPIClient;

class HomeController extends Controller
{

    /**
     * Create a new authentication controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
        $this->client = new WealthbetterAPIClient();
    }

    public function index()
    {
        $parmas = ['muname' => '15510722548', 'mupass' => hashPassword(987654321)];
        try {
            $result = $this->client->request('http://open.chkinn.com/api/rest/login', $parmas);
            var_dump($result);
        } catch (APIException $e) {
            var_dump($e->getMessage());
        }
    }
}
