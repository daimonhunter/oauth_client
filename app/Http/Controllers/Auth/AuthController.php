<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Support\Facades\Session;
use Mockery\CountValidator\Exception;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\WealthbetterClient as WealthbetterClient;
use App\Extensions\WealthbetterAPIClient as WealthbetterAPIClient;
class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
//        Service::registerProvider('wealthbetter', 'Eva\EvaOAuth\OAuth2\Providers\Wealthbetter');
//        $this->service = new Service('wealthbetter', [
//            'key' => '1',  //对应微博的API Key
//            'secret' => 'gKYG75sw', //对应微博的Secret
//            'callback' => 'http://oauth_client.com/auth/' //回调地址
//        ]);
//
//        $this->service->getAdapter()->registerGrantStrategy(Client::GRANT_PASSWORD,'Eva\EvaOAuth\OAuth2\GrantStrategy\Password');
//        $this->service->getAdapter()->changeGrantStrategy(Client::GRANT_PASSWORD);

        //league/oauth2-client section
//        $this->provider = new WealthbetterClient([
//            'clientId' => '1',
//            'clientSecret' => 'gKYG75sw',
//            'redirectUri' => 'http://oauth_client.com/auth/',
//            'scopes' => ['basic'],
//        ]);
        $this->client = new WealthbetterAPIClient();

    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:2',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|max:255', 'password' => 'required',
        ]);
//        $resourceServer = new ResourceServer('http://51_demo.com/api/oauth2/access_token');

        $params = ['username' => $request->get('username'), 'password' => $request->get('password')];
//        $token = $this->service->getAdapter()->getAccessToken($resourceServer,$params);
//        $token = $this->provider->getAccessToken('password', $params);
        $token = $this->client->getAccessToken('password',$params);
        Session::put('token', $token);
//        var_dump($token);

    }

    public function getUsers()
    {
        try {

//            $httpClient = new AuthorizedHttpClient($token);
//            $response = $httpClient->get('http://51_demo.com/api/users/1', ['query' => ['access_token' => $token->getTokenValue()]]);
//            if ($response->getStatusCode() == 200) {
//                $stream = $response->getBody();
//                var_dump(json_decode($stream->getContents()));
//            } else {
//                echo $response->getStatusCode();
//            }
//            $response = $this->provider->makeRequest('http://51_demo.com/api/users',['uid' => 1],'GET');
//            var_dump($response);
            $token = Session::get('token');
            $result = $this->client->request('http://51_demo.com/api/users/1',['access_token' => $token->accessToken],'GET');
            var_dump($result);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
