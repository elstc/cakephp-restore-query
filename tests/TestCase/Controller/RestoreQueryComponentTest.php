<?php

namespace Elastic\RestoreQuery\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
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
        $request = new ServerRequest([
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
        ], $request->getSession()->read('StoredQuerystring.app.Users.index'));
    }

    /**
     * Test store request
     *
     * @return void
     */
    public function testStoreWithPlugin()
    {
        $request = new ServerRequest([
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
        ], $request->getSession()->read('StoredQuerystring.plugin.Users.Authors.index'));
    }

    /**
     * Test store request
     *
     * @return void
     */
    public function testStoreWithPrefix()
    {
        $request = new ServerRequest([
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
        ], $request->getSession()->read('StoredQuerystring.app.Manager.Posts.index'));
    }

    /**
     * Test store request
     *
     * @return void
     */
    public function testStoreWithPluginAndPrefix()
    {
        $request = new ServerRequest([
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
        ], $request->getSession()->read('StoredQuerystring.plugin.Awesome.Manager.Posts.index'));
    }

    /**
     * Test restore request
     *
     * @return void
     */
    public function testRestore()
    {
        $request = new ServerRequest([
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
        $request->getSession()->write('StoredQuerystring.app.Users.index', [
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
        ], $modifiedRequest->getQuery());
    }

    /**
     * Test run store action on beforeRender
     *
     * @return void
     */
    public function testBeforeRender()
    {
        $request = new ServerRequest([
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
        ], $request->getSession()->read('StoredQuerystring.app.Users.index'));
    }

    /**
     * Test run restore action on beforeFilter
     *
     * @return void
     */
    public function testBeforeFilter()
    {
        $request = new ServerRequest([
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
        $request->getSession()->write('StoredQuerystring.app.Users.index', [
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ]);
        $response = new Response;
        $event = new Event('Controller.beforeRender', new Controller($request, $response));

        $this->RestoreQuery->beforeFilter($event);
        $modifiedRequest = $event->getSubject()->request;
        /* @var $modifiedResponse ServerRequest */
        $modifiedResponse = $event->getSubject()->response;
        /* @var $modifiedResponse Response */

        $this->assertTrue($event->isStopped(), 'Stop event when redirecting');

        $this->assertSame([
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ], $modifiedRequest->getQuery());
        $this->assertSame(302, $modifiedResponse->getStatusCode());
        $this->assertStringEndsWith('/?page=2&sort=created_at&direction=desc&limit=25', $modifiedResponse->getHeaderLine('Location'));
    }

    /**
     * Test run restore action on beforeFilter
     *
     * @return void
     */
    public function testBeforeFilterWithoutRedirect()
    {
        $this->RestoreQuery->setConfig('redirect', false);

        $request = new ServerRequest([
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
        $request->getSession()->write('StoredQuerystring.app.Users.index', [
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ]);
        $response = new Response;
        $event = new Event('Controller.beforeRender', new Controller($request, $response));

        $this->RestoreQuery->beforeFilter($event);
        $modifiedRequest = $event->getSubject()->request;
        /* @var $modifiedResponse ServerRequest */
        $modifiedResponse = $event->getSubject()->response;
        /* @var $modifiedResponse Response */

        $this->assertFalse($event->isStopped(), 'does not redirect, it does not stop');

        $this->assertSame([
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'limit' => 25,
        ], $modifiedRequest->getQuery());
        $this->assertSame(200, $modifiedResponse->getStatusCode(), 'Response remains as default');
    }
}
