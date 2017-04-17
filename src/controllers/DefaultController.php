<?php
/**
 * @author Max Alexandrov <max@maxodrom.ru>
 * @since 2.0
 * @license MIT
 */

namespace maxodrom\redis\ipban\controllers;

use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\data\ArrayDataProvider;
use yii\base\DynamicModel;
use yii\helpers\Html;

/**
 * Class DefaultController
 *
 * @package maxodrom\redis\ipban\controllers
 */
class DefaultController extends Controller
{
    /**
     * @var \yii\redis\Connection Redis connection instance.
     */
    protected $redis;
    /**
     * @var string Redis hash name to store banned IPs.
     */
    protected $hashName;


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->redis = $this->module->redis;
        $this->hashName = $this->module->hashName;
        parent::init();
    }

    /**
     * Lists of all banned IPs.
     *
     * @return string
     */
    public function actionIndex()
    {
        $keys = $this->redis->hkeys($this->hashName);
        $vals = $this->redis->hvals($this->hashName);
        $allModels = [];
        foreach ($keys as $k => $key) {
            $valData = explode('|', $vals[$k]);
            $allModels[] = [
                'ip' => $key,
                'added' => $valData[0],
                'ttl' => $valData[1],
                'hits' => $valData[2],
            ];
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $allModels,
            'totalCount' => $this->redis->hlen($this->hashName),
            'pagination' => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Bans the given IP.
     *
     * @return \yii\web\Response
     */
    public function actionBan()
    {
        $ip = Yii::$app->getRequest()->post('ip');
        $ttl = Yii::$app->getRequest()->post('ttl');

        $model = DynamicModel::validateData(compact('ip', 'ttl'), [
            [['ip', 'ttl'], 'filter', 'filter' => 'trim'],
            ['ip', 'ip'],
            ['ttl', 'default', 'value' => -1],
            ['ttl', 'integer', 'min' => -1],
        ]);

        if ($model->hasErrors()) {
            $msg = preg_replace('/[\n]+/', '', Html::errorSummary($model, [
                'header' => 'Please, fix the following errors:',
                'encode' => true
            ]));
            Yii::$app->getSession()->setFlash(
                'error',
                $msg
            );

            if (Yii::$app->getRequest()->getIsAjax()) {
                echo Json::encode([
                    'success' => false,
                    'msg' => $msg,
                ]);

                Yii::$app->end();
            }

            return $this->redirect(['index']);
        }

        $arr = [microtime(true), $model->ttl, '0'];
        $result = $this->redis->hset($this->hashName, $ip, implode('|', $arr));

        if ($result == 1) {
            if (!Yii::$app->getRequest()->getIsAjax()) {
                Yii::$app->getSession()->setFlash(
                    'success',
                    "$ip was banned successfully."
                );
            } else {
                echo Json::encode([
                    'success' => true,
                    'msg' => "$ip was banned successfully.",
                ]);
            }
        } elseif ($result == 0) {
            if (!Yii::$app->getRequest()->getIsAjax()) {
                Yii::$app->getSession()->setFlash(
                    'info',
                    "$ip is already in banned IPs list."
                );
            } else {
                echo Json::encode([
                    'success' => true,
                    'msg' => "$ip is already in banned IPs list.",
                ]);
            }
        }

        if (Yii::$app->getRequest()->getIsAjax()) {
            Yii::$app->end();
        }

        return $this->redirect(['index']);
    }

    /**
     * Unban the given IP.
     *
     * @param string $ip IP addr
     *
     * @return \yii\web\Response
     */
    public function actionUnban($ip)
    {
        $result = $this->redis->hdel($this->hashName, $ip);

        if ($result === '1') {
            $msg = "$ip was unbanned successfully.";
            if (!Yii::$app->getRequest()->getIsAjax()) {
                Yii::$app->getSession()->setFlash(
                    'success',
                    $msg
                );
            } else {
                echo Json::encode([
                    'success' => true,
                    'msg' => $msg,
                ]);

                Yii::$app->end();
            }
        }

        return $this->redirect(['index']);
    }
}