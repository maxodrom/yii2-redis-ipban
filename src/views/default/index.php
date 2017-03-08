<?php
/**
 * @author Max Alexandrov <max@maxodrom.ru>
 * @since 1.2
 * @license MIT
 */

use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ArrayDataProvider */
?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1>Banned IPs list</h1>

        <?= $this->render('../flash-messages') ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'ip',
                [
                    'attribute' => 'added',
                    'value' => function ($model) {
                        return Yii::$app->getFormatter()->asDatetime($model['added'], 'full');
                    },
                ],
                [
                    'attribute' => 'ttl',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::tag(
                            'span',
                            $model['ttl'] != -1 ? $model['ttl'] . ' sec' : 'permanently',
                            [
                                'class' => 'label label-primary',
                            ]
                        );
                    },
                ],
                [
                    'attribute' => 'hits',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a(
                            $model['hits'],
                            null,
                            ['class' => 'btn btn-warning btn-sm']
                        );
                    },
                ],
                [
                    'class' => ActionColumn::className(),
                    'header' => 'Actions',
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $model, $key) {
                            return Html::a(
                                'unban',
                                Url::to(['unban', 'ip' => $model['ip']]),
                                [
                                    'class' => 'btn btn-success btn-sm'
                                ]
                            );
                        },
                    ]
                ],
            ]
        ]) ?>
    </div>
</div>
