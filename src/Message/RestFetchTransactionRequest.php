<?php
/**
 * PaidYET REST Fetch Transaction Request
 */

namespace Omnipay\PaidYET\Message;

/**
 * PaidYET REST Fetch Transaction Request
 *
 */
class RestFetchTransactionRequest extends AbstractRestRequest
{
    public function getData()
    {
        $this->validate('transactionReference');
        return array();
    }

    /**
     * Get HTTP Method.
     *
     * The HTTP method for fetchTransaction requests must be GET.
     * Using POST results in an error 500.
     *
     * @return string
     */
    protected function getHttpMethod()
    {
        return 'GET';
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/sale/' . $this->getTransactionReference();
    }
}
