<?php

namespace Flagbit\Inxmail\Helper;

use \Flagbit\Inxmail\Model\Request;
use \Flagbit\Inxmail\Model\Request\RequestSubscriptionRecipients;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Customer\Model\ResourceModel\CustomerRepository;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Newsletter\Model\Subscriber;

/**
 * Class Config
 * @package Flagbit\Inxmail\Helper
 */
class SubscriptionData extends AbstractHelper{

    protected $_customerRepository;
    protected $_request;
    protected $_storeManager;

    /**
     * Config constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context,
        CustomerRepository $customerRepository,
        Request $request,
        StoreManagerInterface $storeManager
    )
    {
        $this->_customerRepository = $customerRepository;
        $this->_request = $request;
        $this->_storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     */
    public function getSubscriptionFields(Subscriber $subscriber): array
    {
        $data = $this->getSubscriptionStaticData($subscriber);
        $data = $this->cleanData($data);

        $map = $this->getMapping();

        $result = array();
        foreach ($map as $inxKey => $magKey) {
            $keys = array_keys($data);
            if (in_array($magKey, $keys)){
                $result[$inxKey] = $data[$magKey];
            }
        }

        return $result;
    }

    public function getMapping(): array
    {
        $map = RequestSubscriptionRecipients::getStandardAttributes();

        return $map;
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     */
    private function getSubscriptionStaticData(Subscriber $subscriber): array
    {

        $data = array();
        $data['subscriberId'] = $subscriber->getId();
        $data['status'] = $subscriber->getSubscriberStatus();
        $data['subscriberToken'] = $subscriber->getSubscriberConfirmCode();

        $customerId = $subscriber->getCustomerId();
        $customerData= $this->getCustomerData($customerId);

        $data['storeId'] = $subscriber->getStoreId();
        $storeData = $this->getStoreData($data['storeId']);
        return array_merge($data, $storeData, $customerData);
    }

    private function getStoreData(int $storeId): array
    {
        $data = array();

        $store = $this->_storeManager->getStore($storeId);
        $data['websiteId'] = $store->getWebsiteId();
        $website = $this->_storeManager->getWebsite($data['websiteId']);

        $data['storeName'] = $store->getName();
        $data['storeCode'] = $store->getCode();

        $data['websiteName'] = $website->getName();
        $storeView = $this->_storeManager->getDefaultStoreView();
        $data['storeViewName'] = $storeView->getName();
        $data['storeViewId'] = $storeView->getId();

        return $data;
    }

    private function getCustomerData(int $customerId): array
    {
        $data = array();
        if ($customerId > 0) {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->_customerRepository->getById($customerId);
            $data['firstName'] = $customer->getFirstname();
            $data['lastName'] = $customer->getLastname();
            $data['birthday'] = $customer->getDob();
            $data['gender'] = $customer->getGender();
            $data['group'] = $customer->getGroupId();
        }

        return $data;
    }

    private function cleanData(array $data): array
    {
        foreach ($data as $key => $value)
        {
            $arr = is_array($value);
            if (!$arr && !empty($value)) {
                $data[$key] = trim($value);
            } else if ($arr) {
                foreach ($value as $key2 => $value2) {
                    $data[$key][$key2] = empty($value2) ?? trim($value2);
                }
            }
        }

        return $data;
    }
}