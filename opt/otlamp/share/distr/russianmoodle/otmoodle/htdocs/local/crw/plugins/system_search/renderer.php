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
 * Плагин поиска курсов. Рендер.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_crw\output\renderer;

require_once($CFG->dirroot .'/course/renderer.php');
require_once($CFG->dirroot .'/local/crw/lib.php');
require_once($CFG->dirroot .'/local/crw/plugins/system_search/formlib.php');

class crw_system_search_renderer extends renderer
{
    public function __construct()
    {
    }
    /**
     * Плучить html-код блока поиска
     *
     * @param int $catid - ID категории
     * @param array $options - Опции отображения
     *
     * @return string - html код блока
     */
    public function get_block($catid = 0, $options = array())
    {
        global $OUTPUT, $PAGE;
        // Подготовим переменную для записи html
        $html = '';
        $urlparameters = [];
        $searchstring = '';
        $plugin = $options['plugin'];
        
        if( ! empty($options['crws']) )
        {
            $searchstring = $options['crws'];
            $urlparameters['crws'] = $options['crws'];
        }
        if( ! empty($options['srr']) )
        {
            $urlparameters['srr'] = $options['srr'];
        }
        
        $formaction = $options['formaction'] ?? '/local/crw/search.php';
        
        $inplace = $plugin->get_config(
            'settings_display_results_inplace',
            false
        );
        if ($inplace)
        {
            $formaction = null;
        }
        
        $ajaxsearch = $plugin->get_config('settings_ajax_search', false);
        if ($ajaxsearch && !empty($inplace))
        {
            // фильтрация по ajax может работать только если опция включена
            // и используется отображение на текущей странице
            // если выбрано на отдельной странице - аякс использоваться не будет
            
            $options['ajaxfilter'] = true;
            
            if (isloggedin()) {
                $PAGE->requires->js_call_amd(
                    'crw_system_search/ajax_filter',
                    'init',
                    [
                        $PAGE->context->id,
                        $options['categoryid'] ?? 0
                    ]
                );
            } else {
                $version = get_config('crw_system_search', 'version');
                if ($version >= 2020042300) {
                    $PAGE->requires->js_call_amd(
                        'crw_system_search/ajax_filter_no_auth',
                        'init',
                        [
                            $PAGE->context->id,
                            $options['categoryid'] ?? 0
                        ]
                        );
                }
            }
        }
        
        $style = $plugin->get_config('settings_style', 'default');
        if ($style == 'minimalism')
        {
            $PAGE->requires->js_call_amd(
                'crw_system_search/minimalism-autosubmit',
                'init'
            );
        }
        
        list($processresult, $formhtml, $filters) = $this->get_form_data(
            $options,
            $formaction,
            isset($options['ajaxformdata']) ? $options['ajaxformdata'] : null,
            $options['formredirect'] ?? !$inplace
        );

        
        $class = ['crw_formsearch'];
        $class[] = $style;
        // Получение настройки принудительного отображения формы расширенного поиска
        $fullsearchonly = $plugin->get_config('settings_fullsearch_only');
        if( ! empty($fullsearchonly) || ! empty($searchstring) || !empty($processresult) )
        {
            $class[] = 'fullsearch';
        }
        $html .= html_writer::div($formhtml, implode(' ', $class), array('id' => 'crw_formsearch'));
        $html .= html_writer::div('', '', array('id' => 'crw_formsearch_bg'));
        $html .= html_writer::div('', 'crw_clearboth');
        
        if (isset($filters['sq']))
        {
            $processresult = $filters['sq'];
            list($hints, $totalcount) = $plugin->load_hints($processresult);
            
            $html .= html_writer::tag('h1', get_string('search_hints_header', 'crw_system_search', $processresult), ['class'=>'crw_search_hints_header']);

            if (!empty($hints) && !empty($totalcount))
            {
                $html .= html_writer::start_tag('ul', ['class' => 'crw-search-results form-autocomplete-suggestions']);
                foreach($hints as $hint)
                {
                    if (!empty($hint['courseid']))
                    {
                        $hint['hintimgurl'] = $this->course_image_url($hint['courseid']);
                    }
                    $html .= html_writer::tag('li', $OUTPUT->render_from_template('crw_system_search/search_hints', $hint));
                }
                $html .= html_writer::end_tag('ul');
                $page = optional_param('page', 0, PARAM_INT);
                $html .= $PAGE->get_renderer('core')->paging_bar(
                    $totalcount,
                    $page,
                    \core_search\manager::DISPLAY_RESULTS_PER_PAGE,
                    new moodle_url($formaction, $urlparameters)
                );
            } else
            {
                $html .= html_writer::div(get_string('search_hints_no_results_found', 'crw_system_search'));
            }
        }
        
        if (!empty($options['return_process_result']))
        {
            // требуется вернуть не только html, но и результат обработки формы (crws)
            return [$processresult, $html];
        }
        
        return $html;
    }
    
    /**
     * Генерация формы и получение данных о ней
     *
     * @param array $options - опции формы
     *                          plugin - экземпляр класса субплагина, генерирующего форму
     *                          crws - поисковый запрос
     *                          srr - рендерер результата поиска
     * @param string|null $action - аттрибут action формы
     * @param array $ajaxformdata Forms submitted via ajax, must pass their data here, instead of relying on _GET and _POST.
     * @param boolean $redirect - перенаправление на страницу с отфильтрованными курсами
     * @return array - два значения: результат обработки формы (поисковый запрос crws) и html-код формы
     */
    protected function get_form_data($options = [], $action = null, $ajaxformdata=null, $redirect = true)
    {
        $formoptions = new stdClass();
        $formoptions->plugin = $options['plugin'];
        if( ! empty($options['crws']) )
        {
            $formoptions->crws = $options['crws'];
        }
        if( ! empty($options['srr']) )
        {
            $formoptions->srr = $options['srr'];
        }
        if( ! empty($options['ajaxfilter']) )
        {
            $formoptions->ajaxfilter = $options['ajaxfilter'];
        }
        if( ! empty($options['categoryid']) )
        {
            $formoptions->categoryid = $options['categoryid'];
        }
        
        // Форма поиска
        $form = new crw_search_form($action, $formoptions, 'post', '', null, true, $ajaxformdata);
        $values = $form->prepare_default_values();
        
        $form = new crw_search_form($action, $formoptions, 'post', '', null, true, $ajaxformdata);
        $form->set_data($values);
        $filters = $form->get_sent_filters();
        // Обработчик формы
        $processresult = $form->process($redirect);
        
        $formhtml = '';
        $renderdenied = $options['plugin']->get_config('form_render_denied', false);
        if (empty($renderdenied))
        {
            // Получение html формы
            $formhtml = $form->render();
        }
        
        return [$processresult, $formhtml, $filters];
    }
    
    /**
     * Получить url-адрес изображения для текущего курса
     *
     * @param stdClass $courseid - идентификатор курса
     *
     * @return string - url-адрес изображения для текущего курса
     */
    protected function course_image_url($courseid)
    {
        global $CFG;
        
        require_once ($CFG->libdir . '/filestorage/file_storage.php');
        require_once ($CFG->dirroot . '/course/lib.php');
        
        $courseimgurl = file_encode_url($CFG->wwwroot, '/local/crw/assets/no-photo.gif');
        
        // Если лимит не позволяет - выводим заглушку
        if ( ! empty($CFG->courseoverviewfileslimit) )
        {
            // Получаем хранилище
            $fs = get_file_storage();
            // Получаем контекст
            $context = context_course::instance($courseid);
            // Получаем файлы
            $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
            // Вывод первого файла
            if ( count($files) )
            {
                // Формирование изменений между превью и исходным файлом
                $preview = new stdClass();
                $preview->component = 'local_crw';
                foreach ( $files as $file )
                {
                    if ( $file->is_valid_image() )
                    {// файл является изображением
                        $courseimgurl = local_crw_get_preview($file, $preview);
                        break;
                    }
                }
            }
        }
        return $courseimgurl;
    }
}