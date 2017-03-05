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
     * @var string Current remote IP
     */
    protected $ip;


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
        $this->ip = Yii::$app->getRequest()->getUserIP();
        $redis = $this->redis;
        $info = $redis->hget($this->hashName, $this->ip);
        if (null === $info) {
            return true;
        } else {
            // get timestamp, ttl & total hits
            $i = explode('|', $info);
            list($ts, $ttl, $hits) = $i;

            if ($ttl > 0 && microtime(true) - $ts > $ttl) {
                $redis->hdel($this->hashName, $this->ip);

                return true;
            } else {
                ++$hits;
                $redis->hset(
                    $this->hashName,
                    $this->ip,
                    implode('|', ['ts' => $ts, 'ttl' => $ttl, 'hits' => $hits])
                );
                $this->denyAccess();

                return false;
            }
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