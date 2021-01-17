<?php
/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2019-07-27 11:08
 * description:
 */

namespace yiier\targetSetting\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%target_setting}}".
 *
 * @property int $id
 * @property string $type
 * @property string $target_type
 * @property int $target_id
 * @property string $key
 * @property string $value
 * @property string $description
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class TargetSettingModel extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%target_setting}}';
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
            [['type', 'key'], 'required'],
            [['target_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['value'], 'string'],
            [['type'], 'string', 'max' => 20],
            [['key', 'target_type'], 'string', 'max' => 60],
            [['description'], 'string', 'max' => 255],
            [['target_id', 'key'], 'unique', 'targetAttribute' => ['target_type', 'target_id', 'key']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'target_type' => Yii::t('app', 'Target Type'),
            'target_id' => Yii::t('app', 'Target ID'),
            'key' => Yii::t('app', 'Key'),
            'value' => Yii::t('app', 'Value'),
            'description' => Yii::t('app', 'Description'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    /**
     * Creates an [[ActiveQueryInterface]] instance for query purpose.
     *
     * @return TargetSettingQuery
     */
    public static function find(): TargetSettingQuery
    {
        return new TargetSettingQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();
        Yii::$app->targetSetting->invalidateCache();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        Yii::$app->targetSetting->invalidateCache();
    }

    /**
     * Return array of settings
     *
     * @param string $targetType
     * @return array
     */
    public function getSettings($targetType = '')
    {
        $result = [];
        $settings = static::find()->where(['target_type' => $targetType])->active()->asArray()->all();
        foreach ($settings as $setting) {
            $targetId = $setting['target_id'];
            $key = $setting['key'];
            $settingOptions = [
                'type' => $setting['type'],
                'value' => $setting['value'],
                'description' => $setting['description']
            ];
            if (isset($result[$targetId][$key])) {
                ArrayHelper::merge($result[$targetId][$key], $settingOptions);
            } else {
                $result[$targetId][$key] = $settingOptions;
            }
        }
        return $result;
    }

    /**
     * Set setting
     *
     * @param $key
     * @param $value
     * @param string $targetType
     * @param int $targetId
     * @param string $description
     * @return bool
     */
    public function setSetting($key, $value, $targetType = '', $targetId = 0, $description = '')
    {
        $conditions = ['target_type' => $targetType, 'target_id' => $targetId, 'key' => $key];
        if (!$model = static::find()->where($conditions)->limit(1)->one()) {
            $model = new static();
        }
        $model->target_type = $targetType;
        $model->target_id = $targetId;
        $model->key = $key;
        $model->value = strval($value);
        $model->description = strval($description);
        $model->type = gettype($value);
        return $model->save();
    }

    /**
     * Remove setting
     *
     * @param $key
     * @param string $targetType
     * @param int $targetId
     * @return bool|int|null
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function removeSetting($key, $targetType = '', $targetId = 0)
    {
        $conditions = ['target_type' => $targetType, 'target_id' => $targetId, 'key' => $key];
        if ($model = static::find()->where($conditions)->limit(1)->one()) {
            return $model->delete();
        }
        return false;
    }

    /**
     * Remove all settings
     *
     * @param string $targetType
     * @param int $targetId
     * @return int
     */
    public function removeAllSettings($targetType = '', $targetId = 0)
    {
        return static::deleteAll(['target_type' => $targetType, 'target_id' => $targetId]);
    }

    /**
     * Activates a setting
     *
     * @param $key
     *
     * @param string $targetType
     * @param int $targetId
     * @return bool
     */
    public function activateSetting($key, $targetType = '', $targetId = 0)
    {
        $conditions = ['target_type' => $targetType, 'target_id' => $targetId, 'key' => $key];
        $model = static::find()->where($conditions)->limit(1)->one();
        if ($model && $model->status === self::STATUS_INACTIVE) {
            $model->status = self::STATUS_ACTIVE;
            return $model->save(true, ['status']);
        }
        return false;
    }

    /**
     * Deactivates a setting
     *
     * @param $key
     *
     * @param string $targetType
     * @param int $targetId
     * @return bool
     */
    public function deactivateSetting($key, $targetType = '', $targetId = 0)
    {
        $conditions = ['target_type' => $targetType, 'target_id' => $targetId, 'key' => $key];
        $model = static::find()->where($conditions)->limit(1)->one();
        if ($model && $model->status === self::STATUS_ACTIVE) {
            $model->status = self::STATUS_INACTIVE;
            return $model->save(true, ['status']);
        }
        return false;
    }
}
