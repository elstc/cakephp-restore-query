<?php

namespace Elastic\RestoreQuery\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\Network\Request;

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
     */
    public function beforeFilter(Event $event)
    {
        $controller = $event->subject();
        $request = $controller->request;

        if ($this->hasRestoreQuery($request) && $this->isStoreAction($request)) {
            $request = $this->restore($request);
            if ($this->config('redirect')) {
                $controller->redirect($request->here());
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
        $request = $event->subject()->request;

        if ($this->isStoreAction($request) && !$this->hasRestoreQuery($request)) {
            $this->store($request);
        }
    }

    /**
     * Check the action to save the query strings
     *
     * @param Request $request a current request object.
     * @return bool
     */
    protected function isStoreAction(Request $request)
    {
        return in_array($request->param('action'), $this->config('actions'));
    }

    /**
     * Whether the query string contains a restore key
     *
     * @param Request $request a current request object.
     * @return bool
     */
    protected function hasRestoreQuery(Request $request)
    {
        return (bool)$request->query($this->config('restoreKey'));
    }

    /**
     * Restore query string from session
     *
     * @param Request $request a current request object.
     * @return Request
     */
    public function restore(Request $request)
    {
        $request->query = $request->session()->read($this->getSessionKey($request));

        return $request;
    }

    /**
     * Store query string to session
     *
     * @param Request $request a current request object.
     */
    public function store(Request $request)
    {
        $key = $this->getSessionKey($request);
        $request->session()->delete($key);
        $request->session()->write($key, $request->query);
    }

    /**
     * get session key from request
     *
     * @param Request $request a current request object.
     * @return string
     */
    protected function getSessionKey(Request $request)
    {
        $plugin = $request->param('plugin');
        $prefix = $request->param('prefix');
        $controller = $request->param('controller');
        $action = $request->param('action');

        $keys = [$this->config('sessionKey')];
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
