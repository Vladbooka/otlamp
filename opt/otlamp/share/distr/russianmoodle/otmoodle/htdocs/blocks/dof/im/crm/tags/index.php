<?PHP
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
 * Мои теги
 */

// Подключаем библиотеки
require_once('lib.php');

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Печатаем вкладки
echo $DOF->im('crm')->print_tab($addvars, 'tags', 'alltags');

// Массив get параметров для ссылки
$somevars = $addvars;

//Добавляем задачу
$somevars['action'] = 'newtag';

// Выводим ссылку на создание тега
if ( $DOF->storage('tags')->is_access('create') )
{
    echo html_writer::link(
            $DOF->url_im('crm','/tags/action.php',$somevars),
            $DOF->get_string('create_tag', 'crm'),
            array(
                    'title' => $DOF->get_string('create_tag_title', 'crm'),
                    'class' => 'create_link'
            )
    );
}

// Печать всех тегов
$DOF->im('crm')->print_list_tags(true, $addvars);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>