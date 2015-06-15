<?php
/**
 * @author    AlloVince
 * @copyright Copyright (c) 2015 EvaEngine Team (https://github.com/EvaEngine)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Eva\EvaOAuth\OAuth2;

use Eva\EvaOAuth\OAuth2\Token\AccessToken;
use Eva\EvaOAuth\User\UserInterface;

/**
 * ResourceServer
 * @package Eva\EvaOAuth\OAuth2
 */
class ResourceServer implements ResourceServerInterface
{

    /**
     * access token url
     */
    protected $accessTokenUrl = '';

    public function __construct($accessTokenUrl){
        $this->accessTokenUrl = $accessTokenUrl;
    }
    /**
     * Access Token格式，可能是JSON或JSONP或Query
     * @return string
     */
    public function getAccessTokenFormat(){
        return self::FORMAT_JSON;
    }

    /**
     * Access Token请求方法，一般是POST
     * @return string
     */
    public function getAccessTokenMethod(){
        return self::METHOD_GET;
    }

    /**
     * Access Token Url
     * @return string
     */
    public function getAccessTokenUrl(){
        return $this->accessTokenUrl;
    }

    /**
     * Server返回Access Token与自定义Access Token的映射关系
     * @return array
     */
    //public function getAccessTokenFields();

    /**
     * @param AccessToken $token
     * @return UserInterface
     */
    public function getUser(AccessToken $token){

    }
}
