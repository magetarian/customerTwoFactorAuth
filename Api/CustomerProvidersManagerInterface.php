<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types = 1);

namespace Magetarian\CustomerTwoFactorAuth\Api;

/**
 * Interface CustomerProvidersManagerInterface
 */
interface CustomerProvidersManagerInterface
{
    /**
     * @param int $customerId
     *
     * @return array
     */
    public function getCustomerProviders(int $customerId): array;
}
