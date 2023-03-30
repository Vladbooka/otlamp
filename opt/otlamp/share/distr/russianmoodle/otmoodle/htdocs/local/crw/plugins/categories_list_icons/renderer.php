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
 * Блок списка категорий в виде ссылок с иконками. Рендер.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot .'/course/renderer.php');


class crw_categories_list_icons_renderer extends \local_crw\output\renderer
{
    var $icon = '';
    var $iconback = '';

    public function __construct()
    {
        global $CFG;

        $fs = get_file_storage();
        $files = $fs->get_area_files(context_system::instance()->id, 'crw_categories_list_icons', 'icon');
        foreach ( $files as $file )
        {
            $isimage = $file->is_valid_image();
            if ( $isimage )
            {
                $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $this->icon = '<div class="catli_icon_wrap" ><img class="catli_icon" src="' . $url . '" /></div>';
            }
        }

        $files = $fs->get_area_files(context_system::instance()->id, 'crw_categories_list_icons', 'iconback');
        foreach ( $files as $file )
        {
            $isimage = $file->is_valid_image();
            if ( $isimage )
            {
                $url = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                );
                $this->iconback = '<div class="catli_icon_wrap" ><img class="catli_icon" src="' . $url . '" /></div>';
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
        // Получим категорию
        $currentcategory = \core_course_category::get($catid);

        if ( empty($currentcategory) )
        { // Категория не найдена
            return $html;
        }

        // Получить дочерние категории
    	if ( ! empty($options['categories']) )
        {
            $children = $options['categories'];
        } else
        {
            $children = $currentcategory->get_children();
        }


        $content = '';

        $categories_in_line = get_config('crw_categories_list_icons', 'inline');
        if ( empty($categories_in_line) )
        { // Значение по умолчанию
            $categories_in_line = 6;
        }
        // Отобразить кнопку Назад
        $enable_back_button = get_config('crw_categories_list_icons', 'enable_back_button');

        // Счетчик элементов
        $counter = 0;
        if ( ! empty($enable_back_button) )
        {
            // Сформируем кнопку Назад
            $back = $this->cs_category_block_back($currentcategory->id, 'catli_backbutton catli_courseline' . $categories_in_line);
            if ( ! empty($back) )
            {
                $counter++;
                $classes = '';
                if ( (($counter - 1) % $categories_in_line) == 0 )
                { // Начало строки
                    $classes .= ' first';
                }
                if ( ($counter % $categories_in_line) == 0 )
                { // Конец строки
                    $classes .= ' last';
                }
                // Сформируем кнопку Назад
                $content .= $back;

                if ( ($counter % $categories_in_line) == 0 )
                {// Добавим разграничитель
                    $content .= html_writer::div('', 'catli_clearboth');
                }
            }
        }
        foreach ( $children as $category )
        {
            $counter++;
            $classes = '';
            if ( (($counter - 1) % $categories_in_line) == 0 )
            { // Начало строки
                $classes .= ' first';
            }
            if ( ($counter % $categories_in_line) == 0 )
            { // Конец строки
                $classes .= ' last';
            }
            $searchquery = $options['searchquery'] ?? null;
            $userid = $options['userid'] ?? null;
            $notactive = $options['usercourses_add_not_active'] ?? null;
            // Сформируем блок категории
            $content .= $this->cs_category_block(
                $category->id,
                'catli_courseline' . $categories_in_line.$classes,
                [
                    'searchquery' => $searchquery,
                    'userid' => $userid,
                    'usercourses_add_not_active' => $notactive
                ]
            );

            if ( ($counter % $categories_in_line) == 0 )
            {
                // Добавим разграничитель
                $content .= html_writer::div('', 'catli_clearboth');
            }
        }


        if ( ! empty($content) )
        {
            // Добавим разграничитель
            $html .= html_writer::div('', 'catli_clearboth');
            // Обертка
            $html .= html_writer::div($content, 'catli_block_wrapper');
        }


        // Возвращаем блок
        return $html;
    }

    /**
     * Сформировать блок кнопки назад
     *
     * @param int $catid
     *            - ID родительской категории курсов
     * @param int $addclass
     *            - дополнительные классы блока
     * @param array $opt
     *            - Массив дополнительных опций
     *
     * @return string - HTML-код блока категории
     */
    protected function cs_category_block_back($catid = 0, $addclass, $opt = array())
    {
        global $OUTPUT;
        // Подготовим переменную для записи html
        $html = '';

        // Получим категорию
        $category = \core_course_category::get($catid);
        if ( empty($catid) )
        {// Категория верхнего уровня
            return $html;
        }
        $parent = $category->get_parent_coursecat();
        if ( empty($parent) )
        {// Категория не найдена
            return $html;
        }

        // Формируем ссылку на категорию
        if ($parent->id == 0) {
            $url = new moodle_url('/local/crw/index.php');
        } else {
            $url = new moodle_url('/local/crw/category.php', array('cid' => $parent->id));
        }

        // Добавим иконку
        $html .= $this->iconback;

        // Сформируем блок с названием
        $html .= html_writer::div(get_string('backbutton', 'crw_categories_list_icons'), 'catli_category_name');

        // Обертка
        $return = html_writer::link($url, $html, array (
                'class' => 'catli_category_item ' . $addclass
        ));

        // Возвращаем блок
        return $return;
    }

    /**
     * Сформировать блок одной категории курсов
     *
     * @param int $catid
     *            - ID категории курсов
     * @param int $addclass
     *            - дополнительные классы блока
     * @param array $opt
     *            - Массив дополнительных опций
     *
     * @return string - HTML-код блока категории
     */
    protected function cs_category_block($catid = 0, $addclass, $opt = array())
    {
        global $OUTPUT;
        // Подготовим переменную для записи html
        $html = '';

        // Получим категорию
        $category = \core_course_category::get($catid);
        if ( empty($category) )
        {// Категория не найдена
            return $html;
        }

        $icon = $this->icon;
        $fs = get_file_storage();
        $context = context_coursecat::instance($catid);
        $files = $fs->get_area_files($context->id, 'local_crw', 'categoryicon', $catid);
        // Вывод первого изображения
        if ( count($files) )
        {
            foreach ( $files as $file )
            {
                // Является ли файл изображением
                $isimage = $file->is_valid_image();
                if ( $isimage )
                {
                    // Получаем адрес изображения
                    $url = moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            $file->get_itemid(),
                            $file->get_filepath(),
                            $file->get_filename()
                    );
                    // Возвращаем html
                    $icon = '<div class="catli_icon_wrap" ><img class="catli_icon" src="' . $url . '" /></div>';
                }
            }
        }

        if ( get_config('crw_categories_list_icons', 'anchor_enabled') )
        {// Требуются якоря
            // Формируем ссылку на категорию
            $url = '#catid'.$catid;
        } else
        {
            $urlopts = ['cid' => $catid];
            if (!is_null($opt['searchquery']))
            {
                $urlopts['crws'] = $opt['searchquery'];
            }

            if (!is_null($opt['userid']))
            {
                $urlopts['uid'] = $opt['userid'];

                if (!is_null($opt['usercourses_add_not_active']))
                {
                    $urlopts['na'] = (int)!empty($opt['usercourses_add_not_active']);
                }
            }
            // Формируем ссылку на категорию
            $url = new moodle_url('/local/crw/category.php', $urlopts);
        }

        // Добавим иконку
        $html .= $icon;

        // Добавим название
        if ( isset($category->name) )
        { // Сформируем блок с названием
            $html .= html_writer::div($category->name, 'catli_category_name');
        }

        // Обертка
        $return = html_writer::link($url, $html, array (
                'class' => 'catli_category_item ' . $addclass
        ));

        // Возвращаем блок
        return $return;
    }
}