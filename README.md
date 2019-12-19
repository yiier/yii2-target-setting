Target Settings For Yii2
======================
Target Settings For Yii2

[![Latest Stable Version](https://poser.pugx.org/yiier/yii2-target-setting/v/stable)](https://packagist.org/packages/yiier/yii2-target-setting) 
[![Total Downloads](https://poser.pugx.org/yiier/yii2-target-setting/downloads)](https://packagist.org/packages/yiier/yii2-target-setting) 
[![Latest Unstable Version](https://poser.pugx.org/yiier/yii2-target-setting/v/unstable)](https://packagist.org/packages/yiier/yii2-target-setting) 
[![License](https://poser.pugx.org/yiier/yii2-target-setting/license)](https://packagist.org/packages/yiier/yii2-target-setting)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiier/yii2-target-setting "*"
```

or add

```
"yiier/yii2-target-setting": "*"
```

to the require section of your `composer.json` file.


Configuration
------

### Database Migrations

Before usage this extension, we'll also need to prepare the database.


```
php yii migrate --migrationPath=@yiier/targetSetting/migrations/
```



### Module Setup

To access the module, you need to configure the modules array in your application configuration:

```php
'modules' => [
    'targetSetting' => [
        'class' => 'yiier\targetSetting\Module',
    ],
],

```


Component Setup

To use the Setting Component, you need to configure the components array in your application configuration:

```php
'components' => [
    'targetSetting' => [
        'class' => 'yiier\targetSetting\TargetSetting',
    ],
],
```

Usage
-----

```php
<?php
$setting = Yii::$app->targetSetting;

$value = $setting->get('key');
$value = $setting->get('key', User::tableName(), Yii::$app->user->id);

$setting->set('key', 125.5);
$setting->set('key', 125.5, User::tableName(), Yii::$app->user->id);

$setting->set('key', false, User::tableName(), Yii::$app->user->id, 'Not allowed Update Post');
$setting->set('key', false, '', 0, 'Not allowed Update Post');

// Checking existence of setting
$setting->has('key');
$setting->has('key', User::tableName(), Yii::$app->user->id);

// Activates a setting
$setting->activate('key');
$setting->activate('key', User::tableName(), Yii::$app->user->id);

// Deactivates a setting
$setting->deactivate('key');
$setting->deactivate('key', User::tableName(), Yii::$app->user->id);

// Removes a setting
$setting->remove('key');
$setting->remove('key', User::tableName(), Yii::$app->user->id);

// Removes all settings
$setting->removeAll();
$setting->removeAll(User::tableName(), Yii::$app->user->id);

// Get's all values in the specific section.
$setting->getAllByTarget(User::tableName(),Yii::$app->user->id);

$setting->invalidateCache(); // automatically called on set(), remove();
$setting->invalidateCache(User::tableName()); // automatically called on set(), remove();
```


TargetSettingAction
-----

To use a custom settings form, you can use the included `TargetSettingAction`.

1. Create a model class with your validation rules.
2. Create an associated view with an `ActiveForm` containing all the settings you need.
3. Add `yiier\targetSetting\targetSettingAction` to the controller's actions.

The settings will be stored in section taken from the form name, with the key being the field name.

### Model:

```php
<?php
class SiteForm extends Model
{

    public $siteName, $siteDescription;

    public function rules()
    {
        return [
            [['siteName', 'siteDescription'], 'string'],
        ];
    }

    public function fields()
    {
        return ['siteName', 'siteDescription'];
    }

    public function attributes()
    {
        return ['siteName', 'siteDescription'];
    }

    public function attributeLabels()
    {
        return [
            'siteName' => 'Site Name',
            'siteDescription' => 'Site Description'
        ];
    }

}
```

### Views:


```php
<?php $form = ActiveForm::begin(['id' => 'site-settings-form']); ?>

<?= $form->field($model, 'siteName') ?>
<?= $form->field($model, 'siteDescription') ?>
<?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>

<?php ActiveForm::end(); ?>

```

### Controller:

```php
public function actions() 
{
   return [
   		//....
            'site-settings' => [
                'class' => TargetSettingAction::class,
                'modelClass' => 'app\models\SiteForm',
                //'scenario' => 'site',	// Change if you want to re-use the model for multiple setting form.
                //'targetType' => 'company', // By default use ''
                //'targetId' => 1, // By default use \Yii::$app->user->id
                'viewName' => 'site-settings',	// The form we need to render
                'successMessage' => '保存成功'
            ],
        //....
    ];
}
```


Reference
-----

- [yii2mod/yii2-settings](https://github.com/yii2mod/yii2-settings)
- [phemellc/yii2-settings](https://github.com/phemellc/yii2-settings)
