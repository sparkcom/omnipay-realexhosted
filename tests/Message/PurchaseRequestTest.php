<?php

namespace Omnipay\Realexhosted\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    /** @var  PurchaseRequest */
    protected $request;

    public function setUp()
    {
        parent::setUp();
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testGetData()
    {
        $datetime = new \DateTime();

        $this->request->initialize(
            [
                'merchantId' => 'MyPSPID',
                'sharedSecret' => 'Mysecretsig1875!?',
                'transactionReference' => '1234',
                'amount' => 15.00,
                'currency' => 'EUR',
                'timestamp' => $datetime,
            ]
        );
        $data = $this->request->getData();
        $fields = [
            'MERCHANT_ID' => 'MyPSPID',
            'ORDER_ID' => '1234',
            'AMOUNT' => 1500,
            'CURRENCY' => 'EUR',
            'TIMESTAMP' => $datetime->format('Ymdhis'),
        ];

        foreach ($fields as $key => $value) {
            $this->assertEquals($value, $data[$key], 'Key: ' . $key . ' not found in the data');
        }
        $this->assertArrayNotHasKey('SHAREDSECRET', $data);
        $this->assertArrayHasKey('SHA1HASH', $data);
    }

}
