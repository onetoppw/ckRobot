<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_agent}}".
 *
 * @property int $agent_id 代理编号
 * @property int $user_id 会员编号
 * @property int $parent_id 上级代理
 * @property string $agent_rate 代理占成
 * 利润分配
 * @property string $shuffle_rate 洗码率
 * @property int $shuffle_type 洗码类型
 * 1、单边洗码
 * 2、双边洗码
 * @property string $agent_remark 备注说明
 * @property int $game_id 游戏编号
 * @property int $sku_id 游戏类型编号
 * @property int $id
 */
class UserAgent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_agent}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['agent_id', 'id'], 'required'],
            [['agent_id', 'user_id', 'parent_id', 'shuffle_type', 'game_id', 'sku_id', 'id'], 'integer'],
            [['agent_rate', 'shuffle_rate'], 'number'],
            [['agent_remark'], 'string', 'max' => 200],
            [['agent_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'agent_id' => '代理编号',
            'user_id' => '会员编号',
            'parent_id' => '上级代理',
            'agent_rate' => '代理占成
            利润分配',
            'shuffle_rate' => '洗码率',
            'shuffle_type' => '洗码类型
            1、单边洗码
            2、双边洗码',
            'agent_remark' => '备注说明',
            'game_id' => '游戏编号',
            'sku_id' => '游戏类型编号',
            'id' => 'ID',
        ];
    }
}
