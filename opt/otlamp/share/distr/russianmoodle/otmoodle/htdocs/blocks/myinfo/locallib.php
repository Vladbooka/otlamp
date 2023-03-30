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
 * Блок Информация, хелпер
 *
 * @package    block_myinfo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Получить список стандартных обрабатываемых полей пользователей
 * 
 * @return array
 */
function get_userfields_list($addfields=[])
{
    $userfields = [];
    $profilefields = [
        'email',
        'country',
        'city',
        'address',
        'phone1',
        'phone2',
        'institution',
        'department',
        'idnumber',
        'url',
        'interests',
        'icq',
        'skype',
        'yahoo',
        'aim',
        'msn'
    ];
    foreach(array_merge($profilefields, $addfields) as $k => $v)
    {
        if( is_number($k) )
        {
            $userfields[$v] = get_user_field_name($v);
        } else
        {
            $userfields[$k] = $v;
        }
    }
    return $userfields;
}

/**
 * 
 * Получить список настраиваемых полей пользователей
 * 
 * @return array
 */
function get_customfields_list()
{
    global $DB;
    
    $customfields = [];
    if ($profilefields = $DB->get_records('user_info_field', null, 'sortorder ASC') )
    {
        foreach ($profilefields as $profilefield)
        {
            $customfields['profile_field_'.$profilefield->shortname] = get_string('custom_field','block_myinfo',$profilefield->name);
        }
    }
    return $customfields;
}

/**
 * Получить объект с данными стандартного поля пользователя
 * 
 * @param stdClass $user - объект пользователя
 * @param string $fieldshortname - короткое имя стандартного поля пользователя
 * @return boolean|stdClass - объект с данными поля или false в случае ошибки
 */
function get_userfield_data($user, $fieldshortname, $fieldname=null)
{
    global $CFG, $USER, $OUTPUT;
    
    $result = false;
    $iscurrentuser = ($user->id == $USER->id);
    
    $usercontext = context_user::instance($user->id, MUST_EXIST);
    $canviewhiddenuserfields = has_capability('moodle/user:viewhiddendetails', $usercontext);
    
    if ($canviewhiddenuserfields) 
    {
        $hiddenfields = [];
    } else 
    {
        $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
    }
    
    if (has_capability('moodle/site:viewuseridentity', $usercontext)) 
    {
        $identityfields = array_flip(explode(',', $CFG->showuseridentity));
    } else 
    {
        $identityfields = [];
    }
    
    switch($fieldshortname)
    {
        case 'email':
            if (isset($identityfields['email']) and ($iscurrentuser
                                                    or $user->maildisplay == 1
                                                    or has_capability('moodle/course:useremail', $usercontext)
                                                    or has_capability('moodle/site:viewuseridentity', $usercontext)
                                                    or ($user->maildisplay == 2 and enrol_sharing_course($user, $USER))))
            {
                $displayvalue = obfuscate_mailto($user->email, '');
            }
            break;
        case 'country':
            if (!isset($hiddenfields['country']) && $user->country)
            {
                $displayvalue = get_string($user->country, 'countries');
            }
            break;
        case 'city':
            if (!isset($hiddenfields['city']) && $user->city)
            {
                $displayvalue = $user->city;
            }
            break;
        case 'address':
            // Address not appears in hidden fields list but require viewhiddenfields capability 
            // according to user/lib.php
            if ( $user->address && $canviewhiddenuserfields)
            {
                $displayvalue = $user->address;
            }
            break;
        case 'phone1':
            // phone1 not appears in hidden fields list but require viewhiddenfields capability
            // according to user/lib.php
            if ($user->phone1 && ( isset($identityfields['phone1']) || $canviewhiddenuserfields ))
            {
                $displayvalue = $user->phone1;
            }
            break;
        case 'phone2':
            // phone2 not appears in hidden fields list but require viewhiddenfields capability
            // according to user/lib.php
            if ($user->phone2 && ( isset($identityfields['phone2']) || $canviewhiddenuserfields ))
            {
                $displayvalue = $user->phone2;
            }
            break;
        case 'institution':
            if (isset($identityfields['institution']) && $user->institution) 
            {
                $displayvalue = $user->institution;
            }
            break;
        case 'department':
            if (isset($identityfields['department']) && $user->department) 
            {
                $displayvalue = $user->department;
            }
            break;
        case 'idnumber':
            if (isset($identityfields['idnumber']) && $user->idnumber) 
            {
                $displayvalue = $user->idnumber;
            }
            break;
        case 'url':
            if ($user->url && !isset($hiddenfields['webpage'])) 
            {
                $url = $user->url;
                if (strpos($user->url, '://') === false) {
                    $url = 'http://'. $url;
                }
                $webpageurl = new moodle_url($url);
                $displayvalue = html_writer::link($url, $webpageurl);
            }
            break;
        case 'interests':
            if ($interests = core_tag_tag::get_item_tags('core', 'user', $user->id)) 
            {
                $displayvalue = $OUTPUT->tag_list($interests, '');
            }
            break;
        case 'icq':
            if ($user->icq && !isset($hiddenfields['icqnumber'])) 
            {
                $imurl = new moodle_url('http://web.icq.com/wwp', ['uin' => $user->icq] );
                $iconurl = new moodle_url('http://web.icq.com/whitepages/online', ['icq' => $user->icq, 'img' => '5']);
                $statusicon = html_writer::tag('img', '',[
                    'src' => $iconurl, 
                    'class' => 'icon icon-post', 
                    'alt' => get_string('status')
                ]);
                $displayvalue = html_writer::link($imurl, s($user->icq) . $statusicon);
            }
            break;
        case 'skype':
            if ($user->skype && !isset($hiddenfields['skypeid'])) 
            {
                $imurl = 'skype:'.urlencode($user->skype).'?call';
                $iconurl = new moodle_url('http://mystatus.skype.com/smallicon/'.urlencode($user->skype));
                $statusicon = '';
                if ( ! is_https()) 
                {
                    $statusicon = html_writer::empty_tag('img', [
                        'src' => $iconurl, 
                        'class' => 'icon icon-post', 
                        'alt' => get_string('status')
                    ]);
                }
                $displayvalue = html_writer::link($imurl, s($user->skype) . $statusicon);
            }
            break;
        case 'yahoo':
            if ($user->yahoo && !isset($hiddenfields['yahooid'])) 
            {
                $imurl = new moodle_url('http://edit.yahoo.com/config/send_webmesg', [
                    '.target' => $user->yahoo, 
                    '.src' => 'pg'
                ]);
                $iconurl = new moodle_url('http://opi.yahoo.com/online', [
                    'u' => $user->yahoo, 
                    'm' => 'g', 
                    't' => '0'
                ]);
                $statusicon = html_writer::tag('img', '', [
                    'src' => $iconurl, 
                    'class' => 'iconsmall icon-post', 
                    'alt' => get_string('status')
                ]);
                $displayvalue = html_writer::link($imurl, s($user->yahoo) . $statusicon);
            }
            break;
        case 'aim':
            if ($user->aim && !isset($hiddenfields['aimid'])) 
            {
                $imurl = 'aim:goim?screenname='.urlencode($user->aim);
                $displayvalue = html_writer::link($imurl, s($user->aim));
            }
            break;
        case 'msn':
            if ($user->msn && !isset($hiddenfields['msnid'])) 
            {
                $displayvalue = s($user->msn);
            }
            break;
    }

    if( isset($user->{$fieldshortname}) )
    {
        $result = new stdClass();
        $result->shortname = $fieldshortname;
        $result->value = $user->{$fieldshortname};
        if( is_null($fieldname) )
        {
            $result->name = get_user_field_name($fieldshortname);
        } else {
            $result->name = $fieldname;
        }
        if( isset($displayvalue) )
        {
            $result->displayvalue = $displayvalue;
        }
    }
    
    if(($fieldshortname=='interests' AND isset($displayvalue)))
    {
        $result = new stdClass();
        $result->shortname = $fieldshortname;
        if( is_null($fieldname) )
        {
            $result->name = get_user_field_name($fieldshortname);
        } else {
            $result->name = $fieldname;
        }
        $result->displayvalue = $displayvalue;
        $result->value = $displayvalue;
    }
    
    return $result;
}

/**
 * Получить объект с данными настраиваемого поля пользователя
 *
 * @param stdClass $user - объект пользователя
 * @param string $fieldshortname - короткое имя настраиваемого поля пользователя
 * @return boolean|stdClass - объект с данными поля или false в случае ошибки
 */
function get_customfield_data($user, $fieldshortname)
{
    global $DB, $CFG;
    
    $result = false;
    
    if( isset($user->profile->$fieldshortname) )
    {
        $result = new stdClass();
        $result->shortname = $fieldshortname;
        $result->value = $user->profile->$fieldshortname;
        
        $cfrecord = $DB->get_record('user_info_field', [
            'shortname' => $fieldshortname
        ]);
        
        if( ! empty($cfrecord) )
        {// получено наименование, переопределяем
            $result->name = $cfrecord->name;
            
            // путь к файлу класса кастомного поля данного типа
            $cfclassfile = $CFG->dirroot . '/user/profile/field/' . $cfrecord->datatype . '/field.class.php';
            
            if ( file_exists($cfclassfile) )
            {
                //подключение файла класса плагина поля профиля
                require_once ($cfclassfile);
                // название класса
                $cfclassname = 'profile_field_' . $cfrecord->datatype;
                
                if ( class_exists($cfclassname) )
                {
                    // создание экземпляра класса
                    $cf = new $cfclassname($cfrecord->id, $user->id);
                    
                    if ( $cf->is_visible() && ! $cf->is_empty() && method_exists($cf, 'display_data') )
                    {
                        $result->displayvalue = $cf->display_data();
                    }
                }
            }
        }
    }
    
    return $result;
}

/**
 * Получить данные по полям пользователей
 *
 * @param stdClass $user - объект пользователя
 * @param string $fields - массив кодов полей пользователя (если поле настраиваемое, должно иметь префикс profile_field_
 * @return boolean|stdClass - массив с объектами данных по полям
 */
function get_fields_data($user, $fields)
{
    global $USER, $DB, $CFG;
 
    if( empty($user) )
    {
        $user = clone $USER;
    }
    
    $user->profile = profile_user_record($user->id, false);
    
    $resultfields = [];
    
    foreach($fields as $fieldshortname)
    {
        if ( substr($fieldshortname, 0, 14) == 'profile_field_' )
        {// кастомное поле
            $resultfield = get_customfield_data($user, substr($fieldshortname, 14));
        } else
        {// обычное поле профиля
            $resultfield = get_userfield_data($user, $fieldshortname);
        }
        
        if( ! empty($resultfield) )
        {
            $resultfields[$fieldshortname] = $resultfield;
        }
    }
    
    return $resultfields;
}