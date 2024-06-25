<?php
/**
 * PaidYET Class using REST API
 */

namespace Omnipay\PaidYET;

use Omnipay\Common\AbstractGateway;

/**
 * PaidYET Class using REST API
 *
 * This class forms the gateway class for PaidYET requests via the PaidYET APIs.
 *
 * The PaidYET API uses a RESTful endpoint structure. Bearer token authentication is used.
 * Request and response payloads are formatted as JSON.
 *
 * The PaidYET APIs are supported in two environments. Use the Sandbox environment
 * for testing purposes, then move to the live environment for production processing.
 * When testing, generate an access token with your test credentials to make calls to
 * the Sandbox URIs. When you’re set to go live, use the live credentials assigned to
 * your app to generate a new access token to be used with the live URIs.
 *
 *
 * ### Credentials
 *
 * Authenticate with your PaidYET secret and Merchant ID to retrieve a bearer token. 
 * The bearer token will expire periodically and you will need to obtain a new one.
 * 
 * merchant_id
 * Merchant's PaidYET UUID. This field is only required when using a Partner level API secret.
 * This is found in the Merchant list in the Partner Portal.
 *
 * secret
 * Merchant's PaidYET api secret. These can be managed in the PaidYET dashboard
 *
 *
 * ### Example
 *
 * #### Initialize Gateway
 *
 * <code>
 *   // Create a gateway for the PaidYET RestGateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('PaidYET_Rest');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'clientId' => 'merchant_id',
 *       'secret'   => 'secret',
 *       'testMode' => true, // Or false when you are ready for live transactions
 *   ));
 * </code>
 *
 * #### Direct Credit Card Payment
 *
 *
 * PaidYET Class using REST API
 *
 * This class forms the gateway class for PaidYET requests via the PaidYET APIs.
 *
 * The PaidYET API uses a RESTful endpoint structure. Bearer token authentication is used.
 * Request and response payloads are formatted as JSON.
 *
 * The PaidYET APIs are supported in two environments. Use the Sandbox environment
 * for testing purposes, then move to the live environment for production processing.
 * When testing, generate an access token with your test credentials to make calls to
 * the Sandbox URIs. When you’re set to go live, use the live credentials assigned to
 * your app to generate a new access token to be used with the live URIs.
 *
 *
 * ### Credentials
 *
 * Authenticate with your PaidYET secret and Merchant ID to retrieve a bearer token. 
 * The bearer token will expire periodically and you will need to obtain a new one.
 * 
 * merchant_id
 * Merchant's PaidYET UUID. This field is only required when using a Partner level API secret.
 * This is found in the Merchant list in the Partner Portal.
 *
 * secret
 * Merchant's PaidYET api secret. These can be managed in the PaidYET dashboard
 *
 *
 * @link https://paidyet.readme.io/
 * @see Omnipay\PaidYET\Message\AbstractRestRequest
 * @see Omnipay\PaidYET\Message\AbstractRestRequest
 */
class RestGateway extends AbstractGateway
{

    // Constants used in plan creation
    const BILLING_PLAN_TYPE_FIXED       = 'FIXED';
    const BILLING_PLAN_TYPE_INFINITE    = 'INFINITE';
    const BILLING_PLAN_FREQUENCY_DAY    = 'DAY';
    const BILLING_PLAN_FREQUENCY_WEEK   = 'WEEK';
    const BILLING_PLAN_FREQUENCY_MONTH  = 'MONTH';
    const BILLING_PLAN_FREQUENCY_YEAR   = 'YEAR';
    const BILLING_PLAN_STATE_CREATED    = 'CREATED';
    const BILLING_PLAN_STATE_ACTIVE     = 'ACTIVE';
    const BILLING_PLAN_STATE_INACTIVE   = 'INACTIVE';
    const BILLING_PLAN_STATE_DELETED    = 'DELETED';
    const PAYMENT_TRIAL                 = 'TRIAL';
    const PAYMENT_REGULAR               = 'REGULAR';

    public function getName()
    {
        return 'PaidYET REST';
    }

    public function getDefaultParameters()
    {
        return array(
            'clientId'     => '',
            'secret'       => '',
            'token'        => '',
            'testMode'     => false,
        );
    }

    
    /**
     * Token Access
     *
     * Get an access token by using the your clientId:secret as your Basic Auth
     * credentials.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->getParameter('clientId');
    }

    /**
     * Set OAuth 2.0 client ID for the access token.
     *
     * Get an access token by using the OAuth 2.0 client_credentials
     * token grant type with your clientId:secret as your Basic Auth
     * credentials.
     *
     * @param string $value
     * @return RestGateway provides a fluent interface
     */
    public function setClientId($value)
    {
        return $this->setParameter('clientId', $value);
    }

    /**
     * Get OAuth 2.0 secret for the access token.
     *
     * Get an access token by using the OAuth 2.0 client_credentials
     * token grant type with your clientId:secret as your Basic Auth
     * credentials.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->getParameter('secret');
    }

    /**
     * Set secret (apisecret) for the access token.
     *
     *
     * @param string $value
     * @return RestGateway provides a fluent interface
     */
    public function setSecret($value)
    {
        return $this->setParameter('secret', $value);
    }

    /**
     * Get access token.
     *
     * @param bool $createIfNeeded [optional] - If there is not an active token present, should we create one?
     * @return string
     */
    public function getToken($createIfNeeded = true)
    {
        if ($createIfNeeded && !$this->hasToken()) {
            $response = $this->createToken()->send();
            if ($response->isSuccessful()) {
                $data = $response->getData();
                if (isset($data['access_token'])) {
                    $this->setToken($data['access_token']);
                    $this->setTokenExpires(time() + $data['expires_in']);
                }
            }
        }

        return $this->getParameter('token');
    }

    /**
     * Create access token request.
     *
     * @return \Omnipay\PaidYET\Message\RestTokenRequest
     */
    public function createToken()
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestTokenRequest', array());
    }

    /**
     * Set access token.
     *
     * @param string $value
     * @return RestGateway provides a fluent interface
     */
    public function setToken($value)
    {
        return $this->setParameter('token', $value);
    }

    /**
     * Get access token expiry time.
     *
     * @return integer
     */
    public function getTokenExpires()
    {
        return $this->getParameter('tokenExpires');
    }

    /**
     * Set access token expiry time.
     *
     * @param integer $value
     * @return RestGateway provides a fluent interface
     */
    public function setTokenExpires($value)
    {
        return $this->setParameter('tokenExpires', $value);
    }

    /**
     * Is there a bearer token and is it still valid?
     *
     * @return bool
     */
    public function hasToken()
    {
        $token = $this->getParameter('token');

        $expires = $this->getTokenExpires();
        if (!empty($expires) && !is_numeric($expires)) {
            $expires = strtotime($expires);
        }

        return !empty($token) && time() < $expires;
    }

    /**
     * Create Request
     *
     * This overrides the parent createRequest function ensuring that the
     * access token is passed along with the request data -- unless the
     * request is a RestTokenRequest in which case no token is needed.  If no
     * token is available then a new one is created (e.g. if there has been no
     * token request or the current token has expired).
     *
     * @param string $class
     * @param array $parameters
     * @return \Omnipay\PaidYET\Message\AbstractRestRequest
     *  
     * public function createRequest($class, array $parameters = array())
     * {
     *     if (!$this->hasToken() && $class != '\Omnipay\PaidYET\Message\RestTokenRequest') {
     *         // This will set the internal token parameter which the parent
     *         // createRequest will find when it calls getParameters().
     *         $this->getToken(true);
     *     }
     *
     *     return parent::createRequest($class, $parameters);
     * }
     *
     */
   

    /**
     * Create a purchase request.

     *
     * @link https://paidyet.readme.io/reference/post_transaction
     * @param array $parameters
     * @return \Omnipay\PaidYET\Message\RestPurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestPurchaseRequest', $parameters);
    }

    /**
     * Fetch a purchase request - Not used by PaidYET, placeholder.
     *
     * Use this call to get details about payments that have not completed,
     * such as payments that are created and approved, or if a payment has failed.
     *
     * @link
     * @param array $parameters
     * @return \Omnipay\PaidYET\Message\RestFetchPurchaseRequest
     */
    public function fetchPurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestFetchPurchaseRequest', $parameters);
    }

   
    /**
     * Completes a Sale (purchase) transaction.
     *
     * @link https://paidyet.readme.io/reference/post_transaction
     * @param array $parameters
     * @return Message\AbstractRestRequest
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestCompletePurchaseRequest', $parameters);
    }

    /**
     * Create an authorization request.
     *
     * To collect payment at a later time, first authorize a payment using the /payment resource.
     * You can then capture the payment to complete the sale and collect payment.
     *
     * @param array $parameters
     * @return \Omnipay\PaidYET\Message\RestAuthorizeRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestAuthorizeRequest', $parameters);
    }

    /**
     * Void an authorization.
     *
     * @link https://paidyet.readme.io/reference/patch_transaction-id
     * @param array $parameters
     * @return \Omnipay\PaidYET\Message\RestVoidRequest
     */
    public function void(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestVoidRequest', $parameters);
    }

    /**
     * Capture an authorization.
     *
     * The capture command moves an authorized transaction into the current batch for settlement. It is possible to capture 
	 * an amount other than the one originally authorized, however, you must follow the guidelines established by the merchant 
	 * service bank. Capturing a higher or lower dollar amount could result in additional penalties and fees.
	 *
	 * Most banks typically allow no more than 10 days to pass between the authorization/capture and settlement of a transaction.
     *
     * @link https://paidyet.readme.io/reference/put_transaction-id
     * @param array $parameters
     * @return \Omnipay\PaidYET\Message\RestCaptureRequest
     */
    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestCaptureRequest', $parameters);
    }



    /**
     * Retrieve Transaction
     *
     * Get a single transaction by its ID.
     * 
     *
     * @link https://paidyet.readme.io/reference/get_transaction-id
     * @param array $parameters
     * @return \Omnipay\PaidYET\Message\RestFetchTransactionRequest
     */
    public function fetchTransaction(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestFetchTransactionRequest', $parameters);
    }

    /**
     * Refund a Sale Transaction
     *
     * A refund should be used once the transaction you are refunding has settled. If you are
     * trying to cancel a transaction that is still in the currently open batch, you should use the
	 * void command instead.
	 *
	 * To refund a transaction that has been settled, you will pass in the transaction object
	 * with the type of 'refund', the original transaction id, and the amount you would like to
	 * refund. Most merchant accounts do not allow you to refund more than the original amount of the transaction.
	 * However, depending on the Credit Policy, a refund can be processed for larger than the original transaction amount.
	 *
	 *
     * @link https://paidyet.readme.io/reference/post_transaction-id
     * @param array $parameters
     * @return \Omnipay\PaidYET\Message\RestRefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestRefundRequest', $parameters);
    }

    /**
     * Store a credit card in the vault
     *
     *
     * @link https://paidyet.readme.io/reference/post_card
     * @param array $parameters
     * @return \Omnipay\PaidYET\Message\RestCreateCardRequest
     */
    public function createCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestCreateCardRequest', $parameters);
    }

   

    /**
     * Search for transactions.
     *
     * Get a collection of transactions matching the supplied criteria. At least one parameter is required.
	 *
     */
    public function searchTransaction(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaidYET\Message\RestSearchTransactionRequest', $parameters);
    }

   
}
