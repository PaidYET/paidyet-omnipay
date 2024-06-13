<?php
/**
 * PaidYET REST Capture Request
 */

namespace Omnipay\PaidYET\Message;

/**
 * PaidYET REST Capture Request
 *
 *
 * @see RestAuthorizeRequest
 * @link 
 */
class RestCaptureRequest extends AbstractRestRequest
{
    public function getData()
    {
        $data['type'] = 'capture';
        $data['amount'] = $this->getAmount();
        return $data;
    }

    function getHttpMethod()
    {
        return 'PUT';
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/transaction' . '/' . $this->getTransactionId();
    }
}
