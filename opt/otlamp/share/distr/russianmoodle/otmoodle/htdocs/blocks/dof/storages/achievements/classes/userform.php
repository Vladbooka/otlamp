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

/**
 * Форма редактирования цели / достижения пользователями
 *
 * @package    storage
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Форма создания/редактирования данных пользователя
 */
class dof_storage_achievementin_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    /**
     * @var $userdata - Данные пользователя
     */
    protected $userdata = [];
    /**
     * @var $data - Данные шаблона
     */
    protected $data = [];
    /**
     * @var $addvars - Массив GET-параметров
     */
    protected $addvars = [];
    /**
     * @var $achievement - Шаблон достижения
     */
    protected $achievement = NULL;
    /**
     * @var $creategoal - является ли добавляемое достижение целью 
     */
    protected $creategoal = false;
    
    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->userdata = $this->_customdata->userdata;
        $this->addvars = $this->_customdata->addvars;
        $this->achievement = $this->_customdata->achievementclass->get_achievement();
        $this->data = unserialize($this->achievement->data);
        if( ! empty($this->_customdata->create_goal) )
        {
            $this->creategoal = true;
        }
        
        if( ! empty($this->_customdata->id) )
        {
            $achievementin = $this->dof->storage('achievementins')->get($this->_customdata->id);
        }
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        $goalediting = ! empty($achievementin) && array_key_exists($achievementin->status, $this->dof->workflow('achievementins')->get_meta_list('goal_real'));
        $goalcreating = $this->dof->storage('achievements')->is_goal_add_allowed(
            $this->achievement->scenario
        ) && $this->creategoal;
        
        if ( $goalcreating || $goalediting )
        {
            $mform->addElement(
                'dof_date_selector', 
                'goaldeadline',
                $this->dof->get_string(
                    'achievementin_form_goal_deadline', 
                    'achievements', 
                    null, 
                    'storage'
                ),
                ['onlytimestamp' => true, 'timezone' =>  $this->dof->storage('persons')->get_usertimezone_as_number()]
            );
            if( ! empty($achievementin->goaldeadline) )
            {
                $mform->setDefault('goaldeadline', $achievementin->goaldeadline);
            } else 
            {
                $mform->setDefault('goaldeadline', time() + 86400);
            }
        }
        
        $this->definition_ext($mform);
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        
        // Заполнение формы пользовательскими данными
        $this->set_data($this->userdata);
    }
    
    /**
     * Проверка данных формы
     *
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        $errors = [];
        if ( $mform->elementExists('goaldeadline') )
        {
            // проверим, чтобы дата была в будущем
            if ( $data['goaldeadline'] < time() )
            {
                $errors['goaldeadline'] = $this->dof->get_string('achievementin_userform_error_invalid_goaldeadline_date', 'achievements', null, 'storage');
            }
        }
        
        // Массив ошибок
        $errors = array_merge($errors, $this->validation_ext($data, $files));
        
        // Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
        
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            $result = [];
            
            if( ! empty($formdata->goaldeadline) )
            {
                $result['goaldeadline'] = $formdata->goaldeadline;
            }
            
            $processext = $this->process_ext($formdata);
            if( ! empty($processext) )
            {
                $result['userdata'] = $processext;
            }
            
            return $result;
        }
        return NULL;
    }
    
    /**
     * @param $mform MoodleQuickForm
     */
    protected function definition_ext(&$mform)
    {
    }
    
    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    protected function validation_ext($data, $files)
    {
        return [];
    }
    
    /**
     * @param stdClass $formdata
     * @return array
     */
    protected function process_ext($formdata)
    {
        return [];
    }
}