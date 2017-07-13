<?php

namespace  aerozoll\blog\models;

use common\components\behaviors\StatusBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use yii\helpers\Url;
use yii\web\UploadedFile;
use common\models\User;
use common\models\ImageManager;


/**
 * This is the model class for table "blog".
 *
 * @property integer $id
 * @property string $title
 * @property string $text
 * @property string $url
 * @property integer $status_id
 * @property integer $sort
 * @property string $date_update
 * @property string $date_create
 * @property string $image
 */
class Blog extends \yii\db\ActiveRecord
{
    public $tags_array;
    public $file;

    const STATUS_LIST = ['off','on'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'blog';
    }

    public function behaviors()
    {
        return [
            'timestampBehavior' =>[
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'date_create',
                'updatedAtAttribute' => 'date_update',
                'value' => new Expression('NOW()'),
            ],
            'statusBehavior' =>[
                'class' => StatusBehavior::className(),
                'statusList' => self::STATUS_LIST,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'url'], 'required'],
            [['text'], 'string'],
            [['url'], 'unique'],
            [['status_id'], 'integer'],
            [['sort'], 'integer','min'=>1, 'max' =>99],
            [['title', 'url'], 'string', 'max' => 255],
            [['image'], 'string', 'max' => 100],
            [['file'], 'image'],
            [['tags_array', 'date_create', 'date_update'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'text' => 'Текст',
            'url' => 'Url',
            'status_id' => 'Статус',
            'sort' => 'Сортировка',
            'tags_array' => 'Теги',
            'date_update' => 'Дата обновления',
            'date_create' => 'Дата создания',
            'image' => 'image',
        ];
    }


    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getBlogTag()
    {
        return $this->hasMany(BlogTag::className(), ['blog_id' => 'id']);
    }

    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->via('blogTag');
    }

    public function getImages()
    {
        return $this->hasMany(ImageManager::className(), ['item_id' => 'id'])->andWhere([
            'class'=>self::tableName()])->orderBy('sort');
    }

    public function getImagesLinks(){

        return ArrayHelper::getColumn($this->images, 'imageUrl');
    }

    public function getImagesLinksData(){

        return ArrayHelper::toArray($this->images,[
            ImageManager::className()=> [
                'caption' => 'name',
                'key' => 'id',
            ]
        ]);
    }

    public function getTagsAsString()
    {
        $arr = ArrayHelper::map($this->tags, 'id','name');
        return implode(',',$arr);

    }
    public function getSmallImage()
    {
        if(empty($this->image)){
           return str_replace('admin.','',Url::home(true)).'uploads/images/image_removed.jpg';
        }
        $path = str_replace('admin.','',Url::home(true)).'uploads/images/blog/';
        return $path.'50x50/'.$this->image;

    }

    public function afterFind()
    {
        parent::afterFind();

        $this->tags_array = $this->tags;
    }

    public function beforeDelete()
    {
        parent::beforeDelete();

        if(parent::beforeDelete()){
            BlogTag::deleteAll(['tag_id'=> $this->id]);
            return true;
        }else{
            return false;
        }
    }

    public function beforeSave($insert)
    {
        if($file = UploadedFile::getInstance($this, 'file')){
            $dir = Yii::getAlias('@images').'/blog/';
            if(!empty($this->image)){
                if(file_exists($dir.$this->image)){
                    unlink($dir.$this->image);
                }
                if(file_exists($dir.'50x50/'.$this->image)){
                    unlink($dir.'50x50/'.$this->image);
                }
                if(file_exists($dir.'800x/'.$this->image)){
                    unlink($dir.'800x/'.$this->image);
                }
            }

            $this->image = strtotime('now').'_'.Yii::$app->getSecurity()->generateRandomString(3).
                '.'.$file->extension;
            $file->saveAs($dir.$this->image);
            $imag = Yii::$app->image->load($dir.$this->image);
            $imag->background('#fff',0);
            $imag->resize('50', '50', Yii\image\drivers\Image::INVERSE);
            $imag->crop('50', '50');
            $imag->save($dir.'50x50/'.$this->image, 90);
            $imag = Yii::$app->image->load($dir.$this->image);
            $imag->background('#fff',0);
            $imag->resize('800', null, Yii\image\drivers\Image::INVERSE);
            $imag->save($dir.'800x/'.$this->image, 90);

        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $old_tag = ArrayHelper::map($this->tags, 'id','id');

        if(is_array($this->tags_array)){
            foreach ($this->tags_array as $one){
                if(!in_array($one, $old_tag)){
                    $model = new BlogTag();
                    $model->blog_id = $this->id;
                    $model->tag_id = $one;
                    $model->save();
                }
                if(isset($old_tag[$one])){
                    unset($old_tag[$one]);
                }
            }
        }else  BlogTag::deleteAll(['tag_id'=> $old_tag]);
    }
}
