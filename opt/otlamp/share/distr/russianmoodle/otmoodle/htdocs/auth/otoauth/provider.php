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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Manage providers
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB;

require('../../config.php');

$action = optional_param('action', null, PARAM_ALPHA);
$id = optional_param('id', null, PARAM_INT);

$syscontext = context_system::instance();

$baseurl = new moodle_url('/auth/otoauth/provider.php');
$PAGE->set_url($baseurl);
$PAGE->set_context($syscontext);
$PAGE->set_pagelayout('admin');

$PAGE->navbar->add(get_string('provider_management', 'auth_otoauth'), $baseurl);
switch($action)
{
    case 'add':
        $actionstr = get_string('provider_management_add', 'auth_otoauth');
        break;
    case 'edit':
        $actionstr = get_string('provider_management_edit', 'auth_otoauth');
        break;
    case 'delete':
        $actionstr = get_string('provider_management_delete', 'auth_otoauth');
        break;
    default:
        $actionstr = get_string('provider_management_viewlist', 'auth_otoauth');
        break;
}

$PAGE->navbar->add($actionstr);
$PAGE->set_title($actionstr);
$PAGE->set_heading($actionstr);




require_capability('auth/otoauth:managecustomproviders', $syscontext);




$html = '';

if (is_null($action))
{
    // настраиваемые провайдеры из мудл даты
    $customproviders = [];
    // файлом можно переопределить конфиг, заданный через интерфейс
    if (file_exists($CFG->dataroot.'/plugins/auth_otoauth/custom.php'))
    {//имеется файл с настройками кастомных провайдеров
        include($CFG->dataroot.'/plugins/auth_otoauth/custom.php');
    }
    
    // отображение списка настраиваемых провайдеров
    $dbcustomproviders = \auth_otoauth\customprovider::get_custom_providers();
    $customprovidershtml = '';
    if (!empty($dbcustomproviders))
    {
        // формирование списка настраиваемых провайдеров
        foreach ($dbcustomproviders as $dbcpcode => $dbcustomprovider)
        {
            $name = html_writer::span($dbcustomprovider->name, 'custom_provider_name');
            $status = html_writer::span(mb_strtolower($dbcustomprovider->displaystatus), 'custom_provider_status');
            $overriden = '';
            if (array_key_exists($dbcpcode, $customproviders))
            {
                $overriden = html_writer::span('!', 'custom_provider_overriden',
                    ['title' => get_string('custom_provider_overriden', 'auth_otoauth')]);
            }
            $description = html_writer::span($dbcustomprovider->description, 'custom_provider_description');
            
            $deleteurl = clone $baseurl;
            $deleteurl->param('action', 'delete');
            $deleteurl->param('id', $dbcustomprovider->id);
            $deleteurl->param('sesskey', sesskey());
            $delete = $OUTPUT->action_link(
                $deleteurl,
                get_string('custom_provider_delete', 'auth_otoauth'),
                new confirm_action(get_string('custom_provider_delete_confirm', 'auth_otoauth'))
            );
            
            $editurl = clone $baseurl;
            $editurl->param('action', 'edit');
            $editurl->param('id', $dbcustomprovider->id);
            $editlink = html_writer::link($editurl, get_string('custom_provider_edit', 'auth_otoauth'));
            
            $customproviderhtml = html_writer::div($name.$status.$overriden, 'custom_provider_line_header');
            $customproviderhtml .= html_writer::div($description, 'custom_provider_line_content');
            $customproviderhtml .= html_writer::div($editlink.$delete, 'custom_provider_line_actions');
            
            $classes = [
                'custom_provider',
                'custom_provider_'.$dbcustomprovider->code,
                'custom_provider_status_'.$dbcustomprovider->status
            ];
            $customprovidershtml .= html_writer::div($customproviderhtml, implode(' ', $classes));
        }
    } else
    {
        // нет зарегистрированных настраиваемых провайдеров
        $customprovidershtml .= get_string('custom_providers_list_empty', 'auth_otoauth');
    }
    $html .= html_writer::div($customprovidershtml, 'custom_providers');
    
    // ссылка на добавление нового настраиваемого провайдера
    $addurl = clone $baseurl;
    $addurl->param('action', 'edit');
    $addlink = html_writer::link($addurl, get_string('provider_management_add', 'auth_otoauth'), ['class'=>'btn btn-primary']);
    $html .= html_writer::div($addlink);
}

if ($action == 'edit')
{
    $editurl = clone $baseurl;
    $editurl->param('action', 'edit');
    
    $customdata = ['baseurl' => $baseurl];
    
    if (!is_null($id))
    {
        $editurl->param('id', $id);
        $customdata['id'] = $id;
    }
    
    $form = new \auth_otoauth\customprovider_form($editurl, $customdata);
    
    if (!is_null($id))
    {
        $customproviderrecord = \auth_otoauth\customprovider::get_custom_provider($id);
        if (!empty($customproviderrecord))
        {
            $customprovider = new \auth_otoauth\customprovider($customproviderrecord);
            $form->set_data($customprovider->to_object('cp_'));
        }
    }
    
    $errorcode = 'custom_provider_error_while_'.(is_null($id)?'creating':'editing');
    try {
        $form->process();
    }
    catch (\auth_otoauth\customprovider_exception $ex)
    {
        core\notification::error(get_string($errorcode, 'auth_otoauth', $ex->getMessage()));
    }
    catch (\dml_exception $ex)
    {
        $error = get_string($errorcode, 'auth_otoauth', $ex->getMessage());
        core\notification::error($error);
        debugging($error);
    }
    
    $html .= $form->render();
}

if ($action == 'delete')
{
    if (is_null($id))
    {
        core\notification::error(get_string('custom_provider_error_missing_id', 'auth_otoauth'));
    } else
    {
        \auth_otoauth\customprovider::delete_custom_provider($id);
        core\notification::info(get_string('custom_provider_delete_success', 'auth_otoauth'));
    }
    redirect($baseurl);
}



echo $OUTPUT->header();
echo $html;
echo $OUTPUT->footer();

$authplugin = get_auth_plugin('otoauth');
