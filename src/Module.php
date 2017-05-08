<?php
/**
 * @author Max Alexandrov <max@maxodrom.ru>
 * @since 2.0
 * @license MIT
 */

namespace maxodrom\redis\ipban;

use Yii;
use yii\web\Application;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yii\di\Instance;
use yii\redis\Connection;

/**
 * Class RedisIpBanModule
 *
 * @package maxodrom\redis\ipban
 */
class Module extends \yii\base\Module
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
     * @var array RBAC named roles, e.g. ['Admin', 'Editor']
     */
    public $allowedRoles;


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

        // check allowed IPs
        if (is_array($this->allowedIPs) && !empty($this->allowedIPs)) {
            if (Yii::$app instanceof Application && !$this->checkAccess()) {
                throw new ForbiddenHttpException('You are not allowed to access this page.');
            }
        }

        // check RBAC roles
        $authManager = Yii::$app->getAuthManager();
        if (null !== $authManager && is_array($this->allowedRoles) && !empty($this->allowedRoles)) {
            $userRoles = array_keys($authManager->getRolesByUser(Yii::$app->user->id));
            $hasAccess = false;
            foreach ($userRoles as $role) {
                if (in_array($role, $this->allowedRoles)) {
                    $hasAccess = true;
                    break;
                }
            }
            if (false === $hasAccess) {
                throw new ForbiddenHttpException('You are not allowed to access this page.');
            }
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