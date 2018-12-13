<?php

namespace common\models;

use common\helpers\CronParser;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%task}}".
 *
 * @property int $id
 * @property string $name 定时任务名称
 * @property string $route 任务路由
 * @property string $crontab_str crontab格式
 * @property int $switch 任务开关 0关闭 1开启
 * @property int $status 任务运行状态 0正常 1任务报错
 * @property int $run_times 运行次数
 * @property int $error_times 任务失败次数
 * @property int $last_run_at 任务上次运行时间
 * @property int $next_run_at 任务下次运行时间
 * @property int $exec_mem 任务执行消耗内存(单位/byte)
 * @property int $exec_time 任务执行消耗时间
 */
class Task extends \yii\db\ActiveRecord
{
    const STATUS_OK = 0;
    const STATUS_ERROR = 1;
    const SWITCH_ENABLED = 1;
    const SWITCH_DISABLED = 0;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%task}}';
    }

    public static function getStatuses($key = null)
    {
        $status = [
            self::STATUS_OK => '正常',
            self::STATUS_ERROR => '错误'
        ];
        return $status[$key] ?? $status;
    }

    public static function getSwitchs($key = null)
    {
        $status = [
            self::SWITCH_DISABLED => '关闭',
            self::SWITCH_ENABLED => '开启'
        ];
        return $status[$key] ?? $status;
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
            [['name', 'route', 'crontab_str'], 'required'],
            [['switch', 'status', 'run_times', 'error_times', 'last_run_at', 'next_run_at', 'exec_mem', 'exec_time'], 'integer'],
            [['name', 'route', 'crontab_str'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '任务编号',
            'name' => '任务名称',
            'route' => '路由',
            'crontab_str' => 'crontab格式',
            'switch' => '任务开关',
            'status' => '运行状态',
            'run_times' => '运行次数',
            'error_times' => '失败次数',
            'last_run_at' => '上次运行时间',
            'next_run_at' => '下次运行时间',
            'exec_mem' => '消耗内存',
            'exec_time' => '消耗时间(ms)',
        ];
    }

    /**
     * 计算下次运行时间
     * @author jlb
     */
    public function getNextRunDate()
    {
        if (!CronParser::check($this->crontab_str)) {
            throw new \Exception("格式校验失败: {$this->crontab_str}", 1);
        }
        return strtotime(CronParser::formatToDate($this->crontab_str, 1)[0]);
    }

}
