<?php

namespace Omnipay\Realexhosted;

use Omnipay\Common\AbstractGateway;
use Omnipay\Realexhosted\Message\PurchaseRequest;

/**
 * Realex Hosted Gateway
 */
class Gateway extends AbstractGateway
{

    public function getName()
    {
        return 'Realexhosted';
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return array(
            'merchantId' => '',
            'sharedSecret' => '',
            'testMode' => true,
        );
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($merchantId)
    {
        return $this->setParameter('merchantId', $merchantId);
    }

    public function getSharedSecret()
    {
        return $this->getParameter('sharedSecret');
    }

    public function setSharedSecret($sharedSecret)
    {
        return $this->setParameter('sharedSecret', $sharedSecret);
    }

    // This is the same in both instances since epay recommendes using the payment window.
    public function authorize(array $parameters = array())
    {
        return $this->purchase($parameters);
    }

    // This is the same in both instances since epay recommendes using the payment window.
    public function completeAuthorize(array $parameters = array())
    {
        return $this->purchase($parameters);
    }

    /**
     * @param array $parameters
     * @return PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Realexhosted\Message\PurchaseRequest', $parameters);
    }

}
