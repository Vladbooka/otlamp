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
 * Библиотека работы с плагинами проверки на заимствования. Базовый класс работы с плагином плагиаризма.
 *
 * @package    modlib
 * @subpackage plagiarism
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DOF;
require_once($DOF->plugin_path('modlib', 'plagiarism', '/classes/class.plagiarism.php'));

class dof_modlib_plagiarism_plagiarism_apru extends dof_modlib_plagiarism_plagiarism
{
    /**
     * Объект основного класса плагина
     * @var plagiarism_plugin_apru|null
     */
    private $plugin = null;
    
    /**
     * Код плагина
     * @var string
     */
    protected $plugincode = 'apru';
    
    /**
     * Проверка доступности плагина
     * 
     * @return bool - Возможность работы с данным плагином
     */
    static function is_enabled()
    {
        global $CFG;
        if (!empty($CFG->enableplagiarism)) {
            $pluginenabled = get_config('plagiarism_apru', 'enabled');
            if ($pluginenabled) {
                return true;
            }
        }
        return false;
    }
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - Объект ядра Деканата
     */
    public function __construct( dof_control $dof )
    {
        global $CFG;
        parent::__construct($dof);
        // Получение плагина плагиаризма
        if ( self::is_enabled() )
        {
            $filepath = $CFG->dirroot . '/plagiarism/apru/lib.php';
            if (file_exists($filepath)) {
                require_once($filepath);
                $plagiarismclass = 'plagiarism_plugin_' . $this->plugincode;
                if (class_exists($plagiarismclass)) {
                    $this->plugin =  new plagiarism_plugin_apru();
                }
            }
        }
    }
    
    /**
     * Получить локализованное название плагина
     *
     * @return string - Имя плагина
     */
    public function get_name()
    {
        return $this->dof->get_string('plagiarism_apru', 'plagiarism', null, 'modlib');
    }
    
    /**
     * Добавление файла в очередь на загрузку в систему
     *
     * @param string $pathnamehash - Хэш пути файла
     * @param string $options - Дополнительные опции обработки
     *              'additional' - Массив опций документа
     */
    public function add_file($pathnamehash, $options = [])
    {
        if ( $this->plugin )
        {// Плагин готов к использованию
            // Добавление файла в очередь
            try 
            {
                $this->plugin->add_file_to_queue($pathnamehash, 0, $options);
            } catch ( moodle_exception $e )
            {// Ошибка добавления файла в очередь
                $this->dof->messages->add($e->getMessage());
            }
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see dof_modlib_plagiarism_plagiarism::update_file()
     */
    public function update_file($aprurecord, $options = [])
    {
        if ( $this->plugin )
        {// Плагин готов к использованию
            // Добавление файла в очередь
            try
            {
                $this->plugin->update_file_in_queue($aprurecord, $options);
            } catch ( moodle_exception $e )
            {// Ошибка добавления файла в очередь
                $this->dof->messages->add($e->getMessage());
            }
        }
    }

    /**
     * Удаление файла из очереди 
     *
     * @param dof_control $dof - Объект ядра Деканата
     */
    public function delete_file($pathhash, $options = [])
    {
        return null;
    }
    
    /**
     * Получение информации о проверке файла
     *
     * @param string $pathnamehash - Хэш пути файла
     * @param string $options - Дополнительные опции обработки
     *
     * @return stdClass
     */
    public function get_file_info($pathnamehash, $options = [])
    {
        global $DB;
        return $DB->get_record('plagiarism_' . $this->plugincode . '_files', ['identifier' => $pathnamehash]);;
    }
}
?>