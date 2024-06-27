<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Omnipay\Paidyet\RestGateway;
use Omnipay\Common\Http\Client as OmnipayClient;



class SimpleTest extends TestCase
{
    protected $mockHandler;
    
    public function testMockResponse()
    {
        // Create a mock response
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'success', 'data' => 'mocked data']))
        ]);


        // Create a handler stack with the mock handler
        $handlerStack = HandlerStack::create($mock);

        // Create a Guzzle client with the handler stack
        $client = new OmnipayClient(['handler' => $handlerStack]);

        // Create an instance of the gateway
        $gateway = new RestGateway($client);

        // Set necessary parameters for the gateway
        $gateway->setSecret('Your-API-key');
        $gateway->setTestMode(true);

         // Perform the request
         $request = $gateway->purchase(['amount' => '10.00', 'currency' => 'USD', ]);
         $response = $request->send();

         
        // Assert the response
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('mocked data', $response->getData()['data']);
    }
}