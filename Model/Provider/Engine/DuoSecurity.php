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
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;
use Magento\TwoFactorAuth\Model\Provider\Engine\DuoSecurity as MagentoDuoSecurity;

/**
 * Class DuoSecurity
 * DuoSecurity engine
 */
class DuoSecurity implements EngineInterface
{
    /**
     * Enabled for customer XML Path
     */
    public const XML_PATH_ENABLED_CUSTOMER = 'twofactorauth/duo/enabled_customer';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * DuoSecurity constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get API hostname
     *
     * @return string
     */
    public function getApiHostname()
    {
        return $this->scopeConfig->getValue(MagentoDuoSecurity::XML_PATH_API_HOSTNAME);
    }

    /**
     * Get application key
     *
     * @return string
     */
    private function getApplicationKey()
    {
        return $this->scopeConfig->getValue(MagentoDuoSecurity::XML_PATH_APPLICATION_KEY);
    }

    /**
     * Get secret key
     *
     * @return string
     */
    private function getSecretKey()
    {
        return $this->scopeConfig->getValue(MagentoDuoSecurity::XML_PATH_SECRET_KEY);
    }

    /**
     * Get integration key
     *
     * @return string
     */
    private function getIntegrationKey()
    {
        return $this->scopeConfig->getValue(MagentoDuoSecurity::XML_PATH_INTEGRATION_KEY);
    }

    /**
     * Sign values
     *
     * @param string $key
     * @param string $values
     * @param string $prefix
     * @param int $expire
     * @param int $time
     * @return string
     */
    private function signValues(string $key, string $values, string $prefix, int $expire, int $time): string
    {
        $exp = $time + $expire;
        $cookie = $prefix . '|' . base64_encode($values . '|' . $exp);

        $sig = hash_hmac('sha1', $cookie, $key);
        return $cookie . '|' . $sig;
    }

    /**
     * Parse signed values and return username
     *
     * @param string $key
     * @param string $val
     * @param string $prefix
     * @param int $time
     * @return string|false
     */
    private function parseValues(string $key, string $val, string $prefix, int $time): ?string
    {
        $integrationKey = $this->getIntegrationKey();

        $timestamp = ($time ? $time : time());

        $parts = explode('|', $val);
        if (count($parts) !== 3) {
            return null;
        }
        [$uPrefix, $uB64, $uSig] = $parts;

        $sig = hash_hmac('sha1', $uPrefix . '|' . $uB64, $key);
        if (hash_hmac('sha1', $sig, $key) !== hash_hmac('sha1', $uSig, $key)) {
            return null;
        }

        if ($uPrefix !== $prefix) {
            return null;
        }

        // @codingStandardsIgnoreStart
        $cookieParts = explode('|', base64_decode($uB64));
        // @codingStandardsIgnoreEnd

        if (count($cookieParts) !== 3) {
            return null;
        }
        [$user, $uIkey, $exp] = $cookieParts;

        if ($uIkey !== $integrationKey) {
            return null;
        }
        if ($timestamp >= (int) $exp) {
            return null;
        }

        return $user;
    }

    /**
     * Get request signature
     *
     * @param CustomerInterface $customer
     * @return string
     */
    public function getRequestSignature(CustomerInterface $customer): string
    {
        $time = time();

        $values = $customer->getEmail(). $customer->getId() . '|' . $this->getIntegrationKey();
        $duoSignature = $this->signValues(
            $this->getSecretKey(),
            $values,
            MagentoDuoSecurity::DUO_PREFIX,
            MagentoDuoSecurity::DUO_EXPIRE,
            $time
        );
        $appSignature = $this->signValues(
            $this->getApplicationKey(),
            $values,
            MagentoDuoSecurity::APP_PREFIX,
            MagentoDuoSecurity::APP_EXPIRE,
            $time
        );

        return $duoSignature . ':' . $appSignature;
    }

    /**
     * Return true on token validation
     *
     * @param CustomerInterface $customer
     * @param DataObject $request
     * @return bool
     */
    public function verify(CustomerInterface $customer, DataObject $request): bool
    {
        $time = time();

        list($authSig, $appSig) = explode(':', $request->getData('tfa_code'));

        $authUser = $this->parseValues($this->getSecretKey(), $authSig, MagentoDuoSecurity::AUTH_PREFIX, $time);
        $appUser = $this->parseValues($this->getApplicationKey(), $appSig, MagentoDuoSecurity::APP_PREFIX, $time);

        return (($authUser === $appUser) && ($appUser === $customer->getEmail().$customer->getId()));
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
            !!$this->getApiHostname() &&
            !!$this->getIntegrationKey() &&
            !!$this->getApiHostname() &&
            !!$this->getSecretKey();
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string
    {
        return MagentoDuoSecurity::CODE;
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
        return ['apiHost'=> $this->getApiHostname(), 'signature' => $this->getRequestSignature($customer)];
    }
}
