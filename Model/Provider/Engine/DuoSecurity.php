<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model\Provider\Engine;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magetarian\CustomerTwoFactorAuth\Api\EngineInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\DuoSecurity as MspDuoSecurity;

class DuoSecurity implements EngineInterface
{
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
     * @return string
     */
    public function getApiHostname()
    {
        return $this->scopeConfig->getValue(MspDuoSecurity::XML_PATH_API_HOSTNAME);
    }

    /**
     * Get application key
     * @return string
     */
    private function getApplicationKey()
    {
        return $this->scopeConfig->getValue(MspDuoSecurity::XML_PATH_APPLICATION_KEY);
    }

    /**
     * Get secret key
     * @return string
     */
    private function getSecretKey()
    {
        return $this->scopeConfig->getValue(MspDuoSecurity::XML_PATH_SECRET_KEY);
    }

    /**
     * Get integration key
     * @return string
     */
    private function getIntegrationKey()
    {
        return $this->scopeConfig->getValue(MspDuoSecurity::XML_PATH_INTEGRATION_KEY);
    }

    /**
     * Sign values
     * @param string $key
     * @param string $values
     * @param string $prefix
     * @param int $expire
     * @param int $time
     * @return string
     */
    private function signValues($key, $values, $prefix, $expire, $time)
    {
        $exp = $time + $expire;
        $cookie = $prefix . '|' . base64_encode($values . '|' . $exp);

        $sig = hash_hmac("sha1", $cookie, $key);
        return $cookie . '|' . $sig;
    }

    /**
     * Parse signed values and return username
     * @param string $key
     * @param string $val
     * @param string $prefix
     * @param int $time
     * @return string|false
     */
    private function parseValues($key, $val, $prefix, $time)
    {
        $integrationKey = $this->getIntegrationKey();

        $timestamp = ($time ? $time : time());

        $parts = explode('|', $val);
        if (count($parts) !== 3) {
            return false;
        }
        list($uPrefix, $uB64, $uSig) = $parts;

        $sig = hash_hmac("sha1", $uPrefix . '|' . $uB64, $key);
        if (hash_hmac("sha1", $sig, $key) !== hash_hmac("sha1", $uSig, $key)) {
            return false;
        }

        if ($uPrefix !== $prefix) {
            return false;
        }

        // @codingStandardsIgnoreStart
        $cookieParts = explode('|', base64_decode($uB64));
        // @codingStandardsIgnoreEnd

        if (count($cookieParts) !== 3) {
            return false;
        }
        list($user, $uIkey, $exp) = $cookieParts;

        if ($uIkey !== $integrationKey) {
            return false;
        }
        if ($timestamp >= (int) $exp) {
            return false;
        }

        return $user;
    }

    /**
     * Get request signature
     * @param CustomerInterface $customer
     * @return string
     */
    public function getRequestSignature(CustomerInterface $customer)
    {
        $time = time();

        $values = $customer->getEmail() . '|' . $this->getIntegrationKey();
        $duoSignature = $this->signValues(
            $this->getSecretKey(),
            $values,
            MspDuoSecurity::DUO_PREFIX,
            MspDuoSecurity::DUO_EXPIRE,
            $time
        );
        $appSignature = $this->signValues(
            $this->getApplicationKey(),
            $values,
            MspDuoSecurity::APP_PREFIX,
            MspDuoSecurity::APP_EXPIRE,
            $time
        );

        return $duoSignature . ':' . $appSignature;
    }

    /**
     * Return true on token validation
     * @param CustomerInterface $customer
     * @param DataObject $request
     * @return bool
     */
    public function verify(CustomerInterface $customer, DataObject $request)
    {
        $time = time();

        list($authSig, $appSig) = explode(':', $request->getData('sig_response'));

        $authUser = $this->parseValues($this->getSecretKey(), $authSig, MspDuoSecurity::AUTH_PREFIX, $time);
        $appUser = $this->parseValues($this->getApplicationKey(), $appSig, MspDuoSecurity::APP_PREFIX, $time);

        return (($authUser === $appUser) && ($appUser === $customer->getEmail()));
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled()
    {
        return
            !!$this->scopeConfig->getValue(MspDuoSecurity::XML_PATH_ENABLED) &&
            !!$this->getApiHostname() &&
            !!$this->getIntegrationKey() &&
            !!$this->getApiHostname() &&
            !!$this->getSecretKey();
    }

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function isTrustedDevicesAllowed()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return MspDuoSecurity::CODE;
    }
}
