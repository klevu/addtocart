<?php

namespace Klevu\Addtocart\Test\Integration\Controller\Adminhtml\System\Config\Edit\SearchConfiguration;

use Klevu\Search\Api\Service\Account\GetFeaturesInterface;
use Klevu\Search\Api\Service\Account\Model\AccountFeaturesInterface;
use Klevu\Search\Service\Account\GetFeatures;
use Klevu\Search\Service\Account\Model\AccountFeatures;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Config\Storage\Writer as ScopeConfigWriter;
use Magento\Framework\App\Config\Storage\WriterInterface as ScopeConfigWriterInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController as AbstractBackendControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class RenderPluginTest extends AbstractBackendControllerTestCase
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MockObject&LoggerInterface
     */
    private $loggerMock;

    /**
     * @var MockObject&AccountFeaturesInterface
     */
    private $accountFeaturesMock;

    /**
     * @var MockObject&GetFeaturesInterface
     */
    private $getFeaturesMock;

    /**
     * @var MockObject&ScopeConfigWriterInterface
     */
    private $scopeConfigWriterMock;

    /**
     * @var string
     */
    protected $resource = 'Klevu_Search::config_search';

    /**
     * @var int
     */
    protected $expectedNoAccessResponseCode = 302;

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/admin/url/use_custom 1
     * @magentoConfigFixture default_store admin/url/use_custom 1
     * @magentoConfigFixture default/admin/url/custom http://localhost/
     * @magentoConfigFixture default_store admin/url/custom http://localhost/
     * @magentoConfigFixture default/klevu_search/add_to_cart/enabledaddtocartfront 0
     * @magentoConfigFixture default_store klevu_search/add_to_cart/enabledaddtocartfront 0
     */
    public function testRender_DefaultScope()
    {
        $this->setupPhp5();

        $this->scopeConfigWriterMock->expects($this->never())
            ->method('save');

        $request = $this->getRequest();
        $request->setParam('section', 'klevu_search');
        $request->setMethod('GET');

        $this->dispatch($this->getAdminFrontName() . '/admin/system_config/edit');

        $response = $this->getResponse();
        $httpResponseCode = $response->getHttpResponseCode();
        $this->assertNotSame(404, $httpResponseCode);
        $this->assertNotSame($this->expectedNoAccessResponseCode, $httpResponseCode);

        $responseBody = $response->getBody();
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('<div id="system_config_tabs"', $responseBody);
        } else {
            $this->assertContains('<div id="system_config_tabs"', $responseBody);
        }
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        } else {
            $this->assertRegExp('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        }

        $matches = [];
        preg_match('#<tr[^>]+id="row_klevu_search_add_to_cart_enabled_info".*?</tr>#s', $responseBody, $matches);
        $this->assertCount(1, $matches);
        $addToCartButtonRow = current($matches);
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Switch to Store View scope to manag', $addToCartButtonRow);
        } else {
            $this->assertContains('Switch to Store View scope to manage', $addToCartButtonRow);
        }
        if (method_exists($this, 'assertDoesNotMatchRegularExpression')) {
            $this->assertDoesNotMatchRegularExpression('#<tr[^>]+id="row_klevu_search_add_to_cart_enabledaddtocartfront".*?</tr>#s', $responseBody);
            $this->assertDoesNotMatchRegularExpression('#<(input|select).*?id="klevu_search_add_to_cart_enabledaddtocartfront"#s', $responseBody);
        } else {
            $this->assertNotRegExp('#<tr[^>]+id="row_klevu_search_add_to_cart_enabledaddtocartfront".*?</tr>#s', $responseBody);
            $this->assertNotRegexp('#<(input|select).*?id="klevu_search_add_to_cart_enabledaddtocartfront"#s', $responseBody);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/admin/url/use_custom 1
     * @magentoConfigFixture default_store admin/url/use_custom 1
     * @magentoConfigFixture default/admin/url/custom http://localhost/
     * @magentoConfigFixture default_store admin/url/custom http://localhost/
     * @magentoConfigFixture default/klevu_search/add_to_cart/enabledaddtocartfront 0
     * @magentoConfigFixture default_store klevu_search/add_to_cart/enabledaddtocartfront 0
     */
    public function testRender_WebsiteScope()
    {
        $this->setupPhp5();

        $this->scopeConfigWriterMock->expects($this->never())
            ->method('save');

        $defaultStore = $this->storeManager->getDefaultStoreView();

        $request = $this->getRequest();
        $request->setParam('website', $defaultStore->getWebsiteId());
        $request->setParam('section', 'klevu_search');
        $request->setMethod('GET');

        $this->dispatch($this->getAdminFrontName() . '/admin/system_config/edit');

        $response = $this->getResponse();
        $httpResponseCode = $response->getHttpResponseCode();
        $this->assertNotSame(404, $httpResponseCode);
        $this->assertNotSame($this->expectedNoAccessResponseCode, $httpResponseCode);

        $responseBody = $response->getBody();
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        } else {
            $this->assertRegExp('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        }

        $matches = [];
        preg_match('#<tr[^>]+id="row_klevu_search_add_to_cart_enabled_info".*?</tr>#s', $responseBody, $matches);
        $this->assertCount(1, $matches);
        $addToCartButtonRow = current($matches);
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('Switch to Store View scope to manag', $addToCartButtonRow);
        } else {
            $this->assertContains('Switch to Store View scope to manage', $addToCartButtonRow);
        }
        if (method_exists($this, 'assertDoesNotMatchRegularExpression')) {
            $this->assertDoesNotMatchRegularExpression('#<tr[^>]+id="row_klevu_search_add_to_cart_enabledaddtocartfront".*?</tr>#s', $responseBody);
            $this->assertDoesNotMatchRegularExpression('#<(input|select).*?id="klevu_search_add_to_cart_enabledaddtocartfront"#s', $responseBody);
        } else {
            $this->assertNotRegExp('#<tr[^>]+id="row_klevu_search_add_to_cart_enabledaddtocartfront".*?</tr>#s', $responseBody);
            $this->assertNotRegexp('#<(input|select).*?id="klevu_search_add_to_cart_enabledaddtocartfront"#s', $responseBody);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/admin/url/use_custom 1
     * @magentoConfigFixture default_store admin/url/use_custom 1
     * @magentoConfigFixture default/admin/url/custom http://localhost/
     * @magentoConfigFixture default_store admin/url/custom http://localhost/
     * @magentoConfigFixture default/klevu_search/add_to_cart/enabledaddtocartfront 0
     * @magentoConfigFixture default_store klevu_search/add_to_cart/enabledaddtocartfront 0
     */
    public function testRender_StoreScope_AddToCartAvailable_DisabledInConfig()
    {
        $this->setupPhp5();

        $this->scopeConfigWriterMock->expects($this->never())
            ->method('save');

        $this->accountFeaturesMock->method('isFeatureAvailable')->willReturnCallback(
            static function ($feature, $strict = false) {
                switch ($feature) {
                    case AccountFeatures::PM_FEATUREFLAG_ADD_TO_CART:
                        return true;
                        break;

                    default:
                        return false;
                        break;
                }
            }
        );

        $defaultStore = $this->storeManager->getDefaultStoreView();

        $request = $this->getRequest();
        $request->setParam('store', $defaultStore->getId());
        $request->setParam('section', 'klevu_search');
        $request->setMethod('GET');

        $this->dispatch($this->getAdminFrontName() . '/admin/system_config/edit');

        $response = $this->getResponse();
        $httpResponseCode = $response->getHttpResponseCode();
        $this->assertNotSame(404, $httpResponseCode);
        $this->assertNotSame($this->expectedNoAccessResponseCode, $httpResponseCode);

        $responseBody = $response->getBody();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        } else {
            $this->assertRegExp('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        }
        if (method_exists($this, 'assertDoesNotMatchRegularExpression')) {
            $this->assertDoesNotMatchRegularExpression('#<tr[^>]+id="row_klevu_search_add_to_cart_enabled_info".*?</tr>#s', $responseBody);
        } else {
            $this->assertNotRegExp('#<tr[^>]+id="row_klevu_search_add_to_cart_enabled_info".*?</tr>#s', $responseBody);
        }

        $matches = [];
        preg_match('#<tr[^>]+id="row_klevu_search_add_to_cart_enabledaddtocartfront".*?</tr>#s', $responseBody, $matches);
        $this->assertCount(1, $matches, 'Add to cart row');
        $addToCartButtonRow = current($matches);
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString('Switch to Store View scope to manag', $addToCartButtonRow);
            $this->assertStringNotContainsString('<div class="klevu-upgrade-block">TEST UPGRADE MESSAGE</div>', $addToCartButtonRow);
        } else {
            $this->assertNotContains('Switch to Store View scope to manage', $addToCartButtonRow);
            $this->assertNotContains('<div class="klevu-upgrade-block">TEST UPGRADE MESSAGE</div>', $addToCartButtonRow);
        }

        $matches = [];
        preg_match('#<select id="klevu_search_add_to_cart_enabledaddtocartfront".*?>.*?</select>#s', $responseBody, $matches);
        $this->assertCount(1, $matches, 'Add to cart field');
        $addToCartButtonField = current($matches);
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString('disabled', $addToCartButtonField);
        } else {
            $this->assertNotContains('disabled', $addToCartButtonField);
        }
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<option[^>]+value="0"[^>]+selected.*?>\s*No\s*</option>#s', $addToCartButtonField);
            $this->assertMatchesRegularExpression('#<option[^>]+value="1".*?>\s*Yes\s*</option>#s', $addToCartButtonField);
        } else {
            $this->assertRegExp('#<option[^>]+value="1".*?>\s*Yes\s*</option>#s', $addToCartButtonField);
            $this->assertRegExp('#<option[^>]+value="0"[^>]+selected.*?>\s*No\s*</option>#s', $addToCartButtonField);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/admin/url/use_custom 1
     * @magentoConfigFixture default_store admin/url/use_custom 1
     * @magentoConfigFixture default/admin/url/custom http://localhost/
     * @magentoConfigFixture default_store admin/url/custom http://localhost/
     * @magentoConfigFixture default/klevu_search/add_to_cart/enabledaddtocartfront 0
     * @magentoConfigFixture default_store klevu_search/add_to_cart/enabledaddtocartfront 0
     */
    public function testRender_StoreScope_AddToCartUnavailable_DisabledInConfig()
    {
        $this->setupPhp5();

        $this->scopeConfigWriterMock->expects($this->never())
            ->method('save');

        $this->accountFeaturesMock->method('isFeatureAvailable')->willReturnCallback(
            static function ($feature, $strict = false) {
                switch ($feature) {
                    case AccountFeatures::PM_FEATUREFLAG_ADD_TO_CART:
                    default:
                        return false;
                        break;
                }
            }
        );

        $defaultStore = $this->storeManager->getDefaultStoreView();

        $request = $this->getRequest();
        $request->setParam('store', $defaultStore->getId());
        $request->setParam('section', 'klevu_search');
        $request->setMethod('GET');

        $this->dispatch($this->getAdminFrontName() . '/admin/system_config/edit');

        $response = $this->getResponse();
        $httpResponseCode = $response->getHttpResponseCode();
        $this->assertNotSame(404, $httpResponseCode);
        $this->assertNotSame($this->expectedNoAccessResponseCode, $httpResponseCode);

        $responseBody = $response->getBody();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        } else {
            $this->assertRegExp('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        }
        if (method_exists($this, 'assertDoesNotMatchRegularExpression')) {
            $this->assertDoesNotMatchRegularExpression('#<tr[^>]+id="row_klevu_search_add_to_cart_enabled_info".*?</tr>#s', $responseBody);
        } else {
            $this->assertNotRegExp('#<tr[^>]+id="row_klevu_search_add_to_cart_enabled_info".*?</tr>#s', $responseBody);
        }

        $matches = [];
        preg_match('#<tr[^>]+id="row_klevu_search_add_to_cart_enabledaddtocartfront".*?</tr>#s', $responseBody, $matches);
        $this->assertCount(1, $matches, 'Add to cart row');
        $addToCartButtonRow = current($matches);
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString('Switch to Store View scope to manag', $addToCartButtonRow);
        } else {
            $this->assertNotContains('Switch to Store View scope to manage', $addToCartButtonRow);
        }
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('<div class="klevu-upgrade-block">TEST UPGRADE MESSAGE</div>', $addToCartButtonRow);
        } else {
            $this->assertContains('<div class="klevu-upgrade-block">TEST UPGRADE MESSAGE</div>', $addToCartButtonRow);
        }

        $matches = [];
        preg_match('#<select id="klevu_search_add_to_cart_enabledaddtocartfront".*?>.*?</select>#s', $responseBody, $matches);
        $this->assertCount(1, $matches, 'Add to cart field');
        $addToCartButtonField = current($matches);
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringContainsString('disabled', $addToCartButtonField);
        } else {
            $this->assertContains('disabled', $addToCartButtonField);
        }
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<option[^>]+value="1".*?>\s*Yes\s*</option>#s', $addToCartButtonField);
            $this->assertMatchesRegularExpression('#<option[^>]+value="0"[^>]+selected.*?>\s*No\s*</option>#s', $addToCartButtonField);
        } else {
            $this->assertRegExp('#<option[^>]+value="1".*?>\s*Yes\s*</option>#s', $addToCartButtonField);
            $this->assertRegExp('#<option[^>]+value="0"[^>]+selected.*?>\s*No\s*</option>#s', $addToCartButtonField);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/admin/url/use_custom 1
     * @magentoConfigFixture default_store admin/url/use_custom 1
     * @magentoConfigFixture default/admin/url/custom http://localhost/
     * @magentoConfigFixture default_store admin/url/custom http://localhost/
     * @magentoConfigFixture default/klevu_search/add_to_cart/enabledaddtocartfront 1
     * @magentoConfigFixture default_store klevu_search/add_to_cart/enabledaddtocartfront 1
     */
    public function testRenderStoreScope_AddToCartAvailable_EnabledInConfig()
    {
        $this->setupPhp5();

        $this->scopeConfigWriterMock->expects($this->never())
            ->method('save');

        $this->accountFeaturesMock->method('isFeatureAvailable')->willReturnCallback(
            static function ($feature, $strict = false) {
                switch ($feature) {
                    case AccountFeatures::PM_FEATUREFLAG_ADD_TO_CART:
                        return true;
                        break;

                    default:
                        return false;
                        break;
                }
            }
        );

        $defaultStore = $this->storeManager->getDefaultStoreView();

        $request = $this->getRequest();
        $request->setParam('store', $defaultStore->getId());
        $request->setParam('section', 'klevu_search');
        $request->setMethod('GET');

        $this->dispatch($this->getAdminFrontName() . '/admin/system_config/edit');

        $response = $this->getResponse();
        $httpResponseCode = $response->getHttpResponseCode();
        $this->assertNotSame(404, $httpResponseCode);
        $this->assertNotSame($this->expectedNoAccessResponseCode, $httpResponseCode);

        $responseBody = $response->getBody();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        } else {
            $this->assertRegExp('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        }
        if (method_exists($this, 'assertDoesNotMatchRegularExpression')) {
            $this->assertDoesNotMatchREgularExpression('#<tr[^>]+id="row_klevu_search_add_to_cart_enabled_info".*?</tr>#s', $responseBody);
        } else {
            $this->assertNotRegExp('#<tr[^>]+id="row_klevu_search_add_to_cart_enabled_info".*?</tr>#s', $responseBody);
        }

        $matches = [];
        preg_match('#<tr[^>]+id="row_klevu_search_add_to_cart_enabledaddtocartfront".*?</tr>#s', $responseBody, $matches);
        $this->assertCount(1, $matches, 'Add to cart row');
        $addToCartButtonRow = current($matches);
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString('Switch to Store View scope to manag', $addToCartButtonRow);
            $this->assertStringNotContainsString('<div class="klevu-upgrade-block">TEST UPGRADE MESSAGE</div>', $addToCartButtonRow);
        } else {
            $this->assertNotContains('Switch to Store View scope to manage', $addToCartButtonRow);
            $this->assertNotContains('<div class="klevu-upgrade-block">TEST UPGRADE MESSAGE</div>', $addToCartButtonRow);
        }

        $matches = [];
        preg_match('#<select id="klevu_search_add_to_cart_enabledaddtocartfront".*?>.*?</select>#s', $responseBody, $matches);
        $this->assertCount(1, $matches, 'Add to cart field');
        $addToCartButtonField = current($matches);
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString('disabled', $addToCartButtonField);
        } else {
            $this->assertNotContains('disabled', $addToCartButtonField);
        }
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<option[^>]+value="1"[^>]+selected.*?>\s*Yes\s*</option>#s', $addToCartButtonField);
            $this->assertMatchesRegularExpression('#<option[^>]+value="0".*?>\s*No\s*</option>#s', $addToCartButtonField);
        } else {
            $this->assertRegExp('#<option[^>]+value="1"[^>]+selected.*?>\s*Yes\s*</option>#s', $addToCartButtonField);
            $this->assertRegExp('#<option[^>]+value="0".*?>\s*No\s*</option>#s', $addToCartButtonField);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/admin/url/use_custom 1
     * @magentoConfigFixture default_store admin/url/use_custom 1
     * @magentoConfigFixture default/admin/url/custom http://localhost/
     * @magentoConfigFixture default_store admin/url/custom http://localhost/
     * @magentoConfigFixture default/klevu_search/add_to_cart/enabledaddtocartfront 1
     * @magentoConfigFixture default_store klevu_search/add_to_cart/enabledaddtocartfront 1
     */
    public function testRenderStoreScope_AddToCartUnavailable_EnabledInConfig()
    {
        $this->setupPhp5();

        $this->scopeConfigWriterMock->expects($this->atLeastOnce())
            ->method('save')
            ->with(
                'klevu_search/add_to_cart/enabledaddtocartfront',
                0,
                'stores',
                1
            );
        $this->loggerMock->expects($this->atLeastOnce())
            ->method('debug')
            ->with('Automatically updated config value for "klevu_search/add_to_cart/enabledaddtocartfront" following feature check');

        $this->accountFeaturesMock->method('isFeatureAvailable')->willReturnCallback(
            static function ($feature, $strict = false) {
                switch ($feature) {
                    case AccountFeatures::PM_FEATUREFLAG_ADD_TO_CART:
                    default:
                        return false;
                        break;
                }
            }
        );

        $defaultStore = $this->storeManager->getDefaultStoreView();

        $request = $this->getRequest();
        $request->setParam('store', $defaultStore->getId());
        $request->setParam('section', 'klevu_search');
        $request->setMethod('GET');

        $this->dispatch($this->getAdminFrontName() . '/admin/system_config/edit');

        $response = $this->getResponse();
        $httpResponseCode = $response->getHttpResponseCode();
        $this->assertNotSame(404, $httpResponseCode);
        $this->assertNotSame($this->expectedNoAccessResponseCode, $httpResponseCode);

        $responseBody = $response->getBody();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        } else {
            $this->assertRegExp('#<fieldset[^>]+id="klevu_search_add_to_cart"#', $responseBody);
        }
        if (method_exists($this, 'assertDoesNotMatchRegularExpression')) {
            $this->assertDoesNotMatchRegularExpression('#<tr[^>]+id="row_klevu_search_add_to_cart_enabled_info".*?</tr>#s', $responseBody);
        } else {
            $this->assertNotRegExp('#<tr[^>]+id="row_klevu_search_add_to_cart_enabled_info".*?</tr>#s', $responseBody);
        }

        $matches = [];
        preg_match('#<tr[^>]+id="row_klevu_search_add_to_cart_enabledaddtocartfront".*?</tr>#s', $responseBody, $matches);
        $this->assertCount(1, $matches, 'Add to cart row');
        $addToCartButtonRow = current($matches);
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString('Switch to Store View scope to manag', $addToCartButtonRow);
        } else {
            $this->assertNotContains('Switch to Store View scope to manage', $addToCartButtonRow);
        }
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('<div class="klevu-upgrade-block">TEST UPGRADE MESSAGE</div>', $addToCartButtonRow);
        } else {
            $this->assertContains('<div class="klevu-upgrade-block">TEST UPGRADE MESSAGE</div>', $addToCartButtonRow);
        }

        $matches = [];
        preg_match('#<select id="klevu_search_add_to_cart_enabledaddtocartfront".*?>.*?</select>#s', $responseBody, $matches);
        $this->assertCount(1, $matches, 'Add to cart field');
        $addToCartButtonField = current($matches);
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString('disabled', $addToCartButtonField);
        } else {
            $this->assertContains('disabled', $addToCartButtonField);
        }
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<option[^>]+value="1".*?>\s*Yes\s*</option>#s', $addToCartButtonField);
            $this->assertMatchesRegularExpression('#<option[^>]+value="0"[^>]+selected.*?>\s*No\s*</option>#s', $addToCartButtonField);
        } else {
            $this->assertRegExp('#<option[^>]+value="1".*?>\s*Yes\s*</option>#s', $addToCartButtonField);
            $this->assertRegExp('#<option[^>]+value="0"[^>]+selected.*?>\s*No\s*</option>#s', $addToCartButtonField);
        }
    }

    /**
     * @inheritdoc
     */
    public function testAclHasAccess()
    {
        $this->setupPhp5();

        if ($this->uri === null) {
            $this->markTestIncomplete('AclHasAccess test is not complete');
        }
        if ($this->httpMethod) {
            $this->getRequest()->setMethod($this->httpMethod);
        }
        $this->dispatch($this->uri);
        $this->assertNotSame(404, $this->getResponse()->getHttpResponseCode());
        $this->assertNotSame($this->expectedNoAccessResponseCode, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * @inheritdoc
     */
    public function testAclNoAccess()
    {
        $this->setupPhp5();
        if ($this->resource === null || $this->uri === null) {
            $this->markTestIncomplete('Acl test is not complete');
        }
        if ($this->httpMethod) {
            $this->getRequest()->setMethod($this->httpMethod);
        }
        $this->_objectManager->get(\Magento\Framework\Acl\Builder::class)
            ->getAcl()
            ->deny(null, $this->resource);
        $this->dispatch($this->uri);
        $this->assertSame($this->expectedNoAccessResponseCode, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * Alternative setup method to accommodate lack of return type casting in PHP5.6,
     *  given setUp() requires a void return type
     *
     * @return void
     * @throws AuthenticationException
     * @todo Move to setUp when PHP 5.x is no longer supported
     */
    private function setupPhp5()
    {
        $this->setUp();

        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager->addSharedInstance($this->loggerMock, 'Klevu\Search\Logger\Logger\Search');

        $this->scopeConfigWriterMock = $this->getMockBuilder(ScopeConfigWriter::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $this->_objectManager->addSharedInstance($this->scopeConfigWriterMock, ScopeConfigWriterInterface::class);
        $this->_objectManager->addSharedInstance($this->scopeConfigWriterMock, ScopeConfigWriter::class);

        $this->accountFeaturesMock = $this->getMockBuilder(AccountFeatures::class)
            ->disableOriginalConstructor()
            ->setMethods(['isFeatureAvailable', 'getUpgradeMessage'])
            ->getMock();
        $this->accountFeaturesMock->method('getUpgradeMessage')
            ->willReturn('TEST UPGRADE MESSAGE');

        $this->getFeaturesMock = $this->getMockBuilder(GetFeaturesInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();
        $this->getFeaturesMock->method('execute')->willReturn($this->accountFeaturesMock);

        $this->_objectManager->addSharedInstance($this->getFeaturesMock, GetFeaturesInterface::class);
        $this->_objectManager->addSharedInstance($this->getFeaturesMock, GetFeatures::class);

        $this->uri = $this->getAdminFrontName() . '/admin/system_config/edit/section/klevu_search';
    }

    /**
     * Returns configured admin front name for use in dispatching controller requests
     *
     * @return string
     */
    private function getAdminFrontName()
    {
        /** @var AreaList $areaList */
        $areaList = $this->_objectManager->get(AreaList::class);
        $adminFrontName = $areaList->getFrontName('adminhtml');
        if (!$adminFrontName) {
            /** @var FrontNameResolver $backendFrontNameResolver */
            $backendFrontNameResolver = $this->_objectManager->get(FrontNameResolver::class);
            $adminFrontName = $backendFrontNameResolver->getFrontName(true);
        }

        return (string)$adminFrontName;
    }
}
