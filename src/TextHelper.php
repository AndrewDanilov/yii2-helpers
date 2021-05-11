<?php
namespace andrewdanilov\helpers;

use yii\helpers\StringHelper;

class TextHelper
{
	public static function shortText($text, $trim_words_count, $suffix='...', $allowable_tags='<p><i><b><strong>')
	{
		$text = strip_tags($text, $allowable_tags);
		$text = str_replace('&nbsp;', ' ', $text);
		$text = preg_replace('~<p[^>]*>\s*</p>~', '', $text);
		$text = preg_replace('~<i[^>]*>\s*</i>~', '', $text);
		$text = preg_replace('~<b[^>]*>\s*</b>~', '', $text);
		$text = preg_replace('~<strong[^>]*>\s*</strong>~', '', $text);
		if (preg_match_all('~<p[^>]*>(.+)</p>~Us', $text, $matches)) {
			$paragraphs = $matches[1];
			$total_words_count = 0;
			$text = '';
			foreach ($paragraphs as $paragraph) {
				$words_count = preg_match_all('~[\p{L}\'\-\xC2\xAD]+~u', trim(strip_tags($paragraph)));
				$total_words_count += $words_count;
				if ($total_words_count < $trim_words_count) {
					$text .= '<p>' . $paragraph . '</p>';
				} else {
					$diff = $total_words_count - $trim_words_count;
					if ($diff > 0) {
						$text .= '<p>' . StringHelper::truncateWords($paragraph, $words_count - $diff, $suffix, true) . '</p>';
					}
					break;
				}
			}
			return $text;
		}
		return StringHelper::truncateWords(strip_tags($text), $trim_words_count, $suffix, true);
	}
}