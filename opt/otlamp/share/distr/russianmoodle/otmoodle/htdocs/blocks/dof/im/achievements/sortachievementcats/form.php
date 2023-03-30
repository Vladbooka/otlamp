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
 * Классы форм
 * 
 * @package    im
 * @subpackage achievements
 * @subpackage sortachievementcats
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем базовые функции плагина
require_once(dirname(realpath(__FILE__)).'/lib.php');

// Подключение библиотек форм
$DOF->modlib('widgets')->webform();

/**
 * Форма сортировки категорий достижений
 */
class dof_im_achievementcats_sort_form extends dof_modlib_widgets_form
{
    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control $dof
     */
    protected $dof;

    /**
     * ID раздела
     * 
     * @var int $id
     */
    protected $id = 0;

    /**
     * GET параметры для генерации ссылок
     * 
     * @var array $addvars
     */
    protected $addvars = [];

    /**
     * ID подразделения
     * 
     * @var int $id
     */
    protected $departmentid = 0;

    /**
     * Инициализация формы
     */
    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = & $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->id = $this->addvars['parentcat'];
        $this->departmentid = $this->addvars['departmentid'];

        // Установка идентификатора формы
        $mform->updateAttributes([
            'id' => 'sortachievementcats'
        ]);

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'departmentid', $this->departmentid);
        $mform->setType('departmentid', PARAM_INT);

        $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
        $statuses = array_keys($statuses);
        // Получение дочерних категорий первого уровня вложенности
        $achievementcats = $this->dof->storage('achievementcats')->get_categories($this->id,
            [
                'levels' => 1,
                'departmentid' => $this->departmentid,
                'exclude_subdepartments' => true,
                'statuses' => $statuses
            ]
        );
        if ( ! empty($achievementcats) )
        {// Разделы найдены
            foreach ($achievementcats as $achievementcat)
            {
                // Добавление скрытых полей категорий с текущими значениями их весов
                $mform->addElement(
                    'hidden', 
                    'achievementcat_'.$achievementcat->id,
                    $achievementcat->sortorder,
                    ['title' => $achievementcat->name]
                );
                $mform->setType('achievementcat_'.$achievementcat->id, PARAM_INT);
            }
        }

        // Добавление кнопки сохранения
        $mform->addElement(
            'submit', 
            'submit',
            $this->dof->get_string('form_achievementcats_edit_submit', 'achievements'),
            ['id' => 'sortachievementcats_submit']
        );
    }

    /**
     * Обработка данных формы
     *
     * @return bool
     */
    public function process()
    {
        if ( $this->is_submitted() && confirm_sesskey() && $formdata = $this->get_data() )
        {
            $success = false;

            $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
            $statuses = array_keys($statuses);
            // Получение дочерних категорий первого уровня вложенности
            $achievementcats = $this->dof->storage('achievementcats')->get_categories(
                $formdata->id, [
                    'levels' => 1,
                    'departmentid' => $this->departmentid,
                    'exclude_subdepartments' => true,
                    'statuses' => $statuses
                ]);

            if ( ! empty($achievementcats) )
            {
                // Инициализация транзакции
                $transaction = $this->dof->storage('achievementcats')->begin_transaction();
                
                // Сохранение сортировки
                $sortresult = true;
                foreach ( $achievementcats as $achievementcat )
                {
                    if ( ! empty($formdata->{'achievementcat_' . $achievementcat->id}) && 
                        $this->dof->storage('achievementcats')->is_access('edit', $achievementcat->id) )
                    {// Обработка раздела
                        if( $this->dof->im('achievements')->is_access('category/edit', $achievementcat->id) )
                        {// Установка порядка сортировки, который пришел из формы
                            $achievementcat->sortorder = $formdata->{'achievementcat_'.$achievementcat->id};
                            
                            // Сохранение объекта с новым порядком сортировки
                            $saveresult = $this->dof->storage('achievementcats')->save($achievementcat);
                            if( $saveresult == false )
                            {
                                $sortresult = false;
                                break;
                            }
                        } else
                        {
                            $sortresult = false;
                        }
                    } else
                    {
                        $sortresult = false;
                        break;
                    }
                }

                if ( $sortresult )
                {// Сохранение порядка произошло успешно
                    
                    // Коммит транзакции
                    $this->dof->storage('achievementcats')->commit_transaction($transaction);
                    $success = true;
                } else
                {// Отмена транзакции
                    $this->dof->storage('achievementcats')->rollback_transaction($transaction);
                }
            }
            
            // Редирект с указанием результата
            $somevars = array_merge(
                $this->addvars, 
                ['catsortsuccess' => $success]
            );
            redirect($this->dof->url_im('achievements', '/admin_panel.php', $somevars));
        }
    }
}