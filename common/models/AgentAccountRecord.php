<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%agent_account_record}}".
 *
 * @property int $id 代理账户变更记录ID
 * @property int $agent_id 代理ID
 * @property string $name 变更名称
 * @property string $amount 变更额度
 * @property int $switch 收支 1 收入 2支出
 * @property string $after_amount 变更后余额
 * @property string $remark 备注
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class AgentAccountRecord extends \yii\db\ActiveRecord
{

    const SWITCH_IN = 1;
    const SWITCH_OUT = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%agent_account_record}}';
    }

    public static function getSwitchStatus($key = null)
    {
        $ary = [
            static::SWITCH_IN => '收入',
            static::SWITCH_OUT => '支出',
        ];
        return $ary[$key] ?? $ary;
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
    public function rules()
    {
        return [
            [['agent_id'], 'required'],
            [['agent_id', 'switch', 'updated_at', 'created_at'], 'integer'],
            [['amount', 'after_amount'], 'number'],
            [['name', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '交易ID',
            'agent_id' => '代理ID',
            'name' => '交易项目',
            'amount' => '交易额度',
            'switch' => '收支',
            'after_amount' => '交易后余额',
            'remark' => '备注',
            'updated_at' => '更新日期',
            'created_at' => '交易日期',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(Agent::class, ['id' => 'agent_id']);
    }
}
