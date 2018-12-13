<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%platform}}".
 *
 * @property int $id 平台ID
 * @property string $name 游戏平台名称
 * @property string $code 平台代码
 * @property string $api_host api地址
 * @property string $app_id 应用ID
 * @property string $app_secret ap密钥
 * @property string $login_url 登陆地址
 * @property int $status 平台状态 1 激活 0 停用
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class Platform extends \yii\db\ActiveRecord
{

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%platform}}';
    }

    public static function findByCode($code)
    {
        return static::findOne(['code' => $code, 'status' => static::STATUS_ENABLED]);
    }

    public static function getStatuses($key = null)
    {
        $status = [
            self::STATUS_ENABLED => '激活',
            self::STATUS_DISABLED => '停用',
        ];
        return $status[$key] ?? $status;
    }

    public static function getPlatformNames()
    {
        return ArrayHelper::map(self::find()->orderBy("id asc")
            ->andFilterWhere(['status' => Platform::STATUS_ENABLED])
            ->asArray()->all(), 'id', 'name');
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
            [['name', 'code'], 'required'],
            [['status', 'updated_at', 'created_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['code'], 'string', 'max' => 16],
            [['api_host', 'app_id', 'app_secret', 'login_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '平台ID',
            'name' => '游戏平台名称',
            'code' => '平台代码',
            'api_host' => 'api地址',
            'app_id' => '应用ID',
            'app_secret' => 'ap密钥',
            'login_url' => '登陆地址',
            'status' => '状态',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(PlatformAccount::class, ['platform_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $account = new PlatformAccount();
            $account->platform_id = $this->id;
            $account->save(false);
        }
    }
}
