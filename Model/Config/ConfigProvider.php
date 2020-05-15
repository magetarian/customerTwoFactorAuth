<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
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
    const XML_PATH_CUSTOMER_FORCE_TFA = 'msp_securitysuite_twofactorauth/general/customer_force_tfa';

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
    public function isTfaForced(): bool
    {
        return $this->scopeConfig->isSetFlag(static::XML_PATH_CUSTOMER_FORCE_TFA, ScopeInterface::SCOPE_WEBSITE);
    }
}
