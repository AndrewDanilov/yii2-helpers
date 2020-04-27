<?php
namespace andrewdanilov\helpers;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * NestedCategoryHelper class
 */
class NestedCategoryHelper
{
	private static $_groupedCategories = null;

	/**
	 * @param ActiveQuery $categories
	 * @param string $parent_key
	 * @return null
	 */
	public static function getGroupedCategories($categories, $parent_key='parent_id')
	{
		if (static::$_groupedCategories === null) {
			$categories = $categories->indexBy('id')->all();
			static::$_groupedCategories = [];
			foreach ($categories as $category_id => $category) {
				if (isset($category->$parent_key)) {
					static::$_groupedCategories[$category->$parent_key][$category_id] = $category;
				}
			}
		}
		return static::$_groupedCategories;
	}

	/**
	 * Возвращает псевдо-иерархический плоский список
	 * в виде массива элементов, с указанием в каждом
	 * id категории, уровня вложенности и модели
	 * ActiveRecord категории. Элементы массива отсортированы
	 * согласно их взаимной вложенности.
	 *
	 * @param ActiveQuery $categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @param int $level
	 * @return array
	 */
	public static function getPlaneTree($categories, $parent_id=0, $parent_key='parent_id', $level=0)
	{
		$tree = [];
		$grouped_categories = static::getGroupedCategories($categories, $parent_key);
		if (isset($grouped_categories[$parent_id])) {
			foreach ($grouped_categories[$parent_id] as $id => $category) {
				$tree[$id] = [
					'id' => $id,
					'level' => $level,
					'category' => $category,
				];
				if (isset($grouped_categories[$id])) {
					$tree = ArrayHelper::merge($tree, static::getPlaneTree($categories, $id, $parent_key, $level + 1));
				}
			}
		}
		return $tree;
	}

	/**
	 * Возвращает псевдо-иерархический Dropdown-список
	 * для использования в полях форм.
	 *
	 * @param ActiveQuery $categories
	 * @param int $parent_id
	 * @param string $name_attribute
	 * @param string $parent_key
	 * @return array
	 */
	public static function getDropdownTree($categories, $parent_id=0, $name_attribute='name', $parent_key='parent_id') {
		$tree = [];
		foreach (static::getPlaneTree($categories, $parent_id, $parent_key) as $item) {
			$tree[$item['id']] = str_repeat('│ ', $item['level']) . '├ ' . $item['category']->$name_attribute;
		}
		return $tree;
	}

	/**
	 * Возвращает список ID's всех иерархически вложенных
	 * дочерних категорий начиная с указанной родительской
	 *
	 * @param ActiveQuery $categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @return array
	 */
	public static function getChildrenIds($categories, $parent_id=0, $parent_key='parent_id')
	{
		$tree = static::getPlaneTree($categories, $parent_id, $parent_key);
		return array_keys($tree);
	}

	/**
	 * Returns path to category as delimited string
	 *
	 * @param ActiveQuery $categories
	 * @param int $category_id
	 * @param string $name_attribute
	 * @param string $parent_key
	 * @param string $delimiter
	 * @return string
	 */
	public static function getCategoryPath($categories, $category_id, $name_attribute='name', $parent_key='parent_id', $delimiter='/')
	{
		$path = static::getCategoryPathArray($categories, $category_id, $parent_key);
		$path = ArrayHelper::map($path, 'id', $name_attribute);
		return implode($delimiter, $path);
	}

	/**
	 * Returns path to category as array, where first element
	 * is parent category, others is nested childs. Each element
	 * is ActiveRecord object
	 *
	 * @param ActiveQuery $categories
	 * @param int $category_id
	 * @param string $parent_key
	 * @return array
	 */
	public static function getCategoryPathArray($categories, $category_id, $parent_key='parent_id')
	{
		$categories = $categories->indexBy('id')->all();
		$path = [];
		if (isset($categories[$category_id])) {
			do {
				$path[] = $categories[$category_id];
				$category_id = $categories[$category_id]->$parent_key;
			} while ($category_id !== 0);
			$path = array_reverse($path);
		}
		return $path;
	}
}