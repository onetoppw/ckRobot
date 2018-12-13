<?php

namespace common\models;

use common\libs\Constants;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%notice}}".
 *
 * @property int $id 公告ID
 * @property string $content 公告内容
 * @property int $user_type 公告对象 0 全体 1 会员 2 代理 3 管理员
 * @property int $set_top 置顶
 * @property int $expire_at 公告截止日期
 * @property int $is_deleted 删除标记 1 删除
 * @property int $deleted_at 删除日期
 * @property int $is_cancled 取消标记 1 取消
 * @property int $cancled_at 取消日期
 * @property int $publish_by 发布者ID
 * @property string $publish_name 发布者
 * @property int $updated_at 更新日期
 * @property int $created_at 创建日期
 */
class Notice extends \yii\db\ActiveRecord
{

    const OBJ_ALL = 0;
    const OBJ_MEMBER = 1;
    const OBJ_AGENT = 2;
    const OBJ_ADMIN = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%notice}}';
    }

    /**
     *
     * 获取最近的公告，如果不设限制，则返回全部未到期公告
     * @param int $limit
     * @param int $obj
     * @return array
     */
    public static function getRecentNoticeS($limit = 6, $obj = self::OBJ_MEMBER)
    {

        $query = static::find()->where(['and',
            ['user_type' => [0, $obj]],
            ['>', 'expire_at', time()],
            ['is_deleted' => Constants::YesNo_No]
        ]);

        $count = $query->count('id');
        $notices = $query->orderBy(['set_top' => SORT_DESC, 'created_at' => SORT_DESC]);
        if ($limit > 0) $notices = $notices->limit($limit);

        return ['count' => $count, 'data' => $notices->all()];
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public static function getUserTypes($key = null)
    {
        $items = [
            self::OBJ_ALL => '全部',
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
    public static function getStatus($key = null)
    {
        $items = [
            1 => '已删除',
        ];
        return $items[$key] ?? $items;
    }

    /**
     * @return array
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
            [['content', 'set_top', 'expire_at', 'user_type'], 'required'],
            [['user_type', 'set_top', 'is_deleted', 'deleted_at', 'is_cancled', 'cancled_at', 'publish_by', 'updated_at', 'created_at'], 'integer'],
            [['content'], 'string', 'max' => 512],
            [['publish_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '公告ID',
            'content' => '公告内容',
            'user_type' => '公告对象',
            'set_top' => '置顶',
            'expire_at' => '公告截止日期',
            'is_deleted' => '删除标记',
            'deleted_at' => '删除日期',
            'is_cancled' => '取消标记',
            'cancled_at' => '取消日期',
            'publish_by' => '发布者ID',
            'publish_name' => '发布者',
            'updated_at' => '更新日期',
            'created_at' => '创建日期',
        ];
    }

    public function beforeSave($insert)
    {

        $this->expire_at = strtotime($this->expire_at);
        if ($this->expire_at < time())
            $this->expire_at = time() + 7 * 24 * 3600;

        $this->publish_by = yii::$app->getUser()->getId();
        $this->publish_name = yii::$app->getUser()->getIdentity()->username;

        return parent::beforeSave($insert);
    }
}
