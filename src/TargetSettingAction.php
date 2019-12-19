<?php
/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2019-07-27 12:53
 * description:
 */

namespace yiier\targetSetting;

use Yii;
use yii\base\Action;

class TargetSettingAction extends Action
{
    /**
     * @var string class name of the model which will be used to validate the attributes.
     * The class should have a scenario matching the `scenario` variable.
     * The model class must implement [[Model]].
     * This property must be set.
     */
    public $modelClass;
    /**
     * @var string The scenario this model should use to make validation
     */
    public $scenario;
    /**
     * @var string the name of the view to generate the form. Defaults to 'setting'.
     */
    public $viewName = 'target-setting';

    /**
     * @var string target id. Default is user
     */
    public $targetType = 'user';

    /**
     * @var int target id. Default Yii::$app->user->id
     */
    public $targetId = 0;

    public $successMessage = 'Successfully saved setting';

    /**
     * Render the setting form.
     */
    public function run()
    {
        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass();
        $targetId = ($this->targetId ?: Yii::$app->user->id);
        $targetType = $this->targetType;
        if ($this->scenario) {
            $model->setScenario($this->scenario);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            foreach ($model->toArray() as $key => $value) {
                Yii::$app->targetSetting->set($key, $value, $targetType, $targetId, $model->getAttributeLabel($key));
            }
            Yii::$app->getSession()->addFlash('success', Yii::t('app', $this->successMessage));
        }
        foreach ($model->attributes() as $key) {
            $model->{$key} = Yii::$app->targetSetting->get($key, $targetType, $targetId);
        }
        return $this->controller->render($this->viewName, ['model' => $model]);
    }
}
