<?php
/**
 * @author Max Alexandrov <max@maxodrom.ru>
 * @since 2.0
 * @license MIT
 */

namespace maxodrom\redis\ipban\controllers;

use yii\web\Controller;

/**
 * Class DefaultController
 *
 * @package maxodrom\redis\ipban\controllers
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
}