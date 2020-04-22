<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model\Provider\Engine;

use Base32\Base32;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use MSP\TwoFactorAuth\Api\EngineInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Google as MspGoogle;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;

class Google //implements EngineInterface
{
    //@todo replace with getCode
    const CODE = 'google';

    private $totp = null;

    private $customerConfigManager;

    private $storeManager;

    private $scopeConfig;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CustomerConfigManagerInterface $customerConfigManager
    ) {
        $this->customerConfigManager = $customerConfigManager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Generate random secret
     * @return string
     */
    private function generateSecret()
    {
        $secret = random_bytes(128);
        return preg_replace('/[^A-Za-z0-9]/', '', Base32::encode($secret));
    }

    /**
     * Get the secret code used for Google Authentication
     * @param CustomerInterface $customer
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @author Konrad Skrzynski <konrad.skrzynski@accenture.com>
     */
    public function getSecretCode(CustomerInterface $customer)
    {
        $config = $this->customerConfigManager->getProviderConfig($customer->getId(), static::CODE);

        if (!isset($config['secret'])) {
            $config['secret'] = $this->generateSecret();
            $this->customerConfigManager->setProviderConfig($customer->getId(), static::CODE, $config);
        }

        return $config['secret'] ?? null;
    }

    /**
     * Render TFA QrCode for a customer
     * @param CustomerInterface $customer
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Endroid\QrCode\Exception\ValidationException
     */
    public function getQrCodeAsPng(CustomerInterface $customer)
    {
        // @codingStandardsIgnoreStart
        $qrCode = new QrCode($this->getProvisioningUrl($customer));
        $qrCode->setSize(400);
        $qrCode->setErrorCorrectionLevel('high');
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $qrCode->setLabelFontSize(16);
        $qrCode->setEncoding('UTF-8');

        $writer = new PngWriter();
        $pngData = $writer->writeString($qrCode);
        // @codingStandardsIgnoreEnd

        return $pngData;
    }

    /**
     * Get TFA provisioning URL for a customer
     * @param CustomerInterface $customer
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProvisioningUrl(CustomerInterface $customer)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        // @codingStandardsIgnoreStart
        $issuer = parse_url($baseUrl, PHP_URL_HOST);
        // @codingStandardsIgnoreEnd

        $totp = $this->getTotp($customer);
        $totp->setIssuer($issuer);

        return $totp->getProvisioningUri();
    }

    /**
     * Get TOTP object
     * @param CustomerInterface $customer
     * @return \OTPHP\TOTP
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getTotp(CustomerInterface $customer)
    {
        if ($this->totp === null) {
            $config = $this->customerConfigManager->getProviderConfig($customer->getId(), static::CODE);

            if (!isset($config['secret'])) {
                $config['secret'] = $this->getSecretCode($customer);
            }

            // @codingStandardsIgnoreStart
            $this->totp = new \OTPHP\TOTP(
                $customer->getEmail(),
                $config['secret']
            );
            // @codingStandardsIgnoreEnd
        }

        return $this->totp;
    }

    /**
     * Return true on token validation
     * @param CustomerInterface $customer
     * @param DataObject $request
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function verify(CustomerInterface $customer, DataObject $request)
    {
        $token = $request->getData('tfa_code');

        $totp = $this->getTotp($user);
        $totp->now();

        return $totp->verify($token);
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled()
    {
        return !!$this->scopeConfig->getValue(MspGoogle::XML_PATH_ENABLED);
    }

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function isTrustedDevicesAllowed()
    {
        return !!$this->scopeConfig->getValue(MspGoogle::XML_PATH_ALLOW_TRUSTED_DEVICES);
    }
}
