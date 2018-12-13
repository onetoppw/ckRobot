<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%platform_account_record}}".
 *
 * @property int $id 游戏平台账户变更记录ID
 * @property int $platform_id 代理ID
 * @property string $name 变更名称
 * @property string $trade_no 交易单号
 * @property string $amount 变更额度
 * @property int $switch 收支 1 收入 2支出
 * @property string $after_amount 变更后余额
 * @property string $remark 备注
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class PlatformAccountRecord extends \yii\db\ActiveRecord
{

    const SWITCH_IN = 1;
    const SWITCH_OUT = 2;
    public $available_amount;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%platform_account_record}}';
    }

    /**
     * @return array
     */
    public static function getSwitchs($key = null)
    {
        $ary = [
            self::SWITCH_IN => '增加',
            self::SWITCH_OUT => '减少',
        ];
        return $ary[$key] ?? $ary;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['platform_id', 'switch', 'amount'], 'required'],
            [['platform_id', 'switch', 'updated_at', 'created_at'], 'integer'],
            [['amount', 'after_amount'], 'integer', 'min' => 0],
            [['amount'], 'checkAmount'],
            [['name', 'remark'], 'string', 'max' => 255],
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

    public function scenarios()
    {
        return [
            'create' => ['switch', 'available_amount', 'amount', 'remark'],
        ];
    }

    public function checkAmount($attribute, $params)
    {
        if ($this->switch && $this->switch == 2) {
            if ($this->amount > $this->available_amount)
                $this->addError($attribute, '减少额度超出当前平台可用额度' . $this->available_amount);
        }

    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '游戏平台账户变更记录ID',
            'platform_id' => '游戏平台',
            'name' => '交易名称',
            'trade_no' => '交易单号',
            'amount' => '交易额度',
            'switch' => '收支',
            'after_amount' => '交易后余额',
            'remark' => '备注',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }

    public function beforeSave($insert)
    {
        if (!$insert)
            return parent::beforeSave($insert);
        $account = PlatformAccount::findOne(['platform_id' => $this->platform_id]);
        if (!$account) return false;
        $account->available_amount = $account->available_amount + (3 - 2 * $this->switch) * $this->amount;
        $this->after_amount = $account->available_amount;

        if (!$account->save(false)) {
            return false;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatform()
    {
        return $this->hasOne(Platform::class, ['id' => 'platform_id']);
    }
}
