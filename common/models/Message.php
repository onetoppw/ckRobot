<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%message}}".
 *
 * @property int $id 消息ID
 * @property string $title 消息标题
 * @property string $content 消息内容
 * @property int $is_canceled 是否取消 1取消 0 否
 * @property int $canceled_at 取消时间
 * @property int $is_deleted 是否删除 1 删除 0 否
 * @property int $deleted_at 删除时间
 * @property int $level 优先级 1 普通 2 高 3 紧急
 * @property int $user_type 用户类型 1 会员 2 代理 3 管理员
 * @property int $notify_obj 通告对象类型 1单个用户 2 多个用户 3 全部用户 4用户类型
 * @property int $user_group 用户组
 * @property int $sender_id 发送者ID 0为系统发送
 * @property string $sender_name 发送者名称
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class Message extends \yii\db\ActiveRecord
{
    const OBJ_MEMBER = 1;
    const OBJ_AGENT = 2;
    const OBJ_ADMIN = 3;

    const SEND_ONE = 1;
    const SEND_MULTI = 2;
    const SEND_ALL = 3;
    const SEND_GROUP = 4;

    const LEVEL_LOW = 1;
    const LEVEL_MID = 2;
    const LEVEL_HIGH = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%message}}';
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public static function getUserTypes($key = null)
    {
        $items = [
            self::OBJ_MEMBER => '会员',
            self::OBJ_AGENT => '代理',
            self::OBJ_ADMIN => '管理员',
        ];
        return $items[$key] ?? $items;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public static function getNotifyObjs($key = null)
    {
        $items = [
            self::SEND_ONE => '单用户',
            self::SEND_MULTI => '多用户',
            self::SEND_ALL => '全体',
            self::SEND_GROUP => '群组',
        ];
        return $items[$key] ?? $items;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public static function getLevels($key = null)
    {
        $items = [
            self::LEVEL_LOW => '普通',
            self::LEVEL_MID => '重要',
            self::LEVEL_HIGH => '紧急',
        ];
        return $items[$key] ?? $items;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public static function getStatus($key = null)
    {
        $items = [
            1 => '已删除',
        ];
        return $items[$key] ?? $items;
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
            [['title', 'content', 'level', 'user_type'], 'required'],
            [['is_deleted', 'deleted_at', 'level', 'user_type', 'notify_obj', 'user_group', 'sender_id', 'updated_at', 'created_at'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['content'], 'string', 'max' => 512],
            [['sender_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '消息ID',
            'title' => '消息标题',
            'content' => '消息内容',
            'is_deleted' => '是否删除',
            'deleted_at' => '删除时间',
            'level' => '优先级',
            'user_type' => '用户类型',
            'notify_obj' => '通告对象',
            'user_group' => '用户组',
            'sender_id' => '发送者ID',
            'sender_name' => '发送者名称',
            'updated_at' => '更新日期',
            'created_at' => '发送时间',
        ];
    }

    public function beforeSave($insert)
    {

        return parent::beforeSave($insert);
    }

}
