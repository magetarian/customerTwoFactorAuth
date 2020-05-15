# Two Factor Authentication for Customers
[![Latest Stable Version](https://poser.pugx.org/magetarian/module-customer-two-factor-auth/v/stable)](https://packagist.org/packages/magetarian/module-customer-two-factor-auth)
[![Total Downloads](https://poser.pugx.org/magetarian/module-customer-two-factor-auth/downloads)](https://packagist.org/packages/magetarian/module-customer-two-factor-auth)
[![Latest Unstable Version](https://poser.pugx.org/magetarian/module-customer-two-factor-auth/v/unstable)](https://packagist.org/packages/magetarian/module-customer-two-factor-auth)
[![License](https://poser.pugx.org/magetarian/module-customer-two-factor-auth/license)](https://packagist.org/packages/magetarian/module-customer-two-factor-auth)
[![Pipeline Status](https://gitlab.com/sashas777/customerTwoFactorAuth/badges/master/pipeline.svg)](https://gitlab.com/sashas777/customerTwoFactorAuth/-/commits/master)
[![Coverage Report](https://gitlab.com/sashas777/customerTwoFactorAuth/badges/master/coverage.svg)](https://gitlab.com/sashas777/customerTwoFactorAuth/-/commits/master)

The module adds ability for customers login using 2FA.

## Installation

Run the following command at Magento 2 root folder:

```
composer require magetarian/module-customer-two-factor-auth
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Uninstallation

Run the following command at Magento 2 root folder:

```
composer remove magetarian/module-customer-two-factor-auth
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Contribute to this module
 Feel free to Fork and contrinute to this module and create a pull request so we will merge your changes to master branch.

## Credits
Thanks the [the contributors](https://github.com/magetarian/customerTwoFactorAuth/graphs/contributors)
