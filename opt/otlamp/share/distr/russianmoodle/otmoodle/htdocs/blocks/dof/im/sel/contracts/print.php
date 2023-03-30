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
 * Панель управления приемной комиссии. Экспорт договоров.
 *
 * @package    im
 * @subpackage sel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');

// Получение GET-параметров
$type = optional_param('type', null, PARAM_ALPHA);
$contractid = optional_param('id', 0, PARAM_INT);
$templaterdata = $DOF->storage('contracts')->get($contractid);
if ( empty($templaterdata) )
{// Договор не найден
	// Уведомление об ошибке
    $DOF->messages->add(
        $DOF->get_string('error_page_contract_contract_not_found', 'sel'),
        'error'
    );
} else
{// Договор найден
    if ( ! $DOF->storage('contracts')->is_access('view', $contractid, null) )
    {// Доступ закрыт
        // Уведомление об ошибке
        $DOF->messages->add(
            $DOF->get_string('error_page_contract_contract_view_access_denied', 'sel'),
            'error'
        );
    } else
    {// Пользователь имеет доступ к договору
        
        // Генерация дополнительной информации по договору
        
        // Получаем персональную информацию
        $templaterdata->selfirstname = '';
        $templaterdata->sellastname = '';
        $templaterdata->selmiddlename = '';
        if ( $seller = $DOF->storage('persons')->get($templaterdata->sellerid) )
        {
            $templaterdata->selfirstname = $seller->firstname;
            $templaterdata->sellastname = $seller->lastname;
            if ( !isset($seller->middlename) )
            {
                $seller->middlename = '';
            }
            $templaterdata->selmiddlename = $seller->middlename;
        }
        
        $student = (array) $DOF->storage('persons')->get($templaterdata->studentid);
        $student += (array) $DOF->storage('addresses')->get($student['passportaddrid']);
        $student = (object) $student;
        $student->name = 'student';
        $templaterdata->clientfirstname = $student->firstname;
        $templaterdata->clientlastname = $student->lastname;
        if ( ! isset($student->middlename) )
        {
            $student->middlename = '';
        }
        $templaterdata->clientmiddlename = $student->middlename;
        
        if ( ( $student->passtypeid == 0 ) or ( ! isset($student->passtypeid) ) )
        {
            $student->passtypeid = $DOF->get_string('nonepasport', 'sel');
            $student->passportdate = '';
        
        } else
        {
            $student->passtypeid = $DOF->modlib('refbook')->pasport_type($student->passtypeid);
            $student->passportnum = $student->passportnum . ' ' . $DOF->get_string('given', 'sel');
        }
        
        $client = (array) $DOF->storage('persons')->get($templaterdata->clientid);
        $client += (array) $DOF->storage('addresses')->get($client['passportaddrid']);
        $client = (object) $client;
        $client->name = 'specimen';
        if ( ( $client->passtypeid == 0 ) or ( ! isset($client->passtypeid) ) )
        {
            $client->passtypeid = $DOF->get_string('nonepasport', 'sel');
            $client->passportdate = '';
        } else
        {
            $client->passtypeid = $DOF->modlib('refbook')->pasport_type($client->passtypeid);
            $client->passportnum = $client->passportnum. ' ' . $DOF->get_string('given', 'sel');
        }
        
        $templaterdata->clientfirstname = $client->firstname;
        $templaterdata->clientlastname = $client->lastname;
        if ( !isset($client->middlename) )
        {
            $client->middlename = '';
        }
        $templaterdata->clientmiddlename = $client->middlename;
        $templaterdata->student = $student;
        $templaterdata->client = $client;
        
        // Добавляем языковые строки
        $templaterdata->fullname = $DOF->get_string('fullname', 'sel');
        $templaterdata->dateofbirth = $DOF->get_string('dateofbirth', 'sel');
        $templaterdata->emailshort = $DOF->get_string('emailshort', 'sel');
        $templaterdata->addresshome = $DOF->get_string('addresshome', 'sel');
        $templaterdata->passtype = $DOF->get_string('passtype', 'sel');
        $templaterdata->contacts = $DOF->get_string('contacts', 'sel');
        $templaterdata->phonehome = $DOF->get_string('phonehome', 'sel');
        $templaterdata->acceptseller = $DOF->get_string('acceptseller', 'sel');
        $templaterdata->acceptclient = $DOF->get_string('acceptclient', 'sel');
        $templaterdata->specimen = $DOF->get_string('specimen', 'sel');
        $templaterdata->phonecell = $DOF->get_string('phonecell', 'sel');
        $templaterdata->curator = $DOF->get_string('curator', 'sel');
        $templaterdata->phonework = $DOF->get_string('phonework', 'sel');
        $templaterdata->protokolfull = $DOF->get_string('protokolfull', 'sel');
        $templaterdata->tocontract = $DOF->get_string('tocontract', 'sel');
        
        $templater_package = $DOF->modlib('templater')->template( 'im', 'sel', $templaterdata, 'protokol');
        
        // Выбираем формат экспорта
        switch ($type)
        {
            case 'odf' : 
                $templater_package->send_file('odf');
                die;
                break;
            case 'csv' : 
                $templater_package->send_file('csv');
                die;
                break;
            case 'html' : 
                $templater_package->send_file('html');
                die;
                break;
            case 'dbg' :
            default : 
                $templater_package->send_file('dbg');
                die;
        }
    }
}


$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>