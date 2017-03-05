yii2-redis-ipban
================

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist maxodrom/yii2-redis-ipban
```

or add

```json
"maxodrom/yii2-redis-ipban": "0.1"
```

to the require section of your composer.json.


Configuration & Usage
---------------------

To use this extension, you have to configure the Connection class in your application configuration:

```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ]
];
```

In your Controller you should use:

```php
/**
 * @inheritdoc
 */
public function behaviors()
{
    return [
        ...,
        'ipban' => [
            'class' => \maxodrom\redis\filters\RedisIpBan::className(),
            'redis' => Yii::$app->redis,
        ],
    ];
}
```