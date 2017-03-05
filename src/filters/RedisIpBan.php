<?php
/**
 * @author Max Alexandrov <max@maxodrom.ru>
 * @since 0.1
 * @license MIT
 */

namespace maxodrom\redis\ipban\filters;

use Yii;
use yii\base\ActionFilter;
use yii\di\Instance;
use yii\redis\Connection;
use yii\web\ForbiddenHttpException;

/**
 * Class IpBanFilter
 *
 * @package common\actions\redis
 */
class RedisIpBan extends ActionFilter
{
    /**
     * @var \yii\redis\Connection Redis connection instance
     */
    public $redis;
    /**
     * @var string Set name in Redis to store baned IPs
     */
    public $hashName = 'ipban';
    /**
     * @var string
     */
    public $exceptionMessage;


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->redis = Instance::ensure($this->redis, Connection::className());
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $redis = $this->redis;
        $result = $redis->hget(
            $this->hashName,
            Yii::$app->getRequest()->getUserIP()
        );
        if (null === $result) {
            return true;
        } else {
            $this->denyAccess();

            return false;
        }
    }

    protected function denyAccess()
    {
        $exception = new ForbiddenHttpException($this->exceptionMessage);

        $response = Yii::$app->getResponse();
        $errorHandler = Yii::$app->getErrorHandler();

        $response->setStatusCode($exception->statusCode, $exception->getMessage());
        $response->data = $errorHandler->renderFile($errorHandler->errorView, ['exception' => $exception]);
        $response->send();

        Yii::$app->end();
    }
}