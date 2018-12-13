<?php

namespace common\models;

use common\libs\Constants;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%bet_list}}".
 *
 * @property string $id 投注流水ID
 * @property string $record_id 投注单号
 * @property string $user_id 用户ID
 * @property string $username 用户名
 * @property string $platform_username 平台用户名
 * @property int $platform_id 游戏平台ID
 * @property string $game_type 游戏类型
 * @property string $table_no 桌号
 * @property int $period_boot 靴次
 * @property int $period_round 局次
 * @property int $bet_amount 投注金额
 * @property string $game_result 开奖结果
 * @property string $bet_record 投注点
 * @property int $profit 赢输
 * @property int $amount_before 投注前余额
 * @property int $amount_after 投注后余额
 * @property int $xima_status 洗码状态
 * @property int $xima_type 洗码类型
 * @property string $xima_rate 洗码率
 * @property string $xima 洗码值
 * @property int $state 游戏状态
 * @property int $bet_at 投注时间
 * @property string $player_cards 闲家牌面
 * @property string $banker_cards 庄家牌面
 * @property int $draw_at 开奖时间
 * @property int $created_at 创建时间
 */
class BetList extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bet_list}}';
    }

    public static function recordLabels($record = null)
    {
        $labels = [
            'player' => '<span class="label label-info">闲</span>',
            'palyer' => '<span class="label label-info">闲</span>',
            'banker' => '<span class="label label-danger">庄</span>',
            'tie' => '<span class="label label-success">和</span>',
            'player_pair' => '<span class="label label-primary">闲对</span>',
            'banker_pair' => '<span class="label label-warning">庄对</span>',
            'dragon' => '<span class="label label-danger">龙</span>',
            'tiger' => '<span class="label label-info">虎</span>',
            'dragon_even' => '<span class="label label-warning">龙双</span>',
            'dragon_odd' => '<span class="label label-warning">龙单</span>',
            'tiger_even' => '<span class="label label-primary">虎双</span>',
            'tiger_odd' => '<span class="label label-primary">虎单</span>',
            'small' => '<span class="label label-info">小</span>',
            'big' => '<span class="label label-danger">大</span>',
            'even' => '<span class="label label-info">单</span>',
            'odd' => '<span class="label label-danger">双</span>',
        ];

        return $record ? ($labels[$record] ?? '<span class="label label-default">' . $record . '</span>') : $labels;
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
    public function rules()
    {
        return [
            [['record_id', 'user_id', 'platform_id', 'bet_amount', 'profit', 'amount_before', 'amount_after', 'state', 'bet_at', 'draw_at', 'created_at'], 'integer'],
            [['user_id', 'platform_id'], 'required'],
            [['xima', 'xima_status', 'xima_type', 'xima_rate'], 'number'],
            [['username', 'platform_username', 'game_type'], 'string', 'max' => 64],
            [['table_no', 'period_boot', 'period_round'], 'string', 'max' => 32],
            [['game_result,player_cards,banker_cards'], 'string', 'max' => 128],
            [['bet_record'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '投注流水ID',
            'record_id' => '投注单号',
            'user_id' => '用户ID',
            'username' => '用户名',
            'platform_username' => '平台用户名',
            'platform_id' => '游戏平台ID',
            'game_type' => '游戏类型',
            'table_no' => '桌号',
            'period_boot' => '靴次',
            'period_round' => '局次',
            'bet_amount' => '投注金额',
            'game_result' => '开奖结果',
            'bet_record' => '投注点',
            'profit' => '赢输',
            'amount_before' => '投注前余额',
            'amount_after' => '投注后余额',
            'xima_status' => '洗码状态',
            'xima_type' => '洗码类型',
            'xima_rate' => '洗码率',
            'xima' => '洗码值',
            'state' => '游戏状态',
            'banker_cards' => '庄家牌面',
            'player_cards' => '闲家牌面',
            'bet_at' => '投注时间',
            'draw_at' => '开奖时间',
            'created_at' => '创建时间',
        ];
    }

    /**
     * @return User|\yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return Platform|\yii\db\ActiveQuery
     */
    public function getPlatform()
    {
        return $this->hasOne(Platform::class, ['id' => 'platform_id']);
    }

    /**
     * @return GameType|\yii\db\ActiveQuery
     */
    public function getGameType()
    {
        return $this->hasOne(GameType::class, ['name_en' => 'game_type']);
    }

    public function calculateXima()
    {

        //如果状态不为null，则表示已经计算洗码
        if ($this->xima_status !== null)
            return false;

        $user = $this->user;
        $members[] = $user;
        $members[] = $this->user->inviteAgent;
        while (end($members)->parent) {
            $members[] = end($members)->parent;
        }

        //获得顶级代理账号
        $model = array_pop($members);

        $option = Yii::$app->option;
        //如果洗码状态为不可见,返回洗码值0
        if ($option->agent_xima_status == Constants::YesNo_No || $model->xima_status == Constants::YesNo_No) {
            $this->xima_status = Constants::YesNo_No;
            $this->xima = 0;
            return false;
        }

        if ($option->agent_xima_type == Constants::XIMA_ONE_SIDED && $model->xima_type == Constants::XIMA_TWO_SIDED) {
            $model->xima_type = Constants::XIMA_ONE_SIDED;
        }
        if ($option->agent_xima_rate < $model->xima_rate) {
            $model->xima_rate = $option->agent_xima_rate;
        }

        $this->xima_status = $model->xima_status;
        $this->xima_type = $model->xima_type;
        $this->xima_rate = $model->xima_rate;


        //如果投注结果为输，洗码为单边洗码则返回洗码值0
        if ($this->xima_type == Constants::XIMA_ONE_SIDED && $this->profit <= 0) {
            $this->xima = 0;
            return false;
        }

        //洗码值等于洗码率 x 投注金额
        $this->xima = $this->xima_rate * $this->bet_amount;

        //逐级分配代理洗码值
        while ($sub = array_pop($members)) {
            $ximaRecord = new AgentXimaRecord([
                'user_id' => $this->user_id,
                'bet_id' => $this->id,
                'record_id' => $this->record_id,
                'agent_id' => $model->id,
                'bet_amount' => $this->bet_amount,
                'platform_id' => $this->platform_id,
                'game_type' => $this->game_type,
                'profit' => $this->profit,
                'xima_type' => $model->xima_type,
                'xima_rate' => $model->xima_rate
            ]);
            $sub = $this->resetXimaSetting($model, $sub);
            if ($sub->xima_status == Constants::YesNo_No || ($sub->xima_type == Constants::XIMA_ONE_SIDED && $this->profit <= 0)) {
                $ximaRecord->sub_xima_amount = 0;
                $ximaRecord->sub_xima_rate = 0;
                $ximaRecord->xima_amount = $model->xima_rate * $this->bet_amount;
                return $ximaRecord->save(false);
            }
            $ximaRecord->sub_xima_rate = $sub->xima_rate;
            $ximaRecord->sub_xima_amount = $sub->xima_rate * $this->bet_amount;
            $ximaRecord->xima_amount = ($model->xima_rate - $sub->xima_rate) * $this->bet_amount;
            $ximaRecord->save(false);
            $model = clone $sub;
        }

        //计算用户洗码值

        $userXimaRecord = new AgentXimaRecord([
            'user_id' => $this->user_id,
            'bet_id' => $this->id,
            'record_id' => $this->record_id,
            'bet_amount' => $this->bet_amount,
            'platform_id' => $this->platform_id,
            'game_type' => $this->game_type,
            'profit' => $this->profit,
            'xima_type' => $model->xima_type,
            'xima_rate' => $model->xima_rate
        ]);

        if ($model->xima_status == Constants::YesNo_No || ($model->xima_type == Constants::XIMA_ONE_SIDED && $this->profit <= 0)) {
            return false;
        }
        $userXimaRecord->xima_amount = $model->xima_rate * $this->bet_amount;
        return $userXimaRecord->save(false);
    }

    /**
     * 根据上级重置下级洗码设置
     * @param $parent
     * @param $sub
     */
    private function resetXimaSetting($parent, $sub)
    {
        if ($parent->xima_status == Constants::YesNo_No) {
            $sub->xima_status = Constants::YesNo_No;
            $sub->xima_rate = 0;
        }

        if ($parent->xima_type == Constants::XIMA_ONE_SIDED && $sub->xima_type == Constants::XIMA_TWO_SIDED) {
            $sub->xima_type = Constants::XIMA_ONE_SIDED;
        }
        if ($parent->xima_rate < $sub->xima_rate) {
            $sub->xima_rate = $parent->xima_rate;
        }
        return $sub;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $user = User::findOne(['id' => $this->user_id]);
        if ($user) {
            $start_time = strtotime(date('Y-m-d 00:00:00'));
            $end_time = strtotime(date('Y-m-d 23:59:59'));
            $platform_id = $this->platform_id;
            $agent_id = isset($user->invite_agent_id) ? $user->invite_agent_id : 0;
            $amount = isset($this->bet_amount) ? $this->bet_amount : 0;
            $win_profit = 0;
            $lost_profit = 0;
            if (isset($this->profit) && $this->profit > 0) {
                $win_profit = $this->profit;
            }
            if (isset($this->profit) && $this->profit < 0) {
                $lost_profit = abs($this->profit);
            }
            /*
            $dis_count = BetList::find()->select('user_id')->where(['between', 'bet_at', $start_time, $end_time])->distinct()->count();
            $all_count = BetList::find()->select('user_id')->where(['between', 'bet_at', $start_time, $end_time])->count();
            $sum_money = BetList::find()->where(['between', 'bet_at', $start_time, $end_time])->sum('bet_amount');
            $win_money = BetList::find()->where(['>', 'profit', 0])->andFilterWhere(['between', 'bet_at', $start_time, $end_time])->sum('profit');
            $lost_money = BetList::find()->where(['<', 'profit', 0])->andFilterWhere(['between', 'bet_at', $start_time, $end_time])->sum('profit');

            $daliy = [
                'dbu' => $dis_count,
                'dbo' => $all_count,
                'dba' => $sum_money,
                'dpa' => $win_money,
                'dla' => abs($lost_money)
            ];
            Daily::updateCounter($daliy);

            $data = [];
            $plat = BetList::find()->select(['platform_id','id'=>'count(id)','bet_amount'=>'sum(bet_amount)'])
                    ->where(['between', 'bet_at', $start_time, $end_time])->groupBy('platform_id')->all();
            foreach ($plat as $key =>$val){
                $data[$val->platform_id]['platform_id'] = $val->platform_id;
                $data[$val->platform_id]['dbo'] = $val->id;
                $data[$val->platform_id]['dba'] = $val->bet_amount;
            }
            $dis_num = BetList::find()->select(['platform_id','user_id'=>'count(distinct(user_id))'])
                       ->where(['between', 'bet_at', $start_time, $end_time])->groupBy('platform_id')->all();
            foreach ($dis_num as $key => $val){
                $data[$val->platform_id]['dbu'] = $val->user_id;
            }
            $win_amount = BetList::find()->select(['platform_id','profit'=>'sum(profit)'])->where(['>', 'profit', 0])
                          ->andFilterWhere(['between', 'bet_at', $start_time, $end_time])->groupBy('platform_id')->all();
            foreach ($win_amount as $key => $val){
                $data[$val->platform_id]['dpa'] =  $val->profit;
            }
            $lost_amount = BetList::find()->select(['platform_id','profit'=>'sum(profit)'])->where(['<', 'profit', 0])
                           ->andFilterWhere(['between', 'bet_at', $start_time, $end_time])->groupBy('platform_id')->all();
            foreach ($lost_amount as $key =>  $val){
                $data[$val->platform_id]['dla'] = abs($val->profit);
            }
            foreach($data as $arr){
                PlatformDaily::updateCounter($arr);
            }
            */

            $count = BetList::find()->select(['platform_id',])->where(['user_id' => $user->id])
                ->andFilterWhere(['between', 'bet_at', $start_time, $end_time])->count();

            $com_data = [
                'dba' => $amount,     //日投注额度
                'dpa' => $win_profit, //日赢额度
                'dla' => $lost_profit //日输额度
            ];
            if ($count == 1) {
                $com_data['dbu'] = 1; //日投注用户数
                $com_data['dbo'] = 1; //日投注单数
                $agent_data['agent_id'] = $agent_id; //代理id
                $platform_data['platform_id'] = $platform_id; //平台id
                Daily::addCounter($com_data);
                PlatformDaily::addCounter(array_merge($com_data, $platform_data));
                AgentDaily::addCounter(array_merge($com_data, $agent_data));
            } else {
                $com_data['dbo'] = 1;
                $agent_data['agent_id'] = $agent_id;
                $platform_data['platform_id'] = $platform_id;
                Daily::addCounter($com_data);
                PlatformDaily::addCounter(array_merge($com_data, $platform_data));
                AgentDaily::addCounter(array_merge($com_data, $agent_data));
            }
        }
    }

}
