<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2016-10-16 17:15
 */

namespace common\libs;

use InvalidArgumentException;
use yii;

class Constants
{

    const YesNo_Yes = 1;
    const YesNo_No = 0;
    const AGENT_OPENED = 0;
    const AGENT_CLOSE_REG = 1;
    const AGENT_CLOSE_LOGIN = 2;
    const AGENT_CLOSED = 9;
    const XIMA_ONE_SIDED = 1;
    const XIMA_TWO_SIDED = 2;
    const COMMENT_INITIAL = 0;
    const COMMENT_PUBLISH = 1;
    const COMMENT_RUBISSH = 2;
    const TARGET_BLANK = '_blank';
    const TARGET_SELF = '_self';
    const HTTP_METHOD_ALL = 0;
    const HTTP_METHOD_GET = 1;
    const HTTP_METHOD_POST = 2;
    const PUBLISH_YES = 1;
    const PUBLISH_NO = 0;
    const INPUT_INPUT = 1;
    const INPUT_TEXTAREA = 2;
    const INPUT_UEDITOR = 3;
    const INPUT_IMG = 4;
    const ARTICLE_VISIBILITY_PUBLIC = 1;
    const ARTICLE_VISIBILITY_COMMENT = 2;
    const ARTICLE_VISIBILITY_SECRET = 3;
    const ARTICLE_VISIBILITY_LOGIN = 4;
    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 0;
    const AD_IMG = 1;
    const AD_VIDEO = 2;
    const AD_TEXT = 3;
    const TRADE_TYPE_DESOPIT = 1;
    const TRADE_TYPE_WITHDRAW = 2;
    const TRADE_TYPE_ADDAMOUNT = 3;
    const TRADE_TYPE_REDUCEAMOUNT = 4;
    const TRADE_TYPE_ADMINADD = 5;
    const TRADE_TYPE_ADMINREDUCE = 6;

    public static function getYesNoItems($key = null)
    {
        $items = [
            self::YesNo_Yes => yii::t('app', 'Yes'),
            self::YesNo_No => yii::t('app', 'No'),
        ];
        return self::getItems($items, $key);
    }

    private static function getItems($items, $key = null)
    {
        if ($key !== null) {
            if (key_exists($key, $items)) {
                return $items[$key];
            }
            throw new InvalidArgumentException('Unknown key:' . $key);
        }
        return $items;
    }

    public static function getWebsiteStatusItems($key = null)
    {
        $items = [
            self::YesNo_Yes => yii::t('app', 'Opened'),
            self::YesNo_No => yii::t('app', 'Closed'),
        ];
        return self::getItems($items, $key);
    }

    public static function getAgentStatusItems($key = null)
    {
        $items = [
            self::AGENT_OPENED => '开放代理',
            self::AGENT_CLOSE_REG => '关闭代理注册',
            self::AGENT_CLOSE_LOGIN => '关闭代理登陆',
            self::AGENT_CLOSED => '关闭代理',

        ];
        return self::getItems($items, $key);
    }

    public static function getXimaTypes($key = null)
    {
        $items = [
            self::XIMA_ONE_SIDED => '单边',
            self::XIMA_TWO_SIDED => '双边'
        ];
        return self::getItems($items, $key);
    }

    public static function getCommentStatusItems($key = null)
    {
        $items = [
            self::COMMENT_INITIAL => yii::t('app', 'Not Audited'),
            self::COMMENT_PUBLISH => yii::t('app', 'Passed'),
            self::COMMENT_RUBISSH => yii::t('app', 'Unpassed'),
        ];
        return self::getItems($items, $key);
    }

    public static function getTargetOpenMethod($key = null)
    {
        $items = [
            self::TARGET_BLANK => yii::t('app', 'Yes'),
            self::TARGET_SELF => yii::t('app', 'No'),
        ];
        return self::getItems($items, $key);
    }

    public static function getHttpMethodItems($key = null)
    {
        $items = [
            self::HTTP_METHOD_ALL => 'all',
            self::HTTP_METHOD_GET => 'get',
            self::HTTP_METHOD_POST => 'post',
        ];
        return self::getItems($items, $key);
    }

    public static function getArticleStatus($key = null)
    {
        $items = [
            self::PUBLISH_YES => yii::t('app', 'Publish'),
            self::PUBLISH_NO => yii::t('app', 'Draft'),
        ];
        return self::getItems($items, $key);
    }

    public static function getInputTypeItems($key = null)
    {
        $items = [
            self::INPUT_INPUT => 'input',
            self::INPUT_TEXTAREA => 'textarea',
            self::INPUT_UEDITOR => 'ueditor',
            self::INPUT_IMG => 'image',
        ];
        return self::getItems($items, $key);
    }

    public static function getArticleVisibility($key = null)
    {
        $items = [
            self::ARTICLE_VISIBILITY_PUBLIC => yii::t('app', 'Public'),
            self::ARTICLE_VISIBILITY_COMMENT => yii::t('app', 'Reply'),
            self::ARTICLE_VISIBILITY_SECRET => yii::t('app', 'Password'),
            self::ARTICLE_VISIBILITY_LOGIN => yii::t('app', 'Login'),
        ];
        return self::getItems($items, $key);
    }

    public static function getStatusItems($key = null)
    {
        $items = [
            self::STATUS_ENABLE => '启用',
            self::STATUS_DISABLE => '禁用',
        ];
        return self::getItems($items, $key);
    }

    public static function getAdTypeItems($key = null)
    {
        $items = [
            self::AD_IMG => 'image',
            self::AD_VIDEO => 'video',
            self::AD_TEXT => 'text',
        ];
        return self::getItems($items, $key);
    }

    public static function getTradeTypeItems($key = null)
    {

        $items = [
            self::TRADE_TYPE_DESOPIT => '存款',
            self::TRADE_TYPE_WITHDRAW => '取款',
            self::TRADE_TYPE_ADDAMOUNT => '上分',
            self::TRADE_TYPE_REDUCEAMOUNT => '下分',
            self::TRADE_TYPE_ADMINADD => '人工增加',
            self::TRADE_TYPE_ADMINREDUCE => '人工减少',
        ];
        return self::getItems($items, $key);
    }
}
