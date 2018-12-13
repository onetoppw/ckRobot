<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%message_flag}}".
 *
 * @property string $id 消息标记ID
 * @property int $message_id 消息ID
 * @property string $user_id 用户ID
 * @property int $is_read 是否阅读
 * @property int $read_at 阅读时间
 * @property int $is_deleted 是否删除
 * @property int $deleted_at 删除时间
 * @property int $user_type 用户类型 1 会员 2 代理 3 管理员
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class MessageFlag extends \yii\db\ActiveRecord
{
    const UN_READ = 0;
    const ALREADY_READ = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%message_flag}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message_id', 'user_id'], 'required'],
            [['message_id', 'user_id', 'is_read', 'read_at', 'is_deleted', 'deleted_at', 'user_type', 'updated_at', 'created_at'], 'integer'],
            [['message_id', 'user_id'], 'unique', 'targetAttribute' => ['message_id', 'user_id']],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => '消息标记ID',
            'message_id' => '消息ID',
            'user_id' => '用户ID',
            'is_read' => '是否阅读',
            'read_at' => '阅读时间',
            'is_deleted' => '是否删除',
            'deleted_at' => '删除时间',
            'user_type' => '用户类型 1 会员 2 代理 3 管理员',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }
}
