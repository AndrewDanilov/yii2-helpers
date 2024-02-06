<?php
namespace andrewdanilov\helpers;

use Yii;

class DateHelper
{
    /**
     * Возвращает дату в формате mysql
     *
     * @param string $date
     * @param bool $use_time
     * @param bool $default
     * @return false|null|string
     */
    public static function getISODate(string $date, bool $use_time=false, bool $default=false)
    {
        $date_format = 'Y-m-d';
        if ($use_time) {
            $date_format .= ' H:i:s';
        }
        if (empty($date) && $default) {
            return date($date_format);
        }
        return $date ? date($date_format, strtotime($date)) : null;
    }

    /**
     * Возвращает дату в формате для отображения
     *
     * @param string $date
     * @param bool $use_time
     * @return string|null
     */
    public static function getDisplayDate(string $date, bool $use_time=false): ?string
    {
        if ($use_time) {
            return $date ? Yii::$app->formatter->asDateTime($date) : null;
        }
        return $date ? Yii::$app->formatter->asDate($date) : null;
    }
}