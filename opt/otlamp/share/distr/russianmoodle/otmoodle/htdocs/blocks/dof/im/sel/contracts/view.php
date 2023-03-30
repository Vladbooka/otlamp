<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
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
 * Панель управления приемной комиссии. Страница договора на обучение.
 *
 * @package    im
 * @subpackage sel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once($DOF->plugin_path('im','departments','/lib.php'));

// HTML-код старинцы
$html = '';

// Отображение сообщений на основе GET-параметров
$DOF->im('sel')->messages();

// Получение GET-параметров
// ID текущего договора
$currentcontractid = optional_param('id', 0, PARAM_INT);

// Формирование GET-параметров, которые не входят в навигацию верхнего уровня
$addvars['id'] = $currentcontractid;

// Получение текущей персоны
$person = $DOF->storage('persons')->get_bu(null, true);

// Получение текущего договора
$currentcontract = $DOF->storage('contracts')->get($currentcontractid);

if ( ! $currentcontract )
{// Договор не найден
    // Уведомление об ошибке
    $DOF->messages->add(
        $DOF->get_string('error_page_contract_contract_not_found', 'sel'),
        'error'
    );
    
} else 
{// Договор найден
    
    // Проверка доступа на просмотр договоров
    $accessview = $DOF->storage('contracts')->is_access(
        'view',
        (int)$currentcontract->id, 
        (int)$person->mdluser, 
        (int)$currentcontract->departmentid
    );
    if ( ! $accessview )
    {// Доступ закрыт
        // Уведомление об ошибке
        $DOF->messages->add(
            $DOF->get_string('error_page_contract_contract_view_access_denied', 'sel'),
            'error'
        );
    } else 
    {// Пользователь имеет доступ к договору
        
        // ССЫЛКИ НА ДОПОЛНИТЕЛЬНЫЕ ИНТЕРФЕЙСЫ ПО ДОГОВОРУ
        
        // Подписки на программы по текущему договору
        $sbclinks = '';
        if ( $DOF->storage('programmsbcs')->is_access('view') )
        {
            $somevars = [
                'departmentid' => $addvars['departmentid'],
                'contractid' => $currentcontractid
            ];
            $url = $DOF->url_im('programmsbcs', '/list.php', $somevars);
            $text = $DOF->get_string('page_contract_link_contract_programmsbcs', 'sel');
            $sbclinks .= dof_html_writer::link($url, $text, ['class' => 'btn btn-secondary button dof_button btn-sm']);
        }
        
        // Cоздание подписки на программу
        if ( $DOF->storage('programmsbcs')->is_access('create') )
        {
            // Лимит подписок на программу
            if ( $DOF->storage('config')->get_limitobject('programmsbcs', $addvars['departmentid']) )
            {// Лимит подписок не привышен
                $somevars = [
                    'departmentid' => $addvars['departmentid'],
                    'contractid' => $currentcontractid
                ];
                $url = $DOF->url_im('programmsbcs', '/edit.php', $somevars);
                $text = $DOF->get_string('page_contract_link_contract_programmsbc_create', 'sel');
                $sbclinks .= dof_html_writer::link($url, $text, ['class' => 'btn btn-secondary button dof_button btn-sm']);
            } else
            {// Лимит привышен
                $text = $DOF->get_string('page_contract_link_contract_programmsbc_create_limit', 'sel');
                $sbclinks .= dof_html_writer::span($text, ['class' => 'btn btn-secondary button dof_button btn-sm']);
            }
        }
        $html .= dof_html_writer::div($sbclinks);
        
        // Сведения об обучении
        if ( $DOF->storage('persons')->is_access('viewpersonal', $currentcontract->studentid) )
        {
            $somevars = [
                'departmentid' => $addvars['departmentid'],
                'clientid' => $currentcontract->studentid
            ];
            $url = $DOF->url_im('recordbook', '/index.php', $somevars);
            $text = $DOF->get_string('page_contract_link_recordbook', 'sel');
            $link = dof_html_writer::link($url, $text, ['class' => 'btn btn-secondary button dof_button btn-sm mt-1']);
            $html .= dof_html_writer::div($link);
        }
        
        // Редактирование договора
        if ( $DOF->storage('contracts')->is_access('edit', $currentcontractid, $person->mdluser) )
        {
            $somevars = [
                'departmentid' => $addvars['departmentid'],
                'contractid' => $currentcontractid
            ];
            $url = $DOF->url_im('sel', '/contracts/edit_first.php', $somevars);
            $text = $DOF->get_string('page_contract_link_edit_contract', 'sel');
            $html .= dof_html_writer::link($url, $text, ['class' => 'btn btn-secondary button dof_button btn-sm my-1']);
        }
        
        // Распечатки договора
        $somevars = $addvars + [
            'type' => 'html'
        ];
        $url = $DOF->url_im('sel', '/contracts/print.php', $somevars);
        $text = $DOF->get_string('page_contract_link_export_html', 'sel');
        $html .= dof_html_writer::link($url, $text, ['class' => 'btn btn-secondary button dof_button pull-right btn-sm my-1', 'target' => '_blank']).PHP_EOL;
        $somevars = $addvars + [
            'type' => 'odf'
        ];
        $url = $DOF->url_im('sel', '/contracts/print.php', $somevars);
        $text = $DOF->get_string('page_contract_link_export_odf', 'sel');
        $html .= dof_html_writer::link($url, $text, ['class' => 'btn btn-secondary button dof_button pull-right btn-sm my-1', 'target' => '_blank']).PHP_EOL;
        
        // ДАННЫЕ ПО ДОГОВОРУ
        // Отображение данных по договору
        $html .= $DOF->im('sel')->block_contract_info($currentcontract, $addvars);
        
        ob_start();
        // Кидаем широковещательный запрос
        $DOF->send_event('im', 'sel', 'contractdata', $currentcontractid);
        $html .= (string)ob_get_clean();
        
        // Отображение данных по персонам договора
        $html .= dof_html_writer::tag('h3', $DOF->get_string('student', 'sel'));
        $html .=$DOF->im('persons')->show_person_html($currentcontract->studentid, $addvars);
        if ( $currentcontract->clientid <> $currentcontract->studentid )
        {
            $html .= dof_html_writer::tag('h3', $DOF->get_string('specimen', 'sel'));
            $html .=$DOF->im('persons')->show_person_html($currentcontract->clientid, $addvars);
        }
        
        // ДОПОЛНИТЕЛЬНЫЕ МЕХАНИЗМЫ ДОГОВОРА
        // Инициализация механизма смены подразделения
        $options = [];
        $change_department = new dof_im_departments_change_department($DOF, 'contracts', $options);
        
        // Отображение формы смены подразделения
        $url = $DOF->url_im('sel', '/contracts/view.php', $addvars);
        $html .= '<form action="'.$url.'" method=POST name="change_department">';
        $html .= '<input type="hidden" name="'.$change_department->options['prefix'].'_'.
            $change_department->options['listname'].'['.$currentcontractid.']" value="'.$currentcontractid.'"/>';
        
        $html .= $change_department->get_form();
        $html .= '</form>';
            
        // Исполнение механизма
        $errors = $change_department->execute_form();
        
        if ( $errors != 1 )
        {// О господи, помоги тому человеку, кто сделал это условие
            // @todo отправиться в прошлое и пристрелить его
            
            if ( empty($errors) )
            {// Обработка прошла успешно
                // Уведомление об ошибке
                $DOF->messages->add(
                    $DOF->get_string('departments_change_success', 'sel'),
                    'message'
                );
            } else
            {// Возникли ошибки при смене подразделения
                // Уведомление об ошибке
                foreach ( (array)$errors as $error )
                {
                    $DOF->messages->add(
                        $error,
                        'error'
                    );
                }
            }
        }
    }
}

// Добавление уровня навигации плагина
$a = new stdClass();
if ( isset($currentcontract->num) )
{
    $a->contractnum = $currentcontract->num;
}
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_contract_view_name', 'sel', $a),
    $DOF->url_im('sel', '/contracts/view.php'),
    $addvars
);

$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print($html);

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>