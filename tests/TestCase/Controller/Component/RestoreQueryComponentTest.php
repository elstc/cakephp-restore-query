<?php

namespace Elastic\RestoreQuery\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;
use Elastic\RestoreQuery\Controller\Component\RestoreQueryComponent;

/**
 * RestoreQueryComponent Test Case
 */
class RestoreQueryComponentTest extends TestCase
{

    /**
     * Test subject
     *
     * @var RestoreQueryComponent
     */
    private $RestoreQuery;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->RestoreQuery = new RestoreQueryComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->RestoreQuery);

        parent::tearDown();
    }

    /**
     * Test store request
     *
     * @return void
     */
    public function testStore()
    {
        $request = new Request([
            'params' => [
                'plugin' => null,
                'prefix' => null,
                'controller' => 'Users',
                'action' => 'index',
            ],
            'query' => [
                'page' => 2,
                'sort' => 'created_at',
                'direction' => 'desc',
                'limit' => 25,
            ],
        ]);

        $this->RestoreQuery->store($request);

        $this->assertSame([
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ], $request->session()->read('StoredQuerystring.app.Users.index'));
    }

    /**
     * Test store request
     *
     * @return void
     */
    public function testStoreWithPlugin()
    {
        $request = new Request([
            'params' => [
                'plugin' => 'Users',
                'prefix' => null,
                'controller' => 'Authors',
                'action' => 'index',
            ],
            'query' => [
                'page' => 2,
                'sort' => 'created_at',
                'direction' => 'desc',
                'limit' => 25,
            ],
        ]);

        $this->RestoreQuery->store($request);

        $this->assertSame([
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ], $request->session()->read('StoredQuerystring.plugin.Users.Authors.index'));
    }

    /**
     * Test store request
     *
     * @return void
     */
    public function testStoreWithPrefix()
    {
        $request = new Request([
            'params' => [
                'plugin' => null,
                'prefix' => 'Manager',
                'controller' => 'Posts',
                'action' => 'index',
            ],
            'query' => [
                'page' => 2,
                'sort' => 'created_at',
                'direction' => 'desc',
                'limit' => 25,
            ],
        ]);

        $this->RestoreQuery->store($request);

        $this->assertSame([
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ], $request->session()->read('StoredQuerystring.app.Manager.Posts.index'));
    }

    /**
     * Test store request
     *
     * @return void
     */
    public function testStoreWithPluginAndPrefix()
    {
        $request = new Request([
            'params' => [
                'plugin' => 'Awesome',
                'prefix' => 'Manager',
                'controller' => 'Posts',
                'action' => 'index',
            ],
            'query' => [
                'page' => 2,
                'sort' => 'created_at',
                'direction' => 'desc',
                'limit' => 25,
            ],
        ]);

        $this->RestoreQuery->store($request);

        $this->assertSame([
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ], $request->session()->read('StoredQuerystring.plugin.Awesome.Manager.Posts.index'));
    }

    /**
     * Test restore request
     *
     * @return void
     */
    public function testRestore()
    {
        $request = new Request([
            'params' => [
                'plugin' => null,
                'prefix' => null,
                'controller' => 'Users',
                'action' => 'index',
            ],
            'query' => [
                '_restore' => '1',
            ],
        ]);
        $request->session()->write('StoredQuerystring.app.Users.index', [
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ]);

        $modifiedRequest = $this->RestoreQuery->restore($request);

        $this->assertSame([
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ], $modifiedRequest->query);
    }

    /**
     * Test run store action on beforeRender
     *
     * @return void
     */
    public function testBeforeRender()
    {
        $request = new Request([
            'params' => [
                'plugin' => null,
                'prefix' => null,
                'controller' => 'Users',
                'action' => 'index',
            ],
            'query' => [
                'page' => 2,
                'sort' => 'created_at',
                'direction' => 'desc',
                'limit' => 25,
            ],
        ]);
        $event = new Event('Controller.beforeRender', new Controller($request));

        $this->RestoreQuery->beforeRender($event);

        $this->assertSame([
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ], $request->session()->read('StoredQuerystring.app.Users.index'));
    }

    /**
     * Test run restore action on beforeFilter
     *
     * @return void
     */
    public function testBeforeFilter()
    {
        $request = new Request([
            'params' => [
                'plugin' => null,
                'prefix' => null,
                'controller' => 'Users',
                'action' => 'index',
            ],
            'query' => [
                '_restore' => '1',
            ],
        ]);
        $request->session()->write('StoredQuerystring.app.Users.index', [
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ]);
        $response = new Response;
        $event = new Event('Controller.beforeRender', new Controller($request, $response));

        $this->RestoreQuery->beforeFilter($event);
        $modifiedRequest = $event->subject()->request;
        /* @var $modifiedResponse Request */
        $modifiedResponse = $event->subject()->response;
        /* @var $modifiedResponse Response */

        $this->assertTrue($event->isStopped(), 'Stop event when redirecting');

        $this->assertSame([
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ], $modifiedRequest->query);
        $this->assertSame(302, $modifiedResponse->statusCode());
        $this->assertStringEndsWith('/?page=2&sort=created_at&direction=desc&limit=25', $modifiedResponse->location());
    }

    /**
     * Test run restore action on beforeFilter
     *
     * @return void
     */
    public function testBeforeFilterWithoutRedirect()
    {
        $this->RestoreQuery->config('redirect', false);

        $request = new Request([
            'params' => [
                'plugin' => null,
                'prefix' => null,
                'controller' => 'Users',
                'action' => 'index',
            ],
            'query' => [
                '_restore' => '1',
            ],
        ]);
        $request->session()->write('StoredQuerystring.app.Users.index', [
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ]);
        $response = new Response;
        $event = new Event('Controller.beforeRender', new Controller($request, $response));

        $this->RestoreQuery->beforeFilter($event);
        $modifiedRequest = $event->subject()->request;
        /* @var $modifiedResponse Request */
        $modifiedResponse = $event->subject()->response;
        /* @var $modifiedResponse Response */

        $this->assertFalse($event->isStopped(), 'does not redirect, it does not stop');

        $this->assertSame([
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ], $modifiedRequest->query);
        $this->assertSame(200, $modifiedResponse->statusCode(), 'Response remains as default');
    }
}
