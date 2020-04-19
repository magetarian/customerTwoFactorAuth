<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigProvider
 * Configuration values provider
 */
class ConfigProvider
{
    const XML_PATH_ENABLED         = 'msp_securitysuite_twofactorauth/general/enabled_customer';
    const XML_PATH_FORCE_PROVIDERS = 'msp_securitysuite_twofactorauth/general/force_providers_customer';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return array
     */
    public function getForcedProviders(): array
    {
        $providers = $this->scopeConfig->getValue(static::XML_PATH_FORCE_PROVIDERS, ScopeInterface::SCOPE_WEBSITE);
        return $providers ? explode(',', $providers) : [];
    }
}
