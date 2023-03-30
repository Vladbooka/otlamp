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
 * Internal library of functions for module auth_dof
 *
 * @package    auth_dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Фундаментальные настройки плагина которые не может изменять администратор (минимум настроек)
 * 
 * @return []
 */
function auth_dof_fundamental_settings() {
    $fundamentalsettings = [
        'user_field_username' => ['modifiers' => ['required' => 1], 'display' => [1, 2]],
        'user_field_password' => ['modifiers' => ['required' => 1], 'display' => [1, 2]],
        'user_field_email'    => ['modifiers' => ['required' => 1, 'generated' => 0], 'display' => [1, 2]],
        'user_field_phone2'   => ['modifiers' => ['required' => 1, 'generated' => 0], 'display' => [1, 2]]
    ];
    // Удалим фундаментальные настройки из массива с не выполненными условиями
    foreach ($fundamentalsettings as $fldname => $fldval) {
        switch ($fldname) {
            case 'user_field_email':
                if (strpos(get_config('auth_dof', 'sendmethod'), 'email') === false) {
                    unset($fundamentalsettings[$fldname]);
                }
                break;
            case 'user_field_phone2':
                if (strpos(get_config('auth_dof', 'sendmethod'), 'otsms') === false) {
                    unset($fundamentalsettings[$fldname]);
                }
                break;
        }
    }
    // Если кастомное поле имеет системный флаг регистрационное 
    // то уберем у него режим отображения "не показывать поле"
    $dof = auth_dof_get_dof();
    if (!is_null($dof)) {
        $customfields = $dof->modlib('ama')->user(false)->get_user_custom_fields();
    } else {
        new \moodle_exception('Plugin dof requred');
    }
    if (!empty($customfields)) {
        foreach ($customfields as $customfld) {
            if ($customfld->signup) {
                $fundamentalsettings['user_profilefield_' . $customfld->shortname] = ['display' => [1, 2]];
            }
        }
    }
    return $fundamentalsettings;
}

/**
 * Возвращает объект dof
 * @return NULL dof_control
 */
function auth_dof_get_dof() {
    global $CFG;
    $dof = null;
    if (file_exists($CFG->dirroot . '/blocks/dof/locallib.php')) {
        require_once($CFG->dirroot . '/blocks/dof/locallib.php');
        global $DOF;
        $dof = & $DOF;
    }
    return $dof;
}

/**
 * Получение всех обработчиков в указанной подкатегории дочерней к classes
 *
 * @param string $folder
 * @return NULL | []
 */
function auth_dof_get_handlers(string $folder) {
    global $CFG;
    $handlers = [];
    // Директория с классами
    $classesdir = $CFG->dirroot."/auth/dof/classes/{$folder}/";
    // Интерфейс для просмотра содержимого каталогов
    $dir = new \DirectoryIterator($classesdir);
    foreach ($dir as $fileinfo) {
        if ($fileinfo->isFile()) {
            $file = $fileinfo->getBasename('.php');
            $classname = '\\auth_dof\\' . $folder . '\\' . $file;
            if ( class_exists($classname))
            {// Класс найден
                $handlers[$file] = $classname::get_name_string();
            }
        }
    }
    return $handlers;
}

/**
 * Получение настроек ресурсов по переданному массиву имен
 *
 * @param string $prefix
 * @param array $names
 * @return array
 */
function auth_dof_get_src_config_fields(string $prefix, array $names) {
    $config = [];
    if (! empty($prefix) && ! empty($names)) {
        $firstname = array_shift($names);
        $i = 1;
        while (($val = get_config('auth_dof', $prefix . $i . '_' . $firstname)) !== false) {
            $config[$i][$firstname] = $val;
            foreach ($names as $name) {
                $config[$i][$name] = get_config('auth_dof', $prefix . $i . '_' . $name);
            }
            $i++;
        }
    }
    return $config;
}

/**
 * Получение настроек пользовательских полей по переданному массиву имен
 *
 * @param string $prefix
 * @param array $uftypes
 * @param int $displaystep - 0, 1, 2
 * @return array
 */
function auth_dof_get_user_config_fields(string $prefix, array $uftypes, $displaystep = null) {
    $data = [];
    if (! empty($prefix) && ! empty($uftypes)) {
        $dof = auth_dof_get_dof();
        if (!is_null($dof)) {
            $fields = $dof->modlib('ama')->user(false)->get_all_user_fields_list(['password']);
        } else {
            new \moodle_exception('Plugin dof requred');
        }
        foreach ($fields as $fieldname => $string) {
            if ($displaystep !== null 
                && get_config('auth_dof', $prefix . $fieldname . '_display') != $displaystep)
            {
                continue;
            }
            foreach ($uftypes as $fldtype) {
                $fldfullname = $prefix . $fieldname . '_' . $fldtype;
                if (($fldvalue = get_config('auth_dof', $fldfullname)) !== false) {
                    $data[$fldfullname] = $fldvalue;
                }
            }
        }
    }
    return $data;
}


/**
 * Метод выбирает активные поля, сортирует их согласно весу, комплектует данными о внешнем источнике
 * вообщем выполняет все требуемую подготовку для дальнейшей передачи обработчикам полей для генирации формы
 * 
 * @param int $step - этап регистрации
 * @param int $uftypes
 * @return array
 */
function auth_dof_prepare_fields(int $step = null, array $uftypes = ['mod', 'srcfld', 'display']) {
    $data = [];
    if (! in_array('order', $uftypes)) {
        $uftypes[] = 'order';
    }
    $formfields = auth_dof_get_user_config_fields('fld_', $uftypes, $step);
    foreach ($formfields as $configfldname => $value) {
        $matches = [];
        preg_match('/fld_([A-Za-z0-9_]+)_(.+)/', $configfldname, $matches);
        list(, $fieldname, $settingtype) = $matches;
        if (isset($fieldname) && isset($settingtype)) {
            $data[$fieldname][$settingtype] = $value;
        }
    }
    uasort($data, function ( $a, $b ) {
        if ( $a['order'] == $b['order']) {
            return 0;
        }
        return ($a['order'] < $b['order']) ? -1 : 1;
    });
        return $data;
}

/**
 * Получение записи из источника соответствующего условиям поисковых полей
 * 
 * @param array $fieldscfg - конфиг полей получееный методом auth_dof_prepare_fields
 * @param array $formdata
 * @return NULL|false|array - запись(строка) из первого соответствующего условиям поисковых полей источника
 */
function auth_dof_get_source_data(array $fieldscfg, array $formdata) {
    $externalrecord = null;
    $srccfgfields = auth_dof_get_src_config_fields('src_', ['type', 'connection', 'table']);
    foreach ($srccfgfields as $srcid => $srcconfig) {
        if (! empty($srcconfig['type'])
            && array_key_exists($srcconfig['type'], auth_dof_get_handlers('sourcetype')))
        {
            $classname = '\\auth_dof\\sourcetype\\'.$srcconfig['type'];
            if (class_exists($classname)) {
                $conditions = \auth_dof\modifiers\search::get_conditions(
                    $fieldscfg, $formdata, $srcid);
                if (!$conditions) {
                    return false;
                }
                $srcinstance = new $classname();
                try {
                    // получение записи из внешнего источника
                    $extrecord = $srcinstance->get_external_fields_data(
                        $srcconfig['connection'], $srcconfig['table'], $conditions);
                } catch(\Exception $e) {
                    debugging($e->getMessage(), DEBUG_DEVELOPER);
                    continue;
                }
                $externalrecord[$srcid] = $extrecord;
                break;
            }
        }
    }
    return $externalrecord;
}

/**
 * Проверяет наличие полей на переданном шаге регистрации.
 * 
 * @param int $step
 * @return boolean
 */
function auth_dof_is_displayed_fields_in_step(int $step) {
    $modshidefld = ['hidden', 'generated'];
    $modfields = auth_dof_get_user_config_fields('fld_', ['mod'], $step);
    foreach ($modfields as $modfield) {
        if (is_array($modcfg = json_decode($modfield, true))) {
            $fieldishidden = false;
            foreach ($modshidefld as $hidefld) {
                if (isset($modcfg[$hidefld])) {
                    if ($modcfg[$hidefld]) {
                        $fieldishidden = true;
                    }
                } else {
                    print_error('Wrong user field config');
                }
            }
            if(!$fieldishidden) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Устанавливает настройки по умолчанию для регистрационных полей. 
 * 
 * @param array $defsignupfields последовательность полей к отображению на первом шаге
 * @param array $requiredfields проставляет модификатор "обязательное поле" полям к отображению по списку
 * @return [] - порядок сортировки в формате [$fldname]['order']
 */
function auth_dof_init_defaults_fields(array $defsignupfields, array $requiredfields) {
    global $DB;
    $dof = auth_dof_get_dof();
    if (!is_null($dof)) {
        $userfields = $dof->modlib('ama')->user(false)->get_userfields_list(['password']);
        $customfields = $dof->modlib('ama')->user(false)->get_user_custom_fields();
    } else {
        new \moodle_exception('Plugin dof requred');
    }
    $fsetconfig = function(array $modcfg, string $fldname, int &$i, array &$orderlist, int $display) {
        set_config("fld_{$fldname}_display", $display, 'auth_dof');
        set_config("fld_{$fldname}_order", $i, 'auth_dof');
        set_config("fld_{$fldname}_mod", json_encode($modcfg), 'auth_dof');
        $orderlist[$fldname]['order'] = $i;
        $i++;
    };
    $modifiers = array_merge(auth_dof_get_handlers('modifiers'), auth_dof_get_handlers('group_modifiers'));
    $modcfg = [];
    // Создадим массив со всеми выключенными модификаторами
    foreach ($modifiers as $modname => $str) {
        $modcfg[$modname] = 0;
    }
    $i = 0;
    $orderlist = [];
    // Добавим в конфиг настройки для переданных в метод полей 
    // в начало списка согласно их последовательности
    foreach ($defsignupfields as $fldname) {
        $modcfg['required'] = 0;
        if (in_array($fldname, $requiredfields)) {
            $modcfg['required'] = 1;
        }
        $fsetconfig($modcfg, 'user_field_' . $fldname, $i, $orderlist, 1);
    }
    // Далее добавим останьные стандартные поля в выключенном состоянии
    $modcfg['required'] = 0;
    foreach ($userfields as $fldname => $str) {
        if (! get_config('auth_dof', "fld_user_field_{$fldname}_display")) {
            $fsetconfig($modcfg, 'user_field_' . $fldname, $i, $orderlist, 0);
        }
    }
    // После стандартных добавим костомные поля которые имеют флаг отображения на форме регистрации
    // также установим им флаг "обязательное поле" если он задан в системной настройке поля 
    if ($fields = $DB->get_records('user_info_field', ['signup' => 1])) {
        foreach ($fields as $field) {
            if (property_exists($field,'required')) {
                $modcfg['required'] = $field->required;
                $fsetconfig($modcfg, 'user_profilefield_' . $field->shortname, $i, $orderlist, 1);
            }
        }
    }
    // В конец списка добавим остальные кастомные поля в выключенном состоянии
    $modcfg['required'] = 0;
    foreach ($customfields as $fldname => $str) {
        if (! get_config('auth_dof', "fld_user_profilefield_{$fldname}_display")) {
            $fsetconfig($modcfg, 'user_profilefield_' . $fldname, $i, $orderlist, 0);
        }
    }
    return $orderlist;
}
/**
 * Устанавливает статус активности модификатора для переданного поля
 * 
 * @param string $modcfgname
 * @param string $modname - - если пуст просто инициирует выключенные модификаторы
 * @param bool|null $modstate - если не передан просто инициирует выключенные модификаторы
 */
function auth_dof_set_field_modifier_cfg(string $fldname, string $modname = '', $modstate = null) {
    $modcfgname = 'fld_' . $fldname . '_mod';
    $modscfg = json_decode(get_config('auth_dof', $modcfgname), true);
    // Если ранее у поля отсутствовали настройки - создадим массив со всеми выключенными модификаторами
    if (!is_array($modscfg)) {
        $modscfg = [];
        // Получение модификаторов полей
        foreach (auth_dof_get_handlers('modifiers') as $modname => $str) {
            $modscfg[$modname] = 0;
        }
        foreach (auth_dof_get_handlers('group_modifiers') as $modname => $str) {
            $modscfg[$modname] = 0;
        }
    }
    if (!is_null($modstate) && ! empty($modname)) {
        $modscfg[$modname] = $modstate ? 1 : 0;
    }
    set_config($modcfgname, json_encode($modscfg), 'auth_dof');
}

/**
 * Проверяет попытки действия, возвращает ошибку если не удовлетворяет условиям
 * 
 * @param string $mode
 * @param int $livetime
 * @param string $containername
 * @param int $attemptsnumm
 * @param int|null $userid
 */
function auth_dof_check_attempts(string $mode, int $livetime, string $containername,
    int $attemptsnumm, $userid = null)
{
    global $SESSION;
    $firstattempttime = false;
    $currentattempts = false;
    if (is_null($userid)) {
        if (property_exists($SESSION, $containername . '_time')) {
            $firstattempttime = $SESSION->{$containername . '_time'};
        }
        if (property_exists($SESSION, $containername . '_attempt')) {
            $currentattempts = $SESSION->{$containername . '_attempt'};
        }
    } else {
        $firstattempttime = get_user_preferences($containername . '_time', false, $userid);
        $currentattempts = get_user_preferences($containername . '_attempt', false, $userid);
    }
    if ($firstattempttime) {
        switch ($mode) {
            case 'expiried':
                if (! $firstattempttime) {
                    if (is_null($userid)) {
                        set_user_preference($containername . '_time', time(), $userid);
                    } else {
                        $SESSION->{$containername . '_time'} = time();
                    }
                }
                if (time() > $firstattempttime + $livetime) {
                    print_error('check_attempts_time_expiried', 'auth_dof');
                }
                if ($currentattempts > $attemptsnumm) {
                    print_error('check_attempts_exhausted_all', 'auth_dof');
                }
                break;
            case 'retry':
                if ($currentattempts > $attemptsnumm && time() < $firstattempttime + $livetime) {
                    $timeleft = $firstattempttime + $livetime - time();
                    print_error('check_attempts_exhausted_all_wait', 'auth_dof', '', ceil($timeleft/60));
                }
                if (time() > $firstattempttime + $livetime) {
                    if (is_null($userid)) {
                        unset ($SESSION->{$containername . '_time'});
                        unset ($SESSION->{$containername . '_attempt'});
                    } else {
                        unset_user_preference($containername . '_time', $userid);
                        unset_user_preference($containername . '_attempt', $userid);
                    }
                }
                break;
            default:
                print_error("Mode '{$mode}' not supported");
        }
    } 
}

/**
 * Устанавливает неудачную попытку действия
 * 
 * @param string $mode
 * @param string $containername
 * @param int|null $userid
 */
function auth_dof_set_unsuccessful_attempt(string $mode, string $containername, $userid = null) {
    global $SESSION;
    $attempttime = false;
    $currentattempts = 1;
    if (is_null($userid)) {
        if (property_exists($SESSION, $containername . '_time')) {
            $attempttime = $SESSION->{$containername . '_time'};
        }
        if (! $attempttime || $mode == 'retry') {
            $SESSION->{$containername . '_time'} = time();
        }
        if (property_exists($SESSION, $containername . '_attempt')) {
            $currentattempts = $SESSION->{$containername . '_attempt'};
        }
        $currentattempts = $currentattempts + 1;
        $SESSION->{$containername . '_attempt'} = $currentattempts;
    } else {
        $attempttime = get_user_preferences($containername . '_time', false, $userid);
        $currentattempts = get_user_preferences($containername . '_attempt', 1, $userid);
        $currentattempts = $currentattempts + 1;
        set_user_preference($containername . '_attempt', $currentattempts, $userid);
        if (! $attempttime || $mode == 'retry') {
            set_user_preference($containername . '_time', time(), $userid);
        }
    }  
}

/**
 * Добавляет настройки поля
 * 
 * @param string $fldname
 * @param int $i
 * @param int $display
 * @param array|null $modcfg
 */
function auth_dof_add_field(string $fldname, int &$i, int $display, $modcfg = null) {
    set_config("fld_{$fldname}_display", $display, 'auth_dof');
    set_config("fld_{$fldname}_order", $i, 'auth_dof');
    //Инициируем настройку модификаторов c нулевыми значениями
    auth_dof_set_field_modifier_cfg($fldname);
    if (is_array($modcfg)) {
        foreach ($modcfg as $modname => $modstate) {
            auth_dof_set_field_modifier_cfg($fldname, $modname, $modstate);
        }
    }
    $i++;
}

/**
 * Удаляет все настройки поля
 * 
 * @param string $fldname
 */
function auth_doff_delete_field(string $fldname) {
    foreach (['mod', 'srcfld', 'display', 'order'] as $type) {
        unset_config('fld_' . $fldname . '_' . $type, 'auth_dof');
    }
}
