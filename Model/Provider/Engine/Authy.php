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

    public function verify(CustomerInterface $customer, DataObject $request): bool
    {
        $code = $request->getData('tfa_code');
        if (!preg_match('/^\w+$/', $code)) {
            throw new LocalizedException(__('Invalid code format'));
        }
        $providerInfo = $this->customerConfigManager->getProviderConfig((int) $customer->getId(),$this->getCode());
        try {
            if (isset($providerInfo['phone_confirmed']) && $providerInfo['phone_confirmed']) {
                $this->authenticate($customer, $providerInfo, $code);
            } else {
                $this->verifyAndEnroll($customer, $providerInfo, $code);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    private function authenticate(CustomerInterface $customer, array $providerInfo, string $code)
    {
        if (!isset($providerInfo[static::CONFIG_CUSTOMER_KEY])) {
            throw new LocalizedException(__('Missing customer information'));
        }
        if (isset($providerInfo['pending_approval'])) {
            $this->verifyOneTouch($customer, $providerInfo);
        } else {
            $url = $this->getProtectedApiEndpoint('verify/' . $code . '/' . $providerInfo[static::CONFIG_CUSTOMER_KEY]);
            $response = $this->makeApiRequest($url, [], 'GET');
        }


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

    public function requestToken(CustomerInterface $customer, string $method, ?string $approvalCode)
    {
        if (!in_array($method, ['call', 'sms', 'onetouch'])) {
            throw new LocalizedException(__('Unsupported method'));
        }

        $providerInfo = $this->customerConfigManager->getProviderConfig((int) $customer->getId(), $this->getCode());
        if (!isset($providerInfo[static::CONFIG_CUSTOMER_KEY])) {
            throw new LocalizedException(__('Missing customer information'));
        }

        if ($method == 'onetouch') {
            $response = $this->requestOneTouch($customer, $providerInfo, $approvalCode);
        } else {
            $url = $this->getProtectedApiEndpoint('' . $method . '/' . $providerInfo[static::CONFIG_CUSTOMER_KEY]);
            $response = $this->makeApiRequest($url. '?force=true', [], 'GET');
        }
        return $response;
    }

    private function verifyAndEnroll(CustomerInterface $customer, ?array $providerInfo, string $code)
    {
        if (!isset($providerInfo['country_code'])) {
            throw new LocalizedException(__('Missing country information'));
        }

        $checkUrl = $this->getProtectedApiEndpoint('phones/verification/check');
        $enrollUrl = $this->getProtectedApiEndpoint('users/new');

        $data =  [
            'country_code' => $providerInfo['country_code'],
            'phone_number' => $providerInfo['phone_number'],
            'verification_code' => $code,
        ];
        $response = $this->makeApiRequest($checkUrl, $data, 'GET');

        $data = [
            'user[email]' => $customer->getEmail(),
            'user[cellphone]' => $providerInfo['phone_number'],
            'user[country_code]' => $providerInfo['country_code'],
        ];
        $response = $this->makeApiRequest($enrollUrl, $data);

        $providerInfo[static::CONFIG_CUSTOMER_KEY] =  $response['user']['id'];
        $providerInfo['phone_confirmed'] = true;
        $this->customerConfigManager->setProviderConfig((int) $customer->getId(), $this->getCode(), $providerInfo);
    }

    public function requestEnroll(
        CustomerInterface $customer,
        string $country,
        string $phoneNumber,
        string $method
    ): array {
        $url = $this->getProtectedApiEndpoint('phones/verification/start');
        $data = [
            'via' => $method,
            'country_code' => $country,
            'phone_number' => $phoneNumber
        ];
        $response = $this->makeApiRequest($url, $data);
        $this->customerConfigManager->setProviderConfig((int) $customer->getId(), $this->getCode(), [
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
        return $response;
    }

    private function requestOneTouch(CustomerInterface $customer, array $providerInfo, $approvalCode)
    {
        if ($approvalCode) {
            return [
                'code' => $approvalCode,
                'status' => $this->validateOneTouch($customer, $providerInfo, $approvalCode)
            ];
        }
        $url = $this->getOneTouchApiEndpoint(
            'users/' . $providerInfo[static::CONFIG_CUSTOMER_KEY] . '/approval_requests'
        );

        $data = [
            'message' => __('Login request')->getText(),
            'details[User]' => $customer->getLastname().' '.$customer->getLastname(),
            'details[Email]' => $customer->getEmail(),
            'seconds_to_expire' => 300,
        ];
        $response = $this->makeApiRequest($url, $data);
        return [
            'code' => $response['approval_request']['uuid'],
            'status' => $this->validateOneTouch($customer, $providerInfo, $response['approval_request']['uuid'])
        ];
    }

    private function validateOneTouch(CustomerInterface $customer, array $providerInfo, string $approvalCode): string
    {
        if (!preg_match('/^\w[\w\-]+\w$/', $approvalCode)) {
            throw new LocalizedException(__('Invalid approval code'));
        }
        $url = $this->getOneTouchApiEndpoint('approval_requests/' . $approvalCode);
        $response = $this->makeApiRequest($url, [], 'GET');
        $status = $response['approval_request']['status'];
        if ($status == 'approved' && !isset($providerInfo['pending_approval'])) {
            $providerInfo['pending_approval'] =  $approvalCode;
            $this->customerConfigManager->setProviderConfig((int) $customer->getId(), $this->getCode(), $providerInfo);
        }

        return $status;
    }

    private function verifyOneTouch(CustomerInterface $customer, array $providerInfo)
    {
        if (!isset($providerInfo['pending_approval'])) {
            throw new LocalizedException(__('No approval requests for this customer'));
        }

        $approvalCode = $providerInfo['pending_approval'];
        $status = $this->validateOneTouch($customer, $providerInfo, $approvalCode);
        if ($status == 'approved') {
            unset($providerInfo['pending_approval']);
            $this->customerConfigManager->setProviderConfig((int) $customer->getId(), $this->getCode(), $providerInfo);
        }
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
        $providerInfo = $this->customerConfigManager->getProviderConfig((int) $customer->getId(), $this->getCode());
        $phoneConfirmed = (isset($providerInfo['phone_confirmed']) ? $providerInfo['phone_confirmed'] : false);
        return ['countryList' => $this->getCountriesList(), 'phoneConfirmed' => $phoneConfirmed];
    }
}
