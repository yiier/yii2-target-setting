<?php
/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2019-07-27 11:08
 * description:
 */

namespace yiier\targetSetting;

use Yii;
use yii\caching\Cache;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

class TargetSetting extends \yii\base\Component
{
    /**
     * @var string setting model class name
     */
    public $modelClass = 'yiier\targetSetting\models\TargetSettingModel';
    /**
     * @var Cache|array|string the cache used to improve RBAC performance. This can be one of the followings:
     *
     * - an application component ID (e.g. `cache`)
     * - a configuration array
     * - a [[yii\caching\Cache]] object
     *
     * When this is not set, it means caching is not enabled
     */
    public $cache = 'cache';
    /**
     * @var string the key used to store settings data in cache
     */
    public $cacheKey = 'yiier-target-setting';
    /**
     * @var \yiier\targetSetting\models\TargetSettingModel setting model
     */
    protected $model;
    /**
     * @var array list of settings
     */
    protected $items;
    /**
     * @var mixed setting value
     */
    protected $setting;

    /**
     * Initialize the component
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if ($this->cache !== null) {
            $this->cache = Instance::ensure($this->cache, Cache::class);
        }
        $this->model = Yii::createObject($this->modelClass);
    }

    /**
     * Get's all values by target
     *
     * @param string $targetType
     * @param int $targetId
     * @param null $default
     *
     * @return mixed
     */
    public function getAllByTarget($targetType = '', $targetId = 0, $default = null)
    {
        $items = $this->getSettingsConfig($targetType);
        if (isset($items[$targetId])) {
            $this->setting = ArrayHelper::getColumn($items[$targetId], 'value');
        } else {
            $this->setting = $default;
        }
        return $this->setting;
    }

    /**
     * Get's the value for the key and target
     *
     * @param string $key
     * @param string $targetType
     * @param int $targetId
     * @param null $default
     *
     * @return mixed
     */
    public function get($key, $targetType = '', $targetId = 0, $default = null)
    {
        $items = $this->getSettingsConfig($targetType);
        if (isset($items[$targetId][$key])) {
            return ArrayHelper::getValue($items[$targetId][$key], 'value');
        }
        return $default;
    }

    /**
     * Add a new setting or update an existing one.
     *
     * @param string $key
     * @param string $value
     * @param string $targetType
     * @param int $targetId
     * @param string $description
     *
     * @return bool
     */
    public function set($key, $value, $targetType = '', $targetId = 0, $description = '')
    {
        if ($this->model->setSetting($key, $value, $targetType, $targetId, $description)) {
            if ($this->invalidateCache($targetType)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checking existence of setting
     *
     * @param string $key
     * @param string $targetType
     * @param int $targetId
     * @return bool
     */
    public function has($key, $targetType = '', $targetId = 0)
    {
        $setting = $this->get($key, $targetType, $targetId);
        return !empty($setting);
    }

    /**
     * Remove setting by target and key
     *
     * @param string $key
     * @param string $targetType
     * @param int $targetId
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove($key, $targetType = '', $targetId = 0)
    {
        if ($this->model->removeSetting($key, $targetType, $targetId)) {
            if ($this->invalidateCache($targetType)) {
                return true;
            }
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
    public function removeAll($targetType = '', $targetId = 0)
    {
        return $this->model->removeAllSettings($targetType, $targetId);
    }

    /**
     * Activates a setting
     *
     * @param string $key
     * @param string $targetType
     * @param int $targetId
     * @return bool
     */
    public function activate($key, $targetType = '', $targetId = 0)
    {
        return $this->model->activateSetting($key, $targetType, $targetId);
    }

    /**
     * Deactivates a setting
     *
     * @param string $key
     * @param string $targetType
     * @param int $targetId
     * @return bool
     */
    public function deactivate($key, $targetType = '', $targetId = 0)
    {
        return $this->model->deactivateSetting($key, $targetType, $targetId);
    }

    /**
     * Returns the settings config
     *
     * @param string $targetType
     * @return array
     */
    protected function getSettingsConfig($targetType = '')
    {
        $cacheKey = $this->cacheKey . $targetType;
        if (!$this->cache instanceof Cache) {
            $this->items = $this->model->getSettings($targetType);
        } else {
            $cacheItems = $this->cache->get($cacheKey);
            if (!empty($cacheItems)) {
                $this->items = $cacheItems;
            } else {
                $this->items = $this->model->getSettings($targetType);
                $this->cache->set($cacheKey, $this->items);
            }
        }
        return $this->items;
    }

    /**
     * Invalidate the cache
     *
     * @param string $targetType
     * @return bool
     */
    public function invalidateCache($targetType = '')
    {
        if ($this->cache !== null) {
            $cacheKey = $this->cacheKey . $targetType;
            $this->cache->delete($cacheKey);
            $this->items = null;
        }
        return true;
    }
}
