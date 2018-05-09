<?php

namespace Elastic\RestoreQuery\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\Http\ServerRequest;

/**
 * Retaining and Restoring query strings
 */
class RestoreQueryComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'sessionKey' => 'StoredQuerystring',
        'restoreKey' => '_restore',
        'redirect' => true,
        'actions' => ['index', 'search'],
    ];

    /**
     * Restore query and redirect
     *
     * @param Event $event a Event object.
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        $controller = $event->getSubject();
        $request = $controller->request;

        if ($this->hasRestoreQuery($request) && $this->isStoreAction($request)) {
            $controller->request = $this->restore($request);
            if ($this->getConfig('redirect')) {
                $event->result = $controller->redirect($controller->request->getRequestTarget());
                $event->stopPropagation();
            }
        }
    }

    /**
     * Store query strings
     *
     * @param Event $event a Event object.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        $request = $event->getSubject()->request;

        if ($this->isStoreAction($request) && !$this->hasRestoreQuery($request)) {
            $this->store($request);
        }
    }

    /**
     * Check the action to save the query strings
     *
     * @param ServerRequest $request a current request object.
     * @return bool
     */
    protected function isStoreAction(ServerRequest $request)
    {
        return in_array($request->getParam('action'), $this->getConfig('actions'));
    }

    /**
     * Whether the query string contains a restore key
     *
     * @param ServerRequest $request a current request object.
     * @return bool
     */
    protected function hasRestoreQuery(ServerRequest $request)
    {
        return (bool)$request->getQuery($this->getConfig('restoreKey'));
    }

    /**
     * Restore query string from session
     *
     * @param ServerRequest $request a current request object.
     * @return ServerRequest
     */
    public function restore(ServerRequest $request)
    {
        $query = $request->getSession()->read($this->getSessionKey($request));
        $uri = $request->getUri()->withQuery(http_build_query($query));

        return $request
            ->withRequestTarget(null)
            ->withUri($uri)
            ->withQueryParams($query);
    }

    /**
     * Store query string to session
     *
     * @param ServerRequest $request a current request object.
     * @return void
     */
    public function store(ServerRequest $request)
    {
        $key = $this->getSessionKey($request);
        $request->getSession()->delete($key);
        $request->getSession()->write($key, $request->getQuery());
    }

    /**
     * get session key from request
     *
     * @param ServerRequest $request a current request object.
     * @return string
     */
    protected function getSessionKey(ServerRequest $request)
    {
        $plugin = $request->getParam('plugin');
        $prefix = $request->getParam('prefix');
        $controller = $request->getParam('controller');
        $action = $request->getParam('action');

        $keys = [$this->getConfig('sessionKey')];
        $keys[] = !$plugin ? 'app' : 'plugin';
        if ($plugin) {
            $keys[] = $plugin;
        }
        if ($prefix) {
            $keys[] = $prefix;
        }
        $keys[] = $controller;
        $keys[] = $action;

        return implode('.', $keys);
    }
}
