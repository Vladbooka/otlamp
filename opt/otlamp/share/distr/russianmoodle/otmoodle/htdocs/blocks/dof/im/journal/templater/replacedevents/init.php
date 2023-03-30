<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
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

/*
 * Описание файла
*/
class dof_im_journal_templater_replacedevents extends dof_modlib_templater_package //класс шаблона документа
{
    /**
     * Загрузить необработанные данные в объект.
     * @param object $obj - набор данных для вывода в файл
     * @return bool
     */
    private function set_data($obj)
    {
        $this->data = $obj;
        return true;
    }
    /**
     * Получить необработанный объект с данными.
     * @return object
     */
    private function get_data()
    {
        return $this->data;
    }
    
    /**
     * Возвращает экземпляр класса для преобразования
     * данных в файл определенного типа
     * @param string $type - тип файла, в который надо превратить данные
     * @param object $options - дополнительные параметры
     * @return mixed - dof_modlib_templater_format -
     * объект dof_modlib_templater_format_$type
     * или false
     */
    public function create_format($type, $options = null)
    {
        if ( !$type OR ! is_string($type) )
        {// неизвестно, в какой формат экспортировать данные';
            return false;
        }
        $formats = $this->get_formats();
        if ( !in_array($type, $formats) )
        {// в списке поддерживаемых форматов запрашиваемый не значится';
            return false;
        }
        if ( isset($this->formats->$type) )
        {//уже создали объект для экспорта в такой тип файлов';
            return $this->formats->$type;//вернем его
        }
        // определим путь к подключаемому файлу
        $path = $this->format_path($type);
        if ( file_exists($path) )
        {// файл есть - подключаем';
            require_once($path);
        }else
        {// файла нет - сообщаем об этом';
            return false;
        }
        // определяем имя класса, занимающегося форматированием
        $classname = 'dof_im_journal_replacedevents_format_'.$type;
        if ( class_exists($classname) )
        {// класс с нужным названием есть в папке';
            //создаем его экземпляр и сохраняем его
            $this->formats->$type = new $classname($this->dof,
                $this->plugintype, $this->pluginname, $this->templatename);
            return $this->formats->$type;
        }else
        {// в файле нет класса с нужным названием';
            return false;
        }
    }
    
    /**
     * Возвращает путь к файлу, в котором лежит контейнер
     * во внешнем плагине или внутри templater
     * @param mixed string - путь к файлу init.php
     * или bool false, если файла нет в templater
     */
    private function format_path($type)
    {
        //формируем путь к package внешнего плагина
        $extpath = $this->template_path($type.'/init.php',true);
    
        if ( $extpath )
        {//файл есть - возвращаем
            return $extpath;
        }
        //формируем путь к собственному package
        return $this->template_path('formats/'.$type.'/init.php',false);
    }
}
?>