<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Exception\IDPException as IDPException;
class WealthbetterClient extends AbstractProvider
{
    public $scopeSeparator = ' ';

    public $scopes = [
        'basic',
    ];

    public $authorizationHeader = 'OAuth';

    /**
     * @var string If set, this will be sent to google as the "hd" parameter.
     * @link https://developers.google.com/accounts/docs/OAuth2Login#hd-param
     */
    public $hostedDomain = '';

    public function setHostedDomain($hd)
    {
        $this->hostedDomain = $hd;
    }

    public function getHostedDomain()
    {
        return $this->hostedDomain;
    }

    /**
     * @var string If set, this will be sent to google as the "access_type" parameter.
     * @link https://developers.google.com/accounts/docs/OAuth2WebServer#offline
     */
    public $accessType = '';

    public function setAccessType($accessType)
    {
        $this->accessType = $accessType;
    }

    public function getAccessType()
    {
        return $this->accessType;
    }

    public function urlAuthorize()
    {
        return 'http://51_demo.com/api/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'http://51_demo.com/api/oauth2/access_token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'http://51_demo.com/api/users';
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $response = (array) $response;

        $user = new User();

        $imageUrl = (isset($response['image']) &&
            $response['image']->url) ? $response['image']->url : null;
        $email =
            (isset($response['emails']) &&
            count($response['emails']) &&
            $response['emails'][0]->value)? $response['emails'][0]->value : null;

        $user->exchangeArray([
            'uid' => $response['id'],
            'name' => $response['displayName'],
            'firstname' => $response['name']->givenName,
            'lastName' => $response['name']->familyName,
            'email' => $email,
            'imageUrl' => $imageUrl,
        ]);

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return ($response->emails &&
            count($response->emails) &&
            $response->emails[0]->value) ? $response->emails[0]->value : null;
    }

    public function userScreenName($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return [$response->name->givenName, $response->name->familyName];
    }

    public function getAuthorizationUrl($options = array())
    {
        $url = parent::getAuthorizationUrl($options);

        if (!empty($this->hostedDomain)) {
            $url .= '&' . $this->httpBuildQuery(['hd' => $this->hostedDomain]);
        }

        if (!empty($this->accessType)) {
            $url .= '&' . $this->httpBuildQuery(['access_type'=> $this->accessType]);
        }

        return $url;
    }

    public function getAccessToken($grant = 'authorization_code', $params = [])
    {
        if (is_string($grant)) {
            // PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
            $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $grant)));
            $grant = 'League\\OAuth2\\Client\\Grant\\'.$className;
            if (! class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }
            $grant = new $grant();
        } elseif (! $grant instanceof GrantInterface) {
            $message = get_class($grant).' is not an instance of League\OAuth2\Client\Grant\GrantInterface';
            throw new \InvalidArgumentException($message);
        }

        $defaultParams = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => $grant,
        ];

        $requestParams = $grant->prepRequestParams($defaultParams, $params);

        try {
            switch (strtoupper($this->method)) {
                case 'GET':
                    // @codeCoverageIgnoreStart
                    // No providers included with this library use get but 3rd parties may
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->urlAccessToken() . '?' . $this->httpBuildQuery($requestParams, '', '&'));
                    $request = $client->get(null, $this->getHeaders(), $requestParams)->send();
                    $response = $request->getBody();
                    break;
                // @codeCoverageIgnoreEnd
                case 'POST':
                    $client = $this->getHttpClient();
                    $client->setBaseUrl($this->urlAccessToken());
                    $request = $client->post(null, $this->getHeaders(), $requestParams)->send();
                    $response = $request->getBody();
                    break;
                // @codeCoverageIgnoreStart
                default:
                    throw new \InvalidArgumentException('Neither GET nor POST is specified for request');
                // @codeCoverageIgnoreEnd
            }
        } catch (BadResponseException $e) {
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

        if (isset($result['status_code']) && $result['status_code'] != 200) {
            // @codeCoverageIgnoreStart
            return $result;
            // @codeCoverageIgnoreEnd
        }

        $result = $this->prepareAccessTokenResult($result);

        return $grant->handleResponse($result);
    }

}
