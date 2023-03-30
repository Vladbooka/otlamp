<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Блок списка категорий в виде плагина Блок. Рендер.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot .'/course/renderer.php');


class crw_categories_list_block_renderer extends \local_crw\output\renderer
{
    public function __construct()
    {
    }
    /**
     * Плучить html-код блока категорий
     * @param number $catid
     * @param unknown $options
     * @return unknown|string
     */
    public function get_block($catid = 0, $options = array())
    {
        $html = '';
        // Получим категорию
        $currentcategory = \core_course_category::get($catid);

        // Получим дочерние категории
        if ( ! empty($options['categories']) )
        {
            $children = $options['categories'];
        } else
        {
            $children = $currentcategory->get_children();
        }


        $content = '';

        $searchquery = $options['searchquery'] ?? null;
        $userid = $options['userid'] ?? null;
        $notactive = $options['usercourses_add_not_active'] ?? null;
        foreach ( $children as $category )
        {// Сформируем ссылку категории
            $content .= $this->cs_category($category->id, 'catlb_category_item', $searchquery, $userid, $notactive);
        }


        if( ! empty($content) )
        {
            // Добавим разграничитель
            $content .= html_writer::div('', 'catlb_clearboth');
            $html .= html_writer::div($content, 'content');
        }


        if( empty($html) )
        {
            return '';
        }

        // Получим настройки
        $region = get_config('crw_categories_list_block', 'region');
        $weight = get_config('crw_categories_list_block', 'weight');

        // Обертка
        $return = html_writer::div(
            $html,
            'catlb_categories_list_block block_catlb_categories_list block',
            [
                'data-block' => 'catlb_categories_list',
                'data-region' => $region,
                'data-weight' => $weight
            ]
        );
        // Возвращаем блок
        return $return;
    }

    /**
     * Сформировать плитку категории курсов
     *
     * @param int $catid - ID категории курсов
     * @param int $addclass - дополнительные классы плитки
     *
     * @return string - HTML-код плитки категории
     */
    protected function cs_category( $catid = 0 , $addclass='', $searchquery=null, $userid=null, $notactive=null)
    {
        // Подготовим переменную для записи html
        $html = '';

        // Получим категорию
        $category = \core_course_category::get($catid);
        if ( empty($category) )
        {// Категория не найдена
            return $html;
        }

        $urlopts = ['cid' => $catid];
        if (!is_null($searchquery))
        {
            $urlopts['crws'] = $searchquery;
        }
        if (!is_null($userid))
        {
            $urlopts['uid'] = $userid;

            if (!is_null($notactive))
            {
                $urlopts['na'] = (int)!empty($notactive);
            }
        }
        // Формируем ссылку на категорию
        $url = new moodle_url('/local/crw/category.php', $urlopts);

        if ( isset($category->name) )
        {// Сформируем блок с названием
            $html .= html_writer::div(
                    '<span>></span>'.$category->name,
                    'catlb_category_name'
            );
        }

        // Обертка
        $return = html_writer::link($url, $html, array('class' => $addclass));

        // Возвращаем блок
        return $return;
    }
}