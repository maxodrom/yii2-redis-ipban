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