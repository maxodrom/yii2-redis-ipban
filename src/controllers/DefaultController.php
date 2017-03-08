<?php
/**
 * @author Max Alexandrov <max@maxodrom.ru>
 * @since 2.0
 * @license MIT
 */

namespace maxodrom\redis\ipban\controllers;

use Yii;
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
            ['ip', 'ip'],
            ['ttl', 'default', 'value' => -1],
            ['ttl', 'integer'],
        ]);

        if ($model->hasErrors()) {
            Yii::$app->getSession()->setFlash(
                'error',
                preg_replace('/[\n]+/', '', Html::errorSummary($model, [
                    'header' => 'Please, fix the following errors:',
                    'encode' => true
                ]))
            );

            return $this->redirect(['index']);
        }

        $arr = [microtime(true), $model->ttl, '0'];
        $result = $this->redis->hset($this->hashName, $ip, implode('|', $arr));

        if ($result == 1) {
            Yii::$app->getSession()->setFlash(
                'success',
                "$ip was banned successfully."
            );
        } elseif ($result == 0) {
            Yii::$app->getSession()->setFlash(
                'info',
                "$ip is already in banned IPs list."
            );
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
            Yii::$app->getSession()->setFlash(
                'success',
                "$ip was unbanned successfully."
            );
        }

        return $this->redirect(['index']);
    }
}