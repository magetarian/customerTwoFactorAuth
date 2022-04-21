<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\ViewModel\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magetarian\CustomerTwoFactorAuth\Setup\Patch\Data\CreateCustomerTFAAttributes;
use Magetarian\CustomerTwoFactorAuth\Api\ProviderPoolInterface;

/**
 * Class Configuration
 * ViewModel for customer account configuration
 */

class Configuration implements ArgumentInterface
{
    /**
     * @var CustomerMetadataInterface
     */
    protected $customerMetadata;

    /**
     * @var ProviderPoolInterface
     */
    private $providerPool;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var array
     */
    private $enabledProviders = [];

    public function __construct(
        CustomerMetadataInterface $customerMetadata,
        ProviderPoolInterface $providerPool,
        Session $customerSession
    ) {
        $this->providerPool     = $providerPool;
        $this->customerSession  = $customerSession;
        $this->customerMetadata = $customerMetadata;
    }

    /**
     * Verify is the provider enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) count($this->getEnabledProviders());
    }

    /**
     * Get list of enabled providers
     *
     * @return array
     */
    public function getEnabledProviders(): array
    {
        if (!count($this->enabledProviders)) {
            $this->enabledProviders = $this->providerPool->getEnabledProviders();
        }
        return $this->enabledProviders;
    }

    /**
     * Get list of selected providers
     *
     * @return array
     */
    public function getSelectedProviders(): array
    {
        $selectedProviders = $this->customerSession
            ->getCustomer()
            ->getDataModel()
            ->getCustomAttribute(CreateCustomerTFAAttributes::PROVIDERS);
        if (!$selectedProviders) {
            return [];
        }
        $selectedProvidersValue = $selectedProviders->getValue();
        return explode(',', $selectedProvidersValue);
    }

    /**
     * Get 2FA Provider Attribute
     *
     * @return \Magento\Customer\Model\Data\AttributeMetadata
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderConfigAttribute()
    {
        /**
         * @var $attribute \Magento\Customer\Model\Data\AttributeMetadata
         */
        $attribute = $this->customerMetadata->getAttributeMetadata(
            CreateCustomerTFAAttributes::PROVIDERS
        );

        return $attribute;
    }
}
