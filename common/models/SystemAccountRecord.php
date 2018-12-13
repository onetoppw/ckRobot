<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%system_account_record}}".
 *
 * @property int $id 系统账户变更ID
 * @property string $name 账户变更名称
 * @property string $amount 变更额度
 * @property string $trade_no 交易编号
 * @property int $switch 收支 1 收入 2 支出
 * @property string $after_amount 交易后余额
 * @property string $remark 备注信息
 * @property int $confirm_by_id 确认者ID
 * @property string $confirm_by_name 确认者名称
 * @property int $confirm_at 确认时间
 * @property string $confirm_remark 确认备注
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class SystemAccountRecord extends \yii\db\ActiveRecord
{

    const SWITCH_IN = 1;
    const SWITCH_OUT = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%system_account_record}}';
    }

    /**
     * @return array
     */
    public static function getSwitchs($key = null)
    {
        $ary = [
            self::SWITCH_IN => '收入',
            self::SWITCH_OUT => '支出',
        ];
        return $ary[$key] ?? $ary;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount', 'after_amount', 'trade_no'], 'number'],
            [['switch', 'confirm_by_id', 'confirm_at', 'updated_at', 'created_at'], 'integer'],
            [['name', 'remark', 'confirm_remark'], 'string', 'max' => 255],
            [['confirm_by_name'], 'string', 'max' => 64],
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
            'id' => '系统账户变更ID',
            'name' => '账户变更名称',
            'amount' => '变更额度',
            'trade_no' => '交易编号',
            'switch' => '收支',
            'after_amount' => '交易后余额',
            'remark' => '备注信息',
            'confirm_by_id' => '确认者ID',
            'confirm_by_name' => '确认者名称',
            'confirm_at' => '确认时间',
            'confirm_remark' => '确认备注',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }

}
