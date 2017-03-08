<?php
/**
 * @author Max Alexandrov <max@maxodrom.ru>
 * @since 2.0
 * @license MIT
 */

namespace maxodrom\redis\ipban;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yii\base\Module;
use yii\di\Instance;
use yii\redis\Connection;

/**
 * Class RedisIpBanModule
 *
 * @package maxodrom\redis\ipban
 */
class RedisIpBanModule extends Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'maxodrom\redis\ipban\controllers';
    /**
     * @var string Set name in Redis to store baned IPs
     */
    public $hashName = 'ipban';
    /**
     * @var string Redis connection component name or configuration for component.
     */
    public $redis;
    /**
     * @var array the list of IPs that are allowed to access this module.
     * Each array element represents a single IP filter which can be either an IP address
     * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed
     * by localhost.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->redis = Instance::ensure($this->redis, Connection::className());
        if (!is_string($this->hashName) && strlen($this->hashName) === 0) {
            throw new InvalidConfigException(
                'Property $hashName must be defined as string to identify Redis hash to store banned IPs.'
            );
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app instanceof \yii\web\Application && !$this->checkAccess()) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }

        return true;
    }

    /**
     * @return boolean whether the module can be accessed by the current user
     */
    protected function checkAccess()
    {
        $ip = Yii::$app->getRequest()->getUserIP();
        foreach ($this->allowedIPs as $filter) {
            if (
                $filter === '*' ||
                $filter === $ip ||
                (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))
            ) {
                return true;
            }
        }
        Yii::warning(
            'Access to web shell is denied due to IP address restriction. The requested IP is ' . $ip,
            __METHOD__
        );

        return false;
    }
}