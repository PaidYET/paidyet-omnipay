<?php
/**
 * PaidYET REST Token Request
 */

namespace Omnipay\PaidYET\Message;

/**
 * Oauth2 REST Token Request
 *
 * token request for oauth2
 */
class RestTokenRequest extends AbstractRestRequest
{
    public function getData()
    {
        return array('grant_type' => 'client_credentials');
    }

    protected function getEndpoint()
    {
        return parent::getEndpoint() . '/oauth2/token';
    }

    public function sendData($data)
    {
        $body = $data ? http_build_query($data, '', '&') : null;
        $httpResponse = $this->httpClient->request(
            $this->getHttpMethod(),
            $this->getEndpoint(),
            array(
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("{$this->getClientId()}:{$this->getSecret()}"),
            ),
            $body
        );
        // Empty response body should be parsed also as and empty array
        $body = (string) $httpResponse->getBody()->getContents();
        $jsonToArrayResponse = !empty($body) ? json_decode($body, true) : array();
        return $this->response = new RestResponse($this, $jsonToArrayResponse, $httpResponse->getStatusCode());
    }
}
