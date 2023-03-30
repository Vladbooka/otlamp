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
 * Абстрактный класс шаблонов достижений
 * 
 * @package    storage
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
abstract class dof_storage_achievements_base
{
    /**
     * @var dof_control - Ссылка на объект деканата
     */
    protected $dof;
    
    /**
     * Объект достижения из БД
     */
    protected $achievement;
    
    /**
     * Дополнительные опции 
     */
    protected $options;
    
    /**
     * Форма настроек
     */
    public $settingsform;
    
    /**
     * Форма пользователя
     */
    public $userform;
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     * @param object $achievement - Объект достижения из БД
     * @param array $options - Дополнительные опции
     */
    public function __construct(dof_control $dof, $achievement, $options = [])
    {
        // Сохраняем ссылку на DOF
        $this->dof = $dof;
        // Объект достижения из БД
        $this->achievement = $achievement;
        // Дополнительные опции
        $this->options = $options;
    }
    
    /**
     * Возвращает код класса
     *
     * @return string
     */
    public static function get_classname()
    {
        return 'base';
    }
    
    /**
     * Возвращает максимальное число копий достижения для одного пользователя
     *
     * @return integer|NULL
     */
    public static function instances_maxnumber()
    {
        // Без ограничений
        return NULL;
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
     * Получение шаблона достижения, на основе которого строится класс
     * @return object
     */
    public function get_achievement()
    {
        return $this->achievement;
    }

    /**
     * Поддержка ручного создания
     *
     * @param int $personid - ID пользователя - владельца достижения
     *
     * @return array - Массив ошибок, по причине которых добавление достижения запрещено
     */
    public function manual_create($personid)
    {
        return [
            $this->dof->get_string(
                'dof_storage_achievements_base_manual_create_error', 
                'achievements', 
                null, 
                'storage'
            )
        ];
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
     * Инициализация статуса вновь созданного достижения
     * 
     * @param int $id
     * 
     * @return bool
     */
    public function init_status_achievementins($id)
    {
        // Получаем объект
        if ( ! $object = $this->dof->storage('achievementins')->get($id) )
        {// Объект не найден
            return false;
        }
        
        // Меняем статуc
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = 'draft';
        
        return $this->dof->storage('achievementins')->update($obj);
    }
    
    /**
     * Доступность подсистемы модерации для данного класса
     * 
     * @return bool
     */
    public function moderate_enabled()
    {
        if ( isset($this->options['moderation_enabled']) )
        {// Значение настройки определено в опциях
            // Вернуть настройку
            return (bool)($this->options['moderation_enabled']);
        } else 
        {// Настройки подсистемы не найдено - подиситема отключена
            return false;
        }
    }
    
    /**
     * Доступность подсистемы рейтинга для данного класса
     *
     * @return bool
     */
    public function rating_enabled()
    {
        if ( isset($this->options['rating_enabled']) )
        {// Значение настройки определено в опциях
            // Вернуть настройку
            return (bool)($this->options['rating_enabled']);
        } else
        {// Настройки подсистемы не найдено - подиситема отключена
            return false;
        }
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
        return NULL;
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
        return NULL;
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
     *
     * @return $userdata - Обработанные пользовательские достижения
     */
    public function moderate_confirm($userdata, $options = [])
    {
        return $userdata;
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
        return (float) 0;
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
        return '';
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
        // По умолчанию нет пустых достижений
        return false;
    }
    
    /**
     * Автофиксация выполнения/подтверждения цели/достижения
     * 
     * @return bool
     */
    public function is_autocompletion()
    {
        return false;
    }
}
