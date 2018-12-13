<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%platform_daily}}".
 *
 * @property int $ymd 日期
 * @property int $platform_id 平台ID
 * @property int $dnu 日新增用户
 * @property int $dau 日活跃用户
 * @property int $dua 日上分额度
 * @property int $dda 日下分额度
 * @property int $dbu 日投注人数
 * @property int $dbo 日投注单数
 * @property int $dba 日投注额度
 * @property int $dpa 日赢额度
 * @property int $dla 日输额度
 * @property string $dxm 日洗码值
 */
class PlatformDaily extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%platform_daily}}';
    }

    /**
     *
     *  PlatDaily::addCounter(['dnu'=>1]);
     * @param $data
     *
     * @return bool
     */
    public static function addCounter($data)
    {
        $model = static::findOne(['ymd' => date('Ymd'), 'platform_id' => $data['platform_id']]);
        if (!$model) {
            $model = new PlatformDaily();
            $model->ymd = date('Ymd');
            $model->platform_id = $data['platform_id'];
        }
        foreach ($data as $attr => $num) {
            if ($model->hasAttribute($attr)) {
                if ($attr != 'platform_id')
                    $model->$attr += (int)$num;
            }
        }
        return $model->save(false);
    }

    /**
     *
     *  PlatDaily::updateCounter(['dau'=>10]);
     * @param $data
     *
     * @return bool
     */
    public static function updateCounter($data)
    {
        $model = static::findOne(['ymd' => date('Ymd'), 'platform_id' => $data['platform_id']]);
        if (!$model) {
            $model = new PlatformDaily();
            $model->ymd = date('Ymd');
            $model->platform_id = $data['platform_id'];
        }
        foreach ($data as $attr => $num) {
            if ($model->hasAttribute($attr)) {
                if ($attr != 'platform_id')
                    $model->$attr += (int)$num;
            }
        }
        return $model->save(false);
    }

    public static function getBetData($platform_id, $startDate = '', $endDate = '')
    {
        $result = static::find()
            ->select('sum(dbo) as dbo, sum(dpa) as dpa, sum(dla) as dla')
            ->where(['platform_id' => $platform_id])
            ->andFilterWhere(['between', 'ymd', $startDate, $endDate])->asArray()->one();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ymd', 'platform_id'], 'required'],
            [['ymd', 'platform_id', 'dnu', 'dau', 'dua', 'dda', 'dbu', 'dbo', 'dba', 'dpa', 'dla'], 'integer'],
            [['dxm'], 'number'],
            [['ymd', 'platform_id'], 'unique', 'targetAttribute' => ['ymd', 'platform_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ymd' => '日期',
            'platform_id' => '平台',
            'dnu' => '新增用户',
            'dau' => '活跃用户',
            'dua' => '上分额度',
            'dda' => '下分额度',
            'dbu' => '投注人数',
            'dbo' => '投注单数',
            'dba' => '投注额度',
            'dpa' => '赢额度',
            'dla' => '输额度',
            'dxm' => '洗码值',
        ];
    }

    /*
     *  后台首页“平台投注”和“平台输赢”统计图数据
     * @param int $platform_id 平台id
     * @param string $startDate  开始时间
     * @param string $endDate  截止时间
     * @return array
     */

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatform()
    {
        return $this->hasOne(Platform::class, ['id' => 'platform_id']);
    }
}
