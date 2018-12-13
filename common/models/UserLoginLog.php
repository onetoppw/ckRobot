<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_login_log}}".
 *
 * @property int $id 记录编号
 * @property int $user_id 会员编号
 * @property string $username 会员名称
 * @property string $login_ip 登录IP
 * @property int $client_type 登录客户端类型
 * 1、H5
 * 2、安卓
 * 3、iOS
 * @property string $client_version 客户端版本号
 * @property int $created_at 创建时间
 * @property int $updated_at 最后修改时间
 */
class UserLoginLog extends \yii\db\ActiveRecord
{

    const CLIENT_TYPE_H5 = 1;
    const CLIENT_TYPE_ANDROID = 2;
    const CLIENT_TYPE_IOS = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_login_log}}';
    }

    public static function getLoginClients($key = null)
    {
        $ary = [
            static::CLIENT_TYPE_H5 => 'H5',
            static::CLIENT_TYPE_ANDROID => '安卓',
            static::CLIENT_TYPE_IOS => 'iOS',
        ];
        return $ary[$key] ?? $ary;
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
            [['id'], 'required'],
            [['id', 'user_id', 'client_type', 'created_at', 'updated_at'], 'integer'],
            [['username'], 'string', 'max' => 64],
            [['login_ip'], 'string', 'max' => 50],
            [['client_version'], 'string', 'max' => 255],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '记录编号',
            'user_id' => '会员编号',
            'username' => '会员名称',
            'login_ip' => '登录IP',
            'client_type' => '登录客户端',
            'client_version' => '客户端版本号',
            'created_at' => '登陆时间',
            'updated_at' => '最后修改时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
