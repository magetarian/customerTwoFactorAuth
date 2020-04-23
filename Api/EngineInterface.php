<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

namespace Magetarian\CustomerTwoFactorAuth\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;

interface EngineInterface
{
    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled();

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function isTrustedDevicesAllowed();

    /**
     * Return true on token validation
     * @param CustomerInterface $customer
     * @param DataObject $request
     * @return bool
     */
    public function verify(CustomerInterface $customer, DataObject $request);
}
