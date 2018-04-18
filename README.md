# Retaining and Restoring query strings plugin for CakePHP

<p align="center">
    <a href="LICENSE.txt" target="_blank">
        <img alt="Software License" src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square">
    </a>
    <a href="https://travis-ci.org/elstc/cakephp-restore-query" target="_blank">
        <img alt="Build Status" src="https://img.shields.io/travis/elstc/cakephp-restore-query/master.svg?style=flat-square">
    </a>
    <a href="https://codecov.io/gh/elstc/cakephp-restore-query" target="_blank">
        <img alt="Codecov" src="https://img.shields.io/codecov/c/github/elstc/cakephp-restore-query.svg?style=flat-square">
    </a>
    <a href="https://packagist.org/packages/elstc/cakephp-restore-query" target="_blank">
        <img alt="Latest Stable Version" src="https://img.shields.io/packagist/v/elstc/cakephp-restore-query.svg?style=flat-square">
    </a>
</p>

This plugin provides a component that makes it possible to restore the conditions in the list, search page, etc. even after transitioning to another page.

## Requirements

- CakePHP 3.x

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require elstc/cakephp-restore-query
```

Add the following line to your application `config/bootstrap.php`:

```
use Cake\Core\Plugin;
Plugin::load('Elastic/RestoreQuery');
```

## Usage

### Retaining query string

Load the component with your controller's `initialize` method.

```php
class AppController extends Controller
{
    public function initialize()
    {
        $this->loadComponent('Elastic/RestoreQuery.RestoreQuery', [
            'actions' => ['index', 'search'], // List of actions to record query string
        ]);
    }
}
```

The component automatically saves the Query string for the target action.

### Restore saved query string

By creating a link in the template as follows, the component will call the saved query string and redirect to the target page.

```php
    <?=
    $this->Html->link('link text', [
        'action' => 'index',
        '?' => ['_restore' => true], // NOTE: _restore=true, the component will restore the saved query.
    ]);
    ?>
```

### Elastic/RestoreQuery.RestoreQueryComponent Options

#### `actions`

List of actions to record the query string.

default: `['index', 'search']`

#### `sessionKey`

Name of session key for query string saving

default: `'StoredQuerystring'`

#### `restoreKey`

Name of the query string for restore action.

default: `'_restore'`

#### `redirect`

Redirect when restoring the query string.

default: `true`
