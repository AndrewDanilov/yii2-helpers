<?php
namespace andrewdanilov\helpers;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * NestedCategoryHelper class
 */
class NestedCategoryHelper
{
	private static $_groupedCategories = null;

	/**
	 * @param ActiveRecord[] $categories
	 * @param string $parent_key
	 * @return null
	 */
	public static function getGroupedCategories($categories, $parent_key='parent_id')
	{
		if (static::$_groupedCategories === null) {
			static::$_groupedCategories = [];
			foreach ($categories as $category) {
				if (property_exists($category, $parent_key)) {
					static::$_groupedCategories[$category->getAttribute($parent_key)][$category->getPrimaryKey()] = $category;
				}
			}
		}
		return static::$_groupedCategories;
	}

	/**
	 * Возвращает псевдо-иерархический Dropdown-список
	 * для использования в полях форм.
	 *
	 * @param ActiveRecord[] $categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @param int $level
	 * @return array
	 */
	public static function getDropdownTree($categories, $parent_id=0, $parent_key='parent_id', $level=0) {
		$tree = [];
		$grouped_categories = static::getGroupedCategories($categories, $parent_key);
		if (isset($grouped_categories[$parent_id])) {
			foreach ($grouped_categories[$parent_id] as $id => $category) {
				$tree[$id] = str_repeat('│ ', $level) . '├ ' . $category->name;
				if (isset($grouped_categories[$id])) {
					$tree += static::getDropdownTree($categories, $id, $parent_key, $level + 1);
				}
			}
		}
		return $tree;
	}

	/**
	 * Возвращает список ID's дочерних категорий любого уровня вложенности
	 *
	 * @param ActiveRecord[] $categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @return array
	 */
	public static function getChildrenIds($categories, $parent_id=0, $parent_key='parent_id')
	{
		$tree = static::getDropdownTree($categories, $parent_id, $parent_key);
		return array_keys($tree);
	}

	/**
	 * Returns path to category as delimited string
	 *
	 * @param ActiveRecord[] $categories
	 * @param int $category_id
	 * @param string $name_attribute
	 * @param string $parent_key
	 * @param string $delimiter
	 * @return string
	 */
	public static function getCategoryPath($categories, $category_id, $name_attribute='name', $parent_key='parent_id', $delimiter='/')
	{
		$categories_indexed = ArrayHelper::map($categories, 'id', $name_attribute);
		$path = [];
		if (isset($categories_indexed[$category_id])) {
			$parent_id = $category_id;
			do {
				foreach ($categories_indexed as $category) {
					if ($category['id'] == $parent_id) {
						$path[] = $category[$name_attribute];
						$parent_id = $category[$parent_key];
					}
				}
			} while ($parent_id !== 0);
			rsort($path);
		}
		return implode($delimiter, $path);
	}
}