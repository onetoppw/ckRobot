<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%rebate}}".
 *
 * @property int $id 序号
 * @property string $ym 期数
 * @property int $agent_id 代理ID
 * @property string $agent_name 代理账号
 * @property int $agent_level 代理层级
 * @property string $rebate_rate 占成
 * @property string $self_bet_amount 自身有效投注
 * @property string $self_profit_loss 自身会员损益
 * @property string $sub_bet_amount 下级有效投注总额
 * @property string $sub_profit_loss 下级代理会员损益
 * @property string $sub_rebate_amount 下级返佣
 * @property string $self_rebate_amount 自身返佣
 * @property string $total_rebate_amount 合计返佣
 * @property int $created_at 计佣时间
 */
class Rebate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%rebate}}';
    }

    public static function getYms()
    {
        $data = self::find()->select('ym')->distinct(true)->orderBy('ym')->asArray()->all();

        return ArrayHelper::map($data, 'ym', 'ym');
    }

    public static function getLevels()
    {
        $maxLevel = Agent::find()->max('agent_level');
        $levels = [];
        for ($i = 1; $i <= $maxLevel; $i++) {
            $levels[$i] = $i;
        }
        return $levels;
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
            [['agent_id', 'agent_level', 'created_at'], 'integer'],
            [['rebate_rate', 'self_bet_amount', 'self_profit_loss', 'sub_bet_amount', 'sub_profit_loss', 'sub_rebate_amount', 'self_rebate_amount', 'total_rebate_amount'], 'number'],
            [['ym'], 'string', 'max' => 7],
            [['agent_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '序号',
            'ym' => '期数',
            'agent_id' => '代理ID',
            'agent_name' => '代理账号',
            'agent_level' => '代理层级',
            'rebate_rate' => '返佣率',
            'self_bet_amount' => '自身会员有效投注',
            'self_profit_loss' => '自身会员损益',
            'sub_bet_amount' => '下级有效投注总额',
            'sub_profit_loss' => '下级代理会员损益',
            'sub_rebate_amount' => '下级返佣',
            'self_rebate_amount' => '自身返佣',
            'total_rebate_amount' => '合计返佣',
            'created_at' => '计佣时间',
        ];
    }

    /**
     * 计算返佣值
     */
    public function calRebate($profit, $amount)
    {
        $this->agent_level = $this->agent->agent_level;
        $this->agent_name = $this->agent->username;
        $this->rebate_rate = $this->agent->rebate_rate;
        $this->self_profit_loss = $profit;
        $this->self_bet_amount = $amount;
        $this->self_rebate_amount = $this->rebate_rate * $profit;
    }


    /**
     * 添加下级返佣
     */
    public function addSubRebate($profit, $amount, $rebate_rate)
    {

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(Agent::class, ['id' => 'agent_id']);
    }
}
