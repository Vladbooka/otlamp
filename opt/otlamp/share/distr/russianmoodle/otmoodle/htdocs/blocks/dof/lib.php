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

include_once($CFG->dirroot.'/blocks/dof/locallib.php');

/**
 * Подготовка сохраненных файлов блока
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * 
 * @return bool
 */
function block_dof_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) 
{
    global $DOF;
    
    // Проверка на контекст
    if ( $context->contextlevel != CONTEXT_BLOCK ) 
    {
        return false;
    }

    // ID файловой зоны
    $itemid = array_shift($args);
    
    // Проверка на зону
    if ( $filearea !== 'public' ) 
    {
        $fileaccess = $DOF->modlib('filestorage')->file_access($filearea, $itemid);
        if($fileaccess == false)
        {
            return false;
        }
    }
    
    // Проверка прав доступа
    if ( ! has_capability('block/dof:view', $context) ) 
    {
        return false;
    }
    
    // Имя файла
    $filename = array_pop($args);
    
    // Путь файла
    if ( ! $args ) 
    {
        $filepath = '/'; 
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }
    
    // Получение файлового хранилища
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_dof', $filearea, $itemid, $filepath, $filename);
    if ( ! $file) 
    {// Файл не найден
        return false; 
    }

    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}
?>