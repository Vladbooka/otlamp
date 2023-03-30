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
 * Класс составного шаблона достижений c единичными критериями
 * 
 * @package    storage
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class dof_storage_achievements_simple extends dof_storage_achievements_base 
{
    /**
     * Возвращает код класса
     * 
     * @return string
     */
    public static function get_classname()
    {
        return 'simple';
    }
    
    /**
     * Содержит ли класс дополнительные настройки
     *
     * @return bool
     */
    public static function has_additional_settings()
    {
        return true;
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
     * @return object|null - Массив типов достижений
     */
    public function settingsform($url, $customdata, $options = [])
    {
        $this->dof->modlib('widgets')->webform();
        require_once $this->dof->plugin_path('storage', 'achievements','/classes/simple/form.php');
        
        if ( empty($customdata) )
        {
            $customdata = new stdClass();
        }
        $customdata->achievementclass = $this;
        $form = new dof_storage_simple_settings_form($url, $customdata);
        $this->settingsform = $form;
        return $this->settingsform;
    }
    
    /**
     * Создать форму настроек
     *
     * @param string $url - Url перехода
     * @param object $customdata - Опции формы
     * @param array $options - Массив дополнительных опций
     *
     * @return object|null - Массив типов достижений
     */
    public function userform($url, $customdata, $options = [])
    {
        $this->dof->modlib('widgets')->webform();
        
        require_once $this->dof->plugin_path('storage', 'achievements','/classes/simple/form.php');
        
        if ( empty($customdata) )
        {
            $customdata = new stdClass();
        }
        // Шаблон достижения
        $customdata->achievementclass = $this;
        $form = new dof_storage_simple_user_form($url, $customdata);
        $this->userform = $form;
        
        return $this->userform;
    }
    
    /**
     * Подтвердить элемент пользовательского достижения
     *
     * @param array $userdata - Данные пользовательского достижения
     *
     * @param array $options - Дополнительные опции, определяющие подтверждающий элемент
     *              ['additionalid'] - Дополнительный параметр INTEGER
     *              ['additionalname'] - Дополнительный параметр STRING
     *              ['additionalid2'] - Дополнительный параметр INTEGER
     *              ['confirmall'] - флаг подтверждения всех критериев
     *
     * @return $userdata - Обработанные пользовательские достижения
     */
    public function moderate_confirm($userdata, $options = [])
    {
        if ( ! empty($options['confirmall']) )
        {
            // подтверждение всех критериев
            if ( ! empty($options['achievement_data']['simple_data']) )
            {
                foreach ( $options['achievement_data']['simple_data'] as $id => $unuseddata )
                {
                    $userdata["simple{$id}_confirm"] = 1;
                }
            } elseif ( empty($userdata) )
            {
                foreach ( $userdata as $key => $status)
                {
                    $userdata['simple' . filter_var($key, FILTER_SANITIZE_NUMBER_INT). '_confirm'] = 1;
                }
            }
            
            return $userdata;
        }
        // Получение ID критерия
        if ( isset($options['additionalid']) && ! is_null($options['additionalid']) )
        {
            $key = 'simple'.$options['additionalid'].'_confirm';
            if ( isset($options['additionalid2']) && ! is_null($options['additionalid2']) )
            {// Сброс подтверждения
                $userdata[$key] = 0;
            } else
            {
                $userdata[$key] = 1;
            }
            
            return $userdata;
        } else
        {
            return false;
        }
    }
    
    /** 
     * Вычислить баллы по достижению
     *
     * @param array $userdata - Данные пользовательского достижения
     * @param array $options - Дополнительные опции
     * 
     * @return float|bool - Баллы пользователя по достижению или false в случае ошибки
     */
    public function instance_calculate_userpoints($userdata, $options = [])
    {
        // Получение баллов шаблона
        $basicpoints = $this->achievement->points;
        
        $result = 0;
        
        // Получение критериев шаблона
        $adata = unserialize($this->achievement->data);
        
        if ( isset($adata['simple_data']) && ! empty($adata['simple_data']) )
        {// Критерии определены
            foreach ( $adata['simple_data'] as $id => $criteria )
            {
                // Вычисление в зависимости от типа критерия
                switch ( $criteria->type )
                {
                    case 'text' :
                    case 'data' :
                    case 'file' :
                        if ( ! empty($criteria->significant) )
                        {// Требуется подтверждение поля
                            $ukey = 'simple'.$id.'_confirm';
                            if ( isset($userdata[$ukey]) && ! empty($userdata[$ukey]) )
                            {// Поле подтверждено
                                $ukey = 'simple'.$id.'_value';
                                if ( isset($userdata[$ukey]) && ! empty($userdata[$ukey]) )
                                {// Поле заполнено
                                    if ( isset($criteria->rate) && ! empty($criteria->rate) )
                                    {// Коэфициент определен
                                        $result = $result + ( $basicpoints * $criteria->rate );
                                    }
                                }
                            }
                         } else 
                         {
                            $ukey = 'simple'.$id.'_value';
                            if ( isset($userdata[$ukey]) && ! empty($userdata[$ukey]) )
                            {// Поле заполнено
                                if ( isset($criteria->rate) && ! empty($criteria->rate) )
                                {// Коэфициент определен
                                    $result = $result + ( $basicpoints * $criteria->rate );
                                }
                            }
                        }
                        break;
                    case 'select' :
                        if ( ! empty($criteria->significant) )
                        {// Требуется подтверждение поля
                            $ukey = 'simple'.$id.'_confirm';
                            if ( isset($userdata[$ukey]) && ! empty($userdata[$ukey]) )
                            {// Поле подтверждено
                                $ukey = 'simple'.$id.'_value';
                                if ( isset($userdata[$ukey]) &&
                                     ! is_null($userdata[$ukey]) &&
                                     isset($criteria->options[$userdata[$ukey]])
                                   )
                                {// Поле выбрано и есть в критериях
                                    $option = $criteria->options[$userdata[$ukey]];
                                    if ( isset($option->rate) && ! empty($option->rate) )
                                    {// Коэфициент определен
                                        $result = $result + ( $basicpoints * $option->rate );
                                    }
                                }
                            }
                        } else
                        {
                            $ukey = 'simple'.$id.'_value';
                            if ( isset($userdata[$ukey]) &&
                                     ! is_null($userdata[$ukey]) &&
                                     isset($criteria->options[$userdata[$ukey]])
                               )
                            {// Поле выбрано и есть в критериях
                                $option = $criteria->options[$userdata[$ukey]];
                                if ( isset($option->rate) && ! empty($option->rate) )
                                {// Коэфициент определен
                                    $result = $result + ( $basicpoints * $option->rate );
                                }
                            }
                        }
                        break;
                }
            }
        }
        return $result;
    }
    
    /**
     * Произвести действия над пользовательским достижением перед сохранением
     *
     * @param object $newinstance - Объект пользовательского достижения, готового к обновлению
     * @param object $oldinstance - Объект пользовательского достижения до обновления
     *
     * @return object|bool $newinstance - Отредактированный объект пользовательского достижения
     *                                    или false в случае ошибки
     */
    public function beforesave_process($newinstance, $oldinstance = NULL)
    {
        return $newinstance;
    }
    
    /**
     * Проверить на необходимость модерации данных пользователя
     *
     * @param array $userdata - Данные пользовательского достижения
     *
     * @return bool - TRUE - Данные не требуют модерации
     *                FALSE - Данные требуют модерации
     *                NULL - Ошибка
     */
    public function is_completely_confirmed($data, $instance)
    {     
        if ( empty($data) )
        {// Данных нет
            return true;
        } 
        
        if ( ! isset($this->achievement->data) )
        {// Данные не найдены
            return NULL;
        }
        $achievementdata = unserialize($this->achievement->data);
        if ( ! isset($achievementdata['simple_data']) )
        {// Данные не найдены
            return NULL;
        }
        foreach ( $achievementdata['simple_data'] as $id => $criteria )
        {
            if ( isset($criteria->significant) && ! empty($criteria->significant) )
            {// Критерий требует модерации
                if ( isset($data['simple'.$id.'_value']) )
                {// Критерий заполнен
                    
                    if ( isset($data['simple'.$id.'_confirm']) )
                    {
                        if ( empty($data['simple'.$id.'_confirm']) )
                        {
                            return false;
                        }
                    } else 
                    {// Критерий не подтвержден
                        return false;
                    }
                }
            }
        }
        return true;
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
        $adata = unserialize($this->achievement->data);
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        
        if ( isset($adata['simple_data']) )
        {// Определены критерии достижения
            $table = new stdClass;
            $table->tablealign = "center";
            $table->cellpadding = 0;
            $table->cellspacing = 0;
            $table->head = [];
            $table->data = [];
            $table->align = [];
            $table->size = [];
            $table->do = [];
            $table->style = [];
            $data = [];
            $actions = [];
            $style = [];
            $counttomoderate = 0;
            foreach ( $adata['simple_data'] as $key => $criteria )
            {
                if( ! isset($criteria->name) )
                {
                    $criteria->name = '';
                }
                
                // Добавление заголовка
                $table->head[$key] = $criteria->name;
                $table->align[] = "center";
                $table->size[] = "200px";
                switch ( $criteria->type )
                {
                    // Текстовое поле
                    case 'text' :
                        if ( isset($userdata['simple'.$key.'_value']) )
                        {// Критерий определен
                            $data[$key] = $userdata['simple'.$key.'_value'];
                        } else 
                        {
                            $data[$key] = '';
                        }
                        break;
                        // Поле выбора даты
                    case 'data' :
                        if ( isset($userdata['simple'.$key.'_value']) )
                        {// Критерий определен
                            $data[$key] = dof_userdate($userdata['simple'.$key.'_value'], '%d.%m.%Y', $usertimezone, false);
                        } else 
                        {
                            $data[$key] = '';
                        }
                        break;
                        // Выпадающий список
                    case 'select' :
                        $data[$key] = '';
                        if ( isset($userdata['simple'.$key.'_value']) )
                        {// Критерий определен
                            $optionid = $userdata['simple'.$key.'_value'];
                            if ( isset($criteria->options[$optionid]->name) )
                            {
                                $data[$key] = $criteria->options[$optionid]->name;
                            }
                        }
                        break;
                        // Загрузка файлов
                    case 'file' :
                        if ( isset($userdata['simple'.$key.'_value']) )
                        {// Критерий определен
                            $data[$key] = $this->dof->modlib('filestorage')->link_files($userdata['simple'.$key.'_value']);
                        } else 
                        {
                            $data[$key] = '';
                        }
                        break;
                    default :
                        break;
                }
                
                // ДЕЙСТВИЯ
                $criteriaaction = [];
                if ( isset($criteria->significant) && ! empty($criteria->significant) )
                {// Требуется подтверждение критерия
                    // Ключ для проверки подтверждения критерия
                    $userkey = 'simple'.$key.'_confirm';
                    if ( isset($userdata[$userkey]) && ! empty($userdata[$userkey]) )
                    {// Критерий подтвержден
                        $do = 'deconfirm';
                        $hash = $key;
                        $icon = 'moderation_confirm';
                        // Добавление действия по снятию подтверждения
                        $criteriaaction[] = ['do' => $do, 'hash' => $hash, 'icon' => $icon ];
                    } else
                    {// Критерий не подтвержден
                        $counttomoderate++;
                        $do = 'confirm';
                        $hash = $key;
                        $icon = 'moderation_need';
                        // Добавление действия по снятию подтверждения
                        $criteriaaction[] = ['do' => $do, 'hash' => $hash, 'icon' => $icon ];
                    }
                    $actions[$key] = $criteriaaction;
                }
            }
            $table->data[0] = $data;
            $table->style[0] = $style;
            $table->do[0] = $actions;
            $table->stat['tomoderate'] = $counttomoderate;
        }
        return $table;
    }     
    
    /**
     * Выполнить действия перед подтверждением достижения
     *
     * @param array $userdata - Пользовательские данные
     *
     * @return void
     */
    public function before_completely_confirmed_process($userdata)
    {
        if ( empty($userdata) )
        {// Данных нет
            return;
        } 
        
        if ( ! isset($this->achievement->data) )
        {// Данные не найдены
            return;
        }
        $achievementdata = unserialize($this->achievement->data);
        if ( ! isset($achievementdata['simple_data']) )
        {// Данные не найдены
            return;
        }

        foreach ( $achievementdata['simple_data'] as $id => $criteria )
        {
            switch ( $criteria->type )
            {
                // Файл
                case 'file' :
                    if ( isset($userdata['simple'.$id.'_value']) )
                    {// Критерий определен, исполнение действий над файлом
                        // Получение хэша загруженного файла
                        $pathnamehashes = $this->dof->modlib('filestorage')->
                            get_pathnamehashes($userdata['simple'.$id.'_value']);
                        if ( ! empty($pathnamehashes) )
                        {// Хэшы найдены
                            // Получение включенных плагинов плагиаризма
                            $plugins = $this->dof->sync('achievements')->get_plagiarism_plugins_code();
                            foreach ( $plugins as $plugincode => $plugin )
                            {
                                $addtoindex = 'plagiarism_'.$plugincode.'_addtoindex';
                                if ( isset($criteria->$addtoindex) && ! empty($criteria->$addtoindex) )
                                {// Требуется добавить файлы критерия в индекс
                                    foreach ( $pathnamehashes as $pathnamehash )
                                    {
                                        $this->dof->sync('achievements')->plagiarism_add_to_index_file($plugincode, $pathnamehash);
                                    }
                                }
                            }
                        }
                    }
                    break;
            }
        }
        return;
    }
    
    /**
     * Проверить, является ли достижение пустым (не заполненным)
     * @param stdClass $achievementin объект достижения (запись из базы)
     * @throws moodle_exception
     * @return boolean true, если достижение не заполнено
     */
    public function is_empty_userdata($achievementin)
    {
        // Инициализация и нормализация
        $res = true;
        if( ! is_object($achievementin) )
        {
            if( is_int($achievementin) && (int)$achievementin > 0 )
            {
                $achievementin = $this->dof->storage('achievementins')->get($achievementin);
                if( empty($achievementin) )
                {
                    throw new moodle_exception('achievementin_not_exist');
                }
            } else 
            {
                throw new moodle_exception('not_supported_format');
            }
        }
        $achievement = $this->dof->storage('achievements')->get($achievementin->achievementid);
        if( ! empty($achievement) )
        {// Все данные получили - начинаем проверку
            $data = unserialize($achievement->data);
            $userdata = unserialize($achievementin->data);
            foreach($data['simple_data'] as $key => $criteria)
            {
                if( count($userdata) == 1 && $criteria->type == 'data' )
                {// Если заполнен только один критерий типа "дата", считаем достижение пустым
                    break;
                }
                if( ! $res )
                {// Не проверяем дальше, если уже известно, что достижение не пустое
                    break;
                }
                switch($criteria->type)
                {
                    case 'text':
                        if( ! isset($userdata['simple' . $key . '_value']) || (empty($userdata['simple' . $key . '_value']) && $userdata['simple' . $key . '_value'] != '0') )
                        {
                            $res = $res && true;
                        } else 
                        {
                            $res = false;
                            break;
                        }
                        break;
                    case 'data':
                        // Игнорируем дату, т.к. она не бывает пустой
                        break;
                    case 'select':
                        if( ! isset($userdata['simple' . $key . '_value']) )
                        {// Список пустой, только если не заданы элементы списка
                            $res = $res && true;
                        } else
                        {
                            $res = false;
                            break;
                        }
                        break;
                    case 'file':
                        if( empty($userdata['simple' . $key . '_value']) )
                        {
                            $res = $res && true;
                        } else
                        {
                            $res = false;
                            break;
                        }
                        break;
                    default:
                        // Нет критерия такого типа
                        throw new moodle_exception('not_supported_criteria_type');
                        break;
                }
            }
            return $res;
        }
    }
}