<?php

namespace Omnipay\Realexhosted\Message;

use CommerceGuys\Addressing\Country\CountryRepository;
use Omnipay\Common\Helper;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\ParameterBag;


/**
 * Realex Purchase Request
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

    public function setOrder($order)
    {
        return $this->setParameter('order', $order);
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

        $order = $data['order'];
        $billingInformation = $order->getBillingInformation();
        $shippingInformation = $order->getShippingInformation();

        $countryRepository = new CountryRepository();

        $billingCountryCode = strtolower($billingInformation->getCountryCode());
        if (empty($billingCountryCode)) {
            throw new \InvalidArgumentException('Unknown or empty billing country');
        }

        $shippingCountryCode = strtolower($shippingInformation->getCountryCode());
        if (empty($shippingCountryCode)) {
            throw new \InvalidArgumentException('Unknown or empty shipping country');
        }

        $billingCountry = $countryRepository->get($billingCountryCode);
        $shippingCountry = $countryRepository->get($shippingCountryCode);


        $data = array_change_key_case($data, CASE_UPPER);
        $data['AMOUNT'] = $this->getAmountInteger();
        $data['CURRENCY'] = $this->getCurrency();
        $data['ORDER_ID'] = $this->getTransactionReference();

        $data['MERCHANT_RESPONSE_URL'] = $this->getNotifyUrl();
        $data['AUTO_SETTLE_FLAG'] = 1;
        $data['MERCHANT_ID'] = $this->getMerchantId();
        $data['SHA1HASH'] = $this->generateHash($data);

        $data["HPP_VERSION"] = "2";
        $data["HPP_CHANNEL"] = "ECOM";

        // BEGIN: Mandatory SCA fields
        $data["HPP_CUSTOMER_EMAIL"] = $billingInformation->getEmail();
        $data["HPP_CUSTOMER_PHONENUMBER_MOBILE"] = $billingInformation->getPhone();

        $data["HPP_BILLING_STREET1"] = $billingInformation->getAddressLine1();
        $data["HPP_BILLING_STREET2"] = $billingInformation->getAddressLine2();
        $data["HPP_BILLING_STREET3"] = $billingInformation->getAddressLine2();
        $data["HPP_BILLING_CITY"] = $billingInformation->getLocality();
        $data["HPP_BILLING_POSTALCODE"] = $billingInformation->getPostalCode();
        $data["HPP_BILLING_COUNTRY"] = $billingCountry->getNumericCode();

        $data["HPP_SHIPPING_STREET1"] = $shippingInformation->getAddressLine1();
        $data["HPP_SHIPPING_STREET2"] = $shippingInformation->getAddressLine2();
        $data["HPP_SHIPPING_STREET3"] = $shippingInformation->getAddressLine2();
        $data["HPP_SHIPPING_CITY"] = $shippingInformation->getLocality();
        $data["HPP_SHIPPING_STATE"] = $shippingInformation->getAdministrativeArea();
        $data["HPP_SHIPPING_POSTALCODE"] = $shippingInformation->getPostalCode();
        $data["HPP_SHIPPING_COUNTRY"] = $shippingCountry->getNumericCode();
        // END: Mandatory SCA fields.

        $data["HPP_ADDRESS_MATCH_INDICATOR"] = "FALSE";
        $data["HPP_CHALLENGE_REQUEST_INDICATOR"] = "NO_PREFERENCE";

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
