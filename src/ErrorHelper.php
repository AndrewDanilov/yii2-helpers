<?php
namespace andrewdanilov\helpers;

use yii\base\Model;

class ErrorHelper
{
    /**
     * Возвращает первую ошибку, возникшую в модели в виде строки
     * или пустую строку, если ошибки нет.
     *
     * @param Model $model
     * @return string
     */
    public static function getModelFirstError(Model $model): string
    {
        $errors = $model->getErrors();
        if (!empty($errors)) {
            $first_attribute_errors = reset($errors);
            if (!empty($first_attribute_errors)) {
                return reset($first_attribute_errors);
            }
        }
        return '';
    }
}