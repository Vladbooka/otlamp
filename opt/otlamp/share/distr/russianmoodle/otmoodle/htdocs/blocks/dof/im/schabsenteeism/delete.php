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
 * Интерфейс управления причинами отсутствия
 *
 * @package    im
 * @subpackage schabsenteeism
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');

// ID причины
$id = optional_param('id', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$schabsenteeism = $DOF->storage('schabsenteeism')->get($id);
if ( empty($schabsenteeism) )
{// Причина не найдена
    $DOF->messages->add(
        $DOF->get_string('error_interface_delete_notfound', 'schabsenteeism'),
        'error'
    );
} elseif ( ! $DOF->modlib('journal')->get_manager('schabsenteeism')->can_delete($id) )
{
    $DOF->messages->add(
        $DOF->get_string('error_interface_delete_access_delete_denied', 'schabsenteeism'),
        'error'
    );
}

// Ссылка для возврата при ошибке
$returnurl = $DOF->url_im('schabsenteeism', '/index.php', $addvars);

$addvars['id'] = $id;

// Добавление уровня навигации
$stringvars = new stdClass();
$stringvars->name = $DOF->storage('schabsenteeism')->get_shortname($schabsenteeism);
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('interface_delete_header', 'schabsenteeism', $stringvars),
    $DOF->url_im('schabsenteeism', '/delete.php', $addvars)
);

if ( $confirm )
{
    $DOF->modlib('journal')->get_manager('schabsenteeism')->delete($id);
    redirect($returnurl);
} else
{
    // Подтверждение
    $confirmation = $DOF->get_string('confirmation_delete_schabsenteeism', 'schabsenteeism', $stringvars);
    $confirmurl = $DOF->url_im('schabsenteeism', '/delete.php', $addvars + ['confirm' => 1]);
    
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    
    $DOF->modlib('widgets')->notice_yesno($confirmation, $confirmurl, $returnurl);
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}
?>