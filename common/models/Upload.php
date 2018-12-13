<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%upload}}".
 *
 * @property int $id 编号
 * @property string $name 文件名
 * @property int $size 大小
 * @property string $type 类型
 * @property string $url 地址
 * @property string $thumb 缩略图
 * @property int $deleteUrl 1存在，0删除
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class Upload extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%upload}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['size', 'deleteUrl', 'updated_at', 'created_at'], 'integer'],
            [['name', 'type'], 'string', 'max' => 255],
            [['url', 'thumb'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'name' => '文件名',
            'size' => '大小',
            'type' => '类型',
            'url' => '地址',
            'thumb' => '缩略图',
            'deleteUrl' => '1存在，0删除',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }
}
