yii2-redis-ipban
================

[Yii2 Redis IP ban](./ban-list.png)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist maxodrom/yii2-redis-ipban
```

or add

```json
"maxodrom/yii2-redis-ipban": "~1.0"
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

Also add the following to your application modules config:
```php
'modules' => [
    'redis-ip-ban' => [
        'class' => 'maxodrom\redis\ipban\RedisIpBanModule',
        'redis' => 'redis',
    ],
    ...
]
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
            'class' => \maxodrom\redis\ipban\filters\RedisIpBan::className(),
            'redis' => Yii::$app->redis,
        ],
    ];
}
```