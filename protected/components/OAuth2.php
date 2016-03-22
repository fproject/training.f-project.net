<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// � Copyright f-project.net 2015. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use fproject\common\utils\JsonHelper;
/**
 * OAuth2 client component.
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */

class OAuth2 extends CComponent
{
    const CONTENT_TYPE_JSON = 'json'; // JSON format
    const CONTENT_TYPE_URLENCODED = 'urlencoded'; // urlencoded query string, like name1=value1&name2=value2
    const CONTENT_TYPE_XML = 'xml'; // XML format
    const CONTENT_TYPE_AUTO = 'auto'; // attempts to determine format automatically

    /** The cryptography algorithm used to encrypt/decrypt JWT */
    const CRYPTO_ALG = 'RS256';

    /** The expire duration for pubic key */
    const PUBLIC_KEY_EXPIRE_DURATION = 86400;

    /**
     * @var string protocol version.
     */
    public $version = '2.0';

    /**
     * @var string OAuth client ID.
     */
    public $clientId;

    /**
     * @var string OAuth client secret.
     */
    public $clientSecret;

    /** @var string $clientRSId */
    public $clientRSId;

    /** @var string $clientRSSecret */
    public $clientRSSecret;

    /**
     * @var string authorize URL.
     */
    public $authUrl;

    /**
     * @var string URL, which user will be redirected after authentication at the OAuth provider web site.
     * Note: this should be absolute URL (with http:// or https:// leading).
     * By default current URL will be used.
     */
    public $returnUrl;

    /**
     * @var string token request URL endpoint.
     */
    public $tokenUrl;

    /** @var string $jwkUrl the URL to obtain JWK or JWKSet */
    public $jwkUrl;

    /** @var string $userInfoUrl the URL to obtain user information */
    public $userInfoUrl;

    /** @var  string $logoutUrl the URL to logout from OAuth server */
    public $logoutUrl;

    /**
     * @var string auth request scope.
     */
    public $scope;

    /**
     * The server leeway time in seconds, to aware the acceptable different time between clocks
     * of token issued server and relying parties.
     * When checking nbf, iat or expiration times, we want to provide some extra leeway time to
     * account for clock skew.
     */
    public $leeway = 0;

    /**
     * Initializes the OAuth2 component.
     * This method is called at the end of the component constructor.
     * Note that at this moment, the module has been configured, the behaviors
     * have been attached and the application components have been registered.
     */
    public function init()
    {
        JWT::$leeway = $this->leeway;
    }

    /**
     * @var array cURL request options. Option values from this field will overwrite corresponding
     * values from [[defaultCurlOptions()]].
     */
    private $_curlOptions = [];

    /**
     * @param array $curlOptions cURL options.
     */
    public function setCurlOptions(array $curlOptions)
    {
        $this->_curlOptions = $curlOptions;
    }

    /**
     * @return array cURL options.
     */
    public function getCurlOptions()
    {
        return $this->_curlOptions;
    }

    /**
     * Merge CUrl options.
     * If each options array has an element with the same key value, the latter
     * will overwrite the former.
     * @param array $options1 options to be merged to.
     * @param array $options2 options to be merged from. You can specify additional
     * arrays via third argument, fourth argument etc.
     * @return array merged options (the original options are not changed.)
     */
    protected function mergeCurlOptions($options1, $options2)
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_array($v) && !empty($res[$k]) && is_array($res[$k])) {
                    $res[$k] = array_merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }
        return $res;
    }

    /**
     * Returns default cURL options.
     * @return array cURL options.
     */
    protected function defaultCurlOptions()
    {
        return [
            CURLOPT_USERAGENT => Yii::app()->name . ' OAuth ' . $this->version . ' Client',
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ];
    }

    /**
     * Attempts to determine HTTP request content type by headers.
     * @param array $headers request headers.
     * @return string content type.
     */
    protected function determineContentTypeByHeaders(array $headers)
    {
        if (isset($headers['content_type'])) {
            if (stripos($headers['content_type'], 'json') !== false) {
                return self::CONTENT_TYPE_JSON;
            }
            if (stripos($headers['content_type'], 'urlencoded') !== false) {
                return self::CONTENT_TYPE_URLENCODED;
            }
            if (stripos($headers['content_type'], 'xml') !== false) {
                return self::CONTENT_TYPE_XML;
            }
        }
        return self::CONTENT_TYPE_AUTO;
    }

    /**
     * Attempts to determine the content type from raw content.
     * @param string $rawContent raw response content.
     * @return string response type.
     */
    protected function determineContentTypeByRaw($rawContent)
    {
        if (preg_match('/^\\{.*\\}$/is', $rawContent)) {
            return self::CONTENT_TYPE_JSON;
        }
        if (preg_match('/^[^=|^&]+=[^=|^&]+(&[^=|^&]+=[^=|^&]+)*$/is', $rawContent)) {
            return self::CONTENT_TYPE_URLENCODED;
        }
        if (preg_match('/^<.*>$/is', $rawContent)) {
            return self::CONTENT_TYPE_XML;
        }
        return self::CONTENT_TYPE_AUTO;
    }

    /**
     * Converts XML document to array.
     * @param string|\SimpleXMLElement $xml xml to process.
     * @return array XML array representation.
     */
    protected function convertXmlToArray($xml)
    {
        if (!is_object($xml)) {
            $xml = simplexml_load_string($xml);
        }
        $result = (array) $xml;
        foreach ($result as $key => $value) {
            if (is_object($value)) {
                $result[$key] = $this->convertXmlToArray($value);
            }
        }
        return $result;
    }

    /**
     * Processes raw response converting it to actual data.
     * @param string $rawResponse raw response.
     * @param string $contentType response content type.
     * @throws Exception on failure.
     * @return array actual response.
     */
    protected function processResponse($rawResponse, $contentType = self::CONTENT_TYPE_AUTO)
    {
        if (empty($rawResponse)) {
            return [];
        }
        switch ($contentType) {
            case self::CONTENT_TYPE_AUTO: {
                $contentType = $this->determineContentTypeByRaw($rawResponse);
                if ($contentType == self::CONTENT_TYPE_AUTO) {
                    throw new Exception('Unable to determine response content type automatically.');
                }
                $response = $this->processResponse($rawResponse, $contentType);
                break;
            }
            case self::CONTENT_TYPE_JSON: {
                $response = JsonHelper::decode($rawResponse, true);
                break;
            }
            case self::CONTENT_TYPE_URLENCODED: {
                $response = [];
                parse_str($rawResponse, $response);
                break;
            }
            case self::CONTENT_TYPE_XML: {
                $response = $this->convertXmlToArray($rawResponse);
                break;
            }
            default: {
                throw new Exception('Unknown response type "' . $contentType . '".');
            }
        }
        return $response;
    }

    /**
     * Sends HTTP request.
     * @param string $method request type.
     * @param string $url request URL.
     * @param array $params request params.
     * @param array $headers additional request headers.
     * @return array response.
     * @throws Exception on failure.
     */
    protected function sendRequest($method, $url, array $params = [], array $headers = [])
    {
        $curlOptions = $this->mergeCurlOptions(
            $this->defaultCurlOptions(),
            $this->getCurlOptions(),
            [
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $url,
            ],
            $this->composeRequestCurlOptions(strtoupper($method), $url, $params)
        );
        $curlResource = curl_init();
        foreach ($curlOptions as $option => $value) {
            curl_setopt($curlResource, $option, $value);
        }
        $response = curl_exec($curlResource);
        $responseHeaders = curl_getinfo($curlResource);

        // check cURL error
        $errorNumber = curl_errno($curlResource);
        $errorMessage = curl_error($curlResource);

        curl_close($curlResource);

        if ($errorNumber > 0) {
            throw new Exception('Curl error requesting "' .  $url . '": #' . $errorNumber . ' - ' . $errorMessage);
        }
        if (strncmp($responseHeaders['http_code'], '20', 2) !== 0) {
            throw new Exception('Request failed with code: ' . $responseHeaders['http_code'] . ', message: ' . $response);
        }

        return $this->processResponse($response, $this->determineContentTypeByHeaders($responseHeaders));
    }

    /**
     * Composes HTTP request CUrl options, which will be merged with the default ones.
     * @param string $method request type.
     * @param string $url request URL.
     * @param array $params request params.
     * @return array CUrl options.
     * @throws Exception on failure.
     */
    protected function composeRequestCurlOptions($method, $url, array $params)
    {
        $curlOptions = [];
        switch ($method) {
            case 'GET': {
                $curlOptions[CURLOPT_URL] = $this->composeUrl($url, $params);
                break;
            }
            case 'POST': {
                $curlOptions[CURLOPT_POST] = true;
                $curlOptions[CURLOPT_HTTPHEADER] = ['Content-type: application/x-www-form-urlencoded'];
                $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
                break;
            }
            case 'HEAD': {
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
                if (!empty($params)) {
                    $curlOptions[CURLOPT_URL] = $this->composeUrl($url, $params);
                }
                break;
            }
            default: {
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
                if (!empty($params)) {
                    $curlOptions[CURLOPT_POSTFIELDS] = $params;
                }
            }
        }

        return $curlOptions;
    }

    /**
     * Composes URL from base URL and GET params.
     * @param string $url base URL.
     * @param array $params GET params.
     * @return string composed URL.
     */
    protected function composeUrl($url, array $params = [])
    {
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }
        $url .= http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return $url;
    }

    /** @var  array $publicKey */
    private $_publicKey;

    /**
     * The public key in decoded JWK format used for Token encode/decode
     * @return array|mixed
     */
    public function getPublicKey()
    {
        if(empty($this->_publicKey) && !empty($this->jwkUrl))
        {
            if(Yii::app()->cache)
            {
                $cacheKey = "JWK_".sha1($this->jwkUrl);
                $jwk = Yii::app()->cache->get($cacheKey);
            }

            if(empty($jwk))
            {
                $jwk = $this->sendRequest('GET', $this->jwkUrl);
                if(!empty($jwk) && Yii::app()->cache)
                    Yii::app()->cache->set($cacheKey, $jwk, self::PUBLIC_KEY_EXPIRE_DURATION);
            }

            if(!empty($jwk))
                $this->_publicKey = JWK::parseKeySet($jwk);
        }
        return $this->_publicKey;
    }

    /**
     * Verify and decode a JWT token
     * @param string $token the encoded JWT token
     * @param bool $checkRevoked
     * @return \stdClass the payload data of JWT token
     */
    public function verifyAndDecodeToken($token, $checkRevoked=true)
    {
        $payload = JWT::decode($token, $this->getPublicKey(), [self::CRYPTO_ALG]);
        if($checkRevoked && $this->checkRevokedToken($token, $payload))
            throw new UnexpectedValueException('Token is revoked.');
        return $payload;
    }

    /**
    /**
     * Check if token is revoked
     * @param string $token the JWT token
     * @param \stdClass $payload the token's payload
     * @return bool true if the token is revoked
     */
    public function checkRevokedToken($token, $payload)
    {
        if(!empty($payload) && Yii::app()->cache)
        {
            return Yii::app()->cache->get($this->getRevokedTokenCacheKey($token)) !== false;
        }
        return false;
    }

    /**
     * Save revoked token to cache
     * @param string $token the JWT token
     * @param \stdClass $payload the token's payload
     */
    public function saveRevokedToken($token, $payload)
    {
        if(!empty($payload) && property_exists($payload,'exp') && Yii::app()->cache)
        {
            $duration = (int)$payload->exp + JWT::$leeway - time();

            if($duration > 0 && Yii::app()->cache)
                Yii::app()->cache->set($this->getRevokedTokenCacheKey($token), true, $duration);
        }
    }

    private function getRevokedTokenCacheKey($token)
    {
        return "Revoked_JWT_".sha1($token);
    }

    /**
     * Get user information from OAuth2 provider
     * @param string $accessToken The bearer access token, scoped to retrieve the consented claims for the subject (end-user).
     * @param int $cacheDuration the cache duration
     * @return array
     * @throws Exception
     */
    public function getUserInfo($accessToken, $cacheDuration=-1)
    {
        $userInfo = null;

        if(!empty($accessToken))
        {
            if($cacheDuration > 0 && Yii::app()->cache)
            {
                $cacheKey = "UserInfo_".sha1($accessToken);
                $userInfo = Yii::app()->cache->get($cacheKey);
            }

            if(empty($userInfo))
            {
                $headers = ['Authorization: Bearer ' . $accessToken];
                $userInfo = $this->sendRequest('GET', $this->userInfoUrl, [], $headers);
                if($cacheDuration > 0 && Yii::app()->cache)
                {
                    Yii::app()->cache->set($cacheKey, $userInfo, $cacheDuration);
                }
            }
        }


        return $userInfo;
    }

    /**
     * Logout the current user by identity
     * @param string $token the JWT token of current login user
     * @param string $sid the session ID
     * @param bool $globalLogout
     * @return array|false
     * @throws Exception
     */
    public function logout($token, $sid, $globalLogout=true)
    {
        $this->isLoggingOut = true;

        if($globalLogout)
            Yii::app()->user->logout();

        if(!empty($sid))
        {
            $headers = ['Authorization: Bearer ' . $token];
            $params = ['sid' => $sid];
            return $this->sendRequest('GET', $this->logoutUrl, $params, $headers);
        }
        return false;
    }
}