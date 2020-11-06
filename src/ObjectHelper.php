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
}