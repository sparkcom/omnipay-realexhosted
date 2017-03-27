<?php

namespace Omnipay\Realexhosted\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Tests\TestCase;

class PurchaseResponseTest extends TestCase
{
    /** @var  PurchaseRequest */
    protected $request;

    public function setUp()
    {
        parent::setUp();
        $datetime = new \DateTime();

        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
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
    }

    public function testLiveEndpoint()
    {
        $response = $this->request->send();
        $this->assertTrue($response->isRedirect());
        $this->assertInstanceOf(RedirectResponseInterface::class, $response);
        $this->assertNotContains('sandbox', $response->getRedirectUrl());
    }

    public function testTestEndpoint()
    {
        $this->request->setTestMode(true);
        $response = $this->request->send();
        $this->assertTrue($response->isRedirect());
        $this->assertInstanceOf(RedirectResponseInterface::class, $response);
        $this->assertContains('sandbox', $response->getRedirectUrl());
    }
}
