<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Api;

/**
 * Interface CustomerConfigManagerInterface
 */
interface CustomerConfigManagerInterface
{
    /**
     * Get provider config
     *
     * @param int $customerId
     * @param string $providerCode
     *
     * @return array|null
     */
    public function getProviderConfig(int $customerId, string $providerCode): ?array;

    /**
     * Set provider config
     *
     * @param int $customerId
     * @param string $providerCode
     * @param array|null $config
     *
     * @return mixed
     */
    public function setProviderConfig(int $customerId, string $providerCode, ?array $config);
}
