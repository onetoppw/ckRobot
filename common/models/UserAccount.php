<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_account}}".
 *
 * @property int $user_id 会员编号
 * @property string $available_amount 可用余额
 * @property string $frozen_amount 冻结金额
 * @property int $user_point 会员积分
 * @property string $xima_amount 洗码值
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class UserAccount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_account}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'user_point', 'updated_at', 'created_at'], 'integer'],
            [['available_amount', 'frozen_amount', 'xima_amount'], 'number'],
            [['user_id'], 'unique'],
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
            'user_id' => '会员编号',
            'available_amount' => '可用余额',
            'frozen_amount' => '冻结金额',
            'user_point' => '会员积分',
            'xima_amount' => '洗码值',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
