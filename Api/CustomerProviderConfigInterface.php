<?php
/**
 * @author     Sashas IT Support <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (http://www.extensions.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */
declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Api;

/**
 * Interface CustomerProviderConfigInterface
 */
interface CustomerProviderConfigInterface
{
    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return string
     */
    public function getProviderName(): string;
}
