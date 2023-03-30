<?PHP
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
 * Интерфейс управления персонами. Страница сохранения персоны.
 *
 * @package    im
 * @subpackage persons
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once(dirname(realpath(__FILE__)) . '/lib.php');
require_once('lib.php');
require_once('form.php');

$html = '';
// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('listpersons', 'persons'),
    $DOF->url_im('persons', '/list.php'),
    $addvars
);

// Получение ID персоны
$id = optional_param('id', null, PARAM_INT);
// Проверка прав доступа
if ( $id == 0 )
{// Создание новой персоны
    $DOF->storage('persons')->require_access('create');
} else
{// Редактирование персоны
    if ( ! $DOF->storage('persons')->is_exists($id) )
    {// Персона не найдена
        $errorlink = $DOF->url_im('persons', '', $addvars);
        $DOF->print_error('nopersons', $errorlink, null, 'im', 'persons');
    }
    $DOF->storage('persons')->require_access('edit', $id);
}

// Сформировать дополнительные данные для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->persons = ['id' => $id];
$customdata->returnurl = $DOF->url_im('persons', '/view.php', $addvars);
$customdata->person_gender_field_optional = $DOF->storage('config')->get_config_value(
    'make_person_gender_field_optional',
    'im',
    'persons',
    $addvars['departmentid']
);

$addvars['id'] = (int)$id;
// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('createeditperson', 'persons'),
    $DOF->url_im('persons', '/edit.php'),
    $addvars
);

// Форма сохранения персоны в системе
$form = new dof_im_persons_edit_form(
    $DOF->url_im('persons', '/edit.php', $addvars),
    $customdata,
    'post',
    '',
    ['class' => 'dof_im_persons_edit_form']
);
// Обработчик формы
$form->process();
// Отображение формы
$html .= $form->render();

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $html;

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>