<?php
namespace andrewdanilov\helpers;

use yii\helpers\ArrayHelper;

class ObjectHelper
{
	public static function setObjectAttribute($object, $attribute, $value)
	{
		if (($pos = strrpos($attribute, '.')) !== false) {
			$object = ArrayHelper::getValue($object, substr($attribute, 0, $pos));
			$attribute = substr($attribute, $pos + 1);
		}
		if ($object !== null) {
			try {
				$object->$attribute = $value;
			} catch (\Exception $e) {
				if (!($object instanceof \ArrayAccess)) {
					throw $e;
				}
			}
		}
	}

	public static function getModuleClassName($module)
	{
		if (is_object($module)) {
			return $module->class;
		}

		if (is_string($module)) {
			return $module;
		}

		if (is_array($module)) {
			if (isset($module['__class'])) {
				return $module['__class'];
			}

			if (isset($module['class'])) {
				return $module['class'];
			}
		}

		return null;
	}
}