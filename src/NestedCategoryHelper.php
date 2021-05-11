<?php
namespace andrewdanilov\helpers;

use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * NestedCategoryHelper class
 */
class NestedCategoryHelper
{
	/**
	 * Returns return flat categories list array grouped by parent ID.
	 *
	 * @param array[]|object[]|ActiveQuery $categories
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getGroupedCategories($categories, $parent_key='parent_id', $primary_key='id')
	{
		$categories = static::toArray($categories);
		return ArrayHelper::index($categories, $primary_key, [$parent_key]);
	}

	/**
	 * Returns hierarchical tree as multidimensional array.
	 *
	 * @param array[]|object[]|ActiveQuery $categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getTree($categories, $parent_id=0, $parent_key='parent_id', $primary_key='id')
	{
		$grouped_categories = static::getGroupedCategories($categories, $parent_key, $primary_key);
		if (!empty($grouped_categories)) {
			return static::getTreeRecursive($grouped_categories, $parent_id, $parent_key, $primary_key);
		}
		return [];
	}

	/**
	 * @param array $grouped_categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @param int $level
	 * @return array
	 */
	private static function getTreeRecursive(&$grouped_categories, $parent_id, $parent_key, $primary_key, $level=0)
	{
		$tree = [];
		if (isset($grouped_categories[$parent_id])) {
			foreach ($grouped_categories[$parent_id] as $id => $category) {
				$item = [
					'level' => $level,
					'category' => $category,
				];
				if (isset($grouped_categories[$id])) {
					$item['items'] = static::getTreeRecursive($grouped_categories, $id, $parent_key, $primary_key, $level + 1);
				}
				$tree[] = $item;
			}
		}
		return $tree;
	}

	/**
	 * Returns pseudo-hierarchical list of categories
	 * as plane array. Each element contains hierarchy
	 * depth level along with a category array
	 *
	 * @param array[]|object[]|ActiveQuery $categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getPlaneTree($categories, $parent_id=0, $parent_key='parent_id', $primary_key='id')
	{
		$grouped_categories = static::getGroupedCategories($categories, $parent_key, $primary_key);
		if (!empty($grouped_categories)) {
			return static::getPlaneTreeRecursive($grouped_categories, $parent_id, $parent_key, $primary_key);
		}
		return [];
	}

	/**
	 * @param array $grouped_categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @param int $level
	 * @return array
	 */
	private static function getPlaneTreeRecursive(&$grouped_categories, $parent_id, $parent_key, $primary_key, $level=0)
	{
		$tree = [];
		if (isset($grouped_categories[$parent_id])) {
			foreach ($grouped_categories[$parent_id] as $id => $category) {
				$tree[] = [
					'level' => $level,
					'category' => $category,
				];
				if (isset($grouped_categories[$id])) {
					$tree = ArrayHelper::merge($tree, static::getPlaneTreeRecursive($grouped_categories, $id, $parent_key, $primary_key, $level + 1));
				}
			}
		}
		return $tree;
	}

	/**
	 * Returns pseudo-hierarchical list of categories
	 * as plane array. Each element contains string
	 * padded with special symbols representing category
	 * deph level.
	 * Suitable for use in ActionForm.
	 *
	 * @param array[]|object[]|ActiveQuery $categories
	 * @param int $parent_id
	 * @param string $name_key
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getDropdownTree($categories, $parent_id=0, $name_key='name', $parent_key='parent_id', $primary_key='id') {
		$plane_tree = static::getPlaneTree($categories, $parent_id, $parent_key, $primary_key);
		return ArrayHelper::map($plane_tree, function($element) use ($primary_key) {
			return $element['category'][$primary_key];
		}, function($element) use ($name_key) {
			return str_repeat('│ ', $element['level']) . '├ ' . $element['category'][$name_key];
		});
	}

	/**
	 * Returns ID's list of all hierarchically nested
	 * child categories, starting with $parent_id.
	 * Root parent category ID not included in the list.
	 *
	 * @param array[]|object[]|ActiveQuery $categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getChildrenIds($categories, $parent_id=0, $parent_key='parent_id', $primary_key='id')
	{
		$plane_tree = static::getPlaneTree($categories, $parent_id, $parent_key, $primary_key);
		return ArrayHelper::getColumn($plane_tree, 'category.' . $primary_key);
	}

	/**
	 * Returns path to category as array of elements, where first element
	 * is root parent category array, next are nested childs till needed
	 * category
	 *
	 * @param array[]|object[]|ActiveQuery $categories
	 * @param int $category_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getCategoryPathArray($categories, $category_id, $parent_key='parent_id', $primary_key='id')
	{
		$categories = static::toArray($categories);
		$categories_indexed = ArrayHelper::index($categories, $primary_key);
		$path = [];
		if (isset($categories_indexed[$category_id])) {
			do {
				$path[] = $categories_indexed[$category_id];
				if (isset($categories_indexed[$category_id][$parent_key])) {
					$category_id = $categories_indexed[$category_id][$parent_key];
				} else {
					$category_id = 0;
				}
			} while (!empty($category_id));
			$path = array_reverse($path);
		}
		return $path;
	}

	/**
	 * Returns path to category as delimited string
	 *
	 * @param array[]|object[]|ActiveQuery $categories
	 * @param int $category_id
	 * @param string $delimiter
	 * @param string $name_key
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return string
	 */
	public static function getCategoryPathDelimitedStr($categories, $category_id, $delimiter='/', $name_key='name', $parent_key='parent_id', $primary_key='id')
	{
		$path = static::getCategoryPathArray($categories, $category_id, $parent_key, $primary_key);
		$path = ArrayHelper::map($path, $primary_key, $name_key);
		return implode($delimiter, $path);
	}

	/**
	 * Returns ID's list of nested categories path
	 *
	 * @param array[]|object[]|ActiveQuery $categories
	 * @param int $category_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getCategoryPathIds($categories, $category_id, $parent_key='parent_id', $primary_key='id')
	{
		$path = static::getCategoryPathArray($categories, $category_id, $parent_key, $primary_key);
		return ArrayHelper::getColumn($path, $primary_key);
	}

	/**
	 * Creates categories multidimensional array
	 * suitable for use in \yii\widgets\Menu widget
	 *
	 * @param $categories
	 * @param int $parent_id
	 * @param string $name_key
	 * @param string $parent_key
	 * @param string $primary_key
	 * @param string $url_path
	 * @param string $url_param_name
	 * @param string $url_param_attribute
	 * @return array
	 */
	public static function getCategoryMenuTree($categories, $parent_id=0, $name_key='name', $parent_key='parent_id', $primary_key='id', $url_path='/site/index', $url_param_name='id', $url_param_attribute='id')
	{
		$tree = static::getTree($categories, $parent_id, $parent_key, $primary_key);
		return static::getCategoryMenuTreeRecursive($tree, $name_key, $primary_key, $url_path, $url_param_name, $url_param_attribute);
	}

	private static function getCategoryMenuTreeRecursive(&$tree, $name_key, $primary_key, $url_path, $url_param_name, $url_param_attribute)
	{
		$items = [];
		foreach ($tree as $tree_item) {
			$item = [
				'label' => $tree_item['category'][$name_key],
				'url' => [$url_path, $url_param_name => $tree_item['category'][$url_param_attribute]],
			];
			if (!empty($tree_item['items'])) {
				$item['items'] = static::getCategoryMenuTreeRecursive($tree_item['items'], $name_key, $primary_key, $url_path, $url_param_name, $url_param_attribute);
			}
			$items[] = $item;
		}
		return $items;
	}

	/**
	 * Converts array of objects or ActiveQuery
	 * to array of arrays
	 *
	 * @param array[]|object[]|ActiveQuery $list
	 * @return array[]
	 */
	private static function toArray($list)
	{
		if ($list instanceof ActiveQuery) {
			return $list->asArray()->all();
		}
		return ArrayHelper::toArray($list);
	}
}