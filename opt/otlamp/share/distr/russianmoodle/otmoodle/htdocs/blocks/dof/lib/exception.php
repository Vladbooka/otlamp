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
 * Класс-обёртка для вызова moodle_exception
 */
class dof_exception extends Exception
{
    public $plugintype = 'core';
    
    public $plugincode = '';
    
    public $errorcode;
    
    public $a;
    
    public $link;
    
    public $debuginfo;
    
    public function __toString()
    {
        return $this->getMessage();
    }
    
    /**
     * Конструктор
     * 
     * @param string $errorcode - Код языковой строки ошибки
     * @param string $plugin - Имя плагина в формате plugintype/plugincode
     * @param string $link - URL перехода
     * @param stdClass $a - Макроподстановки для формирования языковой переменной
     * @param string $debuginfo - Дополнительная информация об ошибке
     */
    public function __construct($errorcode, $plugin = '', $link = '', $a = null, $debuginfo = null) 
    {
        global $DOF;
        
        // Определение типа и кода плагина - источника исключения
        $exploded = explode('_', $plugin, 2);
        $this->plugintype = $exploded[0];
        if ( isset($exploded[1]) )
        {
            $this->plugincode = $exploded[1];
        }
        
        $this->errorcode = $errorcode;
        $this->link      = $link;
        $this->a         = $a;
        $this->debuginfo = is_null($debuginfo) ? null : (string)$debuginfo;
    
        // Генерация сообщения
        $message = $this->plugintype.'_'.$this->plugincode.'/'.$errorcode;
        
        if ( $DOF->plugin_exists($this->plugintype, $this->plugincode) )
        {
            $message = $DOF->get_string(
                $errorcode, 
                $this->plugincode, 
                $a, 
                $this->plugintype, 
                ['empry_result' => $message]
            );
        }
        
        parent::__construct($message, 0);
    }
}

/**
 * Класс-обёртка для вызова coding_exception
 */
class dof_exception_coding extends coding_exception
{
    function __construct($hint, $debuginfo = null)
    {
        parent::__construct($hint, $debuginfo);
    }
}

/**
 * Класс-обёртка для вызова dml_exception
 */
class dof_exception_dml extends dml_exception
{
    function __construct($errorcode, $a = null, $debuginfo = null)
    {
        parent::__construct($errorcode, $a, $debuginfo);
    }
}

/**
 * Класс-обёртка для вызова ddl_exception
 */
class dof_exception_ddl extends ddl_exception
{
    function __construct($errorcode, $a = NULL, $debuginfo = null)
    {
        parent::__construct($errorcode, $a, $debuginfo);
    }
}

/**
 * Класс-обёртка для вызова file_exception
 */
class dof_exception_file extends file_exception
{
    function __construct($errorcode, $a = null, $debuginfo = null)
    {
        parent::__construct($errorcode, $a, $debuginfo);
    }
}

