<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%system_account}}".
 *
 * @property string $id 主键
 * @property string $available_amount 可用额度
 * @property string $frozen_amount 冻结额度
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class SystemAccount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%system_account}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['available_amount', 'frozen_amount'], 'number'],
            [['updated_at', 'created_at'], 'integer'],
            [['id'], 'string', 'max' => 1],
            [['id'], 'unique'],
        ];
    }

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
            'id' => '主键',
            'available_amount' => '可用额度',
            'frozen_amount' => '冻结额度',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }
}
