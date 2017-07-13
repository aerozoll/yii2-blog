<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use  aerozoll\blog\models\Blog;
/* @var $this yii\web\View */
/* @var $searchModel  aerozoll\blog\models\search\BlogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->title = 'Blogs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blog-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Blog', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
            'text:ntext',
            ['attribute' =>'url', 'format'=>'raw'],
            [
                'attribute' => 'status_id',
                'filter' => Blog::STATUS_LIST,
                'value' => 'statusName'
            ],
            ['attribute' =>'tags', 'value' => 'tagsAsString'],
            // 'sort',
            'smallImage:image',
            'date_update',
            'date_create',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
