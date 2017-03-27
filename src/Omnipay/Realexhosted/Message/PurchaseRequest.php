<?php

namespace Omnipay\Realexhosted\Message;

use Omnipay\Common\Helper;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\ParameterBag;


/**
 * Epay Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{
    public function getMerchantId()
    {
        return $this->parameters->get('merchantId');
    }

    public function setMerchantId($merchantId)
    {
        return $this->setParameter('merchantId', $merchantId);
    }

    public function getSharedSecret()
    {
        return $this->getParameter('sharedSecret');
    }

    public function setSharedSecret($secret)
    {
        return $this->setParameter('sharedSecret', $secret);
    }

    /**
     * @param \DateTime|integer|string $timestamp
     */
    public function setTimestamp($timestamp)
    {
        if (is_string($timestamp)) {
            $this->setParameter('timestamp', $timestamp);
        } else {
            if (is_integer($timestamp)) {
                // Assume it's a unix timestamp
                $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));
                $dateTime->setTimestamp($timestamp);
            } else {
                if (!$timestamp instanceof \DateTime) {
                    throw new \InvalidArgumentException('Expected a string, a \DateTime instance or an integer');
                }
                $dateTime = $timestamp;
            }
            $this->setParameter('timestamp', $dateTime->format('Ymdhis'));
        }
        return $this;
    }

    public function getData()
    {
        $this->validate('merchantId', 'amount', 'transactionReference', 'currency', 'sharedSecret', 'timestamp');

        $data = $this->parameters->all();
        $data = array_change_key_case($data, CASE_UPPER);
        $data['AMOUNT'] = $this->getAmountInteger();
        $data['CURRENCY'] = $this->getCurrency();
        $data['ORDER_ID'] = $this->getTransactionReference();

        $data['MERCHANT_RESPONSE_URL'] = $this->getNotifyUrl();
        $data['AUTO_SETTLE_FLAG'] = 1;
        $data['MERCHANT_ID'] = $this->getMerchantId();
        $data['SHA1HASH'] = $this->generateHash($data);

        unset($data['NOTIFYURL']);
        unset($data['TRANSACTIONREFERENCE']);
        unset($data['SHAREDSECRET']);
        unset($data['MERCHANTID']);
        unset($data['TESTMODE']);

        return $data;
    }

    protected function get($key)
    {
        $getName = 'get' . ucfirst($key);
        if (!method_exists($this, $getName)) {
            return $this->getParameter($key);
        }
        return $this->{$getName}();
    }

    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    /**
     * Send the request
     *
     * @return ResponseInterface
     */
    public function send()
    {
        return $this->sendData($this->getData());
    }

    protected function generateHash($data)
    {
        $hashString = "TIMESTAMP.MERCHANT_ID.ORDER_ID.AMOUNT.CURRENCY";


        return sha1(sha1(strtr($hashString, $data)) . ".{$this->getSharedSecret()}");
    }
}
