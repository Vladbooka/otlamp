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

$html = '';
// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('listpersons', 'persons'), 
    $DOF->url_im('persons', '/list.php'), 
    $addvars
);

// Получение ID персоны
$id = required_param('id', PARAM_INT);

$defaultbackurl = $DOF->url_im('persons', '/view.php', ['id' => $id]);
$backurl = optional_param(
    'backurl', 
    $defaultbackurl, 
    PARAM_RAW
);
// Страница возврата
$backlink = html_writer::link($backurl, $DOF->get_string('back', 'persons'));

// Получение текущей персоны
$person = $DOF->storage('persons')->get($id);
if ( empty($person) )
{// Персона не найдена
    if ( $defaultbackurl == $backurl )
    {// дефолтный урл для возврата не подходит, так как в нем используется идентификатор несуществующего пользователя
        // Возврат к списку пользователей
        $backlink = html_writer::link(
            $DOF->url_im('persons','/list.php'), 
            $DOF->get_string('listpersons','persons')
        );
    }
    $DOF->messages->add($DOF->get_string('nopersons', 'persons'), 'error');
    $html .= $backlink;
} else
{// Персона существует

    // Страница обработки формы
    $addvars['id'] = $id;
    $addvars['backurl'] = $backurl;
    $actionurl = $DOF->url_im('persons', '/edit_customfields.php', $addvars);
    
    // Инициализация формы доп.полей
    $customdata = new stdClass();
    $customdata->personid = $person->id;
    $DOF->modlib('formbuilder')->init_form('form', $actionurl, $customdata);
    
    // Инициализация вкладки персоны
    $persontab = $DOF->modlib('formbuilder')->add_section(
        'form',
        $DOF->get_string('block_person_customfields_header', 'persons'),
        'person'
    );
    
    // Добавление дополнительных полей персоны на указанную вкладку формы
    $DOF->modlib('formbuilder')->add_customfields(
        'form', 
        $persontab, 
        'persons', 
        $person->departmentid, 
        $person->id
    );
    
    if( $DOF->modlib('formbuilder')->is_form_submitted('form') )
    {
        $error = false;
        try {
            // Обработка формы
            $DOF->modlib('formbuilder')->process_form('form');
        } catch(dof_storage_customfields_exception $ex)
        {
            $error = true;
            // При сохранении возникла ошибка
            $DOF->messages->add($ex->getMessage().'. '.$backlink, 'error');
        }
        if( ! $error )
        {
            $DOF->messages->add(
                $DOF->get_string('save_data_success', 'customfields', $backlink, 'storage'), 
                'message'
            );
            //redirect($backurl);
        }
    }
    
    // Рендеринг формы
    $html .= $DOF->modlib('formbuilder')->render_form('form');
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $html;

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>