<?php
/**
 * Created by PhpStorm.
 * User: Daimon
 * Date: 2015/6/16
 * Time: 14:07
 */

namespace App\Extensions;

use League\OAuth2\Client\Provider\WealthbetterClient as WealthbetterClient;
use App\Exceptions\APIException as APIException;
use Guzzle\Service\Client as GuzzleClient;
/**
 * Class WealthbetterAPIClient
 * @package App\Extensions
 */
class WealthbetterAPIClient extends WealthbetterClient
{
    /**
     * @var Client;
     */
    protected $client;

    /**
     * @var Token;
     */
    protected $token;

    /**
     * @var 获取access_token所需参数
     */
    protected $accessTokenParams;


    /**
     * @param string $token
     * @throws APIException
     */
    public function __construct($token = null)
    {
        $oAuthClientParams = \Config::get('oAuthClient');
        parent::__construct($oAuthClientParams);
        if (is_null($token)) {
            $this->token = $token;
        }
    }


    /**
     * @param array $accessTokenParams
     * @return mixed
     * @throws APIException
     * @throws \League\OAuth2\Client\Exception\IDPException
     */
    public function getAccessToken($grant = 'password', $accessTokenParams = [])
    {
        $result = parent::getAccessToken('password',$accessTokenParams);
        if( ! isset($result->accessToken) ){
            throw new APIException($result);
        }
        return $result;
    }

    public function request($url ,array $requestParams = [] , $method = 'POST')
    {
        try {
            $token = \Session::get('token');
            if ($token){
                $requestParams = array_merge(['access_token' => $token->accessToken],$requestParams);
            }
            switch (strtoupper($method)) {
                case 'GET':
                    // @codeCoverageIgnoreStart
                    // No providers included with this library use get but 3rd parties may
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($url . '?' . $this->httpBuildQuery($requestParams, '', '&'));
                    $request = $client->get(null, $this->getHeaders(), $requestParams)->send();
                    $response = $request->getBody();
                    break;
                // @codeCoverageIgnoreEnd
                case 'POST':
                    $client = $this->getHttpClient();
//                    var_dump($client);
                    $client->setBaseUrl($url);
                    $request = $client->post(null, $this->getHeaders(), $requestParams)->send();
                    $response = $request->getBody();
                    break;
                // @codeCoverageIgnoreStart
                default:
                    throw new APIException('Neither GET nor POST is specified for request');
                // @codeCoverageIgnoreEnd
            }
        } catch (APIException $e) {
            // @codeCoverageIgnoreStart
            $response = $e->getResponse()->getBody();
            // @codeCoverageIgnoreEnd
        }

        switch ($this->responseType) {
            case 'json':
                $result = json_decode($response, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    $result = [];
                }

                break;
            case 'string':
                parse_str($response, $result);
                break;
        }
        //token过期,尝试使用refresh_token获取新access_token,并再次请求接口
        if(isset($result['status_code']) && $result['status_code'] == 401)
        {
            $refreshResult = parent::getAccessToken('refresh_token',$token->refresh_token);
            if($refreshResult['status_code'] != 200){
                throw new APIException('access token has expired');
            }
            $this->token = $refreshResult;
            return self::makeRequest($url,$requestParams,$method);
        }
        if (isset($result['error']) && ! empty($result['error'])) {
            throw new APIException($result);
        }
        return $result;
    }
}
