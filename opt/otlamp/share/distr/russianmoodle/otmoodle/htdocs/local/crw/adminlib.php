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
 * Витрина курсов. Класс элемента страницы настроек для отображения панели управления субплагинами
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class crw_subplugins_settings extends admin_setting 
{
    public function __construct() 
    {
        $this->nosave = true;
        parent::__construct('crwsubplugins', get_string('settings_subplugintype_crw', 'local_crw'), '', '');
    }

    public function get_setting() 
    {
        return true;
    }

    public function get_defaultsetting() 
    {
        return true;
    }

    public function write_setting($data) 
    {
        return '';
    }

    /**
     * Checks if $query is one of the available subplugins.
     *
     * @param string $query The string to search for
     * @return bool Returns true if found, false if not
     */
    public function is_related($query) 
    {
        if (parent::is_related($query)) 
        {
            return true;
        }

        $subplugins = core_component::get_plugin_list('crw');
        foreach ( $subplugins as $name => $dir ) 
        {
            if (stripos($name, $query) !== false) 
            {
                return true;
            }

            $namestr = get_string('pluginname', 'crw_'.$name);
            if (strpos(core_text::strtolower($namestr), core_text::strtolower($query)) !== false) 
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Отобразить панель управления субплагинами
     *
     * @param string $data Unused
     * @param string $query
     * @return string
     */
    public function output_html($data, $query='') 
    {
        global $CFG, $OUTPUT, $PAGE;
        require_once("$CFG->libdir/editorlib.php");
        require_once(__DIR__.'/lib.php');
        
        $pluginmanager = core_plugin_manager::instance();

        // display strings
        $strdisable = get_string('disable');
        $strenable = get_string('enable');
        $strname = get_string('name');
        $strsettings = get_string('settings');
        $struninstall = get_string('uninstallplugin', 'core_admin');
        $strversion = get_string('version');

        $subplugins = core_component::get_plugin_list('crw');

        $return = $OUTPUT->heading(get_string('settings_subplugintype_crw', 'local_crw'), 3, 'main', true);
        $return .= $OUTPUT->box_start('generalbox crwsubplugins');

        $table = new html_table();
        $table->head  = array($strname, $strversion, $strenable, $strsettings, $struninstall);
        $table->align = array('left', 'center', 'center', 'center', 'center');
        $table->data  = array();
        $table->attributes['class'] = 'admintable generaltable';

        // Iterate through subplugins.
        foreach ($subplugins as $name => $dir) 
        {
            $namestr = get_string('pluginname', 'crw_'.$name);
            $version = get_config('crw_'.$name, 'version');
            if ($version === false) 
            {
                $version = '';
            }
            $plugininfo = $pluginmanager->get_plugin_info('crw_'.$name);

            // Add hide/show link.
            $class = '';
            if ( ! $version ) 
            {
                $hideshow = '';
                $displayname = html_writer::tag('span', $name, array('class' => 'error'));
            } else 
                if ($plugininfo->is_enabled()) {
                    $url = new moodle_url('/local/crw/subplugins.php', array(
                        'sesskey' => sesskey(),
                        'return' => 'settings',
                        'disable' => $name
                    ));
                    $hideshow = $OUTPUT->pix_icon('t/hide', $strdisable, 'moodle', [
                        'class' => 'iconsmall'
                    ]);
                    $hideshow = html_writer::link($url, $hideshow);
                    $displayname = $namestr;
                } else {
                    $url = new moodle_url('/local/crw/subplugins.php', array(
                        'sesskey' => sesskey(),
                        'return' => 'settings',
                        'enable' => $name
                    ));
                    $hideshow = $OUTPUT->pix_icon('t/show', $strenable, 'moodle', [
                        'class' => 'iconsmall'
                    ]);
                    $hideshow = html_writer::link($url, $hideshow);
                    $displayname = $namestr;
                    $class = 'dimmed_text';
                }
            
            // Add settings link.
            if (! $version) {
                $settings = '';
            } else if ($url = $plugininfo->get_settings_url()) {
                $settings = html_writer::link($url, $strsettings);
            } else {
                $settings = '';
            }

            // Add uninstall info.
            $uninstall = '';
            if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('crw_' . $name, 'manage')) 
            {
                $uninstall = html_writer::link($uninstallurl, $struninstall);
            }

            // Add a row to the table.
            $row = new html_table_row(array($displayname, $version, $hideshow, $settings, $uninstall));
            if ($class) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }
        $return .= html_writer::table($table);
        $return .= html_writer::tag('p', get_string('tablenosave', 'admin'));
        $return .= $OUTPUT->box_end();
        return highlight($query, $return);
    }
}

