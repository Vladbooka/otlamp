<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////
 
/**
 * Рейтинг студентов
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
require_once(__DIR__.'/plugins/usersfilter/form.php');

if ( ! $DOF->im('achievements')->is_access('rating_view', $addvars['departmentid']) )
{// Рейтинг недоступен
    $DOF->messages->add($DOF->get_string('notice_system_rating_disabled', 'achievements'), 'notice');
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    die;
}

$html = '';

$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->addvars = $addvars;
$customdata->departmentid = $addvars['departmentid'];
$filterform = new dof_im_achievements_usersfilter_userform(NULL, $customdata);

// Проверка права на экспорт таблицы рейтинга
if( $DOF->im('achievements')->is_access('rating_export', $addvars['departmentid']) )
{
    // добавление в форму кнопки экспорта рейтинга
    $filterform->hook_definition(function(&$mform)use($DOF){
        $mform->addElement(
            'submit',
            'form_plugin_userfilter_export',
            $DOF->get_string('export_xls', 'achievements')
        );
    });
}

// обработка фильтра с редиректом и передачей категории в параметре
$filterform->process();

// Выбранный раздел
$achievementcat = null;
$filtersearchparams = optional_param('filter', [], PARAM_RAW);
if( ! empty($filtersearchparams) )
{
    $filtersearchparams = urldecode($filtersearchparams);
    $addvars['filter'] = $filtersearchparams;
    $filtersearchparams = (array)json_decode($filtersearchparams);
    if( isset($filtersearchparams['achievement_category']) )
    {
        $achievementcat = $filtersearchparams['achievement_category'];
    }
}

if ( is_null($achievementcat) )
{// Раздел не выбран
    // Установка раздела по умолчанию из конфигурации
    $defaultachievementcat = $DOF->storage('config')->get_config_value(
        'default_achievementcat', 
        'storage', 
        'achievementcats', 
        $addvars['departmentid']
    );
    if( ! empty($defaultachievementcat) )
    {// Конфигурация найдена
        $achievementcat = $defaultachievementcat;
    } else
    {// Конфигурация не найдена
        $achievementcat = 0;
    }
    $filtersearchparams['achievement_category'] = $achievementcat;
    // Редирект на рейтинг с разделом по умолчанию
    $somevars = array_merge(
        $addvars,
        [
            'filter' => json_encode($filtersearchparams)
        ]
    );
    redirect($DOF->url_im('achievements','/rating.php', $somevars));
}
    
// Проверка раздела на видимость в рейтинге
$affectrating = (bool)$DOF->storage('achievementcats')->get_field(
    $achievementcat, 
    'affectrating'
);
// Активные категории
$achievementcats = (array)$DOF->storage('achievementcats')->get_categories_select_options(0,[]);

$configvalue = [];

// Получение конфигурации фильтра
$configvalue = $DOF->storage('config')->get_config_value(
    'usersfilter_fields',
    'im',
    'achievements',
    $addvars['departmentid']
    );

$configvalue = (array)unserialize($configvalue);
if (empty($configvalue['achievementfields']['category'])) {
    $categoryisset = false;
}else{
    $categoryisset = true;
}

if ( ! $affectrating && $categoryisset )
{// Радел скрыт для рейтинга
    
    $filtersearchparams['achievement_category'] = $achievementcat;
    // Установка выбранного раздела
    $addvars['filter'] = json_encode($filtersearchparams);
    // Формирование url формы выбора раздела
    $defaultachievementcaturl = $DOF->url_im('achievements', '/rating.php', $addvars);
    // Сформируем дополнительные данные
    $defaultachievementcatcustomdata = new stdClass();
    $defaultachievementcatcustomdata->dof = $DOF;
    $defaultachievementcatcustomdata->addvars = $addvars;
    // Сформируем форму
    $defaultachievementcatform = new dof_im_achievementins_default_achievementcat_form($defaultachievementcaturl, $defaultachievementcatcustomdata);
    // Обработчик формы
    $defaultachievementcatform->process();
    $html .= $defaultachievementcatform->render();
} else
{// Раздел доступен в рейтинге
    $limitnum  = optional_param('limitnum', 50, PARAM_INT);
    $limitfrom = optional_param('limitfrom', '1', PARAM_INT);
    // Нормализация
    if ( $limitfrom < 1 )
    {
        $limitfrom = 1;
    }
    if ( $limitnum < 1 )
    {
        $limitnum = 50;
    }
    
    // Опции формирования таблицы разделов достижений
    $options = ['addvars' => $addvars, 'limitfrom' => $limitfrom, 'limitnum' => $limitnum ];
    
    // Получить данные фильтрации
    $filterdata = $filterform->get_filter();
    
    if ( isset($filterdata['persons']) )
    {
        $options['persons'] = $filterdata['persons'];
    }
    if ( isset($filterdata['achievementins']) )
    {
        $options['achievementins'] = $filterdata['achievementins'];
    }
    

    // Формат отображения
    $format = optional_param('format', null, PARAM_ALPHA);
    if( $format == "xls" )
    {
        // Проверка права экспорта таблицы рейтинга
        if( $DOF->im('achievements')->is_access('rating_export', $addvars['departmentid']) )
        {
            // подключаем необходимые классы
            require_once($DOF->plugin_path('modlib','templater','/format.php'));
            $exportdata = $DOF->im('achievements')->get_rating_exportdata([
                'getratingoptions' => $options,
                'additionaloptions' => [
                    'achievement_category' => $achievementcat
                ]
            ]);
            // создаем оъект шаблонизатора, и загружаем в него данные
            $exporter = $DOF->modlib('templater')->template(
                'im', 
                'achievements',
                $exportdata, 
                'rating'
            );
            // устанавливаем собственное имя для файла экспорта
            $fileoptions = new stdClass();
            $fileoptions->filename = 'Rating_'.dof_userdate(time(),'%Y-%m-%d');
            $exporter->get_file('xls', $fileoptions);die;
        } else
        {
            // Ошибка. Нет права экспорта таблицы рейтинга
            $DOF->messages->add(
                $DOF->get_string('error_rating_export_denied', 'achievements'), 
                'notice'
            );
        }
        
    }
    
    
    $ratingtable = $DOF->im('achievements')->get_ratingtable($options);
    
    // Статусы
    $statuses = $DOF->workflow('achievementins')->get_meta_list('active');
    $statuses = array_keys($statuses);
    $options['status'] = $statuses;
    $fullcount = $DOF->storage('achievementins')->get_rating_count($options);
    $pages = $DOF->modlib('widgets')->pages_navigation('achievements' ,$fullcount ,$limitnum, $limitfrom);
    
    $filterform->add_get_params($addvars);
    
    $html .= $filterform->render();
    
    $addvars['limitfrom'] = $pages->get_current_limitfrom();
    $addvars['limitnum'] = $pages->get_current_limitnum();
    $pagesstring = $pages->get_navpages_list('/rating.php', $addvars);
    $html .= $pagesstring;
    
    // Панель редактирования разделов достижений
    $html .= $ratingtable;
    
    $html .= $pagesstring;
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>