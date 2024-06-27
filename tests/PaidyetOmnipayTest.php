<?php

use PHPUnit\Framework\TestCase;
use Omnipay\Omnipay;
use Omnipay\PaidYET\RestGateway;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Omnipay\Common\Http\Client as OmnipayClient;


class PaidyetOmnipayTest extends TestCase
{
    /** @var RestGateway */
    public $gateway;

    protected $mockHandler;

    protected function setUp(): void
    {
         // Create a mock handler and queue responses
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);


        // Create the Omnipay client with the mocked HTTP client
        $omnipayClient = new OmnipayClient($httpClient);
        $this->gateway = Omnipay::create('PaidYET_Rest', $omnipayClient);
        $this->gateway->setSecret('your-api-key');

    }

    public function testPurchaseSuccess()
    {
        $this->gateway->setTestMode(true);

        // Queue a successful response
        $this->mockHandler->append(new Response(200, [], json_encode([
            'result' => 'success',
            'amount' => '10.00',
            'token' => 'sample_token'
        ])));

        try {
            $response = $this->gateway->purchase([
                'amount' => '10.00',
                'currency' => 'USD',
                'credit_card' => [
                    'number' => '4242424242424242',
                    'exp' => '06/2030',
                    'cvv' => '123',
                    'state' => 'CA'
                ]
            ])->send();
            
            $this->assertTrue($response->isSuccessful());

            //$data = json_decode($response->getData(), false); // Decode JSON response into an object
            $data = json_decode($response->getData()->getContents(), false);

            // if (json_last_error() !== JSON_ERROR_NONE) {
            //     throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            // }

            // if (isset($data->result) && $data->result === 'success') {
            //     $this->assertTrue(true);
            //     $this->assertEquals('10.00', $data->amount);
            //     //$this->assertEquals('sample_token', $data->token);
            // } elseif (isset($data->result) && $data->result === 'redirect') {
            //     // Handle redirection if necessary
            //     $this->markTestIncomplete('Redirection required.');
            // } else {
            //     $this->fail('Payment failed: ' . ($data->message ?? 'Unknown error'));
            // }
        } catch (\Exception $e) {
            $this->fail('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function testPurchaseFailure()
    {
        $this->gateway->setTestMode(true);

        // Queue a failure response
        $this->mockHandler->append(new Response(200, [], json_encode([
            'result' => 'failure',
            'message' => 'Card declined'
        ])));

        try {
            $response = $this->gateway->purchase([
                'amount' => '10.00',
                'currency' => 'USD',
                'card' => [
                    'number' => '4000000000000002', // This card number should trigger a failure
                    'expiryMonth' => '6',
                    'expiryYear' => '2030',
                    'cvv' => '123'
                ]
            ])->send();

            $data = json_decode($response->getData(), false); // Decode JSON response into an object

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            if (isset($data->result) && $data->result === 'success') {
                $this->fail('Expected payment to fail, but it succeeded.');
            } elseif (isset($data->result) && $data->result === 'redirect') {
                // Handle redirection if necessary
                $this->markTestIncomplete('Redirection required.');
            } else {
                $this->assertFalse($data->result === 'success');
                $this->assertNotEmpty($data->message ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            $this->fail('An unexpected error occurred: ' . $e->getMessage());
        }
    }
}