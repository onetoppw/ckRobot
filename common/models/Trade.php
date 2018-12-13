<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%trade}}".
 *
 * @property string $id 交易ID
 * @property string $user_id 会员ID
 * @property int $switch 收支类型 1收入 2支出
 * @property string $trade_no 交易单号
 * @property int $trade_type_id 交易类型ID
 * @property string $remark 备注信息
 * @property string $amount 收支金额
 * @property string $after_amount 交易后余额
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class Trade extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%trade}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'switch', 'amount', 'after_amount'], 'required'],
            [['user_id', 'switch', 'trade_type_id', 'updated_at', 'created_at'], 'integer'],
            [['amount', 'after_amount'], 'number'],
            [['trade_no', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'switch' => 'Switch',
            'trade_no' => 'Trade No',
            'trade_type_id' => 'Trade Type ID',
            'remark' => 'Remark',
            'amount' => 'Amount',
            'after_amount' => 'After Amount',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }
}