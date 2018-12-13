<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%trade_type}}".
 *
 * @property int $id 交易种类ID
 * @property string $name 类型名称
 * @property int $inout 收支 0未知 1 收入 2 支出
 */
class TradeType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%trade_type}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['inout'], 'integer'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '交易种类ID',
            'name' => '类型名称',
            'inout' => '收支 0未知 1 收入 2 支出',
        ];
    }
}
