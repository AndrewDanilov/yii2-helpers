<?php
namespace andrewdanilov\helpers;

use Yii;
use mihaildev\elfinder\ElFinder;

class CKEditorHelper
{
	public static function defaultOptions($height=500)
	{
		return [
			'toolbar' => [
				['Source', 'NewPage', 'Preview', 'Viewss'],
				['PasteText', '-', 'Undo', 'Redo'],
				['Replace', 'SelectAll', 'Scayt'],
				['Format', 'FontSize'],
				['Bold', 'Italic', 'Underline', 'TextColor', 'StrikeThrough', '-', 'Outdent', 'Indent', 'RemoveFormat', 'Blockquote', 'HorizontalRule'],
				['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
				['Image', 'oembed', 'Video', 'Iframe'],
				['Link', 'Unlink'],
				['Maximize', 'ShowBlocks'],
			],
			'allowedContent' => true,
			'forcePasteAsPlainText' => true,
			'extraPlugins' => 'image2,widget,oembed,video',
			'language' => Yii::$app->language,
			'height' => $height,
		];
	}
}