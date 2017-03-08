<?php
/**
 * @author Max Alexandrov <max@maxodrom.ru>
 * @since 1.2
 * @license MIT
 */

/* @var $this yii\web\View */
?>
<div class="row">
    <div class="col-lg-12">
        <?php if(Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <?= Yii::$app->getSession()->getFlash('success'); ?>
        </div>
        <?php endif; ?>
        <?php if(Yii::$app->session->hasFlash('info')): ?>
        <div class="alert alert-info alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <?= Yii::$app->getSession()->getFlash('info'); ?>
        </div>
        <?php endif; ?>
        <?php if(Yii::$app->session->hasFlash('warning')): ?>
        <div class="alert alert-warning alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <?= Yii::$app->getSession()->getFlash('warning'); ?>
        </div>
        <?php endif; ?>
        <?php if(Yii::$app->session->hasFlash('danger')): ?>
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <?= Yii::$app->getSession()->getFlash('danger'); ?>
        </div>
        <?php endif; ?>
    </div>
</div>