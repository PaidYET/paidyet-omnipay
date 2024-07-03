<?php

use Omnipay\Tests\GatewayTestCase;
use Omnipay\PaidYET\RestGateway;
use Omnipay\Common\CreditCard;

class RestGatewayTest extends GatewayTestCase
{
    protected $mockHandler;

    public $options;

    public function setUp():void
    {
        
        parent::setUp();

        $this->gateway = new RestGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setToken('token123');
        $this->gateway->setTokenExpires(time()+600);

    }
    
    public function testPurchasexx()
    {
        $this->gateway->setToken('token123');
        $this->gateway->setTokenExpires(time()+600);
        // Create an instance of the gateway
        //$this->gateway = new RestGateway($client);

        // Set necessary parameters for the gateway
        
        $this->gateway->setSecret('Your-API-key');
        $this->gateway->setTestMode(true);

         // Perform the request
         $this->setMockHttpResponse('RestPurchaseSuccess.txt');
         $request = $this->gateway->purchase(['amount' => '10.00', 'currency' => 'USD' ]);
         $response = $request->send();

        // Assert the response
        //$this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertTrue($response->isSuccessful());
        //var_dump($response);
        //exit(get_class($response));
        //$this->assertEquals('mocked data', $response->getData()['data']);
    }

    public function testFetchTransactionxx()
    {
        $this->gateway->setToken('token123');
        $this->gateway->setTokenExpires(time()+600);

         // Perform the request
         $this->setMockHttpResponse('RestFetchPurchaseSuccess.txt');
         
        $request = $this->gateway->fetchTransaction(array('transactionReference' => 'abc123'));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful());

    }

    public function testDeclinexx()
    {
        $this->gateway->setToken('token123');
        $this->gateway->setTokenExpires(time()+600);

         // Perform the request
         $this->setMockHttpResponse('RestDeclineTransaction.txt');
         $request = $this->gateway->purchase(['amount' => '10.00', 'currency' => 'USD' ]);
         $response = $request->send();
        //var_dump(get_class($response));

        $this->assertTrue($response->isSuccessful(), "declined transaction was marked as successful.");

    }

    public function testVoidxx()
    {
        $this->gateway->setToken('token123');
        $this->gateway->setTokenExpires(time()+600);
        $this->gateway->setTestMode(true);

        $request = $this->gateway->void(array(
            'transactionReference' => 'abc123'
        ));

        $this->assertInstanceOf('\Omnipay\PaidYET\Message\RestVoidRequest', $request);
        $this->assertSame('abc123', $request->getTransactionReference());
        $endPoint = $request->getEndpoint();
        $this->assertSame('https://api.sandbox-paidyet.com/v3/transaction/', $endPoint);
        $data = $request->getData();
        $this->assertNotEmpty($data);
    }

    public function testCreateCardxx()
    {
        $this->gateway->setToken('token123');
        $this->gateway->setTokenExpires(time()+600);
        $this->gateway->setTestMode(true);

        
        //Todo: combine first and last name fields and expiry fields
        $this->options = array(
            'amount' => '10.00',
            'card' => new CreditCard(array(
                'firstName' => 'Example',
                'lastName' => 'User',
                'number' => '4111111111111111',
                'expiryMonth' => '12',
                'expiryYear' => date('Y'),
                'cvv' => '123',
            )),
        );

        $this->setMockHttpResponse('RestCreateCardSuccess.txt');

        $response = $this->gateway->createCard($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('CARD-70E78145XN686604FKO3L6OQ', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testRefundxx()
    {
        $this->gateway->setTestMode(true);

        $request = $this->gateway->refund(array(
            'transactionID' => 'abc123',
            'amount' => 10.00,
        ));

        $this->assertInstanceOf('\Omnipay\PaidYET\Message\RestRefundRequest', $request);
        $this->assertSame('abc123', $request->getTransactionID());
        $endPoint = $request->getEndpoint();
        $this->assertSame('https://api.sandbox-paidyet.com/v3/transaction/abc123', $endPoint);
        $data = $request->getData();
        $this->assertNotEmpty($data);
    }

    public function testCapturexx()
    {
        $this->gateway->setTestMode(true);

        $request = $this->gateway->capture(array(
            'transactionID' => 'abc123',
            'amount' => 10.00,
        ));

        $this->assertInstanceOf('\Omnipay\PaidYET\Message\RestCaptureRequest', $request);
        $this->assertSame('abc123', $request->getTransactionID());
        $endPoint = $request->getEndpoint();
        $this->assertSame('https://api.sandbox-paidyet.com/v3/transaction/abc123', $endPoint);
        $data = $request->getData();
        $this->assertNotEmpty($data);
    }
}