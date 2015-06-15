<?php
/**
 * @author    AlloVince
 * @copyright Copyright (c) 2015 EvaEngine Team (https://github.com/EvaEngine)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */


namespace Eva\EvaOAuth\OAuth2\GrantStrategy;

use Doctrine\Common\Cache\Cache;
use Eva\EvaOAuth\Events\BeforeAuthorize;
use Eva\EvaOAuth\Events\BeforeGetAccessToken;
use Eva\EvaOAuth\OAuth2\AuthorizationServerInterface;
use Eva\EvaOAuth\OAuth2\Client;
use Eva\EvaOAuth\OAuth2\ResourceServerInterface;
use Eva\EvaOAuth\OAuth2\Token\AccessToken;
use Eva\EvaOAuth\Exception\InvalidArgumentException;
use Eva\EvaOAuth\Utils\Text;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Event\HasEmitterTrait;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;
use Eva\EvaOAuth\OAuth2\GrantStrategy\GrantStrategyInterface;
/**
 * Class AuthorizationCode
 * @package Eva\EvaOAuth\OAuth2\GrantStrategy
 */
class Password implements GrantStrategyInterface
{
    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Cache
     */
    protected $storage;

    use HasEmitterTrait;


    /**
     * @param AuthorizationServerInterface $authServer
     * @param string $url
     */
    public function requestAuthorize(AuthorizationServerInterface $authServer, $url = '')
    {
        return ;
    }

    /**
     * @param AuthorizationServerInterface $authServer
     * @return string
     */
    public function getAuthorizeUrl(AuthorizationServerInterface $authServer)
    {
        return ;
    }

    /**
     * @param ResourceServerInterface $resourceServer
     * @param array $urlQuery
     * @return \Eva\EvaOAuth\OAuth2\Token\AccessTokenInterface
     */
    public function getAccessToken(ResourceServerInterface $resourceServer, array $urlQuery = [])
    {
        $urlQuery = $urlQuery ?: $_REQUEST;
        $state = empty($urlQuery['state']) ? '' : $urlQuery['state'];
        $username = empty($urlQuery['username']) ? '' : $urlQuery['username'];
        $password = empty($urlQuery['password']) ? '' : $urlQuery['password'];
        $options = $this->options;

//        if (!$code) {
//            throw new InvalidArgumentException("No authorization code found");
//        }

        //TODO: Valid state to void attach

        $parameters = [
            'client_secret' => $options['client_secret'],
            'grant_type'    => 'password',
            'username'      => $username,
            'password'      => $password,
            'client_id'     => $options['client_id'],
        ];
        if ($state) {
            $parameters['state'] = $state;
        }

        $httpClient = $this->httpClient;

        $method = $resourceServer->getAccessTokenMethod();
        $httpClientOptions = ($method == ResourceServerInterface::METHOD_GET) ?
            ['query' => $parameters] :
            ['body' => $parameters];

        $request = $httpClient->createRequest(
            $method,
            $resourceServer->getAccessTokenUrl(),
            $httpClientOptions
        );

        try {
            $this->getEmitter()->emit('beforeGetAccessToken', new BeforeGetAccessToken($request, $resourceServer));
            /** @var Response $response */
            $response = $httpClient->send($request);
            return AccessToken::factory($response, $resourceServer);
        } catch (RequestException $e) {
            throw new \Eva\EvaOAuth\Exception\RequestException(
                'Get access token failed'.$e->getMessage(),
                $e->getRequest(),
                $e->getResponse()
            );
        }
    }

    /**
     * @param HttpClient $httpClient
     * @param array $options
     * @param Cache $storage
     */
    public function __construct(HttpClient $httpClient, array $options, Cache $storage = null)
    {
        $this->httpClient = $httpClient;
        $this->options = $options;
        $this->storage = $storage;
    }
}
