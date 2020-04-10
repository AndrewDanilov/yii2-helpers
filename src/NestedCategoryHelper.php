<?php
namespace andrewdanilov\helpers;

use yii\db\ActiveRecord;

/**
 * NestedCategoryHelper class
 */
class NestedCategoryHelper
{
	private static $_groupedCategories = null;

	/**
	 * @param ActiveRecord[] $categories
	 * @return null
	 */
	public static function getGroupedCategories($categories)
	{
		if (static::$_groupedCategories === null) {
			static::$_groupedCategories = [];
			foreach ($categories as $category) {
				if (property_exists($category, 'parent_id')) {
					static::$_groupedCategories[$category->parent_id][$category->getPrimaryKey()] = $category;
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
	 * @param int $level
	 * @return array
	 */
	public static function getDropdownTree($categories, $parent_id=0, $level=0) {
		$tree = [];
		$grouped_categories = static::getGroupedCategories($categories);
		if (isset($grouped_categories[$parent_id])) {
			foreach ($grouped_categories[$parent_id] as $id => $category) {
				$tree[$id] = str_repeat('│ ', $level) . '├ ' . $category->name;
				if (isset($grouped_categories[$id])) {
					$tree += static::getDropdownTree($categories, $id, $level + 1);
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
	 * @return array
	 */
	public static function getChildrenIds($categories, $parent_id=0)
	{
		$tree = static::getDropdownTree($categories, $parent_id);
		return array_keys($tree);
	}
}