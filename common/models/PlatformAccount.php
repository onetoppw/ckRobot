<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%platform_account}}".
 *
 * @property int $platform_id 代理ID
 * @property string $available_amount 可用余额
 * @property string $frozen_amount 冻结金额
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class PlatformAccount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%platform_account}}';
    }

    public static function getToalAvailableAmount()
    {
        return static::find()->sum('available_amount');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['platform_id'], 'required'],
            [['platform_id', 'updated_at', 'created_at'], 'integer'],
            [['available_amount', 'frozen_amount'], 'number'],
            [['platform_id'], 'unique'],
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
            'platform_id' => '代理ID',
            'available_amount' => '可用余额',
            'frozen_amount' => '冻结金额',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }

}
