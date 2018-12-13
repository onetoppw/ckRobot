<?php

namespace common\models;

/**
 * This is the model class for table "{{%daily}}".
 *
 * @property int $ymd 日期
 * @property int $dnu 日新增用户
 * @property int $dau 日活跃用户
 * @property int $ndu 日首存用户数
 * @property int $nda 日首存额度
 * @property int $dbu 日投注用户数
 * @property int $dba 日投注额度
 * @property int $ddu 日存款用户数
 * @property int $dda 日存款额度
 * @property int $dwu 日取款用户数
 * @property int $dwa 日取款额度
 * @property int $dpa 日赢额度
 * @property int $dla 日输额度
 */
class Daily extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%daily}}';
    }

    /*
     *后台首页  投注输赢
     * @params $startDate string 开始时间
     * @params $endDate string 截止时间
     */
    public static function getSumData($startDate, $endDate = 'now')
    {
        $startDate = (int)date('Ymd', strtotime($startDate));
        $endDate = (int)date('Ymd', strtotime($endDate));
        $data = static::find()
            ->select('sum(dnu) as dnu, sum(dau) as dau, sum(ndu) as ndu, sum(nda) as nda,sum(dbu) as dbu, 
            sum(dba) as dba, sum(ddu) as ddu, sum(dda) as dda, sum(dwu) as dwu, sum(dwa) as dwa,sum(dbo) as dbo, sum(dpa) as dpa, sum(dla) as dla')
            ->where(['between', 'ymd', $startDate, $endDate])->asArray()->one();
        return $data;
    }

    /*
     *  后台首先“用户”和“存取款”统计图数据
     * @param string $startDate  开始时间
     * @param string $endDate  截止时间
     * @param string $month 月份（两位数月份如：'01'）
     * @param array $data
     * @return array
     */
    public static function getDaliyData($startDate = '', $endDate = '', $month, $data = array())
    {
        $sum = static::find()
            ->select('sum(dnu) as dnu, sum(dau) as dau, sum(ndu) as ndu,sum(dda) as dda, sum(dwa) as dwa')
            ->where(['between', 'ymd', $startDate, $endDate])->asArray()->one();
        $data['user'][] = ["{$month}月", $sum['dnu'] ? $sum['dnu'] : 0, $sum['dau'] ? $sum['dau'] : 0, $sum['ndu'] ? $sum['ndu'] : 0];
        $data['dw'][] = ["{$month}月", $sum['dda'] ? $sum['dda'] : 0, $sum['dwa'] ? $sum['dwa'] : 0];
        return $data;
    }

    /**
     *
     *  Daily::addCounter(['dnu'=>1]);
     * @param $data
     *
     * @return bool
     */
    public static function addCounter($data)
    {
        $model = static::findOne(['ymd' => date('Ymd')]);
        if (!$model) {
            $model = new Daily();
            $model->ymd = date('Ymd');
        }
        foreach ($data as $attr => $num) {
            if ($model->hasAttribute($attr)) {
                $model->$attr += (int)$num;
            }
        }
        return $model->save(false);
    }

    /**
     *
     *  Daily::updateCounter(['dau'=>10]);
     * @param $data
     *
     * @return bool
     */
    public static function updateCounter($data)
    {
        $model = static::findOne(['ymd' => date('Ymd')]);
        if (!$model) {
            $model = new Daily();
            $model->ymd = date('Ymd');
        }
        foreach ($data as $attr => $num) {
            if ($model->hasAttribute($attr)) {
                $model->$attr = (int)$num;
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
            [['ymd'], 'required'],
            [['ymd', 'dnu', 'dau', 'ndu', 'nda', 'dbu', 'dba', 'ddu', 'dda', 'dwu', 'dwa', 'dpa', 'dla'], 'integer'],
            [['ymd'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ymd' => '日期',
            'dnu' => '新增用户',
            'dau' => '活跃用户',
            'ndu' => '首存用户数',
            'nda' => '首存额度',
            'dbu' => '投注用户数',
            'dba' => '投注额度',
            'ddu' => '存款用户数',
            'dda' => '存款额度',
            'dwu' => '取款用户数',
            'dwa' => '取款额度',
            'dpa' => '赢额度',
            'dla' => '输额度',
        ];
    }
}
