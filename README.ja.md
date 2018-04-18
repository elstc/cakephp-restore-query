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

このプラグインは、一覧、検索画面などで検索した条件を、別の画面に遷移した後でも復元可能にするコンポーネントを提供します。

## 必要要件

- CakePHP 3.x

## インストール

[composer](http://getcomposer.org) を使用してインストールできます。

以下のようにして、Composer経由でプラグインをCakePHPアプリケーションへ追加します:

```
composer require elstc/cakephp-restore-query
```

アプリケーションの `config/bootstrap.php` ファイルへ、次の行を追加します:

```
use Cake\Core\Plugin;
Plugin::load('Elastic/RestoreQuery');
```

## 使用方法

### クエリストリングの保存

コントローラーの `initialize` メソッドでコンポーネントを呼び出します。

```php
class AppController extends Controller
{
    public function initialize()
    {
        $this->loadComponent('Elastic/RestoreQuery.RestoreQuery', [
            'actions' => ['index', 'search'], // クエリストリングを記録するアクション
        ]);
    }
}
```

クエリストリングの記録はコンポーネントをロードすることで、対象アクションに対し自動的に行われます。

### 保存したクエリストリングを戻す

テンプレートで以下のようにしてリンクを生成することで、保存されたクエリストリングを呼び出して対象のページへリダイレクトします。

```php
    <?=
    $this->Html->link('link text', [
        'action' => 'index',
        '?' => ['_restore' => true], // NOTE: _restore=true とすることで保存されたクエリを呼び出します。
    ]);
    ?>
```

### Elastic/RestoreQuery.RestoreQuery のオプション

#### `actions`

クエリストリングを記録するアクションのリスト。

default: `['index', 'search']`

#### `sessionKey`

状態保存用セッションキーの名前

default: `'StoredQuerystring'`

#### `restoreKey`

リストア用クエリストリングの名前。

default: `'_restore'`

#### `redirect`

リストア用クエリストリングが存在する場合にリダイレクトする。

default: `true`
