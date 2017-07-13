<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use vova07\imperavi\Widget;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use  aerozoll\blog\models\Blog;
use  aerozoll\blog\models\Tag;
use  common\models\ImageManager;
/* @var $this yii\web\View */
/* @var $model aerozoll\blog\models\Blog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="blog-form">

    <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="row">
        <?= $form->field($model, 'file', ['options' =>['class' => 'col-xs-6']
        ])->widget(\kartik\file\FileInput::className(),
            [ 'options' => ['accept' => 'image/*'],]
        );
        ?>

        <?= $form->field($model, 'title', ['options' =>['class' => 'col-xs-6']
        ])->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'url',[
            'options' =>['class' => 'col-xs-6']
        ])->textInput(['maxlength' => true])->label('чпу') ?>

        <?= $form->field($model, 'status_id',[
            'options' =>['class' => 'col-xs-6']
        ])->dropDownList(Blog::STATUS_LIST) ?>

        <?= $form->field($model, 'sort',[
            'options' =>['class' => 'col-xs-6']
        ])->textInput() ?>

        <?= $form->field($model, 'tags_array')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(Tag::find()->all(),'id','name'),
            'language' => 'ru',
            'options' => ['placeholder' => 'Select a tag ...', 'multiple' => true],
            'pluginOptions' => [
                'allowClear' => true,
                'tags' => true,
                'maximumInputLength' => 10
            ],
        ]);?>
    </div>
    <?= $form->field($model, 'text')->widget(Widget::className(), [
        'settings' => [
            'lang' => 'ru',
            'minHeight' => 200,
            'imageUpload' => Url::to(['/site/save-redactor-img', 'sub'=>'blog']),
            'plugins' => ['clips','fullscreen']
        ]
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?= \kartik\file\FileInput::widget([
        'name'=>'ImageManager[attachment]',
         'options' => [
             'multiple' => true
         ],
         'pluginOptions'  =>[
             'deleteUrl'=> yii\helpers\Url::to(['/blog/delete-image']),
             'initialPreview' => $model->imagesLinks,
             'initialPreviewAsData' => true,
             'overwriteInitial' => false,
             'initialPreviewConfig' => $model->imagesLinksData,
             'uploadUrl'=> yii\helpers\Url::to(['site/save-img']),
             'uploadExtraData' => [
                 'ImageManager[class]' => $model->formName(),
                 'ImageManager[item_id]' => $model->id
             ],
             'maxFileCount' => 10,
         ],
        'pluginEvents' =>[
            'filesorted' => new yii\web\JsExpression('function(event, params){
                $.post("'. Url::toRoute(["/blog/sort-image", "id" => $model->id]).'",{sort:params});
            }')
        ],
    ]);
    ?>

</div>
