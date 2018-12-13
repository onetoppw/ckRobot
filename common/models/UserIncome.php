<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_income}}".
 *
 * @property string $id 会员账户变更日志表
 * @property string $user_id 会员ID
 * @property string $username 会员名称
 * @property int $income_type 收支类型 1收入 2支出
 * @property string $trade_no 交易单号
 * @property string $remark 备注信息
 * @property string $amount 收支金额
 * @property string $after_amount 剩余金额
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class UserIncome extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_income}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'income_type', 'amount', 'after_amount'], 'required'],
            [['user_id', 'income_type', 'updated_at', 'created_at'], 'integer'],
            [['amount', 'after_amount'], 'number'],
            [['username'], 'string', 'max' => 64],
            [['trade_no', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '会员账户变更日志表',
            'user_id' => '会员ID',
            'username' => '会员名称',
            'income_type' => '收支类型 1收入 2支出',
            'trade_no' => '交易单号',
            'remark' => '备注信息',
            'amount' => '收支金额',
            'after_amount' => '剩余金额',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }
}
