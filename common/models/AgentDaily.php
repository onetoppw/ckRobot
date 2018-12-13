<?php

namespace common\models;

/**
 * This is the model class for table "{{%agent_daily}}".
 *
 * @property int $ymd 日期
 * @property int $agent_id 代理ID
 * @property int $dnu 日新增用户
 * @property int $dau 日活跃用户
 * @property int $ndu 日首存用户数
 * @property int $nda 日首存额度
 * @property int $dbu 日投注用户数
 * @property int $dbo 日投注单数
 * @property int $dba 日投注额度
 * @property int $ddu 日存款用户数
 * @property int $dda 日存款额度
 * @property int $dwu 日取款用户数
 * @property int $dwa 日取款额度
 * @property int $dpa 日赢额度
 * @property int $dla 日输额度
 * @property string $dxm 日洗码值
 * @property int $dna 日新增代理
 */
class AgentDaily extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%agent_daily}}';
    }

    /**
     *
     *  AgentDaily::addCounter(['dnu'=>1]);
     * @param $data
     *
     * @return bool
     */
    public static function addCounter($data)
    {
        $model = static::findOne(['ymd' => date('Ymd'), 'agent_id' => $data['agent_id']]);
        if (!$model) {
            $model = new AgentDaily();
            $model->ymd = date('Ymd');
            $model->agent_id = $data['agent_id'];
        }
        foreach ($data as $attr => $num) {
            if ($model->hasAttribute($attr)) {
                if ($attr != 'agent_id')
                    $model->$attr += (int)$num;
            }
        }
        return $model->save(false);
    }

    /**
     *
     *  AgentDaily::updateCounter(['dau'=>10]);
     * @param $data
     *
     * @return bool
     */
    public static function updateCounter($data)
    {
        $model = static::findOne(['ymd' => date('Ymd'), 'agent_id' => $data['agent_id']]);
        if (!$model) {
            $model = new AgentDaily();
            $model->ymd = date('Ymd');
            $model->agent_id = $data['agent_id'];
        }
        foreach ($data as $attr => $num) {
            if ($model->hasAttribute($attr)) {
                if ($attr != 'agent_id')
                    $model->$attr += (int)$num;
            }
        }
        return $model->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ymd', 'agent_id'], 'required'],
            [['ymd', 'agent_id', 'dnu', 'dau', 'ndu', 'nda', 'dbu', 'dbo', 'dba', 'ddu', 'dda', 'dwu', 'dwa', 'dpa', 'dla', 'dna'], 'integer'],
            [['dxm'], 'number'],
            [['ymd', 'agent_id'], 'unique', 'targetAttribute' => ['ymd', 'agent_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ymd' => '日期',
            'agent_id' => '代理ID',
            'dnu' => '新增用户',
            'dau' => '活跃用户',
            'ndu' => '首存用户数',
            'nda' => '首存额度',
            'dbu' => '投注用户数',
            'dbo' => '投注单数',
            'dba' => '投注额度',
            'ddu' => '存款用户数',
            'dda' => '存款额度',
            'dwu' => '取款用户数',
            'dwa' => '取款额度',
            'dpa' => '赢额度',
            'dla' => '输额度',
            'dxm' => '洗码值',
            'dna' => '新增代理',
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
