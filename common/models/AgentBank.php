<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%agent_bank}}".
 *
 * @property int $id 银行账号ID
 * @property string $agent_id 代理ID
 * @property string $username 用户名
 * @property string $bank_username 开户姓名
 * @property string $bank_account 银行账号
 * @property string $bank_name 银行名称
 * @property string $province 开户省份
 * @property string $city 开户城市
 * @property string $branch_name 网点名称
 * @property int $card_type 银行卡类型 1:借记卡  2：信用卡
 * @property int $status 账号状态 1：启用 0：停用
 * @property int $created_at 创建日期
 * @property int $updated_at 修改日期
 */
class AgentBank extends \yii\db\ActiveRecord
{
    const CARD_DEBIT = 1;
    const CARD_CREDIT = 2;

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%agent_bank}}';
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public static function getCardTypes($key = null)
    {
        $items = [
            self::CARD_DEBIT => '借记卡',
            self::CARD_CREDIT => '信用卡',
        ];
        return $items;
    }

    public static function getStatuses($key = null)
    {
        $status = [
            self::STATUS_ENABLED => '启用',
            self::STATUS_DISABLED => '停用',
        ];
        return $status;
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
            [['agent_id', 'username', 'bank_username', 'bank_account', 'bank_name'], 'required'],
            [['agent_id', 'card_type', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'bank_username', 'bank_account', 'bank_name'], 'string', 'max' => 64],
            [['province', 'city'], 'string', 'max' => 32],
            [['branch_name'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '银行账号ID',
            'agent_id' => '代理ID',
            'username' => '用户名',
            'bank_username' => '开户姓名',
            'bank_account' => '银行账号',
            'bank_name' => '银行名称',
            'province' => '开户省份',
            'city' => '开户城市',
            'branch_name' => '网点名称',
            'card_type' => '银行卡类型',//'银行卡类型 1:借记卡  2：信用卡',
            'status' => '账号状态',//'账号状态 1：启用 0：停用',
            'created_at' => '创建日期',
            'updated_at' => '修改日期',
        ];
    }
}
