<?php
/**
 * PaidYET REST Refund Request
 */

namespace Omnipay\PaidYET\Message;

/**
 * PaidYET REST Refund Request
 *
 * @see RestPurchaseRequest
 */
class RestRefundRequest extends AbstractRestRequest
{
    public function getData()
    {
        $this->validate('transactionId');
        $data = array(
            'type' => 'refund',
        );
        return $data;

        if ($this->getAmount() > 0) {
            return array(
                'amount' => array(
                    'currency' => $this->getCurrency(),
                    'total' => $this->getAmount(),
                ),
                'description' => $this->getDescription(),
            );
        } else {
            return new \stdClass();
        }
    }

    public function getEndpoint()
    {
        return parent::getEndpoint() . '/transaction' . '/' . $this->getTransactionId();
        
    }
    
}
