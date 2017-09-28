<?php
// ToDo: don't forget
// The subscription response will return the recipient-ID of Inxmail. This ID should be saved.
namespace Flagbit\Inxmail\Model\Request;

use Flagbit\Inxmail\Helper\Config;
use Flagbit\Inxmail\Model\Api\ApiClientFactory;

class RequestSubscriptionRecipients extends AbstractRequest
{
    const REQUEST_PATH = 'events/subscriptions/';

    const REQUEST_PARAMETER_ID = 'id';

    const REQUEST_LIST_ID = 'listId';
    const REQUEST_START_DATE = 'startDate';
    const REQUEST_END_DATE = 'endDate';
    const REQUEST_TYPES = 'types';

    const EVENTS_SUCCESSFUL = array(
        'PENDING_SUBSCRIPTION',
        'PENDING_SUBSCRIPTION_DONE',
        'VERIFIED_SUBSCRIPTION',
        'MANUAL_SUBSCRIPTION',
        'DUPLICATE_SUBSCRIPTION'
    );

    const EVENTS_FAIL = array(
        'SUBSCRIPTION_TIMED_OUT',
        'SUBSCRIPTION_ID_NOT_VALID',
        'SUBSCRIPTION_EMAIL_MISSMATCH',
        'BLACKLISTED',
        'INVALID_ADDRESS_ERROR',
        'SUBSCRIPTION_VERIFICATION_BOUNCED',
        'SUBSCRIPTION_INTERNAL_ERROR'
    );

    public function __construct(Config $config, ApiClientFactory $factory)
    {
        parent::__construct($config, $factory);
    }

    public function sendRequest()
    {
        $client = $this->getApiClient();
        $client->setCredentials($this->getCredentials());
        $client->setRequestPath(self::REQUEST_PATH.$this->_requestParam);
        $client->setRequestMethod(\Zend_Http_Client::GET);
        $client->setRequestUrl($this->_systemConfig->getApiUrl());
        // ToDo: remove dryrun
        $this->_response = $client->getResource('','',null,null, false);

        return json_decode($this->_response, true);
    }

//    public function writeRequest()
//    {
//        if (!empty($this->postData)) {
//            $client = $this->getApiClient();
//            $client->setCredentials($this->getCredentials());
//            $client->setRequestPath(self::REQUEST_PATH . $this->_requestParam);
//            $client->setRequestMethod(\Zend_Http_Client::POST);
//            $client->setRequestUrl($this->_systemConfig->getApiUrl());
//            $client->setPostData( is_array($this->_requestData) ? json_encode($this->_requestData) : $this->requestData);
//            // ToDo: remove dryrun
//            $this->_response = $client->postResource('', '', null, null, '');
//
//            return $client->getResponseStatusCode();
//        }
//
//        return false;
//    }
    public function writeRequest()
    {
        if (!empty($this->_requestData) || !empty($this->_file)) {
            $client = $this->getApiClient();
            $client->setCredentials($this->getCredentials());
            $client->setRequestPath(self::REQUEST_PATH.$this->_requestParam);
            $client->setRequestMethod(\Zend_Http_Client::POST);
//            $header = array(
//                'Accept: application/hal+json,application/problem+json',
//                'Content-Disposition: form-data; name="file"; filename="datafile.csv"',
//                'Content-Type: multipart/form-data; boundary=----Inxmail'
//            );
            $client->setHeader();
            $client->setRequestUrl($this->_systemConfig->getApiUrl());
//            $client->setPostData( 'file='. (is_array($this->_requestData) ? implode(PHP_EOL, $this->_requestData) : $this->_requestData));
            $client->setPostData( $this->_requestData);
            $this->_response = $client->postResource('', '', null, null, '', false);

            var_dump($this->_response);
            return $client->getResponseStatusCode();
        }

        return false;
    }

    public function requestWithAllAttributes(int $id) {
        // ToDo: remove me
        // {"listId":4,"email":"peter.lelewel@flagbit.de"}
        $this->_requestParam = implode('/',explode('/',$this->_requestParam));
        $this->_requestParam .= $id.self::REQUEST_ALL_ATTRIBUTES;
        return $this->sendRequest();
    }

    public function requestWithAttributes(int $id, array $attributes) {
        $this->_requestParam = implode('/',explode('/',$this->_requestParam));
        $this->_requestParam .= $id.'?'.implode('&',$attributes);
        return $this->sendRequest();
    }

    public function getStandardOptions(): array
    {
        return array(
            'listId' => 0,
            'email' => '',
            'attributes' => array ()
        );
    }

    public static function getStandardAttributes(): array
    {
        return array(
            'email' => 'email',
            'Vorname' => 'firstName',
            'Nachname' => 'lastName',
            'magentoSubscriberId' => 'subscriberId',
            'magentoSubscriberToken' => 'subscriberToken',
            'magentoWebsiteName' => 'websiteName',
            'magentoWebsiteId' => 'websiteId',
            'magentoStoreName' => 'storeName',
//            'magentoStoreId' => 'storeId',
            'magentoStoreViewName' => 'storeViewName',
//            'magentoStoreViewId' => 'storeViewId',
            'Geburtsdatum' => 'birthday',
//            'magentoCustomerGroup' => 'group',
            'Geschlecht' => 'gender'
        );
    }
}
