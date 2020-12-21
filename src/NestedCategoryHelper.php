<?php
namespace andrewdanilov\helpers;

use yii\helpers\ArrayHelper;

/**
 * NestedCategoryHelper class
 */
class NestedCategoryHelper
{
	/**
	 * Returns hierarchical tree as multidimensional array.
	 *
	 * @param array $categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getTree($categories, $parent_id=0, $parent_key='parent_id', $primary_key='id')
	{
		$grouped_categories = ArrayHelper::index($categories, $primary_key, [$parent_key]);
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
	 * @param array $categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getPlaneTree($categories, $parent_id=0, $parent_key='parent_id', $primary_key='id')
	{
		$tree = static::getTree($categories, $parent_id, $parent_key, $primary_key);

		return ArrayHelper::map($tree, null, function($element) {
			return [
				'level' => $element['level'],
				'category' => $element['category'],
			];
		});
	}

	/**
	 * Returns pseudo-hierarchical list of categories
	 * as plane array. Each element contains string
	 * padded with special symbols representing category
	 * deph level.
	 * Suitable for use in ActionForm.
	 *
	 * @param array $categories
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
	 * @param array $categories
	 * @param int $parent_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getChildrenIds($categories, $parent_id=0, $parent_key='parent_id', $primary_key='id')
	{
		$plane_tree = static::getPlaneTree($categories, $parent_id, $parent_key, $primary_key);
		return ArrayHelper::map($plane_tree, null, function($element) use ($primary_key) {
			return $element['category'][$primary_key];
		});
	}

	/**
	 * Returns path to category as array of elements, where first element
	 * is root parent category array, next are nested childs till needed
	 * category
	 *
	 * @param array $categories
	 * @param int $category_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getCategoryPathArray($categories, $category_id, $parent_key='parent_id', $primary_key='id')
	{
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
			} while ($category_id !== 0);
			$path = array_reverse($path);
		}
		return $path;
	}

	/**
	 * Returns path to category as delimited string
	 *
	 * @param array $categories
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
	 * @param array $categories
	 * @param int $category_id
	 * @param string $parent_key
	 * @param string $primary_key
	 * @return array
	 */
	public static function getCategoryPathIds($categories, $category_id, $parent_key='parent_id', $primary_key='id')
	{
		$path = static::getCategoryPathArray($categories, $category_id, $parent_key, $primary_key);
		$path = ArrayHelper::map($path, null, $primary_key);
		return $path;
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
	 * @return array
	 */
	public static function getCategoryMenuTree($categories, $parent_id=0, $name_key='name', $parent_key='parent_id', $primary_key='id')
	{
		$tree = NestedCategoryHelper::getTree($categories, $parent_id, $parent_key, $primary_key);
		return static::getWordTaxonomiesMenuTreeRecursive($tree, $name_key, $primary_key);
	}

	private static function getWordTaxonomiesMenuTreeRecursive($tree, $name_key, $primary_key)
	{
		$items = [];
		foreach ($tree as $tree_item) {
			$item = [
				'label' => $tree_item['category'][$name_key],
				'url' => ['/site/words', 'type' => $tree_item['category'][$primary_key]],
			];
			if (!empty($tree_item['items'])) {
				$item['items'] = static::getWordTaxonomiesMenuTreeRecursive($tree_item['items'], $name_key, $primary_key);
			}
			$items[] = $item;
		}
		return $items;
	}
}