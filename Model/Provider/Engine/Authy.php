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
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;
use Magento\TwoFactorAuth\Model\Provider\Engine\Authy as MagentoAuthy;
use Magento\TwoFactorAuth\Model\Provider\Engine\Authy\Service as MagentoAuthyService;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TwoFactorAuth\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

/**
 * Class Authy
 * Authy Provider Engine
 */
class Authy implements EngineInterface
{
    /**
     * Enabled for customer XML Path
     */
    public const XML_PATH_ENABLED_CUSTOMER = 'twofactorauth/authy/enabled_customer';

    /**
     * Key for customer id field
     */
    public const CONFIG_CUSTOMER_KEY = 'customer';

    /**
     * Key for phone confirmation field
     */
    public const CONFIG_PHONE_CONFIRMED_KEY = 'phone_confirmed';

    /**
     * Key for customer approval field
     */
    public const CONFIG_PENDING_APPROVAL_KEY = 'pending_approval';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CustomerConfigManagerInterface
     */
    private $customerConfigManager;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var CountryCollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * Authy constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerConfigManagerInterface $customerConfigManager
     * @param CurlFactory $curlFactory
     * @param Json $json
     * @param CountryCollectionFactory $countryCollectionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerConfigManagerInterface $customerConfigManager,
        CurlFactory $curlFactory,
        Json $json,
        CountryCollectionFactory $countryCollectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerConfigManager = $customerConfigManager;
        $this->curlFactory = $curlFactory;
        $this->json = $json;
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * Verify
     *
     * @param CustomerInterface $customer
     * @param DataObject $request
     *
     * @return bool
     * @throws LocalizedException
     */
    public function verify(CustomerInterface $customer, DataObject $request): bool
    {
        $code = $request->getData('tfa_code');
        if (!preg_match('/^\w+$/', $code)) {
            throw new LocalizedException(__('Invalid code format'));
        }
        $providerInfo = $this->customerConfigManager->getProviderConfig((int) $customer->getId(), $this->getCode());
        try {
            if (isset($providerInfo[static::CONFIG_PHONE_CONFIRMED_KEY])
                && $providerInfo[static::CONFIG_PHONE_CONFIRMED_KEY]
            ) {
                $this->authenticate($customer, $providerInfo, $code);
            } else {
                $this->verifyAndEnroll($customer, $providerInfo, $code);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Auth
     *
     * @param CustomerInterface $customer
     * @param array $providerInfo
     * @param string $code
     *
     * @throws LocalizedException
     */
    private function authenticate(CustomerInterface $customer, array $providerInfo, string $code)
    {
        if (!isset($providerInfo[static::CONFIG_CUSTOMER_KEY])) {
            throw new LocalizedException(__('Missing customer information'));
        }

        if (isset($providerInfo[static::CONFIG_PENDING_APPROVAL_KEY])) {
            $this->verifyOneTouch($customer, $providerInfo);
        } else {
            $url = $this->getProtectedApiEndpoint('verify/' . $code . '/' . $providerInfo[static::CONFIG_CUSTOMER_KEY]);
            $this->makeApiRequest($url, [], 'GET');
        }
    }

    /**
     * Return true if this provider has been enabled by admin
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return
            !!$this->scopeConfig->getValue(static::XML_PATH_ENABLED_CUSTOMER) &&
            !!$this->getApiKey();
    }

    /**
     * Request a token
     *
     * @param CustomerInterface $customer
     * @param string $method
     * @param string|null $approvalCode
     *
     * @return array|bool|float|int|mixed|string
     * @throws LocalizedException
     */
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

    /**
     * Verify and enroll
     *
     * @param CustomerInterface $customer
     * @param array|null $providerInfo
     * @param string $code
     *
     * @throws LocalizedException
     */
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
        $providerInfo[static::CONFIG_PHONE_CONFIRMED_KEY] = true;
        $this->customerConfigManager->setProviderConfig((int) $customer->getId(), $this->getCode(), $providerInfo);
    }

    /**
     * Request Enroll
     *
     * @param CustomerInterface $customer
     * @param string $country
     * @param string $phoneNumber
     * @param string $method
     *
     * @return array
     * @throws LocalizedException
     */
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
                'seconds_to_expire' => $response['seconds_to_expire'],
                'message' => $response['message'],
            ],
            static::CONFIG_PHONE_CONFIRMED_KEY => false,
        ]);
        return $response;
    }

    /**
     * Request one touch
     *
     * @param CustomerInterface $customer
     * @param array $providerInfo
     * @param string $approvalCode
     *
     * @return array
     * @throws LocalizedException
     */
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

    /**
     * Validate One touch
     *
     * @param CustomerInterface $customer
     * @param array $providerInfo
     * @param string $approvalCode
     *
     * @return string
     * @throws LocalizedException
     */
    private function validateOneTouch(CustomerInterface $customer, array $providerInfo, string $approvalCode): string
    {
        if (!preg_match('/^\w[\w\-]+\w$/', $approvalCode)) {
            throw new LocalizedException(__('Invalid approval code'));
        }
        $url = $this->getOneTouchApiEndpoint('approval_requests/' . $approvalCode);
        $response = $this->makeApiRequest($url, [], 'GET');
        $status = $response['approval_request']['status'];
        if ($status == 'approved' && !isset($providerInfo[static::CONFIG_PENDING_APPROVAL_KEY])) {
            $providerInfo[static::CONFIG_PENDING_APPROVAL_KEY] =  $approvalCode;
            $this->customerConfigManager->setProviderConfig((int) $customer->getId(), $this->getCode(), $providerInfo);
        }

        return $status;
    }

    /**
     * Verify One touch
     *
     * @param CustomerInterface $customer
     * @param array $providerInfo
     *
     * @throws LocalizedException
     */
    private function verifyOneTouch(CustomerInterface $customer, array $providerInfo)
    {
        $approvalCode = $providerInfo[static::CONFIG_PENDING_APPROVAL_KEY];
        $status = $this->validateOneTouch($customer, $providerInfo, $approvalCode);

        if ($status == 'approved' || $status == 'denied') {
            unset($providerInfo[static::CONFIG_PENDING_APPROVAL_KEY]);
            $this->customerConfigManager->setProviderConfig((int) $customer->getId(), $this->getCode(), $providerInfo);
        } elseif ($status == 'denied') {
            throw new LocalizedException(__('Authentication denied.'));
        }
    }

    /**
     * Api request
     *
     * @param string $url
     * @param array $data
     * @param string $type
     *
     * @return array|bool|float|int|mixed|string
     * @throws LocalizedException
     */
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

    /**
     * Get api key
     *
     * @return string|null
     */
    private function getApiKey(): ?string
    {
        return $this->scopeConfig->getValue(MagentoAuthyService::XML_PATH_API_KEY);
    }

    /**
     * Get api endpoint
     *
     * @param string $path
     *
     * @return string
     */
    private function getProtectedApiEndpoint(string $path): string
    {
        return MagentoAuthyService::AUTHY_BASE_ENDPOINT . 'protected/json/' . $path;
    }

    /**
     * Get one touch api
     *
     * @param string $path
     *
     * @return string
     */
    private function getOneTouchApiEndpoint(string $path): string
    {
        return MagentoAuthyService::AUTHY_BASE_ENDPOINT . 'onetouch/json/' . $path;
    }

    /**
     * Get country list
     *
     * @return array
     */
    private function getCountriesList(): array
    {
        $countries = [];
        $countriesList = $this->countryCollectionFactory->create()->addOrder('name', 'asc')->getItems();
        /** @var \Magento\TwoFactorAuth\Api\Data\CountryInterface $country */
        foreach ($countriesList as $country) {
            $countries[] = [
                'dial_code' => $country->getDialCode(),
                'name' => $country->getName(),
            ];
        }
        return $countries;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string
    {
        return MagentoAuthy::CODE;
    }

    /**
     * Get additional config
     *
     * @param CustomerInterface $customer
     *
     * @return array
     */
    public function getAdditionalConfig(CustomerInterface $customer): array
    {
        $providerInfo = $this->customerConfigManager->getProviderConfig((int) $customer->getId(), $this->getCode());
        $phoneConfirmed = (isset($providerInfo[static::CONFIG_PHONE_CONFIRMED_KEY])
            ? $providerInfo[static::CONFIG_PHONE_CONFIRMED_KEY]
            : false
        );
        return ['countryList' => $this->getCountriesList(), 'phoneConfirmed' => $phoneConfirmed];
    }
}
