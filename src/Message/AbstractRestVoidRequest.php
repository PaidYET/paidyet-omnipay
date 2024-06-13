<?php
/**
 * PaidYET Abstract REST Request
 */

namespace Omnipay\PaidYET\Message;

use Omnipay\Common\Exception\InvalidResponseException;

/**
 * PaidYET Abstract REST Request
 *
 * This class forms the base class for PaidYET REST requests via the PaidYET REST APIs.
 *
 * @see Omnipay\PaidYET\RestGateway
 */

abstract class AbstractRestVoidRequest extends \Omnipay\Common\Message\AbstractRequest
{
    const API_VERSION = 'v3';

    /**
     * Sandbox Endpoint URL
     *
     * The PaidYET REST APIs are supported in two environments. Use the Sandbox environment
     * for testing purposes, then move to the live environment for production processing.
     * When testing, generate an access token with your test credentials to make calls to
     * the Sandbox URIs. When you’re set to go live, use the live credentials assigned to
     * your app to generate a new access token to be used with the live URIs.
     *
     * @var string URL
     */
    protected $testEndpoint = 'https://api.sandbox-paidyet.com';

    /**
     * Live Endpoint URL
     *
     * When you’re set to go live, use the live credentials assigned to
     * your app to generate a new access token to be used with the live URIs.
     *
     * @var string URL
     */
    protected $liveEndpoint = 'https://api.paidyet.com';

    /**
     * Payer ID
     *
     * @var string PayerID
     */
    protected $payerId = null;

    protected $referrerCode;

    /**
     * @var bool
     */
    protected $negativeAmountAllowed = true;
    
    /**
     * @return string
     */
    public function getReferrerCode()
    {
        return $this->referrerCode;
    }

    /**
     * @param string $referrerCode
     */
    public function setReferrerCode($referrerCode)
    {
        $this->referrerCode = $referrerCode;
    }

    public function getClientId()
    {
        return $this->getParameter('clientId');
    }

    public function setClientId($value)
    {
        return $this->setParameter('clientId', $value);
    }

    public function getSecret()
    {
        return $this->getParameter('secret');
    }

    public function setSecret($value)
    {
        return $this->setParameter('secret', $value);
    }

    public function getToken()
    {
        $data = ['key'=>$this->getSecret()];
        $body = $this->toJSON($data);
        $httpResponse = $this->httpClient->request(
            $this->getHttpMethod(),
            $this->getLoginEndpoint(),
            array(
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
            ),
            $body 
        );
        //print_r($this->getLoginEndpoint());
        //print_r($body);
        $contents = $httpResponse->getBody()->getContents();
        $contentsObj = JSON_decode($contents);
        //print_r($contentsObj);
        //print_r($contentsObj->result);
        //exit();
        return $contentsObj->result->token;
       
    }

    public function setToken($value)
    {
        return $this->setParameter('token', $value);
    }

    public function getPayerId()
    {
        return $this->getParameter('payerId');
    }

    public function setPayerId($value)
    {
        return $this->setParameter('payerId', $value);
    }

    /**
     * Get HTTP Method.
     *
     * This is nearly always POST but can be over-ridden in sub classes.
     *
     * @return string
     */
    protected function getHttpMethod()
    {
        return 'POST';
    }

    protected function getEndpoint()
    {
        $base = $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
        return $base . '/' . self::API_VERSION;
    }

    protected function getLoginEndpoint()
    {
        $base = $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
        $base .= '/' . self::API_VERSION;

        return $base . '/login';
    }

    public function sendData($data)
    {

        // Guzzle HTTP Client createRequest does funny things when a GET request
        // has attached data, so don't send the data if the method is GET.
        if ($this->getHttpMethod() == 'GET') {
            $requestUrl = $this->getEndpoint() . '?' . http_build_query($data);
            $body = null;
        } else {
            $body = $this->toJSON($data);
            $requestUrl = $this->getEndpoint();
        }
        //print_r($this->getEndpoint());
        //exit();
        print_r($body);
        // Print token:
        //print_r($this->getToken());
        //exit();
        try {
            $httpResponse = $this->httpClient->request(
                $this->getHttpMethod(),
                $this->getEndpoint(),
                array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getToken(),
                    'Content-type' => 'application/json',
                ),
                $body
            );
            // Empty response body should be parsed also as and empty array
            $body = (string) $httpResponse->getBody()->getContents();
            $jsonToArrayResponse = !empty($body) ? json_decode($body, true) : array();
            //print_r($jsonToArrayResponse);
            //exit();
            return $this->response = $this->createResponse($jsonToArrayResponse, $httpResponse->getStatusCode());
        } catch (\Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with payment gateway: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    public function toJSON($data, $options = 0)
    {
        // Because of PHP Version 5.3, we cannot use JSON_UNESCAPED_SLASHES option
        // Instead we would use the str_replace command for now.
        // TODO: Replace this code with return json_encode($this->toArray(), $options | 64); once we support PHP >= 5.4
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($data, $options | 64);
        }
        return str_replace('\\/', '/', json_encode($data, $options));
    }

    protected function createResponse($data, $statusCode)
    {
        return $this->response = new RestResponse($this, $data, $statusCode);
    }
}
