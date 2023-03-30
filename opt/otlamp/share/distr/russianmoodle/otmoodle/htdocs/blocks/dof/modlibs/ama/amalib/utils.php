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

/** Проверяет, содержит ли переменная положительное целое
 * @param mixed $val
 * @return bool 
 */
function ama_utils_is_intstring($val)
{
    return is_int_string($val);
}

/** Транслителировать строку в латиницу
 * @param string $lang - двухбуквенный код языка
 * @param string $string - строка
 * @return string 
 */
function ama_utils_translit($lang, $string, $small = true)
{
    if ( $small )
    {
        $string = core_text::strtolower($string);
    }
    if ( $lang === 'ru' )
    {
        $alfabet = array(
            'а' => 'a', 'А' => 'A', 'б' => 'b', 'Б' => 'B', 'в' => 'v', 'В' => 'V',
            'г' => 'g', 'Г' => 'G', 'д' => 'd', 'Д' => 'D', 'е' => 'e', 'Е' => 'E',
            'ё' => 'jo', 'Ё' => 'Jo', 'ж' => 'zh', 'Ж' => 'Zh', 'з' => 'z', 'З' => 'Z',
            'и' => 'i', 'И' => 'I', 'й' => 'j', 'Й' => 'J', 'к' => 'k', 'К' => 'K',
            'л' => 'l', 'Л' => 'L', 'м' => 'm', 'М' => 'M', 'н' => 'n', 'Н' => 'N',
            'о' => 'o', 'О' => 'O', 'п' => 'p', 'П' => 'P', 'р' => 'r', 'Р' => 'R',
            'с' => 's', 'С' => 'S', 'т' => 't', 'Т' => 'T', 'у' => 'u', 'У' => 'U',
            'ф' => 'f', 'Ф' => 'F', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'C',
            'ч' => 'ch', 'Ч' => 'Ch', 'ш' => 'sh', 'Ш' => 'Sh', 'щ' => 'shh', 'Щ' => 'Shh',
            'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'Y', 'ь' => "", 'Ь' => "",
            'э' => 'e', 'Э' => 'E', 'ю' => 'ju', 'Ю' => 'Ju', 'я' => 'ja', 'Я' => 'Ja');
    }
    // Чтоб не было конфликтов перед обработкой убираем экранирование
    return addslashes(strtr(stripslashes($string), $alfabet));
}

/**
 * Возвращает объект класса для работы с логами Moodle
 * @param string $logreader имя необходимого logreader'а
 * @return mixed объект класса для работы с логами Moodle (если не передано конкретное имя, то первый из списка доступных)
 */
function get_logreader($logreader = '')
{
    global $CFG;
    require_once($CFG->libdir . '/datalib.php');
    $logmanager = get_log_manager();
    $readers = $logmanager->get_readers();
    if (empty($readers)) {
        return false;
    }
    if (empty($logreader)) {
        return reset($readers);
    } else if (array_key_exists($logreader, $readers)) {
        return $readers[$logreader];
    }
    return false;
}

/** Получить из настроек Moodle роли, которые можно оценивать
 *
 * @return array - массив id ролей в таблице mdl_roles
 */
function get_graded_roles()
{
    global $CFG;
    if( empty($CFG->gradebookroles) OR ! trim($CFG->gradebookroles) )
    {// нет ролей, которые можно оценивать
        return false;
    }
    $roles = explode(',', $CFG->gradebookroles);
    if( empty($roles) )
    {// нет ролей, которые можно оценивать
        return false;
    }
    return $roles;
}
?>