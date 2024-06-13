<?php
/**
 * PaidYET REST Authorize Request
 */

namespace Omnipay\PaidYET\Message;

/**
 * PaidYET REST Authorize Request
 *
 * To collect payment at a later time, first authorize a payment using the /payment resource.
 * You can then capture the payment to complete the sale and collect payment.
 *
 * @see RestCaptureRequest
 * @see RestPurchaseRequest
 */
class RestAuthorizeRequest extends AbstractRestRequest
{
    public function getData()
    {
        $data = array(
            'type' => 'auth',
            'amount' => $this->getAmount(),

            
           /* 'experience_profile_id' => $this->getExperienceProfileId() */
        );

       
            

        if ($this->getCardReference()) {
            $this->validate('amount');

            $data['payer']['funding_instruments'][] = array(
                'credit_card_token' => array(
                    'credit_card_id' => $this->getCardReference(),
                ),
            );
        } elseif ($this->getCard()) {
            $this->validate('amount', 'card');
            $this->getCard()->validate();

            $data['credit_card'] = array(
                
                    'number' => $this->getCard()->getNumber(),
                    //'type' => $this->getCard()->getBrand(),
                    'exp' => $this->getCard()->getExpiryMonth()."/".$this->getCard()->getExpiryYear(),
                    //'expire_year' => $this->getCard()->getExpiryYear(),
                    'cvv' => $this->getCard()->getCvv(),
                    'name' => $this->getCard()->getFirstName()." ".$this->getCard()->getLastName(),
                    //'type' => $this->getCard()->getLastName(),
                    'billing_address' => array(
                        'address' => $this->getCard()->getAddress1(),
                        //'line2' => $this->getCard()->getAddress2(),
                        'city' => $this->getCard()->getCity(),
                        'state' => $this->getCard()->getState(),
                        'postal' => $this->getCard()->getPostcode(),
                        //'country_code' => strtoupper($this->getCard()->getCountry()),//
                    )
               
            );

            
            $line2 = $this->getCard()->getAddress2();
            
        } else {
            $this->validate('amount', 'returnUrl', 'cancelUrl');

            unset($data['credit_card']);

        }

        return $data;
    }

    /**
    * Get the experience profile id
    *
    * @return string
    *
    *public function getExperienceProfileId()
    *{
    *    return $this->getParameter('experienceProfileId');
    *}
    */

    /**
     * Set the experience profile id
     *
     * @param string $value
     * @return RestAuthorizeRequest provides a fluent interface.
     *
     * public function setExperienceProfileId($value)
     *{
     *    return $this->setParameter('experienceProfileId', $value);
     * }
     */

    /**
     * Get transaction description.
     *
     * The REST API does not currently have support for passing an invoice number
     * or transaction ID.
     *
     * @return string
     */
    public function getDescription()
    {
        $id = $this->getTransactionId();
        $desc = parent::getDescription();

        if (empty($id)) {
            return $desc;
        } elseif (empty($desc)) {
            return $id;
        } else {
            return "$id : $desc";
        }
    }

    /**
     * Get transaction endpoint.
     *
     * Authorization of payments is done using the /transaction resource.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        return parent::getEndpoint() . '/transaction';
    }

    protected function createResponse($data, $statusCode)
    {
        return $this->response = new RestAuthorizeResponse($this, $data, $statusCode);
    }
}
