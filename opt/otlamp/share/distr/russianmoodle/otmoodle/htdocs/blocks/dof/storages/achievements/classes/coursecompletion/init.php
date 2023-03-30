<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://www.deansoffice.ru/>                                           //
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
 * Шаблон "Прохождение курса"
 * 
 * @package    storage
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class dof_storage_achievements_coursecompletion extends dof_storage_achievements_base
{
    /**
     * Возвращает код класса
     *
     * @return string
     */
    public static function get_classname()
    {
        return 'coursecompletion';
    }
    
    
    /**
     * Содержит ли класс дополнительные настройки
     *
     * @return bool
     */
    public static function has_additional_settings()
    {
        return false;
    }
    
    /**
     * Поддержка ручного добавления
     *
     * @param int $personid - ID пользователя, для которого проверяем
     *
     * @return array
     */
    public function manual_create($personid)
    {
        // Массив ошибок
        $errors = [];
        
        // Получение пользователя Moodle по ID персоны
        $person = $this->dof->storage('persons')->get($personid);
        if ( isset($person->mdluser) )
        {// Пользователь синхранизирован с персоной
            // Проверка прав доступа
            if ( ! $this->dof->storage('achievementins')->is_access('create', $personid) )
            {// Прав нет
                $errors[] = $this->dof->get_string(
                        'dof_storage_achievements_base_no_access',
                        'achievements',
                        null,
                        'storage'
                        );
            }
        } else
        {// Пользователь не синхранизирован
            $errors[] = $this->dof->get_string(
                    'dof_storage_achievements_base_no_access',
                    'achievements',
                    null,
                    'storage'
                    );
        }
        
        // Проверка наличия полей достижения
        if ( empty($this->get_achievement()->data) )
        {// Поле data пустое, добавлять достижение нельзя
            $errors[] = $this->dof->get_string(
                    'dof_storage_achievements_base_no_data',
                    'achievements',
                    null,
                    'storage'
                    );
        }
        
        $data = unserialize($this->get_achievement()->data);
        if ( ! empty($data['coursecompletion_data']['auto_add_achievement']) )
        {
            $errors[] = $this->dof->get_string(
                    'coursecompletion_achievementin_auto_adding_on',
                    'achievements',
                    null,
                    'storage'
                    );
        }
        
        return $errors;
    }
    
    /**
     * Поддержка ручного удаления
     *
     * @return bool
     */
    public function manual_delete()
    {
        return true;
    }
    
    /**
     * Создать форму настроек
     *
     * @param string $url - Url перехода
     * @param object $customdata - Опции формы
     * @param array $options - Массив дополнительных опций
     *
     * @return dof_storage_achievement_coursecompletion_settings_form
     */
    public function settingsform($url, $customdata, $options = [])
    {
        $this->dof->modlib('widgets')->webform();
        require_once $this->dof->plugin_path('storage', 'achievements','/classes/coursecompletion/form.php');
        
        if ( empty($customdata) )
        {
            $customdata = new stdClass();
        }
        $customdata->achievementclass = $this;
        $form = new dof_storage_achievement_coursecompletion_settings_form($url, $customdata);
        $this->settingsform = $form;
        return $this->settingsform;
    }
    
    /**
     * Создать форму добавления/редактирования достижения
     *
     * @param string $url - Url перехода
     * @param object $customdata - Опции формы
     * @param array $options - Массив дополнительных опций
     *
     * @return dof_storage_coursecompletion_user_form
     */
    public function userform($url, $customdata, $options = [])
    {
        $this->dof->modlib('widgets')->webform();
        
        require_once $this->dof->plugin_path('storage', 'achievements','/classes/coursecompletion/form.php');
        
        if ( empty($customdata) )
        {
            $customdata = new stdClass();
        }
        // Шаблон достижения
        $customdata->achievementclass = $this;
        $form = new dof_storage_coursecompletion_user_form($url, $customdata);
        $this->userform = $form;
        
        return $this->userform;
    }
    
    /**
     * Получить форматированные данные пользователя
     *
     * @param array $userdata - Пользовательские данные
     *
     * @return string
     */
    public function get_formatted_user_data($userdata)
    {
        // инициализация таблцы вывода данных
        $table = new stdClass;
        $table->tablealign = "center";
        $table->cellpadding = 0;
        $table->cellspacing = 0;
        $table->head = [];
        $table->data = [];
        $table->align = [];
        $table->size = [];
        $table->style = [];
        
        $adata = unserialize($this->achievement->data);
        if ( isset($adata['coursecompletion_data']) )
        {// Определены критерии достижения
            // получение курса moodle
            if ( $this->dof->modlib('ama')->course(false)->is_exists($userdata['courseid']) &&
                    $mdlcourse = $this->dof->modlib('ama')->course($userdata['courseid'])->get() )
            {
                // добавление ссылки на курс
                $url = new moodle_url('/course/view.php', ['id' => $mdlcourse->id]);
                $table->data[0] = [dof_html_writer::tag('a', $mdlcourse->fullname, ['href' => $url, 'target' => '_blank'])];
            } else 
            {
                // курс удален
                $table->data[0] = [$userdata['coursename']];
            }
            
            // добавление заголовка
            $table->head[] = $this->dof->get_string('coursecompletion_achievementin_course_name', 'achievements', null, 'storage');
            $table->align[] = "center";
            $table->size[] = "200px";
        }
        return $table;
    }
    
    /**
     * Проверить на необходимость модерации данных пользователя
     *
     * @param array $userdata - данные пользовательского достижения
     * @param stdClass $achievmentin - объект достижения пользователя
     *
     * @return bool - TRUE - Данные не требуют модерации
     *                FALSE - Данные требуют модерации
     *                NULL - Ошибка
     */
    public function is_completely_confirmed($data, $achievmentin)
    {
        if ( empty($data['courseid']) || empty($achievmentin) )
        {// Данных нет
            return true;
        }
        
        if ( ! isset($this->achievement->data) )
        {// Данные не найдены
            return NULL;
        }
        $achievementdata = unserialize($this->achievement->data);
        if ( ! $this->dof->modlib('ama')->course(false)->is_exists($data['courseid']) )
        {
            // курс не существует
            return false;
        }
        if ( ! $person = $this->dof->storage('persons')->get($achievmentin->userid) )
        {
            // курс не существует
            return false;
        }
        if ( empty($person->mdluser) || ! $this->dof->modlib('ama')->user(false)->is_exists($person->mdluser) )
        {
            return false;           
        }
        if ( $this->dof->modlib('ama')->course($data['courseid'])->is_user_coursecompletion($person->mdluser) )
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * Автофиксация выполнения/подтверждения цели/достижения
     * 
     * @return bool
     */
    public function is_autocompletion()
    {
        return true;
    }
    
    /**
     * вычисление балла
     *
     * @param array $userdata - данные пользовательского достижения
     * @param array $options - дополнительные опции
     *
     * @return float|bool - баллы пользователя по достижению или false в случае ошибки
     */
    public function instance_calculate_userpoints($userdata, $options = [])
    {
        if ( empty($userdata['courseid']) )
        {
            return false;
        }
        if ( ! $this->dof->modlib('ama')->course(false)->is_exists($userdata['courseid']) )
        {
            // курс не существует
            return false;
        }
        
        $mdluserid = 0;
        if ( ! empty($options['userid']) )
        {
            $mdluserid = $options['userid'];
        } elseif ( ! empty($options['instance']['userid']) )
        {
            $person = $this->dof->storage('persons')->get($options['instance']['userid']);
            if ( ! empty($person->mdluser) && $this->dof->modlib('ama')->user(false)->is_exists($person->mdluser) )
            {
                $mdluserid = $person->mdluser;
            }
        }
        if ( empty($mdluserid) )
        {
            // пользователь не существует
            return false;
        }
        
        // получение процента прохождения
        $percent = $this->dof->modlib('ama')->course($userdata['courseid'])
            ->grade()
            ->get_total_grade($mdluserid, null, null, true);
        
        if ( ! empty($percent) && ! empty($this->achievement->points) )
        {
            // оценка за достижение пропорционально оценка за курс
            return $percent/100 * $this->achievement->points;
        }
        
        return 0;
    }
}

