<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "budget".
 *
 * @property int $id
 * @property string $product
 * @property string $category
 * @property float|null $january
 * @property float|null $february
 * @property float|null $march
 * @property float|null $april
 * @property float|null $may
 * @property float|null $june
 * @property float|null $july
 * @property float|null $august
 * @property float|null $september
 * @property float|null $october
 * @property float|null $november
 * @property float|null $december
 */
class Budget extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'budget';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product', 'category'], 'required'],
            [['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'], 'number'],
            [['product', 'category'], 'string', 'max' => 255],
            [['product'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product' => 'Product',
            'category' => 'Category',
            'january' => 'January',
            'february' => 'February',
            'march' => 'March',
            'april' => 'April',
            'may' => 'May',
            'june' => 'June',
            'july' => 'July',
            'august' => 'August',
            'september' => 'September',
            'october' => 'October',
            'november' => 'November',
            'december' => 'December',
        ];
    }
}
