<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;

/**
 * Interface EngineInterface
 */
interface EngineInterface
{
    /**
     * Return true if this provider has been enabled by admin
     *
     * @return boolean
     */
    public function isEnabled();

    /**
     * Return true on token validation
     *
     * @param CustomerInterface $customer
     * @param DataObject $request
     * @return bool
     */
    public function verify(CustomerInterface $customer, DataObject $request);

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Get additional config
     *
     * @param CustomerInterface $customer
     *
     * @return array
     */
    public function getAdditionalConfig(CustomerInterface $customer): array;
}
