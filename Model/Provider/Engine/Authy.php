<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model\Provider\Engine;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy as MspAuthy;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy\Service as MspAuthyService;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy\OneTouch as MspOneTouch;
use MSP\TwoFactorAuth\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

class Authy implements EngineInterface
{
    /**
     * Enabled for customer XML Path
     */
    const XML_PATH_ENABLED_CUSTOMER = 'msp_securitysuite_twofactorauth/authy/enabled_customer';

    /**
     * Key for customer config
     */
    const CONFIG_CUSTOMER_KEY = 'customer';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    private $customerConfigManager;

    private $curlFactory;

    private $json;

    private $dateTime;

    private $countryCollectionFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerConfigManagerInterface $customerConfigManager,
        CurlFactory $curlFactory,
        Json $json,
        DateTime $dateTime,
        CountryCollectionFactory $countryCollectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerConfigManager = $customerConfigManager;
        $this->curlFactory = $curlFactory;
        $this->json = $json;
        $this->dateTime = $dateTime;
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    public function verify(CustomerInterface $customer, DataObject $request)
    {
        $code = $request->getData('tfa_code');
        if (!preg_match('/^\w+$/', $code)) {
            throw new LocalizedException(__('Invalid code format'));
        }

        $providerInfo = $this->customerConfigManager->getProviderConfig($customer->getId(),$this->getCode());
        if (!isset($providerInfo[static::CONFIG_CUSTOMER_KEY])) {
            throw new LocalizedException(__('Missing customer information'));
        }

        $url = $this->getProtectedApiEndpoint('verify/' . $code . '/' . $providerInfo[static::CONFIG_CUSTOMER_KEY]);
        //@todo try catch
        $response = $this->makeApiRequest($url, [], 'GET');

        return true;
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled()
    {
        return
            !!$this->scopeConfig->getValue(MspAuthy::XML_PATH_ENABLED) &&
            !!$this->scopeConfig->getValue(static::XML_PATH_ENABLED_CUSTOMER) &&
            !!$this->getApiKey();
    }

    private function requestToken(CustomerInterface $customer, $via)
    {
        if (!in_array($via, ['call', 'sms'])) {
            throw new LocalizedException(__('Unsupported via method'));
        }

        $providerInfo = $this->customerConfigManager->getProviderConfig($customer->getId(), $this->getCode());
        if (!isset($providerInfo[static::CONFIG_CUSTOMER_KEY])) {
            throw new LocalizedException(__('Missing customer information'));
        }

        $url = $this->getProtectedApiEndpoint('' . $via . '/' . $providerInfo[static::CONFIG_CUSTOMER_KEY]) . '?force=true';
        //@todo try catch
        $response = $this->makeApiRequest($url, [], 'GET');

        return true;
    }

    public function enroll(CustomerInterface $customer)
    {
        $providerInfo = $this->customerConfigManager->getProviderConfig($customer->getId(), $this->getCode());
        if (!isset($providerInfo['country_code'])) {
            throw new LocalizedException(__('Missing phone information'));
        }

        $url = $this->getProtectedApiEndpoint('users/new');

        $data = [
            'user[email]' => $customer->getEmail(),
            'user[cellphone]' => $providerInfo['phone_number'],
            'user[country_code]' => $providerInfo['country_code'],
        ];

        $response = $this->makeApiRequest($url, $data);

        $providerInfo[static::CONFIG_CUSTOMER_KEY] =  $response['user']['id'];
        $this->customerConfigManager->setProviderConfig($customer->getId(), $this->getCode(), $providerInfo);

        return true;
    }

    public function verifyEnroll(CustomerInterface $customer, DataObject $request)
    {
        $providerInfo = $this->customerConfigManager->getProviderConfig($customer->getId(), $this->getCode());
        if (!isset($providerInfo['country_code'])) {
            throw new LocalizedException(__('Missing verify request information'));
        }

        $url = $this->service->getProtectedApiEndpoint('phones/verification/check');
        $data =  [
            'country_code' => $providerInfo['country_code'],
            'phone_number' => $providerInfo['phone_number'],
            'verification_code' => $verificationCode,
        ];
        $response = $this->makeApiRequest($url, $data, 'GET');

        $providerInfo['phone_confirmed'] = true;
        $this->customerConfigManager->setProviderConfig($customer->getId(), $this->getCode(), $providerInfo);

        return true;
    }

    public function requestEnroll(CustomerInterface $customer, $country, $phoneNumber, $method, &$response)
    {
        $url = $this->service->getProtectedApiEndpoint('phones/verification/start');
        $data = [
            'via' => $method,
            'country_code' => $country,
            'phone_number' => $phoneNumber
        ];
        $response = $this->makeApiRequest($url, $data);

        $this->customerConfigManager->setProviderConfig($customer->getId(), $this->getCode(), [
            'country_code' => $country,
            'phone_number' => $phoneNumber,
            'carrier' => $response['carrier'],
            'mobile' => $response['is_cellphone'],
            'verify' => [
                'uuid' => $response['uuid'],
                'via' => $method,
                'expires' => $this->dateTime->timestamp() + $response['seconds_to_expire'],
                'seconds_to_expire' => $response['seconds_to_expire'],
                'message' => $response['message'],
            ],
            'phone_confirmed' => false,
        ]);

        return true;
    }

    public function requestOneTouch(CustomerInterface $customer)
    {
        $providerInfo = $this->customerConfigManager->getProviderConfig($customer->getId(), $this->getCode());
        if (!isset($providerInfo[static::CONFIG_CUSTOMER_KEY])) {
            throw new LocalizedException(__('Missing customer information'));
        }

        $url = $this->getOneTouchApiEndpoint('users/' . $providerInfo[static::CONFIG_CUSTOMER_KEY] . '/approval_requests');

        $data = [
            'message' => $this->scopeConfig->getValue(MspOneTouch::XML_PATH_ONETOUCH_MESSAGE),
//            'details[URL]' => $this->storeManager->getStore()->getBaseUrl(),
            'details[User]' => $customer->getLastname().$customer->getLastname(),
            'details[Email]' => $customer->getEmail(),
            'seconds_to_expire' => 300,
        ];
        $response = $this->makeApiRequest($url, $data);
        $providerInfo['pending_approval'] =  $response['approval_request']['uuid'];
        $this->customerConfigManager->setProviderConfig($customer->getId(), $this->getCode(), $providerInfo);
        return true;
    }

    public function verifyOneTouch(CustomerInterface $customer)
    {
        $providerInfo = $this->customerConfigManager->getProviderConfig($customer->getId(), $this->getCode());
        if (!isset($providerInfo[static::CONFIG_CUSTOMER_KEY])) {
            throw new LocalizedException(__('Missing customer information'));
        }

        if (!isset($providerInfo['pending_approval'])) {
            throw new LocalizedException(__('No approval requests for this customer'));
        }

        $approvalCode = $providerInfo['pending_approval'];

        if (!preg_match('/^\w[\w\-]+\w$/', $approvalCode)) {
            throw new LocalizedException(__('Invalid approval code'));
        }

        $url = $this->service->getOneTouchApiEndpoint('approval_requests/' . $approvalCode);

        $times = 10;

        for ($i=0; $i<$times; $i++) {

            $response = $this->makeApiRequest($url, [], 'GET');

            $status = $response['approval_request']['status'];
            if ($status == 'pending') {
                // @codingStandardsIgnoreStart
                sleep(1); // I know... but it is the only option I have here
                // @codingStandardsIgnoreEnd
                continue;
            }

            if ($status == 'approved') {
                return $status;
            }

            return $status;
        }

        return 'retry';
    }

    private function makeApiRequest(string $url, $data = [], $type = 'POST')
    {
        /** @var \Magento\Framework\HTTP\Client\Curl $curl */
        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->getApiKey());
        if ($type == 'POST') {
            $curl->post($url, $data);
        } else {
            if (count($data)>0) {
                $url = $url. '?' . http_build_query($data);
            }
            $curl->get($url);
        }
        $response = $this->json->unserialize($curl->getBody());

        if ($response === false) {
            throw new LocalizedException(__('Invalid authy webservice response'));
        }

        if (!isset($response['success']) || !$response['success']) {
            throw new LocalizedException(__($response['message']));
        }

        return $response;
    }

    private function getApiKey(): ?string
    {
        return $this->scopeConfig->getValue(MspAuthyService::XML_PATH_API_KEY);
    }

    private function getProtectedApiEndpoint(string $path): string
    {
        return MspAuthyService::AUTHY_BASE_ENDPOINT . 'protected/json/' . $path;
    }

    private function getOneTouchApiEndpoint(string $path): string
    {
        return MspAuthyService::AUTHY_BASE_ENDPOINT . 'onetouch/json/' . $path;
    }

    /**
     * Get a country list
     * return array
     */
    private function getCountriesList(): array
    {
        $countries = [];
        $countriesList = $this->countryCollectionFactory->create()->addOrder('name', 'asc')->getItems();
        /** @var \MSP\TwoFactorAuth\Api\Data\CountryInterface $country */
        foreach ($countriesList as $country) {
            $countries[] = [
                'dial_code' => $country->getDialCode(),
                'name' => $country->getName(),
            ];
        }
        return $countries;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return MspAuthy::CODE;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return array
     */
    public function getAdditionalConfig(CustomerInterface $customer): array
    {
        return ['countryList' => $this->getCountriesList()];
    }
}
