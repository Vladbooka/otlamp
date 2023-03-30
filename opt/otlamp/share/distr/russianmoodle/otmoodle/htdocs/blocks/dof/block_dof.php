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
class block_dof extends block_base
{
    /**
     * Инициализация блока
     *
     * @return void
     */
    public function init()
    {
        $this->title = get_string('title', 'block_dof');
    }

    /**
     * Включает интерфейс настроек
     *
     * @return boolean
     */
    public function has_config()
    {
        return true;
    }

    /**
     * Разрешить добавление нескольких экземпляров блока на одну страницу
     *
     * @return boolean
     */
    function instance_allow_multiple()
    {
        return true;
    }

    /**
     * Возвращает содержимое блока "Электронный деканат"
     *
     * @return string
     */
    public function get_content()
    {
        global $CFG, $COURSE, $USER, $DOF, $DB, $PAGE;
        
        require_once (dirname(realpath(__FILE__)) . '/locallib.php');
        $DOF->context = $PAGE->context;
        
        if ( $this->get_format_content() == 'profile' )
        {
            $userid = optional_param('id', $USER->id, PARAM_INT);
        } else
        {
            $userid = $USER->id;
        }
        
        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->text = '';
        
        // Проверка наличия сохраненных файлов для этого экземпляра блока
        $conditions = [
            'contextid' => $this->context->id,
            'component' => 'block_dof'
        ];
        $recordsexists = $DB->record_exists('files', $conditions);
        
        if ( $this->instance->id == $DOF->instance->id || $recordsexists )
        {// Файлы найдены
            if ( has_capability('moodle/site:manageblocks', $this->context) )
            {// Пользователь имеет возможность удалять блок
                // Отобразить предупреждение
                $this->content->text .= html_writer::div(
                    get_string('warning_delete_instance', 'block_dof'), 'alert alert-danger block_dof_error_message'
                );
            }
        }
        
        if ( $DOF->is_access('view') )
        {// Пользователь имеет доступ к деканату
            // Получение настроек для формирования контента блока
            
            // Режим трансляции
            if ( ! empty($this->config->translation_mode) )
            {
                $translationmode = $this->config->translation_mode;
            } else
            {
                $translationmode = 'block';
            }
            // Транслируемый интерфейс
            if ( ! empty($this->config->translation_im) )
            {
                $translationim = $this->config->translation_im;
            } else
            {
                $translationim = 'standard';
            }
            // Имя отображения
            if ( ! empty($this->config->translation_name) )
            {
                $translationname = $this->config->translation_name;
            } else
            {
                $translationname = '';
            }
            // Тип передаваемых данных
            if ( ! empty($this->config->translation_id_mode) )
            {
                $translationidmode = $this->config->translation_id_mode;
            } else
            {
                $translationidmode = 'userid';
            }
            // Формирование передаваемых данных
            switch ( $translationidmode )
            {
                case 'manual':
                    if ( ! empty($this->config->translation_id) )
                    {
                        $translationid = (int) $this->config->translation_id;
                    } else
                    {
                        $translationid = '';
                    }
                    break;
                case 'userid':
                    $translationid = $userid;
                    break;
                case 'courseid':
                    $translationid = $COURSE->id;
                    break;
                case 'personid':
                    $translationid = $DOF->storage('persons')->get_by_moodleid_id($userid, true);
                    break;
                default:
                    $translationid = $DOF->storage('persons')->get_by_moodleid_id($userid, true);
                    break;
            }
            // Определение метода для получения контента
            switch ( $translationmode )
            {
                case 'block':
                    $translationmethod = 'get_block';
                    break;
                case 'section':
                    $translationmethod = 'get_section';
                    break;
                default:
                    $translationmethod = 'get_block';
                    break;
            }
            // Формирование контента
            if ( method_exists($DOF->im($translationim), $translationmethod) &&
                 $translationid !== false )
            {// Метод получен
                
                // Получение имени блока
                $stringoptions['empry_result'] = $this->title;
                $this->title = $DOF->get_string($translationname, $translationim, null, 'im', $stringoptions);

                if ( ! empty($translationid) )
                {// Передан идентификатор - вызываем функцию с обоими параметрами
                    $this->content->text .= $DOF->im($translationim)->{$translationmethod}(
                        $translationname, $translationid);
                } else
                {// Получение контента на основе базовых параметров
                    $this->content->text .= $DOF->im($translationim)->{$translationmethod}(
                        $translationname);
                }
            }
        }
        return $this->content;
    }

    /**
     * Определение местоположения экземпляра блока
     *
     * @return string - Код страницы:
     *          my - Личный кабинет
     *          main - Главная страница
     *          profile - Страница профиля
     *          other - Все остальные страницы
     */
    public function get_format_content()
    {
        global $PAGE, $CFG;
        
        $path = $PAGE->url->out();
        
        // Определение местоположения на основе URL экземпляра
        if ( strstr($path, 'blocks/dof/im/my') )
        {// контент запущен со страниц im/my
            return 'my';
        } else if ( preg_match("{{$CFG->wwwroot}/(index.php)?$}", $path) )
        { // контент запущен с главной страницы
            return "main";
        } else if ( strstr($path, '/user/profile.php') )
        { // контент запущен со страницы просмотра профиля студента
            return "profile";
        }
        // контент запущен с остальных страниц
        return "other";
    }
}

?>
