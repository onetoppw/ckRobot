<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-12-27 14:53
 */

namespace common\helpers;

use yii\imagine\Image;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class Util
{
    /**
     * 处理单模型单文件上传
     *
     * @param ActiveRecord $model
     * @param $field
     * @param $insert
     * @param $uploadPath
     * @param array $options
     *                  $options[thumbSizes] array 需要截图的尺寸，如[['w'=>100,'h'=>100]]
     *                  $options['filename'] string 新文件名，默认自动生成
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function handleModelSingleFileUpload(ActiveRecord &$model, $field, $insert, $uploadPath, $options = [])
    {
        $upload = UploadedFile::getInstance($model, $field);
        /* @var $cdn \feehi\cdn\TargetInterface */
        $cdn = yii::$app->get('cdn');
        if ($upload !== null) {
            $uploadPath = yii::getAlias($uploadPath);
            if (strpos(strrev($uploadPath), '/') !== 0) $uploadPath .= '/';
            if (!FileHelper::createDirectory($uploadPath)) {
                $model->addError($field, "Create directory failed " . $uploadPath);
                return false;
            }
            $fullName = isset($options['filename']) ? $uploadPath . $options['filename'] : $uploadPath . date('YmdHis') . '_' . uniqid() . '.' . $upload->getExtension();
            if (!$upload->saveAs($fullName)) {
                $model->addError($field, yii::t('app', 'Upload {attribute} error: ' . $upload->error, ['attribute' => yii::t('app', ucfirst($field))]) . ': ' . $fullName);
                return false;
            }
            $model->$field = str_replace(yii::getAlias('@webroot'), '', $fullName);
            $cdn->upload($fullName, $model->$field);
            if (isset($options['thumbSizes'])) self::thumbnails($fullName, $options['thumbSizes']);
            if (!$insert) {
                $file = yii::getAlias('@webroot') . $model->getOldAttribute($field);
                if (file_exists($file) && is_file($file)) unlink($file);
                if ($cdn->exists($model->getOldAttribute($field))) $cdn->delete($model->getOldAttribute($field));
                if (isset($options['thumbSizes'])) self::deleteThumbnails($file, $options['thumbSizes']);
            }
        } else {
            if ($model->$field === '0') {//删除
                $file = yii::getAlias('@webroot') . $model->getOldAttribute($field);
                if (file_exists($file) && is_file($file)) unlink($file);
                if ($cdn->exists($model->getOldAttribute($field))) $cdn->delete($model->getOldAttribute($field));
                if (isset($options['thumbSizes'])) self::deleteThumbnails($file, $options['thumbSizes']);
                $model->$field = '';
            } else {
                if ($insert) {
                    $model->$field = '';
                } else {
                    $model->$field = $model->getOldAttribute($field);
                }
            }
        }
    }

    /**
     * 生成各个尺寸的缩略图
     *
     * @param $fullName string 原图路径
     * @param array $thumbSizes 二维数组 如 [["w"=>110,"height"=>"20"],["w"=>200,"h"=>"30"]]则生成两张缩量图，分别为宽110高20和宽200高30
     */
    public static function thumbnails($fullName, array $thumbSizes)
    {
        foreach ($thumbSizes as $info) {
            $thumbFullName = self::getThumbName($fullName, $info['w'], $info['h']);
            Image::thumbnail($fullName, $info['w'], $info['h'])->save($thumbFullName);
            /** @var $cdn \feehi\cdn\TargetInterface */
            $cdn = yii::$app->get('cdn');
            $cdn->upload($thumbFullName, str_replace(yii::getAlias('@webroot'), '', $thumbFullName));
        }
    }

    /**
     * 根据原图路径生成缩略图路径
     *
     * @param $fullName string 原图路径
     * @param $width int 长
     * @param $heith int 宽
     * @return string 如/path/to/uploads/article/xx@100x20.png
     */
    public static function getThumbName($fullName, $width, $heith)
    {
        $dotPosition = strrpos($fullName, '.');
        $thumbExt = "@" . $width . 'x' . $heith;
        if ($dotPosition === false) {
            $thumbFullName = $fullName . $thumbExt;
        } else {
            $thumbFullName = substr_replace($fullName, $thumbExt, $dotPosition, 0);
        }
        return $thumbFullName;
    }

    /**
     * 删除各个尺寸的缩略图
     *
     * @param $fullName string 原图图片路径
     * @param $thumbSizes array 二维数组 如 [["w"=>110,"height"=>"20"],["w"=>200,"h"=>"30"]]则生成两张缩量图，分别为宽110高20和宽200高30
     */
    public static function deleteThumbnails($fullName, array $thumbSizes, $deleteOrigin = false)
    {
        foreach ($thumbSizes as $info) {
            $thumbFullName = self::getThumbName($fullName, $info['w'], $info['h']);
            if (file_exists($thumbFullName) && is_file($thumbFullName)) unlink($thumbFullName);
            $cdn = yii::$app->get('cdn');
            $cdn->delete(str_replace(yii::getAlias("@webroot"), '', $thumbFullName));
        }
        if ($deleteOrigin) {
            file_exists($fullName) && unlink($fullName);
        }
    }

    /**
     * 处理单模型单文件非常态上传
     *
     * @param ActiveRecord $model
     * @param $field
     * @param $uploadPath
     * @param $oldFullname
     * @param array $options
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function handleModelSingleFileUploadAbnormal(ActiveRecord &$model, $field, $uploadPath, $oldFullname, $options = [])
    {
        if (!isset($options['successDeleteOld'])) $options['successDeleteOld'] = true;//成功后删除旧文件
        if (!isset($options['deleteOldFile'])) $options['deleteOldFile'] = false;//删除旧文件
        $upload = UploadedFile::getInstance($model, $field);
        /* @var $cdn \feehi\cdn\TargetInterface */
        $cdn = yii::$app->get('cdn');
        if ($upload !== null) {
            $uploadPath = yii::getAlias($uploadPath);

            if (strpos(strrev($uploadPath), '/') !== 0) $uploadPath .= '/';
            if (!FileHelper::createDirectory($uploadPath)) {
                $model->addError($field, "Create directory failed " . $uploadPath);
                return false;
            }
            $fullName = isset($options['filename']) ? $uploadPath . $options['filename'] : $uploadPath . date('YmdHis') . '_' . uniqid() . '.' . $upload->getExtension();
            if (!$upload->saveAs($fullName)) {
                $model->addError($field, yii::t('app', 'Upload {attribute} error: ' . $upload->error, ['attribute' => yii::t('app', ucfirst($field))]) . ': ' . $fullName);
                return false;
            }
            $model->$field = str_replace(yii::getAlias('@webroot'), '', $fullName);
            $cdn->upload($fullName, $model->$field);
            if (isset($options['thumbSizes'])) self::thumbnails($fullName, $options['thumbSizes']);
            if ($options['successDeleteOld'] && $oldFullname) {
                $file = yii::getAlias('@webroot') . $oldFullname;
                if (file_exists($file) && is_file($file)) unlink($file);
                if ($cdn->exists($oldFullname)) $cdn->delete($oldFullname);
                if (isset($options['thumbSizes'])) self::deleteThumbnails($file, $options['thumbSizes']);
            }
        } else {
            if ($model->$field === '0') {//删除
                $file = yii::getAlias('@webroot') . $oldFullname;
                if (file_exists($file) && is_file($file)) unlink($file);
                if ($cdn->exists($oldFullname)) $cdn->delete($oldFullname);
                if (isset($options['thumbSizes'])) self::deleteThumbnails($file, $options['thumbSizes']);
                $model->$field = '';
            } else {
                $model->$field = $oldFullname;
            }
        }
        if ($options['deleteOldFile']) {
            $file = yii::getAlias('@webroot') . $oldFullname;
            if (file_exists($file) && is_file($file)) unlink($file);
            if ($cdn->exists($oldFullname)) $cdn->delete($oldFullname);
            if (isset($options['thumbSizes'])) self::deleteThumbnails($file, $options['thumbSizes']);
        }
    }

    /**
     * @param $url :请求路径
     * @param null $post :如果不为空则提交post请求
     * @param bool $header :请求头
     * @param int $timeout :超时时间
     * @return mixed
     */
    public static function request($url, $post = null, $header = false, $timeout = 30)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);          //单位 秒，也可以使用
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36');
        //curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/tmp.cookie");
        //curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/tmp.cookie");
        if (0 === strpos($url, 'https')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return ['code' => $errno, 'msg' => $errmsg, 'result' => $result, 'info' => $info];
    }

    /**
     * 获取客户端类型
     *
     */
    public static function getClientType()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($agent, 'iPhone') || strpos($agent, 'iPad')) {
            return 'IOS';
        } else if (strpos($agent, 'Android')) {
            return 'Android';
        } else {
            return 'H5';
        }
    }

    /**
     * 获取客户端版本号
     *
     */
    public static function getClientVersion()
    {
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $version = '';

        //微信打开
        if (stripos($ua, 'MicroMessenger')) {
            preg_match('/MicroMessenger\/([\d\.]+)/i', $ua, $match);
            return 'Wechat ' . $match[1];
        }

        //获取火狐浏览器的版本号
        if (stripos($ua, 'Firefox/')) {
            preg_match('/Firefox\/([^;)]+)/i', $ua, $match);
            $version = 'Firefox' . $match[1];
        } //360游览器
        else if (stripos($ua, '360SE')) {
            $version = '360游览器' . '';
        } //搜狗游览器
        else if (stripos($ua, 'SE') && stripos($ua, 'MetaSr')) {
            preg_match('/SE\s([\w\.]+)/i', $ua, $match);
            $version = '搜狗' . $match[1];
        } //获取google chrome的版本号
        else if (stripos($ua, 'Chrome')) {
            preg_match('/Chrome\/([\d\.]+)/', $ua, $match);
            $version = 'Chrome' . $match[1];
        } //遨游游览器
        elseif (stripos($ua, 'Maxthon')) {
            preg_match('/Maxthon([\s\d\.]+)/', $ua, $match);
            $version = '傲游' . $match[1];
        } //欧朋游览器
        elseif (stripos($ua, 'opera')) {
            preg_match('/opera\/([\d\.\/]+)/', $ua, $match);
            $version = 'Opera' . $match[1];
        } //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
        else if (stripos($ua, 'Edge')) {
            preg_match('/Edge\/([\d\.]+)/', $ua, $match);
            $version = 'Edge' . $match[1];
        } //UC游览器
        else if (stripos($ua, 'UCBrowser')) {
            preg_match('/UCBrowser\/([\d\.]+)/', $ua, $match);
            $version = 'UCBrowser ' . $match[1];
        } //QQ游览器（note:移动端没有检测版本号）
        else if (stripos($ua, 'TencentTraveler') || stripos($ua, 'QQBrowser')) {
            preg_match('/TencentTraveler\s([\d\.]+)/', $ua, $match);
            $version = 'QQ游览器' . $match[1];
        } //百度游览器
        else if (stripos($ua, 'baidubrowser')) {
            preg_match('/baidubrowser\/([\d\.]+)/', $ua, $match);
            $version = '百度游览器' . $match[1];
        } //移动设备的IE游览器
        else if (stripos($ua, 'IEMobile')) {
            preg_match('/IEMobile\/([\d\.]+)+/i', $ua, $match);
            $version = 'IE' . $match[1];
        } //获取IE的版本号
        else if (stripos($ua, 'MSIE')) {
            preg_match('/MSIE\s+([^;)]+)+/i', $ua, $match);
            $version = 'IE' . $match[1];
        } else if (strpos($ua, 'Android') !== false) {//strpos()定位出第一次出现字符串的位置，这里定位为0
            preg_match("/(?<=Android )[\d\.]{1,}/", $ua, $match);
            $version = 'Android ' . $match[0];
        } elseif (strpos($ua, 'iPhone') !== false) {
            preg_match("/(?<=CPU iPhone OS )[\d\_]{1,}/", $ua, $match);
            echo 'iPhone OS ' . str_replace('_', '.', $match[0]);
        } elseif (strpos($ua, 'iPad') !== false) {
            preg_match("/(?<=CPU OS )[\d\_]{1,}/", $ua, $match);
            echo 'iPad OS ' . str_replace('_', '.', $match[0]);
        } else {
            $version = $ua;
        }

        return $version;
    }

    /*
     * between时间处理
     * @param $timeAttributes string 时间属性
     * @param $request obj/array 请求对象/数组
     * @return array
     */
    public static function getBetweenDate($timeAttributes, $request)
    {
        $startDate = isset($request['startDate']) ? $request['startDate'] : '';
        $endDate = isset($request['endDate']) ? $request['endDate'] : '';
        $info = [];
        if (!empty($startDate)) {
            $startTime = strtotime($startDate . ' 00:00:00');
            $endTime = $endDate ? strtotime($endDate . ' 23:59:59') : strtotime($startDate . ' 23:59:59');
            $info = ['between', $timeAttributes, $startTime, $endTime];
        }
        return $info;
    }

    /*
     * 获取月份
     * @return array
     */
    public static function getMonth()
    {
        $moths = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        return $moths;
    }

}