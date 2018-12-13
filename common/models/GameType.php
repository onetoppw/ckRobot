<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%game_type}}".
 *
 * @property int $id 游戏类型ID
 * @property string $name 类型名称
 * @property string $name_en 游戏类型英文名
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class GameType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%game_type}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['updated_at', 'created_at'], 'integer'],
            [['name', 'name_en'], 'string', 'max' => 128],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '游戏类型ID',
            'name' => '类型名称',
            'name_en' => '游戏类型英文名',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }
}
