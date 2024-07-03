<?php
/**
 * PaidYET REST Authorize Response
 */

namespace Omnipay\PaidYET\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * PaidYET REST Authorize Response
 */
class RestAuthorizeResponse extends RestResponse implements RedirectResponseInterface
{
    public function isSuccessful()
    {
        return empty($this->data['error']) && $this->getCode() >= 200 && $this->getCode() <300;
    }

    public function isRedirect()
    {
        return $this->getRedirectUrl() !== null;
    }

    public function getRedirectUrl()
    {
        if (isset($this->data['links']) && is_array($this->data['links'])) {
            foreach ($this->data['links'] as $key => $value) {
                if ($value['rel'] == 'approval_url') {
                    return $value['href'];
                }
            }
        }

        return null;
    }

    /**
     * Get the URL to complete (execute) the purchase or agreement.
     *
     * The URL is embedded in the links section of the purchase or create
     * subscription request response.
     *
     * @return string
     */
    public function getCompleteUrl()
    {
        if (isset($this->data['links']) && is_array($this->data['links'])) {
            foreach ($this->data['links'] as $key => $value) {
                if ($value['rel'] == 'execute') {
                    return $value['href'];
                }
            }
        }

        return null;
    }

    public function getTransactionReference()
    {
        
        $completeUrl = $this->getCompleteUrl();
        if (empty($completeUrl)) {
            return parent::getTransactionReference();
        }

        $urlParts = explode('/', $completeUrl);

        // The last element of the URL should be "execute"
        $execute = end($urlParts);
        if (!in_array($execute, array('execute', 'agreement-execute'))) {
            return parent::getTransactionReference();
        }

        // The penultimate element should be the transaction reference
        return prev($urlParts);
    }

    /**
     * Get the required redirect method (either GET or POST).
     *
     * @return string
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * Gets the redirect form data array, if the redirect method is POST.
     *
     * @return null
     */
    public function getRedirectData()
    {
        return null;
    }
}
