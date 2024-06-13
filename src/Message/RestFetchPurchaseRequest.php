<?php
/**
 * PaidYET REST Fetch Purchase Request
 */

namespace Omnipay\PaidYET\Message;

/**
 * PaidYET REST Fetch Purchase Request
 *
 * Use this call to get details about payments that have not completed, such
 * as payments that are created and approved, or if a payment has failed.
 *
 * Not curently in use
 */
class RestFetchPurchaseRequest extends AbstractRestRequest
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
        return parent::getEndpoint() . '/payments/payment/' . $this->getTransactionReference();
    }
}
