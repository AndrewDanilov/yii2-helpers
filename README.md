Yii2 Helpers
===================
Various helpers

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require andrewdanilov/yii2-helpers "~1.0.0"
```

or add

```
"andrewdanilov/yii2-helpers": "~1.0.0"
```

to the `require` section of your `composer.json` file.


Usage
=====

ModelHelper
-----------

__ModelHelper::cleanLoadedValues()__

Обрабатывает массив с загружаемыми в модель значениями
атрибутов по заданным правилам.
Правила очистки задаются для конкретных атрибутов
в массиве $attribute_rules:

```php
$attribute_rules = [
  'name' => [ModelHelper::ATTR_RULE_STRIP_TAGS],
  'text' => [ModelHelper::ATTR_RULE_CLEAN_JS, ModelHelper::ATTR_RULE_STRIP_TAGS],
  '<attribute>' => [<attr_rule1>, <attr_rule2>, <attr_rule3>, ...],
  '*' => [<attr_rule4>, ...], // для всех атрибутов, даже если для них уже применялось правило
  '?' => [<attr_rule5>, ...], // только для атрибутов, для которых не указано правило
];
```

Набор правил применяется, если значение элемента массива `$values`
соответствующего атрибуту является строкой. Если значение - массив,
то набор правил будет рекурсивно применен ко всем вложенным строковым
значениям элементов массива, независимо от имен их ключей.
Глубина вложенности неограничена.

Метод `ModelHelper::cleanLoadedValues()` можно применять в модели,
например, переписав базовый метод `\yii\base\Model::setAttributes()`
следующим образом:

```php
public function setAttributes($values, $safeOnly = true)
{
    $values = ModelHelper::cleanLoadedValues($values, [
        'title' => [ModelHelper::ATTR_RULE_STRIP_TAGS],
        'text' => [ModelHelper::ATTR_RULE_CLEAN_JS],
    ]);
    parent::setAttributes($values, $safeOnly);
}
```

Правило `ModelHelper::ATTR_RULE_CLEAN_JS` удалит из значения атрибута
все теги `<script>` как самозакрывающиеся, так и обычные, как содержащие скрипт
в теле тега, так и в html-атрибуте src.

Правило `ModelHelper::ATTR_RULE_STRIP_TAGS` удалит из значения атрибута
все html-теги, применив к нему функцию strip_tags.

Правило `ModelHelper::ATTR_RULE_HTML_SPECIAL_CHARS` заменит в значении атрибута
все html сущности на их &-эквиваленты с помощью функции `htmlspecialchars()`.
