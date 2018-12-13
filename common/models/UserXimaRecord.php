<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_xima_record}}".
 *
 * @property string $id 交易ID
 * @property string $user_id 会员ID
 * @property int $platform_id 平台ID
 * @property string $game_type 游戏类型
 * @property int $record_id 投注单号
 * @property int $bet_id 投注记录ID
 * @property int $bet_amount 投注金额
 * @property int $profit 赢输
 * @property int $xima_type 洗码类型 1单边 2双边
 * @property string $xima_rate 洗码率
 * @property string $xima_amount 洗码值
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class UserXimaRecord extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_xima_record}}';
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
            [['user_id', 'platform_id', 'game_type', 'bet_id', 'profit'], 'required'],
            [['user_id', 'record_id', 'platform_id', 'bet_id', 'bet_amount', 'profit', 'xima_type', 'updated_at', 'created_at'], 'integer'],
            [['xima_rate', 'xima_amount'], 'number'],
            [['game_type'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '洗码流水ID',
            'user_id' => '代理',
            'platform_id' => '游戏平台',
            'game_type' => '游戏类型',
            'record_id' => '投注单号',
            'bet_id' => '投注记录',
            'bet_amount' => '投注金额',
            'profit' => '赢输',
            'xima_type' => '洗码类型',
            'xima_rate' => '洗码率',
            'xima_amount' => '洗码值',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $this->user->account->xima_amount += $this->xima_amount;
            $this->user->account->save(false);
        }
    }
}
