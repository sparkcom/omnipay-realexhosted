<?php
namespace Omnipay\Realexhosted;

use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    /** @var  Gateway */
    protected $gateway;

    public function setUp()
    {
        parent::setUp();
        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase(
            array(
                'amount' => '10.01',
                'transactionReference' => 'N6qsk4kYRZihmPrTXWYS6g',
                'currency' => 'EUR',
                'merchantId' => 'Merchant ID',
                'timestamp' => '20170323165315',
                'account' => 'internet',
                'comment1' => 'Mobile Channel',
                'comment2' => 'Down Payment',
                'shipping_code' => 'E77|4QJ',
                'shipping_co' => 'GB',
                'billing_code' => 'R90|ZQ7',
                'billing_co' => 'GB',
                'cust_num' => '332a85b',
                'var_ref' => 'Invoice 7564a',
                'prod_id' => 'SKU1000054',
                'hpp_lang' => 'GB',
                'hpp_version' => '2',
                'CARD_PAYMENT_BUTTON' => 'Pay Now',
                'SUPPLEMENTARY_DATA' => 'Custom Value',
            )
        );
        $request->setSharedSecret('Shared Secret');
        $request->setNotifyUrl('https://www.example.com/responseUrl');
        $this->assertInstanceOf('Omnipay\Realexhosted\Message\PurchaseRequest', $request);
        $this->assertSame('10.01', $request->getAmount());
        $expectedData = [
            "TIMESTAMP" => "20170323165315",
            "MERCHANT_ID" => "Merchant ID",
            "ACCOUNT" => "internet",
            "ORDER_ID" => "N6qsk4kYRZihmPrTXWYS6g",
            "AMOUNT" => "1001",
            "CURRENCY" => "EUR",
            "SHA1HASH" => "cecebef5a6302b780fc10edf2fb6fc7d1de168d3",
            "AUTO_SETTLE_FLAG" => "1",
            "COMMENT1" => "Mobile Channel",
            "COMMENT2" => "Down Payment",
            "SHIPPING_CODE" => "E77|4QJ",
            "SHIPPING_CO" => "GB",
            "BILLING_CODE" => "R90|ZQ7",
            "BILLING_CO" => "GB",
            "CUST_NUM" => "332a85b",
            "VAR_REF" => "Invoice 7564a",
            "PROD_ID" => "SKU1000054",
            "HPP_LANG" => "GB",
            "HPP_VERSION" => "2",
            "MERCHANT_RESPONSE_URL" => "https://www.example.com/responseUrl",
            "CARD_PAYMENT_BUTTON" => "Pay Now",
            "SUPPLEMENTARY_DATA" => "Custom Value",
        ];
        foreach ($request->getData() as $key => $value) {
            $this->assertEquals($expectedData[$key], $value, "Expected to see {$key} in the data.");
        }
    }
}
