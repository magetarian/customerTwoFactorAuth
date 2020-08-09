<?php
/**
 * @author     The S Group <support@sashas.org>
 * @copyright  2020  Sashas IT Support Inc. (https://www.sashas.org)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

declare(strict_types=1);

namespace Magetarian\CustomerTwoFactorAuth\Test\Unit\Model\Provider\Engine;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magetarian\CustomerTwoFactorAuth\Api\CustomerConfigManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\TwoFactorAuth\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\TwoFactorAuth\Model\ResourceModel\Country\Collection;
use Magetarian\CustomerTwoFactorAuth\Model\Provider\Engine\Authy;
use Magento\TwoFactorAuth\Api\Data\CountryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AuthyTest
 * Test for Authy class
 */
class AuthyTest extends TestCase
{

    /** @var Authy object */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerConfigManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $curlFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $json;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $countryCollectionFactory;

    /**
     * @dataProvider dataProviderGetAdditionalConfig
     */
    public function testGetAdditionalConfig(bool $phoneConfirmed, array $providerInfo)
    {
        $result = ['countryList' => [['dial_code' => '1', 'name' => 'test']], 'phoneConfirmed' => $phoneConfirmed];
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $countryCollection = $this->getMockBuilder(Collection::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $country = $this->getMockBuilder(CountryInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->customerConfigManager->expects($this->atLeastOnce())->method('getProviderConfig')
                                                                   ->willReturn($providerInfo);
        $countryCollection->expects($this->atLeastOnce())->method('addOrder')->willReturn($countryCollection);
        $country->expects($this->atLeastOnce())->method('getDialCode')->willReturn('1');
        $country->expects($this->atLeastOnce())->method('getName')->willReturn('test');
        $countryCollection->expects($this->atLeastOnce())->method('getItems')->willReturn([$country]);
        $this->countryCollectionFactory->expects($this->atLeastOnce())->method('create')
                                                                      ->willReturn($countryCollection);
        $this->assertEquals($result, $this->object->getAdditionalConfig($customer));
    }

    /**
     * @return array|array[]
     */
    public function dataProviderGetAdditionalConfig(): array
    {
        return [
            [true , [Authy::CONFIG_PHONE_CONFIRMED_KEY=>true]],
            [false, []],
        ];
    }

    /**
     * @dataProvider dataProviderRequestToken
     */
    public function testRequestToken(string $method, ?string $approvalCode, array $result)
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $curl = $this->getMockBuilder(Curl::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $this->customerConfigManager
            ->expects($this->atLeastOnce())
            ->method('getProviderConfig')
            ->willReturn([Authy::CONFIG_CUSTOMER_KEY=>'test']);
        $curl->expects($this->atLeastOnce())->method('addHeader')->willReturn($curl);
        $curl->expects($this->atLeastOnce())->method('getBody')->willReturn('{test}');
        $curl->expects($this->any())->method('post')->willReturn($curl);
        $curl->expects($this->any())->method('get')->willReturn($curl);
        $this->json->expects($this->atLeastOnce())
                   ->method('unserialize')
                   ->willReturn([
                       'message' => 'test',
                       'success' => true,
                       'approval_request' => ['uuid' => '123', 'status' => 'approved'],
                   ]);
        $this->curlFactory->expects($this->atLeastOnce())->method('create')->willReturn($curl);
        $this->assertEquals($result, $this->object->requestToken($customer, $method, $approvalCode));
    }

    /**
     * @return array|array[]
     */
    public function dataProviderRequestToken(): array
    {
        return [
            ['sms' , null, [
                'message'=>'test', 'success' => true,'approval_request' => ['uuid' => '123', 'status' => 'approved']
            ]],
            ['onetouch', null, ['code'=>'123', 'status' => 'approved']],
            ['onetouch', '123', ['code'=>'123', 'status' => 'approved']],
            ['call', null, [
                'message'=>'test', 'success' => true, 'approval_request' => ['uuid' => '123', 'status' => 'approved']
            ]],
        ];
    }

    /**
     * @throws LocalizedException
     */
    public function testRequestTokenFailedAuthyResponse()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $curl = $this->getMockBuilder(Curl::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $this->customerConfigManager
            ->expects($this->atLeastOnce())
            ->method('getProviderConfig')
            ->willReturn([Authy::CONFIG_CUSTOMER_KEY=>'test']);
        $curl->expects($this->atLeastOnce())->method('addHeader')->willReturn($curl);
        $curl->expects($this->atLeastOnce())->method('getBody')->willReturn('{test}');
        $curl->expects($this->any())->method('post')->willReturn($curl);
        $curl->expects($this->any())->method('get')->willReturn($curl);
        $this->json->expects($this->atLeastOnce())
                   ->method('unserialize')
                   ->willReturn(['message' => 'Test', 'success' => false]);
        $this->curlFactory->expects($this->atLeastOnce())->method('create')->willReturn($curl);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Test');
        $this->object->requestToken($customer, 'onetouch', null);
    }

    /**
     * @throws LocalizedException
     */
    public function testRequestTokenInvalidAuthyResponse()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $curl = $this->getMockBuilder(Curl::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $this->customerConfigManager
            ->expects($this->atLeastOnce())
            ->method('getProviderConfig')
            ->willReturn([Authy::CONFIG_CUSTOMER_KEY=>'test']);
        $curl->expects($this->atLeastOnce())->method('addHeader')->willReturn($curl);
        $curl->expects($this->atLeastOnce())->method('getBody')->willReturn('{test}');
        $curl->expects($this->any())->method('post')->willReturn($curl);
        $curl->expects($this->any())->method('get')->willReturn($curl);
        $this->json->expects($this->atLeastOnce())
                   ->method('unserialize')
                   ->willReturn(false);
        $this->curlFactory->expects($this->atLeastOnce())->method('create')->willReturn($curl);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid authy webservice response');
        $this->object->requestToken($customer, 'onetouch', null);
    }

    /**
     * @throws LocalizedException
     */
    public function testRequestTokenInvalidApprovalCode()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $this->customerConfigManager
            ->expects($this->atLeastOnce())
            ->method('getProviderConfig')
            ->willReturn([Authy::CONFIG_CUSTOMER_KEY=>'test']);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid approval code');
        $this->object->requestToken($customer, 'onetouch', '   ');
    }

    /**
     * @throws LocalizedException
     */
    public function testRequestTokenIncorrectMethod()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Unsupported method');
        $this->object->requestToken($customer, 'test', null);
    }

    /**
     * @throws LocalizedException
     */
    public function testRequestTokenMissingCustomerInformation()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $this->customerConfigManager->expects($this->atLeastOnce())->method('getProviderConfig')->willReturn([]);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Missing customer information');
        $this->object->requestToken($customer, 'sms', null);
    }

    /**
     *
     */
    public function testIsEnabled()
    {
        $this->scopeConfig->expects($this->atLeastOnce())->method('getValue')->willReturn('test');
        $this->assertTrue($this->object->isEnabled());
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testRequestEnroll()
    {
        $result = [
            'carrier' => 'test',
            'is_cellphone' => 'test',
            'uuid' => 'test',
            'seconds_to_expire' => '60',
            'message' => 'test',
            'success' => true,
        ];
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $curl = $this->getMockBuilder(Curl::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $curl->expects($this->atLeastOnce())->method('addHeader')->willReturn($curl);
        $curl->expects($this->atLeastOnce())->method('getBody')->willReturn('{test}');
        $curl->expects($this->any())->method('post')->willReturn($curl);
        $curl->expects($this->any())->method('get')->willReturn($curl);
        $this->json->expects($this->atLeastOnce())
                   ->method('unserialize')
                   ->willReturn([
                       'carrier' => 'test',
                       'is_cellphone' => 'test',
                       'uuid' => 'test',
                       'seconds_to_expire' => '60',
                       'message' => 'test',
                       'success' => true,
                   ]);
        $this->curlFactory->expects($this->atLeastOnce())->method('create')->willReturn($curl);
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->customerConfigManager->expects($this->atLeastOnce())->method('setProviderConfig');
        $this->assertEquals($result, $this->object->requestEnroll($customer, 'US', '123', 'sms'));
    }

    /**
     * @dataProvider dataProviderVerify
     */
    public function testVerify($customerConfig, $result)
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request = $this->getMockBuilder(DataObject::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $curl = $this->getMockBuilder(Curl::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $this->customerConfigManager
            ->expects($this->atLeastOnce())
            ->method('getProviderConfig')
            ->willReturn($customerConfig);
        $request->expects($this->atLeastOnce())->method('getData')->willReturn('123');
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $customer->expects($this->any())->method('getEmail')->willReturn('test');
        if ($result) {
            $curl->expects($this->atLeastOnce())->method('addHeader')->willReturn($curl);
            $curl->expects($this->atLeastOnce())->method('getBody')->willReturn('{test}');
            $curl->expects($this->any())->method('post')->willReturn($curl);
            $curl->expects($this->any())->method('get')->willReturn($curl);
            $this->json->expects($this->atLeastOnce())
                       ->method('unserialize')
                       ->willReturn([
                           'user' => ['id' => 123],
                           'success' => true,
                           'approval_request' => ['uuid'=>123, 'status' => 'approved']
                       ]);
            $this->curlFactory->expects($this->atLeastOnce())->method('create')->willReturn($curl);
        }

        $this->assertEquals($result, $this->object->verify($customer, $request));
    }

    /**
     * @return array|array[]
     */
    public function dataProviderVerify(): array
    {
        return [
            [[Authy::CONFIG_CUSTOMER_KEY=>'test'] , false],
            [[Authy::CONFIG_CUSTOMER_KEY=>'test', 'country_code' => 'US', 'phone_number' => 123], true],
            [[Authy::CONFIG_PHONE_CONFIRMED_KEY=>'test'], false],
            [[Authy::CONFIG_PHONE_CONFIRMED_KEY=>'test', Authy::CONFIG_CUSTOMER_KEY=>'test'], true],
            [[
                Authy::CONFIG_PHONE_CONFIRMED_KEY=>'test',
                Authy::CONFIG_CUSTOMER_KEY=>'test',
                Authy::CONFIG_PENDING_APPROVAL_KEY => '123'
            ], true],
        ];
    }

    /**
     * @throws LocalizedException
     */
    public function testVerifyInvalidCodeFormat()
    {
        $customer = $this->getMockBuilder(CustomerInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request = $this->getMockBuilder(DataObject::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $request->expects($this->atLeastOnce())->method('getData')->willReturn('  ');
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid code format');
        $this->object->verify($customer, $request);
    }

    /**
     *
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->customerConfigManager = $this->getMockBuilder(CustomerConfigManagerInterface::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->curlFactory = $this->getMockBuilder(CurlFactory::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->json = $this->getMockBuilder(Json::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->countryCollectionFactory = $this->getMockBuilder(CountryCollectionFactory::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $this->object = (new ObjectManager($this))->getObject(
            Authy::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'customerConfigManager' => $this->customerConfigManager,
                'curlFactory' => $this->curlFactory,
                'json' => $this->json,
                'countryCollectionFactory' => $this->countryCollectionFactory
            ]
        );
    }
}
