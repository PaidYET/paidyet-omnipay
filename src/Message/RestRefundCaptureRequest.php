<?php
/**
 * PaidYET REST Refund Captured Payment Request
 */

namespace Omnipay\PaidYET\Message;

/**
 * PaidYET REST Refund Captured Payment Request
 *
 * Not currently used: but can modify/Use this call to refund a captured payment.
 *
 * @see RestAuthorizeRequest
 * @see RestCaptureRequest
 */
class RestRefundCaptureRequest extends AbstractRestRequest
{
    public function getData()
    {
        $this->validate('transactionReference');

        return array(
            'amount' => array(
                'currency' => $this->getCurrency(),
                'total' => $this->getAmount(),
            ),
            'description' => $this->getDescription(),
        );
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/payments/capture/' . $this->getTransactionReference() . '/refund';
    }
}
