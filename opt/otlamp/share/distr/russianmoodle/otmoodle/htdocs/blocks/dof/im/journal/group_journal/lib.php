<?php
// //////////////////////////////////////////////////////////////////////////
// //
// NOTICE OF COPYRIGHT //
// //
// Dean`s Office for Moodle //
// Электронный деканат //
// <http://sourceforge.net/projects/freedeansoffice/> //
// //
// //
// This program is free software: you can redistribute it and/or modify //
// it under the terms of the GNU General Public License as published by //
// the Free Software Foundation, either version 3 of the Licensen. //
// //
// This program is distributed in the hope that it will be useful, //
// but WITHOUT ANY WARRANTY; without even the implied warranty of //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the //
// GNU General Public License for more details. //
// //
// You should have received a copy of the GNU General Public License //
// along with this program. If not, see <http://www.gnu.org/licenses/>. //
// //
// //////////////////////////////////////////////////////////////////////////

/**
 * Журнал предмето-класса.
 * Базовые функции сабинтерфейса.
 *
 * @package im
 * @subpackage journal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once (dirname(realpath(__FILE__)) . "/../lib.php");

$csid = optional_param('csid', 0, PARAM_INT);

$DOF->modlib('nvg')->add_level($DOF->get_string('group_journal', 'journal'),
        $DOF->url_im('journal', '/group_journal/index.php?csid=' . $csid, $addvars));

require_once ($DOF->plugin_path('im', 'journal', '/group_journal/classes/tablebase.php'));
require_once ($DOF->plugin_path('im', 'journal', '/group_journal/classes/tablecstreaminfo.php'));
require_once ($DOF->plugin_path('im', 'journal', '/group_journal/classes/tablegrades.php'));
require_once ($DOF->plugin_path('im', 'journal', '/group_journal/classes/tabletemplans.php'));

/**
 * Возвращает отформатированную дату
 * 
 * @param int $date
 *            - метка времени которую надо вывести
 * @param $format -
 *            тип форматирования даты:
 *            dmy: выводит дд.мм.гг
 *            dm: выводит дд.мм
 *            my: выводит ммм гг, ммм - название месяца из трех букв
 *            m: выводит полное название месяца
 *            d: выводит дд
 * @param string $url
 *            - путь, по которому надо перейти, если дату надо сделать ссылкой
 * @return string
 */
function dof_im_journal_format_date($date, $format = 'dmy', $url = NULL)
{
    global $DOF;
    // получаем путь с нужной функцией
    $amapath = $DOF->plugin_path('modlib', 'ama', '/amalib/utils.php');
    // подключаем путь с нужной функцией
    require_once ($amapath);
    if ( ama_utils_is_intstring($date) )
    { // получена дата - форматируем
        switch ( $format )
        {
            case 'dmy':
                $rez = dof_userdate($date, '%d.%m.%y');
                break;
            case 'dm':
                $rez = dof_userdate($date, '%d.%m');
                break;
            case 'my':
                $rez = dof_userdate($date, '%b %y');
                break;
            case 'm':
                $rez = dof_userdate($date, '%B');
                break;
            case 'd':
                $rez = dof_userdate($date, '%d');
                break;
            default:
                $rez = $date;
        }
        // strftime в win32 возвращает результат в cp1251 - исправим это
        if ( stristr(PHP_OS, 'win') and ! stristr(PHP_OS, 'darwin') )
        { // это виндовый сервер
              // if ( $localewincharset = get_string('localewincharset') )
              // {//изменим кодировку символов из виндовой в utf-8
              // $textlib = textlib_get_instance();
              // $rez = $textlib->convert($rez, $localewincharset, 'utf-8');
              // }
        }
    } else
    { // получена строка - вставим ее
        $rez = trim($date);
    }
    if ( ! is_null($url) and is_string($url) )
    { // делаем дату ссылкой
        $rez = "<a href=\"{$url}\">" . $rez . '</a>';
    }
    return $rez;
}

/**
 * Возвращает отформатироанную дату и
 * значок редактирования как ссылку
 * 
 * @param int $date
 *            метка времени
 * @param string $format
 *            - см. описание к dof_im_journal_format_date
 * @param string $durl
 *            - путь ссылки для даты,
 *            если не указана - дата выводится как просто строка
 * @param string $eurl
 *            - путь ссылки для значка,
 *            если не указана значок не показывается
 * @param bool $imgsubdate
 *            - вывести значок под датой или рядом
 *            по умолчанию выводит значок под датой
 * @return string
 */
function dof_im_journal_date_edit($date, $format = 'dmy', $durl = null, $eurl = null, $imgsubdate = true)
{
    global $DOF;
    // получаем форматированную дату
    $rez = dof_im_journal_format_date($date, $format, $durl);
    // добавляем значок форматирования
    if ( ! is_null($eurl) and is_string($eurl) )
    { // передана строка - делаем ссылку
      // рисуем картинку
        $imgedit = '<img src="' . $DOF->url_im('journal', '/icons/edit.png') . '">';
        // делаем ее ссылкой
        $imglink = "<a class='dof-group-journal-editlesson' href=\"{$eurl}\">" . $imgedit . '</a>';
    } else
    { // ссылка не передана - не показываем значок
        $imglink = '';
    }
    if ( is_bool($imgsubdate) and $imgsubdate )
    {
        return $rez . $imglink;
    }
    return $rez . $imglink;
}

/**
 * Показывает можно редактировать дату
 * элемента темплана или нельзя.
 * 
 * @param int $planid
 *            - id элемента темплана
 * @param int $csid
 *            - id потока
 * @return bool true - можно изменять дату события,
 *         false - нельзя изменять дату события
 */
function dof_im_journal_is_editdate($planid, $csid)
{
    global $DOF;
    if ( ! $DOF->im('journal')->get_cfg('teacher_can_change_lessondate') )
    { // если прав у учителя нет - значит редактировать дату нельзя
        return false;
    }
    require_once ($DOF->plugin_path('modlib', 'ama', '/amalib/utils.php'));
    if ( ! ama_utils_is_intstring($planid) or ! ama_utils_is_intstring($csid) )
    { // передано непонятно что - нельзя дату менять
        return false;
    }
    if ( ! $planid )
    { // для нового элемента темплана
      // дату можно редактировать
        return true;
    }
    if ( ! $DOF->storage('schevents')->get_records(
            array(
                'cstreamid' => $csid,
                'planid' => $planid,
                'status' => array(
                    'plan',
                    'completed',
                    'postponed',
                    'replaced'
                )
            )) )
    { // для данного элемента темплана события нет';
      // дату редактировать нельзя
        return false;
    }
    return true;
}
