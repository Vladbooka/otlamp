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
 * Редактирование целей
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('mypage_title', 'achievements'),
    $DOF->url_im('achievements', '/my.php'),
    $addvars
);

// Идентификатор цели (достижения)
$achievementinid = required_param('id', PARAM_INT);
// Идентификатор шаблона, использующийся при создании
$achievementid = optional_param('aid', 0, PARAM_INT);
// Совершаемое действие
$action = optional_param('action', NULL, PARAM_ALPHA);
// Персона для которой происходит добавление цели (достижения)
$personid = optional_param('personid', 0, PARAM_INT);

// Валидация действия 
if( empty($action) || ! in_array($action, ['add', 'edit', 'approve', 'achieve']) )
{
    // Не удалось разпознать действие
    $DOF->messages->add(
        $DOF->get_string('edit_goal__action_not_recognized', 'achievements'),
        'error'
        );
    print_page('');
    exit;
} else
{
    $addvars['action'] = $action;
}

// Валидация идентификатора достижения
if( ! empty($achievementinid) )
{
    $achievementin = $DOF->storage('achievementins')->get($achievementinid, '*', MUST_EXIST);
    if( empty($achievementin) )
    {
        $DOF->messages->add(
            $DOF->get_string('edit_goal__achivement_not_found', 'achievements'),
            'error'
        );
        print_page('');
        exit;
    }
    $addvars['id'] = $achievementinid;
}

// Определение целевой персоны, чья цель (достижение) добавляется / редактируется
if ( ! empty($personid) )
{// Пользователь, для которого создается достижение передан
    $targetperson = $DOF->storage('persons')->get($personid);
} else
{// Требуется определить целевого пользователя автоматически
    if( empty($achievementinid) )
    {// Идет создание достижения без указания целевой персоны - значит это текущий пользователь
        $targetperson = $DOF->storage('persons')->get_bu();
    } else
    {// Редактирование достижения - получим владельца
        $targetperson = $DOF->storage('persons')->get($achievementin->userid);
    }
}
$addvars['personid'] = $targetperson->id;

// Код для вывода
$html = '';

switch($action)
{
    case 'approve':
        // проверка доступа к странице
        $DOF->storage('achievementins')->is_access('approve_goal', $achievementinid);
        
        $url = $DOF->url_im('achievements', '/edit_goal.php', $addvars);
        $customdata = new stdClass();
        $customdata->dof = $DOF;
        $customdata->id = $achievementinid;
        $customdata->addvars = $addvars;
        $form = new dof_im_achievements_approve_goal_form($url, $customdata);
        $form->process();
        $html .= $form->render();
        break;
    case 'achieve':
        // проверка доступа к странице
        $DOF->storage('achievementins')->require_access('achieve_goal', $achievementinid);
        
        // получение объекта класса шаблона
        $achievementobj = $DOF->storage('achievements')->object($achievementin->achievementid);
        if ( $achievementobj->is_autocompletion() )
        {
            // автоматическое выполнение
            // редиректим обратно
            redirect($DOF->url_im('achievements', '/my.php', $addvars));
        }
        
        $url = $DOF->url_im('achievements', '/edit_goal.php', $addvars);
        $customdata = new stdClass();
        $customdata->dof = $DOF;
        $customdata->id = $achievementinid;
        $customdata->addvars = $addvars;
        $form = new dof_im_achievements_achieve_goal_form($url, $customdata);
        $form->process();
        $html .= $form->render();
        break;
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Выводим код
echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
