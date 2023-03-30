<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //// This program is free software: you can redistribute it and/or modify   //
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
 * Автозагрузчик Деканата.
 *
 * @package    block
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function block_dof_autoloader($classname)
{
    global $CFG;
    
    // Нормализация класса
    $classname = ltrim($classname, '\\');
    
    // Путь до файла
    $filepath = '';
    // Пространство имен
    $namespace = '';
    
    if ( $lastnamespaceposition = strrpos($classname, '\\') )
    {// В классе имеются неймспейсы
        $namespace = substr($classname, 0, $lastnamespaceposition);
        $classname = substr($classname, $lastnamespaceposition + 1);
        $filepath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    
    // Подготовка класса к поиску местоположения
    $classparts = explode('_', $classname);
    $dofpart = array_shift($classparts);
    if ( $dofpart == 'dof' )
    {// Класс принадлежит Деканату
        
        // Базовая директория Деканата
        $basepath = $CFG->dirroot.'/blocks/dof/';
        
        // Получение типа плагина
        $ptype = (string)array_shift($classparts);
        // Получение кода плагина
        $pcode = (string)array_shift($classparts);
        
        // Хак для исправления ошибочного именования типов плагинов
        switch ( $ptype )
        {
            
            case 'modlib' :
            case 'storage' :
            case 'workflow' :
                $ptype .= 's';
                break;
            default : 
                break;
        }
        
        // Проверка класса ядра
        if ( $pcode == '' )
        {// Класс ядра
            $path = $basepath.'lib/'.$ptype.'.php';
            if ( file_exists($basepath) )
            {// Файл найден
                require_once $basepath;
                return true;
            }
            return false;
        }
        
        // Класс принадлежит плагину
        $basepath .= $ptype.'/'.$pcode.'/';
        if ( empty($classparts) )
        {// Главный класс плагина
            $path = $basepath.'init.php';
            if ( file_exists($path) )
            {// Файл найден
                require_once $path;
                return true;
            }
            return false;
        }
        
        // Класс внутри плагина
        $basepath .= 'classes/';
        
        while ( ! empty($classparts) )
        {
            // Поиск в текущей директории
            $basepath .= (string)array_shift($classparts);
            $filename = implode('_', $classparts);
            
            if ( empty($filename) )
            {// Поиск файла в текущей директории
                $path = $basepath.$filename;
            } else 
            {// Имя файла
                $path = $basepath.'_'.$filename;
            }
              
            // Поиск файла
            if ( file_exists($path.'.php') )
            {// Файл найден
                require_once $path.'.php';
                return true;
            }
            if ( file_exists($path.'/init.php') )
            {// Файл найден
                require_once $path.'/init.php';
                return true;
            }
            $basepath .= '/';
        }
    }
    return false;
}

//Регистрируем наш автозагрузчик классов
spl_autoload_register('block_dof_autoloader');

