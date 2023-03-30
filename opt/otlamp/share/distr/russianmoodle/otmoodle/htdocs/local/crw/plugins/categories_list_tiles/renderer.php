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
 * Блок списка категорий в виде плиток. Рендер.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot .'/course/renderer.php');
require_once($CFG->libdir. '/filestorage/file_storage.php');


class crw_categories_list_tiles_renderer extends \local_crw\output\renderer
{
    /**
     * URL-адрес системного изображения категорий курсов по умолчанию
     *
     * @var string
     */
    protected $default_img_url = '';

    /**
     * Буферный массив изображений категорий курсов ('id'=>'url object')
     *
     * @var array
     */
    protected $catimages = [];

    /**
     * Конструктор класса
     */
    public function __construct()
    {
        global $CFG;

        // URL изображения - заглушки
        $this->default_img_url = new moodle_url('/local/crw/assets/no-photo.gif');

        // Получение списка категорий
        $catlist = (array)\core_course_category::make_categories_list();

        $fs = get_file_storage();

        // Заполнения буфера изображений категорий
        foreach ( $catlist as $catid => $catname )
        {
            // Заполнение данными буферного массива
            if ( ! isset($this->catimages[$catid]['url']) )
            {// Определить изображение текущей категории
                $categoryobj = new \local_crw\output\category($catid);
                $context = \context_system::instance();
                $this->catimages[$catid]['url'] = $categoryobj->get_image_url(
                    [$context->id, 'crw_categories_list_tiles', 'categories_icon_default']
                );
            }
        }
    }

    /**
     * Плучить html-код блока категорий
     *
     * @param number $catid
     * @param unknown $options
     * @return unknown|string
     */
    public function get_block($catid = 0, $options = array())
    {
        $html = '';
        $result = '';
        // Получим категорию
        $currentcategory = \core_course_category::get($catid);

        // Получить дочерние категории
    	if ( ! empty($options['categories']) )
        {
            $children = $options['categories'];
        } else
        {
            $children = $currentcategory->get_children();
        }


        $content = '';
        $categories_in_line = get_config('crw_categories_list_tiles', 'categoties_list_tiles_inline');
        if ( empty($categories_in_line) )
        { // Значение по умолчанию
            $categories_in_line = 6;
        }

        $searchquery = $options['searchquery'] ?? null;
        $userid = $options['userid'] ?? null;
        $notactive = $options['usercourses_add_not_active'] ?? null;
        foreach ( $children as $category )
        {
            // Сформируем плитку категории
            $content .= $this->cs_category_tile($category->id, 'cp_courseline' . $categories_in_line, $searchquery, $userid, $notactive);
        }


        if( ! empty($content) )
        {
            // Обертка
            $html .= html_writer::div( $content, 'crw_cs_catblock');
            // Добавим разграничитель
            $html .= html_writer::div( '', 'crw_clearboth crw_catblock_clearboth' );
        }

        // Возвращаем блок
        return $html;
    }

    /**
     * Сформировать плитку категории курсов
     *
     * @param int $catid - ID категории курсов
     * @param int $addclass - Дополнительные классы плитки
     *
     * @return string - HTML-код плитки категории
     */
    protected function cs_category_tile($catid = 0 , $addclass='', $searchquery=null, $userid=null, $notactive=null)
    {
        // Подготовим переменную для записи html
        $html = '';

        // Получим категорию
        $category = \core_course_category::get($catid);
        if ( empty($category) )
        {// Категория не найдена
            return $html;
        }
        // Формируем ссылку на категорию
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
        $url = new moodle_url('/local/crw/category.php', $urlopts);

        // Сформируем блок с изображением
        $html .= $this->cs_category_tile_img($category);

        if ( isset($category->name) )
        {// Сформируем блок с названием
            $html .= html_writer::div(
                    $category->name,
                    'crw_cs_cattile_title'
            );
        }

        // Обертка
        $return = html_writer::link($url, $html, array('class' => 'crw_cs_cattile '.$addclass));

        // Возвращаем блок
        return $return;
    }

    /**
     * Сформировать блок изображения категории курсов
     *
     * @param \core_course_category $category - объект категории
     *
     * @return string - HTML-код блока с изображением
     */
    protected function cs_category_tile_img( \core_course_category $category )
    {
        global $CFG;
        require_once($CFG->libdir. '/filestorage/file_storage.php');
        require_once($CFG->dirroot. '/course/lib.php');

        // Если лимит не позволяет - выводим заглушку
        if (empty($CFG->courseoverviewfileslimit))
        {
            return $this->get_block_from_url($this->default_img_url);
        }
        if ( isset($this->catimages[$category->id]['url']) )
        {// Вывод изображения категории
            return $this->get_block_from_url($this->catimages[$category->id]['url']);
        } else
        {// Вывод изображения по умолчанию
            return $this->get_block_from_url($this->default_img_url);
        }
    }

    /**
     * Возвращает блок с изображением категории
     *
     * @param string $img_url - URL-адрес изображения категории
     *
     * @return string - HTML-код блока
     */
    protected function get_block_from_url($img_url)
    {
        return html_writer::div(
            '',
            'crw_cs_cattile_img',
            ['style' => 'background-image: url("'.$img_url.'");']
        );
    }

    /**
     * Возвращает  родительской категории
     *
     * @param string $catid - ID текущей категории
     *
     * @return int - ID родительской категории
     */
    protected function get_parentid($catid)
    {
        $category = \core_course_category::get($catid);
        if ( isset($category->parent) )
        {
            return (int)$category->parent;
        } else
        {
            return 0;
        }
    }
}