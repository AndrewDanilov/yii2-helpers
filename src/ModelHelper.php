<?php
namespace andrewdanilov\helpers;

use yii\base\Model;

class ModelHelper
{
    const ATTR_RULE_CLEAN_JS = 10;
    const ATTR_RULE_STRIP_TAGS = 20;
    const ATTR_RULE_HTML_SPECIAL_CHARS = 30;

    /**
     * Обрабатывает массив с загружаемыми в модель значениями
     * атрибутов по заданным правилам.
     * Правила очистки задаются для конкретных атрибутов
     * в массиве $attribute_rules:
     * $attribute_rules = [
     *   'name' => [ModelHelper::ATTR_RULE_STRIP_TAGS],
     *   'text' => [ModelHelper::ATTR_RULE_CLEAN_JS, ModelHelper::ATTR_RULE_STRIP_TAGS],
     *   '<attribute>' => [<attr_rule1>, <attr_rule2>, <attr_rule3>, ...],
     *   '*' => [<attr_rule4>, ...], // для всех атрибутов, даже если для них уже применялось правило
     *   '?' => [<attr_rule5>, ...], // только для атрибутов, для которых не указано правило
     * ];
     * Набор правил применяется, если значение элемента массива $values
     * соответствующего атрибуту является строкой. Если значение - массив,
     * то набор правил будет рекурсивно применен ко всем вложенным строковым
     * значениям элементов массива, независимо от имен их ключей.
     * Глубина вложенности неограничена.
     * Метод ModelHelper::cleanLoadedValues() можно применять в модели,
     * например, переписав базовый метод \yii\base\Model::setAttributes()
     * следующим образом:
     * ```php
     * public function setAttributes($values, $safeOnly = true)
     * {
     *   $values = ModelHelper::cleanLoadedValues($values, [
     *     'title' => [ModelHelper::ATTR_RULE_STRIP_TAGS],
     *     'text' => [ModelHelper::ATTR_RULE_CLEAN_JS],
     *   ]);
     *   parent::setAttributes($values, $safeOnly);
     * }
     * ```
     * @param $values
     * @param $attribute_rules
     * @return mixed
     */
    public static function cleanLoadedValues($values, $attribute_rules)
    {
        if (is_array($values)) {
            foreach ($values as $name => $value) {
                if (isset($attribute_rules[$name])) {
                    $value = static::applyCleanRulesToValue($value, $attribute_rules[$name]);
                } elseif (isset($attribute_rules['?'])) {
                    $value = static::applyCleanRulesToValue($value, $attribute_rules['?']);
                }
                if (isset($attribute_rules['*'])) {
                    $value = static::applyCleanRulesToValue($value, $attribute_rules['*']);
                }
                $values[$name] = $value;
            }
        }
    	return $values;
    }

    protected static function applyCleanRulesToValue($value, $rules)
    {
        if (is_string($value)) {
            foreach ($rules as $rule) {
                switch ($rule) {
                    case static::ATTR_RULE_CLEAN_JS:
                        $value = preg_replace('~<script[^>]*>.*</script[^>]*>~Ui', '', $value);
                        $value = preg_replace('~<script[^>]*>~Ui', '', $value);
                        break;
                    case static::ATTR_RULE_STRIP_TAGS:
                        $value = strip_tags($value);
                        break;
                    case static::ATTR_RULE_HTML_SPECIAL_CHARS:
                        $value = htmlspecialchars($value);
                        break;
                }
            }
        } elseif (is_array($value)) {
            foreach ($value as $value_item) {
                static::applyCleanRulesToValue($value_item, $rules);
            }
        }
        return $value;
    }

    /**
     * Возвращает первую ошибку, возникшую в модели в виде строки
     * или пустую строку, если ошибок нет.
     *
     * @param Model $model
     * @return string
     */
    public static function getFirstError(Model $model): string
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