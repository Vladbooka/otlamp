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
 * Панель управления достижениями
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем базовые функции плагина

class dof_im_achievements implements dof_plugin_im
{
    /**
     * Объект деканата для доступа к общим методам
     * @var dof_control
     */
    protected $dof;

    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    /**
     * Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     *
     * @return boolean
     */
    public function install()
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }

    /**
     * Метод, реализующий обновление плагина в системе.
     * Создает или модифицирует существующие таблицы в БД
     *
     * @param string $old_version - Версия установленного в системе плагина
     *
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        if ($oldversion < 2020101300)
        { // Добавление настроек по умолчанию для панели управления пользователями портфолио
            $config = new stdClass();
            $config->code = 'mpanel_fields';
            $config->plugintype = 'im';
            $config->plugincode = 'achievements';
            $config->type = 'text';
            $config->value = serialize(['standardfields' =>
                [
                    'confirmed' => 'confirmed',
                    'unconfirmed' => 'unconfirmed',
                    'approved' => 'approved',
                    'notapproved' => 'notapproved',
                    'lastcreatedtime' => 'lastcreatedtime'

                ], 'fieldssettings' => []]);
            // Список всех корневых подразделений
            $list = $this->dof->storage('departments')->get_records(['depth' => 0]);
            if (!empty($list)) {
                foreach (array_keys($list) as $departmentid) {
                    $config->departmentid = $departmentid;
                    $this->dof->storage('config')->insert($config);
                }
            }
        }

        if ($oldversion < 2021051700) {
            // ранее настройка публичности портфолио была чекбоксом (видно всем, даже авторизованным или только по правам)
            // начиная с версии 2021051700 это селект, у которого есть еще пункт доступно только авторизованным
            $records = $this->dof->storage('config')->get_records(['code' => 'public_my']);
            foreach($records as $record) {
                $record->type = 'select';
                $this->dof->storage('config')->update($record);
            }
        }

        // Обновим права доступа
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }

    /**
     * Возвращает версию установленного плагина
     *
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2021051700;
    }

    /**
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     *
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     *
     * @return string
     */
    public function compat()
    {
        return 'angelfish';
    }

    /**
     * Возвращает тип плагина
     *
     * @return string
     */
    public function type()
    {
        return 'im';
    }

    /**
     * Возвращает короткое имя плагина
     *
     * Оно должно быть уникально среди плагинов этого типа
     *
     * @return string
     */
    public function code()
    {
        return 'achievements';
    }

    /**
     * Возвращает список плагинов, без которых этот плагин работать не может
     *
     * @return array
     */
    public function need_plugins()
    {
        return [
                'modlib' => [
                        'ama'             => 2016041600,
                        'nvg'             => 2008060300,
                        'widgets'         => 2016042000
                ],
                'storage' => [
                        'achievementcats' => 2015090000,
                        'achievements'    => 2015090000,
                        'achievementins'  => 2016041400,
                        'persons'         => 2015012000,
                        'config'          => 2011080900,
                        'acl'             => 2011040504
                ],
                'workflow' => [
                        'achievementcats' => 2015090000,
                        'achievements'    => 2015090000,
                        'achievementins'  => 2016041400,
                ]
        ];
    }

    /**
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin
     *
     * @see dof_modlib_base_plugin::is_setup_possible()
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     *
     * @return bool
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion = 0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }

    /**
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     *
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return [
                'modlib' => [
                        'ama'             => 2016041600,
                        'nvg'             => 2008060300,
                        'widgets'         => 2016042000
                ],
                'storage' => [
                        'achievementcats' => 2015090000,
                        'achievements'    => 2015090000,
                        'achievementins'  => 2016041400,
                        'persons'         => 2015012000,
                        'config'          => 2011080900,
                        'acl'             => 2011040504
                ],
                'workflow' => [
                        'achievementcats' => 2015090000,
                        'achievements'    => 2015090000,
                        'achievementins'  => 2016041400,
                ]
        ];
    }

    /**
     * Список обрабатываемых плагином событий
     *
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
       return [
                       [
                                       'plugintype' => 'im',
                                       'plugincode' => 'my',
                                       'eventcode'  => 'info'
                       ]
            ];
    }

    /**
     * Требуется ли запуск cron в плагине
     *
     * @return bool
     */
    public function is_cron()
    {
       // Запуск каждые 4 часа
       return 14400;
    }

    /**
     * Проверяет полномочия на совершение действий
     *
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта,
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     *
     * @return bool
     *              true - можно выполнить указанное действие по
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function is_access($do, $objid = NULL, $userid = NULL, $departmentid = NULL)
    {
        if ( $this->dof->is_access('datamanage') OR
            $this->dof->is_access('admin') OR
            $this->dof->is_access('manage')
            )
        {// Открыть доступ для администраторов
            return true;
        }
        // Получаем ID персоны, с которой связан данный пользователь
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);

        $depid = NULL;

        switch ( $do )
        {// Определяем дополнительные параметры в зависимости от запрашиваемого права
            case 'admnistration' :
                if ( ! empty($objid) )
                {// Подразделение не передано
                    $depid = $objid;
                    $objid = NULL;
                } else
                {// Подразделение не передано
                    return false;
                }
                break;
            case 'control_panel' :
                if ( ! empty($objid) )
                {
                    $depid = $objid;
                    $objid = NULL;
                }
                break;
            case 'moderation' :
                if ( ! empty($objid) )
                {// Подразделение не передано
                    $depid = $objid;

                    // Получение конфигурации
                    $system_moderation_enabled = $this->dof->storage('config')->
                        get_config_value('system_moderation_enabled', 'im', 'achievements', $depid);
                    if ( empty($system_moderation_enabled) )
                    {// Подсистема модерации отключена
                        return false;
                    }
                    $objid = NULL;
                } else
                {// Подразделение не передано
                    return false;
                }
                break;
            case 'otslider_view' :
                if ( ! empty($objid) )
                {
                    $depid = $objid;
                    $objid = NULL;
                }
                break;
            case 'rating_view' :
                if ( ! empty($objid) )
                {// Подразделение не передано
                    $depid = $objid;
                    // Получение конфигурации
                    $system_rating_enabled = $this->dof->storage('config')->
                        get_config_value('system_rating_enabled', 'im', 'achievements', $depid);
                    if ( empty($system_rating_enabled) )
                    {// Подсистема модерации отключена
                        return false;
                    }
                    $objid = NULL;
                }
                return $this->dof->is_access('view');
                break;
            case 'user_rating_view' :

                $targetpersonid = $objid;
                if ( empty($targetpersonid) )
                {// Пользователь не указан
                    // Установка текущего пользователя
                    $targetperson = $this->dof->storage('persons')->get_bu();
                    $targetpersonid = (int)$targetperson->id;
                }
                // Получение настройки отображения пользовательского рейтинга
                $viewforall = $this->dof->storage('cov')->
                    get_option('im', 'achievements', $targetpersonid, 'user_rating_availability');
                if ( $viewforall )
                {// Рейтинг пользовтеля открыт к просмотру всем без исключения
                    return true;
                }
                if ( $targetpersonid == $personid )
                {
                    // Проверка на право видеть свой рейтинг
                    return $this->is_access('user_rating_view/owner', NULL, $userid);
                }
                // Проверка права на просмотр рейтинга в подразделении целевого пользователя
                $depid = $this->dof->storage('persons')->get_field($targetpersonid, 'departmentid');
                break;
            case 'user_rating_view/owner' :
                /**
                 * @TODO - Как только появятся доверенности у студентов - убрать этот блок.
                 * Пользователи могут видеть свой рейтинг несмотря на права.
                 * Нужен механизм в Деканате по назначению доверенностей студентов
                 */
                return true;

                break;
            case 'rating_availability_edit' :
                $targetpersonid = $objid;
                if ( empty($targetpersonid) )
                {// Пользователь не указан
                    // Установка текущего пользователя
                    $targetperson = $this->dof->storage('persons')->get_bu();
                    $targetpersonid = (int)$targetperson->id;
                }
                if ( $targetpersonid == $personid )
                {// Редактирование отображения своего рейтинга
                    // Проверка на право создания своего достижения
                    return $this->is_access('rating_availability_edit/owner', NULL, $userid);
                }
                break;
            case 'rating_availability_edit/owner' :

                /**
                 * @TODO - Как только появятся доверенности у студентов - убрать этот блок.
                 * Пользователи могут редактировать отображение своего рейтинга несмотря на права.
                 * Нужен механизм в Деканате по назначению доверенностей студентов
                 */
                return true;

                break;
            case 'my' :
                // Открыт для всех
                return true;
                break;
            case 'category/create' :
                // Проверка на принадлежность к реальному мета-статусу родительского раздела
                if ( ! empty($objid) )
                {// Раздел указан
                    // Поиск раздела
                    $category = $this->dof->storage('achievementcats')->get($objid);
                    if ( empty($category) )
                    {// Раздел не найден в системе
                        return false;
                    }
                    // Получение реальных статусов раздела
                    $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
                    if ( ! array_key_exists($category->status , $statuses) )
                    {// Статус родительского раздела не является реальным
                        return false;
                    }

                    $objid = NULL;
                }
                break;
            case 'category/view' :
                // Проверка на существование раздела
                if ( ! empty($objid) )
                {// Раздел указан
                    // Поиск раздела
                    $category = $this->dof->storage('achievementcats')->get($objid);
                    if ( empty($category) )
                    {// Раздел не найден в системе
                        return false;
                    }

                    // Получение реальных статусов раздела
                    $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
                    if ( ! array_key_exists($category->status , $statuses) )
                    {// Статус раздела не является реальным
                        return false;
                    }

                    $objid = NULL;
                }

                break;
            case 'category/edit' :
                // Проверка на принадлежность раздела к реальному мета-статусу
                if ( ! empty($objid) )
                {// Раздел указан
                    // Поиск раздела
                    $category = $this->dof->storage('achievementcats')->get($objid);
                    if ( empty($category) )
                    {// Раздел не найден в системе
                        return false;
                    }
                    // Получение реальных статусов раздела
                    $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
                    if ( ! array_key_exists($category->status , $statuses) )
                    {// Статус раздела не является реальным
                        return false;
                    }
                    $objid = NULL;
                } else
                {// Раздел не указан
                    return false;
                }
                break;
            case 'category/delete' :
                // Проверка на принадлежность раздела к реальному мета-статусу
                if ( ! empty($objid) )
                {// Раздел указан
                    // Поиск раздела
                    $category = $this->dof->storage('achievementcats')->get($objid);
                    if ( empty($category) )
                    {// Раздел не найден в системе
                        return false;
                    }
                    // Получение реальных статусов раздела
                    $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
                    if ( ! array_key_exists($category->status , $statuses) )
                    {// Статус раздела не является реальным
                        return false;
                    }
                    $objid = NULL;
                } else
                {// Раздел не указан
                    return false;
                }
                break;
            case 'category/hide' :
                // Проверка на принадлежность раздела к активному мета-статусу
                if ( ! empty($objid) )
                {// Раздел указан
                    // Поиск раздела
                    $category = $this->dof->storage('achievementcats')->get($objid);
                    if ( empty($category) )
                    {// Раздел не найден в системе
                        return false;
                    }
                    // Получение активных статусов раздела
                    $statuses = $this->dof->workflow('achievementcats')->get_meta_list('active');
                    if ( ! array_key_exists($category->status , $statuses) )
                    {// Статус раздела не является активным
                        return false;
                    }
                    $objid = NULL;
                } else
                {// Раздел не указан
                    return false;
                }
                break;
            case 'category/show' :
                // Проверка на принадлежность к актуальному мета-статусу родительского раздела
                if ( ! empty($objid) )
                {// Раздел указан
                    // Поиск раздела
                    $category = $this->dof->storage('achievementcats')->get($objid);
                    if ( empty($category) )
                    {// Раздел не найден в системе
                        return false;
                    }
                    // Получение акуальных статусов раздела
                    $statuses1 = $this->dof->workflow('achievementcats')->get_meta_list('actual');
                    // Получение активных статусов раздела
                    $statuses2 = $this->dof->workflow('achievementcats')->get_meta_list('active');
                    // Вычисление расхождения массива. Формирования актуальных, но не-активных статусов
                    $statuses = array_diff_key($statuses1, $statuses2);

                    if ( ! array_key_exists($category->status , $statuses) )
                    {// Статус родительского раздела не является активным
                        return false;
                    }
                    $objid = NULL;
                } else
                {// Раздел не указан
                    return false;
                }
                break;
            // Возможность использования категории
            case 'category/use' :
                return $this->dof->storage('achievementcats')->is_access('use', $objid, $userid, null);
                break;
            case 'achievement/create' :
                // Проверка на принадлежность раздела шаблона к реальному мета-статусу
                if ( ! empty($objid) )
                {// Раздел указан
                    // Поиск раздела
                    $category = $this->dof->storage('achievementcats')->get($objid);
                    if ( empty($category) )
                    {// Раздел не найден в системе
                        return false;
                    }
                    // Получение реальных статусов раздела
                    $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
                    if ( ! array_key_exists($category->status , $statuses) )
                    {// Статус родительского раздела не является реальным
                        return false;
                    }
                    $objid = NULL;
                } else
                {// Создание шаблона может происходить только в категории
                    return false;
                }
                break;
            case 'achievement/view' :
                // Проверка на принадлежность шаблона к реальному мета-статусу
                if ( ! empty($objid) )
                {// Шаблон указан
                    // Поиск шаблона
                    $achievement = $this->dof->storage('achievements')->get($objid);
                    if ( empty($achievement) )
                    {// Шаблон не найден в системе
                        return false;
                    }
                    // Получение реальных статусов шаблона
                    $statuses = $this->dof->workflow('achievements')->get_meta_list('real');
                    if ( ! array_key_exists($achievement->status , $statuses) )
                    {// Статус шаблона не является реальным
                        return false;
                    }
                    $objid = NULL;
                } else
                {// Шаблон не указан
                    return false;
                }
                break;
            case 'achievement/edit' :
                // Проверка на принадлежность шаблона к реальному мета-статусу
                if ( ! empty($objid) )
                {// Шаблон указан
                    // Поиск шаблона
                    $achievement = $this->dof->storage('achievements')->get($objid);
                    if ( empty($achievement) )
                    {// Шаблон не найден в системе
                        return false;
                    }
                    // Получение реальных статусов шаблона
                    $statuses = $this->dof->workflow('achievements')->get_meta_list('real');
                    if ( ! array_key_exists($achievement->status , $statuses) )
                    {// Статус шаблона не является реальным
                        return false;
                    }
                    $objid = NULL;
                } else
                {// Шаблон не указан
                    return false;
                }
                break;
            case 'achievement/delete' :
                // Проверка на принадлежность шаблона к реальному мета-статусу
                if ( ! empty($objid) )
                {// Шаблон указан
                    // Поиск шаблона
                    $achievement = $this->dof->storage('achievements')->get($objid);
                    if ( empty($achievement) )
                    {// Шаблон не найден в системе
                        return false;
                    }
                    // Получение реальных статусов шаблона
                    $statuses = $this->dof->workflow('achievements')->get_meta_list('real');
                    if ( ! array_key_exists($achievement->status , $statuses) )
                    {// Статус шаблона не является реальным
                        return false;
                    }

                    $objid = NULL;
                } else
                {// Шаблон не указан
                    return false;
                }
                break;
            case 'achievement/hide' :
                // Проверка на принадлежность шаблона к активному мета-статусу
                if ( ! empty($objid) )
                {// Шаблон указан
                    // Поиск шаблона
                    $achievement = $this->dof->storage('achievements')->get($objid);
                    if ( empty($achievement) )
                    {// Шаблон не найден в системе
                        return false;
                    }
                    // Получение активных статусов шаблона
                    $statuses = $this->dof->workflow('achievements')->get_meta_list('active');
                    if ( ! array_key_exists($achievement->status , $statuses) )
                    {// Статус шаблона не является реальным
                        return false;
                    }

                    $objid = NULL;
                } else
                {// Шаблон не указан
                    return false;
                }
                break;
            case 'achievement/show' :
                if ( ! empty($objid) )
                {// Шаблон указан
                    // Поиск шаблона
                    $category = $this->dof->storage('achievements')->get($objid);
                    if ( empty($category) )
                    {// Шаблон не найден в системе
                        return false;
                    }
                    // Получение акуальных статусов шаблона
                    $statuses1 = $this->dof->workflow('achievements')->get_meta_list('actual');
                    // Получение активных статусов шаблона
                    $statuses2 = $this->dof->workflow('achievements')->get_meta_list('active');
                    // Вычисление расхождения массива. Формирования актуальных, но не-активных статусов
                    $statuses = array_diff_key($statuses1, $statuses2);

                    if ( ! array_key_exists($category->status , $statuses) )
                    {// Статус шаблона не является активным
                        return false;
                    }
                    $objid = NULL;
                } else
                {// Шаблон не указан
                    return false;
                }
                break;
            case 'achievement/use' :
                // Проверка на принадлежность шаблона к активному мета-статусу
                if ( ! empty($objid) )
                {// Шаблон указан
                    // Поиск шаблона
                    $achievement = $this->dof->storage('achievements')->get($objid);
                    if ( empty($achievement) )
                    {// Шаблон не найден в системе
                        return false;
                    }

                    // Получение активных статусов шаблона
                    $statuses = $this->dof->workflow('achievements')->get_meta_list('active');
                    if ( ! array_key_exists($achievement->status , $statuses) )
                    {// Статус шаблона не является реальным
                        return false;
                    }

                    if( ! $achievementcat = $this->dof->storage('achievementcats')->get($achievement->catid) )
                    {// Не получили раздел
                        return false;
                    }
                    if (!$this->dof->storage('achievementcats')->is_access('use:any', null, $userid)
                        && !$this->dof->storage('achievementcats')->is_access('use', $achievement->catid, $userid, $achievementcat->departmentid) )
                    {
                        return false;
                    }
                } else
                {// Шаблон не указан
                    return false;
                }
                break;
            case 'achievementins/create' :
                // Проверка на возможность использовать шаблон
                if ( ! empty($objid) )
                {// Шаблон указан
                    if ( ! $this->is_access('achievement/use', $objid) )
                    {// Шаблон нельзя использовать
                        return false;
                    }

                    return true;
                } else
                {// Шаблон не указан
                    return false;
                }

                break;
            case 'achievementins/view' :

                if (empty($objid))
                {// Достижение не указано
                    return false;
                }

                // Поиск достижения
                $achievementin = $this->dof->storage('achievementins')->get($objid);
                if (empty($achievementin))
                {// Достижение не найдено в системе
                    return false;
                }

                // Получение списка реальных статусов достижения
                $statuses = $this->dof->workflow('achievementins')->get_meta_list('real');
                if (!array_key_exists($achievementin->status , $statuses))
                {// Статус достижения не принадлежит списку реальных статусов
                    return false;
                }

                // Поиск шаблона достижения
                $achievement = $this->dof->storage('achievements')->get($achievementin->achievementid);
                if (empty($achievement))
                {// Шаблон достижения не найден в системе
                    return false;
                }

                // Поиск раздела достижений
                $achievementcat = $this->dof->storage('achievementcats')->get($achievement->catid);
                if (empty($achievementcat))
                {// Раздел достижений не найден в системе
                    return false;
                }

                // Является ли портфолио достижений публичным (доступным всем, даже неавторизованным)
                $publicmy = $this->dof->storage('config')->get_config_value('public_my', 'im', 'achievements', $achievementcat->departmentid);
                $isloggedin = !empty($this->dof->storage('persons')->get_bu());
                if ($publicmy == 1 || ($publicmy == 2 && $isloggedin)) {
                    // настроен доступ к просмотру портфолио в обход прав (всем даже неавторизованным или всем авторизованным)
                    return true;
                }

                // ранее здесь возвращалось true и страница всегда была публичной, игнорируя настройку подразделения public_my
                // а должно было проверять наличие права, иначе зачем оно нужно? вернули проверку прав


                // у достижения в хранилище явно указанного подразделения не имеется
                // ранее подразделение и здесь не определялось вовсе, и как следствие использовалось текущее
                // на странице просмотра портфолио пользователя происходил редирект на подразделение владельца просматриваемого портфолио
                // соовтетственно и право на просмотр достижения всегда проверялось по подразделению владельца достижения
                // теперь укажем это явно, чтобы больше никогда не возникало вопросов, как должно работать это право
                $owner = $this->dof->storage('persons')->get($achievementin->userid);
                if (empty($owner)) {
                    return false;
                }
                $depid = $owner->departmentid;


                break;
            case 'achievementins/edit' :
                if ( ! empty($objid) )
                {// Достижение указано
                    // Поиск достижения
                    $achievementin = $this->dof->storage('achievementins')->get($objid);
                    if ( empty($achievementin) )
                    {// Достижение не найдено в системе
                        return false;
                    }

                    if ( $personid == $achievementin->userid )
                    {// Владелец
                        break;
                    }
                    // Получение реальных статусов достижения
                    $statuses = $this->dof->workflow('achievementins')->get_meta_list('real');
                    if ( ! array_key_exists($achievementin->status , $statuses) )
                    {// Статус шаблона не является активным
                        return false;
                    }

                    $objid = NULL;
                } else
                {// Шаблон не указан
                    return false;
                }
                break;
            case 'achievementins/delete' :
                if ( ! empty($objid) )
                {// Достижение указано
                    // Поиск достижения
                    $achievementin = $this->dof->storage('achievementins')->get($objid);
                    if ( empty($achievementin) )
                    {// Достижение не найдено в системе
                        return false;
                    }

                    // Поддержка ручного удаления достижения
                    if ( ! $this->dof->storage('achievementins')->can_manual_delete($achievementin) )
                    {// Ручное удаление не поддерживается текущим достижением
                        return false;
                    }

                    if ( $personid == $achievementin->userid )
                    {// Владелец
                        break;
                    }
                    // Получение реальных статусов достижения
                    $statuses = $this->dof->workflow('achievementins')->get_meta_list('real');
                    if ( ! array_key_exists($achievementin->status , $statuses) )
                    {// Статус шаблона не является активным
                        return false;
                    }
                    $objid = NULL;
                } else
                {// Достижение не указано
                        return false;
                }
                break;

            // право модерировать в подразделении владельца достижения
            case 'achievementins/moderate' :

                if ( ! empty($objid) )
                {// Достижение указано
                    // Поиск достижения
                    $achievementin = $this->dof->storage('achievementins')->get($objid);
                    if ( empty($achievementin) )
                    {// Достижение не найдено в системе
                        return false;
                    }

                    // Получение реальных статусов достижения
                    $statuses = $this->dof->workflow('achievementins')->get_meta_list('achievement_real');
                    if ( ! array_key_exists($achievementin->status , $statuses) )
                    {// Статус шаблона не является активным
                        return false;
                    }

                    // Получение владельца
                    $owner = $this->dof->storage('persons')->get($achievementin->userid);
                    if ( ! empty($owner) )
                    {// Найден владелец
                        // Проверка на модерацию по подразделению персоны
                        $depid = $owner->departmentid;

                        // Получение конфигурации
                        $system_moderation_enabled = $this->dof->storage('config')->
                            get_config_value('system_moderation_enabled', 'im', 'achievements', $depid);
                        if ( empty($system_moderation_enabled) )
                        {// Подсистема модерации отключена
                            return false;
                        }
                    }
                    $objid = NULL;
                } else
                {// Достижение не указано
                    return false;
                }
                break;
            // право модерировать в подразделении владельца достижения всех персон, кроме себя
            case 'achievementins/moderate_except_myself' :

                if ( ! empty($objid) )
                {// Достижение указано
                    // Поиск достижения
                    $achievementin = $this->dof->storage('achievementins')->get($objid);
                    if ( empty($achievementin) )
                    {// Достижение не найдено в системе
                        return false;
                    }

                    // Получение реальных статусов достижения
                    $statuses = $this->dof->workflow('achievementins')->get_meta_list('achievement_real');
                    if ( ! array_key_exists($achievementin->status , $statuses) )
                    {// Статус шаблона не является активным
                        return false;
                    }

                    // Получение владельца
                    $owner = $this->dof->storage('persons')->get($achievementin->userid);
                    if ( ! empty($owner) )
                    {// Найден владелец

                        if ( $achievementin->userid == $personid )
                        {
                            // право для всех, кроме себя
                            return false;
                        }

                        // Проверка на модерацию по подразделению персоны
                        $depid = $owner->departmentid;

                        // Получение конфигурации
                        $system_moderation_enabled = $this->dof->storage('config')->
                            get_config_value('system_moderation_enabled', 'im', 'achievements', $depid);
                        if ( empty($system_moderation_enabled) )
                        {// Подсистема модерации отключена
                            return false;
                        }
                    }
                    $objid = NULL;
                } else
                {// Достижение не указано
                    return false;
                }
                break;

            // право модерировать категорию достижений
            case 'achievementins/moderate_category':
                if ( ! empty($objid) )
                {
                    // проверка существования категории
                    $achcategory = $this->dof->storage('achievementcats')->get($objid);
                    if ( empty($achcategory) )
                    {
                        return false;
                    }

                    // установка данных для проверки права
                    $depid = $achcategory->departmentid;
                    $objid = $achcategory->id;
                } else
                {
                    // право модерирования по всем категориям и всем достижениям
                    $depid = null;
                    $objid = null;
                }
                break;
            default:
                break;
        }

        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);

        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
            return true;
        }
        return false;
    }

    /**
	 * Требует наличия полномочия на совершение действий
	 *
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта,
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     *
     * @return bool
     *              true - можно выполнить указанное действие по
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "achievements/{$do} (block/dof/im/achievements: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }

    /**
     * Обработать событие
     *
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр
     * @param mixed $mixedvar - дополнительные параметры
     *
     * @return bool - true в случае выполнения без ошибок
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        if ( $gentype == 'im' AND $gencode == 'my' AND $eventcode == 'info' )
        {// Отображение ссылки на странице MY
            $sections = [];
            if ( $this->get_section('main_menu') )
            {//
                $sections[] = [
                                'im' => $this->code(),
                                'name' => 'main_menu',
                                'id' => 1,
                                'title' => $this->dof->get_string('title', $this->code())
                ];
            }
            return $sections;
        }

        return false;
    }

    /**
     * Запустить обработку периодических процессов
     *
     * @param int $loan - нагрузка (
     *              1 - только срочные,
     *              2 - нормальный режим,
     *              3 - ресурсоемкие операции
     *        )
     * @param int $messages - количество отображаемых сообщений (
     *              0 - не выводить,
     *              1 - статистика,
     *              2 - индикатор,
     *              3 - детальная диагностика
     *        )
     *
     * @return bool - true в случае выполнения без ошибок
     */
    public function cron($loan,$messages)
    {
        $result = true;
        return $result;
    }


    /**
     * Получить настройки для плагина
     *
     * @param string $code
     *
     * @return object[]
     */
    public function config_default($code = NULL)
    {
        $config = [];

        // Включение системы модерации
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'system_moderation_enabled';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        // Включение системы рейтинга
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'system_rating_enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Скрывать блок с категорией рейтинга, если баллов нет
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'hide_empty_rating_category_block';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Включение единой таблицы отображения
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'display_single_table';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Включение фильтрации
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'display_filter';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Настройка режима доступа к портфолио
        // начиная с версии текущего плагина 2021051700, дефолтное поведение - доступ к портфолио всем авторизованным
        $obj = new stdClass();
        $obj->type = 'select';
        $obj->code = 'public_my';
        $obj->value = '2';
        $config[$obj->code] = $obj;
        // Записей на странице по умолчанию
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'limitnum_defaultvalue';
        $obj->value = '20';
        $config[$obj->code] = $obj;
        // Поля профиля пользователя moodle для экспорта рейтинга портфолио
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'rating_extrafields';
        $obj->value = '[
            {
                "caption":"'.$this->dof->get_string('report_rating_lastname','achievements').'",
                "code":"dofperson_lastname"
            },
            {
                "caption":"'.$this->dof->get_string('report_rating_firstname','achievements').'",
                "code":"dofperson_firstname"
            },
            {
                "caption":"'.$this->dof->get_string('report_rating_middlename','achievements').'",
                "code":"dofperson_middlename"
            }
        ]';
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'select';
        $obj->code = 'achievements_display_mode';
        $obj->value = 'blocks';
        $config[$obj->code] = $obj;

        //НАСТРОЙКИ СЛАЙДОВ
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slide_image_title';
        $obj->value = '';
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slide_image_description';
        $obj->value = '';
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slide_image_captionalign';
        $obj->value = 'left';
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slide_image_summary';
        $obj->value = '';
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slide_image_captiontop';
        $obj->value = 2;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slide_image_captionright';
        $obj->value = 20;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slide_image_captionbottom';
        $obj->value = 2;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slide_image_captionleft';
        $obj->value = 8;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slide_image_parallax';
        $obj->value = 0;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slide_image_backgroundpositiontop';
        $obj->value = 50;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'slide_image_zoomview';
        $obj->value = 0;
        $config[$obj->code] = $obj;

        //НАСТРОЙКИ СЛАЙДЕРА
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slider_height';
        $obj->value = 18;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'slider_proportionalheight';
        $obj->value = 1;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'select';
        $obj->code = 'slider_slidetype';
        $obj->value = 'fadein';
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'slider_slidescroll';
        $obj->value = 0;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'slider_slidespeed';
        $obj->value = 5;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'slider_navigation';
        $obj->value = 1;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'select';
        $obj->code = 'slider_arrowtype';
        $obj->value = 'thin';
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'slider_navigationpoints';
        $obj->value = 1;
        $config[$obj->code] = $obj;

        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'slider_enabled';
        $obj->value = 0;
        $config[$obj->code] = $obj;

        return $config;
    }

    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     *
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр
     * @param mixed $mixedvar - дополнительные параметры
     *
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }

    /**
     * Конструктор
     *
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************

    /**
     * Возвращает текст для отображения в блоке на странице dof
     *
     * @param string $name - Название набора текстов для отображания
     * @param array $options - Дополнительный параметры
     *
     * @return string - HTML-код содержимого блока
     */
    public function get_block($name, $id = NULL, $options = [] )
    {
        $html = '';
        switch ($name)
        {
            case 'page_main_name':
                $html = "<a href='{$this->dof->url_im('achievements','/index.php')}'>"
                    .$this->dof->get_string('page_main_name')."</a>";
            default:
                break;
        }
        return $html;
    }

    /**
     * Возвращает html-код, который отображается внутри секции
     *
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     *
     * @return string  - html-код содержимого секции секции
     */
    public function get_section($name, $id = 0)
    {
        // Инициализируем генератор HTML
        $this->dof->modlib('widgets')->html_writer();
        $html = '';
        switch ($name)
        {
            case "main_menu":
                $addvars = [];
                $addvars['departmentid'] = $this->dof->storage('departments')->get_user_default_department();
                if ( $this->dof->im('achievements')->is_access('admnistration', $addvars['departmentid']) )
                {// Доступ к панели управления достижениями
                    // Добавление ссылки на панель администрирования
                    $link = dof_html_writer::link(
                        $this->dof->url_im('achievements', '/admin_panel.php', $addvars),
                        $this->dof->get_string('admin_panel_title', 'achievements'),
                        ['class' => 'btn btn-primary dof_button']
                        );
                    $html .= dof_html_writer::tag('div', $link);
                }

                if ( $this->dof->im('achievements')->is_access('moderation', $addvars['departmentid']) )
                {// Доступ к панели модерации пользовательских достижений
                    // Добавление ссылки на панель модерирования
                    $link = dof_html_writer::link(
                        $this->dof->url_im('achievements', '/moderator_panel.php', $addvars),
                        $this->dof->get_string('moderator_panel_title', 'achievements'),
                        ['class' => 'btn btn-primary dof_button']
                        );
                    $html .= dof_html_writer::tag('div', $link);
                }

                if ( $this->dof->im('achievements')->is_access('my', null, null, $addvars['departmentid']) )
                {// Доступ к панели добавления своих достижений
                    // Добавление ссылки на панель своих достижений
                    $link = dof_html_writer::link(
                        $this->dof->url_im('achievements', '/my.php', $addvars),
                        $this->dof->get_string('my_portfolio', 'achievements'),
                        ['class' => 'btn btn-primary dof_button']
                        );
                    $html .= dof_html_writer::tag('div', $link);
                }
                break;
            case 'my_portfolio' :
                $this->dof->modlib('nvg')->add_css('im', 'achievements', '/styles/my_portfolio.css?v='.$this->version());
                // Получение целевой персоны
                if ( ! $id )
                {// Установка текущего пользователя в качестве целевой персоны
                    $targetperson = $this->dof->storage('persons')->get_bu(null, true);
                    $targetpersonid = $targetperson->id;
                } else
                {
                    $targetpersonid = $id;
                    $targetperson = $this->dof->storage('persons')->get($targetpersonid);
                }

                // Доступность рейтинга для пользователя
                $showrating = false;
                $togglejs = false;
                if ( ! empty($targetperson) )
                {// Персона найдена
                    // Cостояние подсистемы рейтинга в подразделении персоны
                    $system_rating_enabled = $this->dof->storage('config')->
                        get_config_value('system_rating_enabled', 'im', 'achievements', $targetperson->departmentid);
                    $display_mode = $this->dof->storage('config')->
                        get_config_value('achievements_display_mode', 'im', 'achievements', $targetperson->departmentid);
                    if ( ! empty($system_rating_enabled) )
                    {// Подсистема рейтинга отключена
                        $showrating = true;
                    }
                    if( $display_mode == 'blocks' )
                    {
                        $togglejs = true;
                    }
                }

                if( $togglejs )
                {
                    $this->dof->modlib('nvg')->add_js('im', 'achievements', '/js/achievements_hide_toggle.js', false);
                }

                if ( $showrating )
                {// Отображение рейтинга
                    $html .= dof_html_writer::start_div('block_my_portfolio_rating');

                    // Информация о рейтинге
                    $rating = $this->get_userrating_info($targetpersonid);
                    $points = round($rating->alluserpoints, 2);
                    $rating = $rating->rating;

                    // Формирование рейтинга
                    $html .= dof_html_writer::tag(
                        'h5',
                        '<span>' . $this->dof->get_string('rating_title', 'achievements') . '</span>',
                        ['class' => 'block_my_portfolio_rating_title']
                    );
                    $html .= dof_html_writer::start_div('block_my_portfolio_rating_rating');
                    $html .= dof_html_writer::span(
                        $this->dof->get_string('table_rating_point', 'achievements'),
                        'block_my_portfolio_rating_rating_title'
                    );
                    $html .= dof_html_writer::span(
                        $rating,
                        'block_my_portfolio_rating_rating_value'
                    );
                    $html .= dof_html_writer::end_div();
                    $html .= dof_html_writer::start_div('block_my_portfolio_rating_points');
                    $html .= dof_html_writer::span(
                        $this->dof->get_string('table_rating_num', 'achievements'),
                        'block_my_portfolio_rating_points_title'
                    );
                    $html .= dof_html_writer::span(
                        $points,
                        'block_my_portfolio_rating_points_value'
                    );
                    $html .= dof_html_writer::end_div();

                    $html .= dof_html_writer::end_div();
                }

                if ( ! empty($targetperson) )
                {// Персона найдена
                    $html .= html_writer::start_div('block_my_portfolio_table');
                    $html .= html_writer::tag(
                        'h5',
                        $this->dof->get_string('last_achievements', 'achievements'),
                        ['class' => 'block_my_portfolio_table_title']
                    );
                    $html .= $this->get_clear_myachievementstable($targetpersonid, ['limitnum' => 5]);
                    $html .= html_writer::end_div();
                }

                // Ссылка на портфолио целевой персоны
                $link = dof_html_writer::link(
                    $this->dof->url_im('achievements', '/my.php', ['personid' => $targetpersonid]),
                    $this->dof->get_string('my_achievements', 'achievements'),
                    ['class' => 'btn btn-primary dof_button']
                );
                $html .= dof_html_writer::tag('div', $link, ['class' => 'portfolio-link']);

                break;
            default:
                break;
        }
        return $html;
    }


    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************

    /**
     * Получить список параметров для фунции has_hight()
     *
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        $result->objectid     = $objectid;

        if ( is_null($depid) )
        {// Подразделение не задано - ищем в GET/POST
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( ! $objectid )
        {// Если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }

        return $result;
    }

    /**
     * Проверить права через плагин acl.
     *
     * Функция вынесена сюда, чтобы постоянно не писать
     * длинный вызов и не перечислять все аргументы
     *
     * @param object $acldata - объект с данными для функции storage/acl->has_right()
     *
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right(
                            $acldata->plugintype,
                            $acldata->plugincode,
                            $acldata->code,
                            $acldata->personid,
                            $acldata->departmentid,
                            $acldata->objectid
        );
    }

    /**
     * Задаем права доступа для объектов
     *
     * @return array
     */
    public function acldefault()
    {
        $a = [];
        $a['admnistration'] = [
            // Право доступа к панели администрирования
            'roles' => [
                'manager'
            ]
        ];
        $a['control_panel'] = [
            // Право доступа к панели управления
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        $a['moderation'] = [
            // Право доступа к панели модерирования
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        $a['otslider_view'] = [
            // Право видеть слайдер изображений по достижениям
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student',
                'user'
            ]
        ];
        $a['rating_view'] = [
            // Право доступа к рейтингу пользователей
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        $a['user_rating_view'] = [
            // Право доступа к рейтингу пользователя
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent'
            ]
        ];
        $a['user_rating_view/owner'] = [
            // Право доступа к просмоту своего рейтинга
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        $a['rating_availability_edit'] = [
            // Право изменения уровня доступности рейтинга пользователя
            'roles' => [
                'manager'
            ]
        ];
        $a['rating_availability_edit/owner'] = [
            // Право изменения уровня доступности своего рейтинга
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        $a['rating_export'] = [
            // Право доступа к экспорту рейтинга пользователей
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        $a['my'] = [
            // Право доступа к панели пользовательских достижений
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];

        /* Разделы достижений */
        $a['category/create'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['category/view'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['category/edit'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['category/delete'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['category/hide'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['category/show'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['category/use'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];

        /* Шаблоны достижений */
        $a['achievement/create'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['achievement/view'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['achievement/edit'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['achievement/delete'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['achievement/hide'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['achievement/show'] = [
            'roles' => [
                'manager'
            ]
        ];
        $a['achievement/use'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student',
                'user'
            ]
        ];

        /* Пользовательские достижения */
        $a['achievementins/create'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        $a['achievementins/view'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];

        // право модерировать в подразделении владельца достижения
        $a['achievementins/moderate'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        // право модерировать в подразделении владельца достижения всех, кроме себя
        $a['achievementins/moderate_except_myself'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        // право модерировать категорию достижений
        $a['achievementins/moderate_category'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];

        // право просматривать список отчетов
        $a['view_reports'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        // право просматривать сводный отчет по индивидуальным планам развития (по подразделениям)
        $a['view:rtreport/idp_summary'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        // право экспортировать сводный отчет по индивидуальным планам развития (по подразделениям)
        $a['export:rtreport/idp_summary'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        // право просматривать персонализированный очтет по индивидуальным планам развития
        $a['view:rtreport/idp_personalized'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        // право экспортировать персонализированный очтет по индивидуальным планам развития
        $a['export:rtreport/idp_personalized'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];


        return $a;
    }

    // **********************************************
    //              Собственные методы
    // **********************************************

    /**
     * Получить URL к собственным файлам плагина
     *
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     *
     * @return string - путь к папке с плагином
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }

    /**
     * Добавить сообщения на основе GET параметров
     *
     * @return void
     */
    public function messages()
    {
        $access = $this->dof->im('achievements')->is_access('admnistration');
        if ( $access )
        {// Сообщения административной части
            $savesuccess = optional_param('settingssavesuccess', 0, PARAM_INT);
            if ( $savesuccess === 1 )
            {// Сообщение об успешном сохранении настроек
                $this->dof->messages->add(
                    $this->dof->get_string('message_settings_save_suссess', 'achievements'),
                    'message'
                );
            }
            $savesuccess = optional_param('сatsavesuccess', 0, PARAM_INT);
            if ( $savesuccess === 1 )
            {// Сообщение об успешном сохранении раздела
                $this->dof->messages->add(
                    $this->dof->get_string('message_form_achievementcats_edit_save_suссess', 'achievements'),
                    'message'
                );
            }
            $savesuccess = optional_param('catblocksuccess', 0, PARAM_INT);
            if ( $savesuccess === 1 )
            {// Сообщение об успешной блокировке раздела
                $this->dof->messages->add(
                    $this->dof->get_string('message_form_achievementcats_block_suссess', 'achievements'),
                    'message'
                );
            }
            $savesuccess = optional_param('catunblocksuccess', 0, PARAM_INT);
            if ( $savesuccess === 1 )
            {// Сообщение об успешной разблокировке раздела
                $this->dof->messages->add(
                    $this->dof->get_string('message_form_achievementcats_unblock_suссess', 'achievements'),
                    'message'
                );
            }
            $savesuccess = optional_param('catdeletesuccess', 0, PARAM_INT);
            if ( $savesuccess === 1 )
            {// Сообщение об успешном удалении раздела
                $this->dof->messages->add(
                    $this->dof->get_string('message_form_achievementcats_delete_suссess', 'achievements'),
                    'message'
                );
            }
            // Сообщение о сохранении шаблона
            $savesuccess = optional_param('achsavesuccess', 0, PARAM_INT);
            if ( $savesuccess === 1 )
            {
                $this->dof->messages->add(
                    $this->dof->get_string('message_form_achievements_edit_save_suссess', 'achievements'),
                    'message'
                );
            }
            $savesuccess = optional_param('achblocksuccess', 0, PARAM_INT);
            if ( $savesuccess === 1 )
            {// Сообщение об успешной блокировке шаблона
                $this->dof->messages->add(
                    $this->dof->get_string('message_form_achievements_block_suссess', 'achievements'),
                    'message'
                );
            }
            $savesuccess = optional_param('achunblocksuccess', 0, PARAM_INT);
            if ( $savesuccess === 1 )
            {// Сообщение об успешной разблокировке шаблона
                $this->dof->messages->add(
                    $this->dof->get_string('message_form_achievements_unblock_suссess', 'achievements'),
                    'message'
                );
            }
            $savesuccess = optional_param('achdeletesuccess', 0, PARAM_INT);
            if ( $savesuccess === 1 )
            {// Сообщение об успешной блокировке шаблона
                $this->dof->messages->add(
                    $this->dof->get_string('message_form_achievements_delete_suссess', 'achievements'),
                    'message'
                );
            }
            $savesuccess = optional_param('catsortsuccess', null, PARAM_INT);
            if ( $savesuccess === 1 )
            {// Сообщение об успешной блокировке шаблона
                $this->dof->messages->add(
                    $this->dof->get_string('achievementcats_sortresult_success', 'achievements'),
                    'message'
                );
            } elseif ( $savesuccess === 0 )
            {
                $this->dof->messages->add(
                    $this->dof->get_string('achievementcats_sortresult_error', 'achievements'),
                    'error'
                );
            }
        }
        // Сообщения пользовательской части
        // Сообщение о сохранении достижения
        $savesuccess = optional_param('achinsavesuccess', 0, PARAM_INT);
        if ( $savesuccess === 1 )
        {
            $this->dof->messages->add(
                $this->dof->get_string('message_form_achievementins_edit_save_suссess', 'achievements'),
                'message'
            );
        }
        $savesuccess = optional_param('achindeletesuccess', 0, PARAM_INT);
        if ( $savesuccess === 1 )
        {// Сообщение об успешном удалении достижения
            $this->dof->messages->add(
                $this->dof->get_string('message_form_achievementins_delete_suссess', 'achievements'),
                'message'
            );
        }
    }
    /**
     * Напечатать таблицу управления разделами достижений
     *
     * @param array $options - массив параметров для переопределения значений
     *  ['addvars'] - Массив GET-параметров
     *
     * @return string - HTML код таблицы
     */
    public function get_achievementcatstable($options)
    {
        // Базовые параметры параметры
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $html = '';

        // Сформируем массив GET параметров
        if ( isset($options['addvars']) )
        {// Массив передан в опциях
            $addvars = $options['addvars'];
        } else
        {
            $addvars = [];
        }

        // Добавим в массив GET параметров необходимые значения
        if ( ! isset($options['addvars']['parentcat']) )
        {// Добавление родительского раздела
            // ID родительского раздела
            $addvars['parentcat'] = optional_param('parentcat', 0, PARAM_INT);
        }
        if ( ! isset($options['addvars']['departmentid']) )
        {// Добавление подразделения
            // ID подразделения
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }

        // Проверка доступа
        if ( ! $this->dof->im('achievements')->is_access('category/view', $addvars['parentcat']) )
        {// Доступа к просмотру раздела и его дочерних элементов нет
            return $html;
        }

        // Получим родительский элемент
        $parent = $this->dof->storage('achievementcats')->get($addvars['parentcat']);

        // Получим cписок дочерних элементов
        $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $list = $this->dof->storage('achievementcats')->get_categories(
            $addvars['parentcat'],
            [
                'statuses' => $statuses,
                'departmentid' => $addvars['departmentid'],
                'levels' => 1,
                'exclude_subdepartments' => true
            ]
        );

        // Ссылка на добавление раздела
        $addlink = dof_html_writer::link(
                $this->dof->url_im('achievements', '/edit_category.php', $addvars),
                $this->dof->get_string('table_achievementcats_add', 'achievements'),
                ['class' => 'btn btn-primary button']
        );
        $html .= dof_html_writer::tag('div', $addlink);

        // Формируем таблицу
        $table = new stdClass;
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = ["left", "left", "center", "center"];
        $table->size = ["120px", "auto", "110px", "70px"];

        // Шапка таблицы
        $table->head = [
                $this->dof->get_string('table_achievementcats_actions', 'achievements'),
                $this->dof->get_string('table_achievementcats_name', 'achievements'),
                $this->dof->get_string('table_achievementcats_date', 'achievements'),
                $this->dof->get_string('table_achievementcats_status', 'achievements')
        ];

        // Заносим данные
        $table->data = [];
        // Параметры для редактирования
        $somevars = $addvars;
        // Активные статусы разделов
        $catstatuses = $this->dof->workflow('achievements')->get_meta_list('active');

        // Родительcкий раздел
        if ( ! empty($parent) )
        {// Есть родитель

            // Ссылка назад
            $backlinkvars = $addvars;
            $backlinkvars['parentcat'] = $parent->parentid;
            $backlink = dof_html_writer::link(
                    $this->dof->url_im('achievements', '/admin_panel.php', $backlinkvars),
                    $this->dof->get_string('table_achievementcats_back', 'achievements'),
                    ['class' => 'btn btn-primary button']
            );
            $html .= dof_html_writer::div($backlink, 'my-2');

            // Массив данных для таблицы родителя
            $data = [];
            $actions = '';
            $attroptions = [];
            if ( $this->dof->im('achievements')->is_access('category/edit', $parent->id) )
            {// Ссылка на редактирование раздела
                $somevars['id'] = $parent->id;
                $attroptions['title'] = $this->dof->get_string('table_achievementcats_edit', 'achievements');
                $actions .= $this->dof->modlib('ig')->icon(
                    'edit',
                    $this->dof->url_im('achievements', '/edit_category.php', $somevars),
                    $attroptions
                );
            }
            if ( $this->dof->im('achievements')->is_access('category/delete', $parent->id) )
            {// Ссылка на удаление раздела
                $somevars['id'] = $parent->id;
                $attroptions['title'] = $this->dof->get_string('table_achievementcats_delete', 'achievements');
                $actions .= $this->dof->modlib('ig')->icon(
                    'delete',
                    $this->dof->url_im('achievements', '/delete_category.php', $somevars),
                    $attroptions
                );
            }
            if ( $this->dof->im('achievements')->is_access('category/hide', $parent->id) )
            {// Ссылка на установку недоступности раздела
                $somevars['id'] = $parent->id;
                $attroptions['title'] = $this->dof->get_string('table_achievementcats_hide', 'achievements');
                $actions .= $this->dof->modlib('ig')->icon(
                    'minus',
                    $this->dof->url_im('achievements', '/hide_category.php', $somevars),
                    $attroptions
                );
            }
            if ( $this->dof->im('achievements')->is_access('category/show', $parent->id) )
            {// Ссылка на установку доступности раздела
                $somevars['id'] = $parent->id;
                $attroptions['title'] = $this->dof->get_string('table_achievementcats_show', 'achievements');
                $actions .= $this->dof->modlib('ig')->icon(
                    'plus',
                    $this->dof->url_im('achievements', '/hide_category.php', $somevars),
                    $attroptions
                );
            }

            $data[] = $actions;
            $data[] = $parent->name;
            $data[] = dof_userdate($parent->createdate, '%d.%m.%Y', $usertimezone, false);

            if ( array_key_exists($parent->status , $catstatuses) &&
                 ! $this->dof->im('achievements')->is_access('category/use', $parent->id)
               )
            {// Раздел не доступен для использоватния
                $data[] = $this->dof->workflow('achievementcats')->get_name($parent->status).
                          $this->dof->get_string('table_achievementcats_cat_not_used', 'achievements');
            } else
            {
                $data[] = $this->dof->workflow('achievementcats')->get_name($parent->status);
            }

            $table->data[] = $data;

            // HTML таблицы с родителем
            $html .= $this->dof->modlib('widgets')->print_table($table, true);
        }

        $table->data = [];
        foreach ( $list as $item )
        {// Формирование таблицы дочерних разделов

            if ( ! $this->dof->im('achievements')->is_access('category/view', $item->id) )
            {// Доступа к просмотру раздела нет
                continue;
            }

            $attroptions = [];
            // Формируем строку раздела
            $data = [];
            // Действия над разделом
            $cell = '';
            if ( $this->dof->im('achievements')->is_access('category/edit', $item->id) )
            {// Ссылка на редактирование раздела
                $somevars['id'] = $item->id;
                $attroptions['title'] = $this->dof->get_string('table_achievementcats_edit', 'achievements');
                $cell .= $this->dof->modlib('ig')->icon(
                    'edit',
                    $this->dof->url_im('achievements', '/edit_category.php', $somevars),
                    $attroptions
                );
            }
            if ( $this->dof->im('achievements')->is_access('category/delete', $item->id) )
            {// Ссылка на удаление раздела
                $somevars['id'] = $item->id;
                $attroptions['title'] = $this->dof->get_string('table_achievementcats_delete', 'achievements');
                $cell .= $this->dof->modlib('ig')->icon(
                    'delete',
                    $this->dof->url_im('achievements', '/delete_category.php', $somevars),
                    $attroptions
                );
            }
            if ( $this->dof->im('achievements')->is_access('category/hide', $item->id) )
            {// Ссылка на установку недоступности раздела
                $somevars['id'] = $item->id;
                $attroptions['title'] = $this->dof->get_string('table_achievementcats_hide', 'achievements');
                $cell .= $this->dof->modlib('ig')->icon(
                    'minus',
                    $this->dof->url_im('achievements', '/hide_category.php', $somevars),
                    $attroptions
                );
            }
            if ( $this->dof->im('achievements')->is_access('category/show', $item->id) )
            {// Ссылка на установку доступности раздела
                $somevars['id'] = $item->id;
                $attroptions['title'] = $this->dof->get_string('table_achievementcats_show', 'achievements');
                $cell .= $this->dof->modlib('ig')->icon(
                    'plus',
                    $this->dof->url_im('achievements', '/hide_category.php', $somevars),
                    $attroptions
                );
            }

            $data[] = $cell;
            // Переход к разделу
            $addvars['parentcat'] = $item->id;
            $link = dof_html_writer::link($this->dof->url_im('achievements', '/admin_panel.php', $addvars), $item->name);
            $data[] = $link;

            $data[] = dof_userdate($item->createdate, '%d.%m.%Y', $usertimezone, false);

            if ( array_key_exists($item->status , $catstatuses) &&
                 ! $this->dof->im('achievements')->is_access('category/use', $item->id)
               )
            {// Раздел не доступен для использоватния
                $data[] = $this->dof->workflow('achievementcats')->get_name($item->status).
                      $this->dof->get_string('table_achievementcats_cat_not_used', 'achievements');
            } else
            {
                $data[] = $this->dof->workflow('achievementcats')->get_name($item->status);
            }

            $table->data[] = $data;
        }

        if ( ! empty($table->data) )
        {// Есть доступные для отображения строки
            if ( empty($parent) )
            {
                $html .= dof_html_writer::tag('h3', $this->dof->get_string('table_achievementcats_top_cats', 'achievements'));
            } else
            {
                $html .= dof_html_writer::tag('h3', $this->dof->get_string('table_achievementcats_children', 'achievements'));
            }

            if ( ! empty($options['sortform']) )
            {// Передана форма сортировки
                // Добавление формы сортировки в вывод
                $html .= $options['sortform'];
                // Для формы требуется идентификатор таблицы
                $table->id = 'achievementcats';
            }

            $html .= $this->dof->modlib('widgets')->print_table($table, true);
        }

        return $html;
    }

    /**
     * Напечатать таблицу управления шаблонами достижений
     *
     * @param array $options - массив параметров для переопределения значений
     *  ['addvars'] - Массив GET-параметров
     *
     * @return string - HTML код таблицы
     */
    public function get_achievementstable($options)
    {
        // Сформируем массив GET параметров
        if ( isset($options['addvars']) )
        {// Массив передан в опциях
            $addvars = $options['addvars'];
        } else
        {
            $addvars = [];
        }

        // Добавим в массив GET параметров необходимые значения
        if ( ! isset($options['addvars']['parentcat']) )
        {// Добавление родительского раздела
            // ID родительского раздела
            $addvars['parentcat'] = optional_param('parentcat', 0, PARAM_INT);
        }
        if ( ! isset($options['addvars']['departmentid']) )
        {// Добавление подразделения
            // ID подразделения
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }

        // Базовые параметры параметры
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $html = '';
        $system_rating_enabled = $this->dof->storage('config')->
            get_config_value('system_rating_enabled', 'im', 'achievements', $addvars['departmentid']);

        // Получим cписок шаблонов достижений
        $statuses = $this->dof->workflow('achievements')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $list = $this->dof->storage('achievements')->
            get_records(array('catid' => $addvars['parentcat'], 'status' => $statuses));

        if ( ! empty($addvars['parentcat']) )
        {// Ссылка на добавление шаблона
            $addlink = dof_html_writer::link(
                    $this->dof->url_im('achievements', '/edit_achievement.php', $addvars),
                    $this->dof->get_string('table_achievements_add', 'achievements'),
                    ['class' => 'btn btn-primary button']
            );
            $html .= dof_html_writer::tag('div', $addlink);
        }

        // Формируем таблицу
        $table = new stdClass;
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;

        if ( empty($system_rating_enabled) )
        {// Подсистема рейтинга отключена
            $table->align = ["left", "left", "center", "center", "center", "center"];
            $table->size = ["10%", "30%", "20%", "20%", "20%"];
            $table->wrap = [true, true, true, true, true];

            // Шапка таблицы
            $table->head = [
                            $this->dof->get_string('table_achievements_actions', 'achievements'),
                            $this->dof->get_string('table_achievements_name', 'achievements'),
                            $this->dof->get_string('table_achievements_type', 'achievements'),
                            $this->dof->get_string('table_achievements_date', 'achievements'),
                            $this->dof->get_string('table_achievements_status', 'achievements')
            ];
        } else
        {// Подсистема рейтинга включена
            $table->align = ["left", "left", "center", "center", "center", "center"];
            $table->size = ["10%", "30%", "15%", "15%", "15%", "15%"];
            $table->wrap = [true, true, true, true, true, true];

            // Шапка таблицы
            $table->head = [
                            $this->dof->get_string('table_achievements_actions', 'achievements'),
                            $this->dof->get_string('table_achievements_name', 'achievements'),
                            $this->dof->get_string('table_achievements_type', 'achievements'),
                            $this->dof->get_string('table_achievements_date', 'achievements'),
                            $this->dof->get_string('table_achievements_points', 'achievements'),
                            $this->dof->get_string('table_achievements_status', 'achievements')
            ];
        }
        // Заносим данные
        $table->data = [];

        foreach ( $list as $item )
        {// Формирование таблицы шаблонов достижений

            if ( ! $this->dof->im('achievements')->is_access('achievement/view', $item->id) )
            {// Доступа к просмотру шаблона нет
                continue;
            }

            // Формируем строку шаблона
            $data = [];
            $opt = [];

            // Действия над шаблоном
            $cell = '';
            if ( $this->dof->im('achievements')->is_access('achievement/edit', $item->id) )
            {// Ссылка на редактирование шаблона
                $addvars['id'] = $item->id;
                $opt['title'] = $this->dof->get_string('table_achievements_edit', 'achievements');
                $cell .= $this->dof->modlib('ig')->icon(
                        'edit',
                        $this->dof->url_im('achievements', '/edit_achievement.php', $addvars),
                        $opt
                );
            }
            if ( $this->dof->im('achievements')->is_access('achievement/delete', $item->id) )
            {// Ссылка на удаление шаблона
                $addvars['id'] = $item->id;
                $opt['title'] = $this->dof->get_string('table_achievements_delete', 'achievements');
                $cell .= $this->dof->modlib('ig')->icon(
                        'delete',
                        $this->dof->url_im('achievements', '/delete_achievement.php', $addvars),
                        $opt
                );
            }
            if ( $item->status === 'available' &&
                    $this->dof->im('achievements')->is_access('achievement/hide', $item->id) )
            {// Ссылка на установку недоступности шаблона
                $addvars['id'] = $item->id;
                $opt['title'] = $this->dof->get_string('table_achievements_hide', 'achievements');
                $cell .= $this->dof->modlib('ig')->icon(
                        'minus',
                        $this->dof->url_im('achievements', '/hide_achievement.php', $addvars),
                        $opt
                );
            }
            if ( $item->status === 'draft' &&
                    $this->dof->im('achievements')->is_access('achievement/show', $item->id) )
            {// Ссылка на установку доступности шаблона
                $addvars['id'] = $item->id;
                $opt['title'] = $this->dof->get_string('table_achievements_show', 'achievements');
                $cell .= $this->dof->modlib('ig')->icon(
                    'state',
                    $this->dof->url_im('achievements', '/hide_achievement.php', $addvars),
                    $opt
                );
            }
            if ( $item->status === 'notavailable'  &&
                    $this->dof->im('achievements')->is_access('achievement/show', $item->id) )
            {// Ссылка на установку доступности шаблона
                $addvars['id'] = $item->id;
                $opt['title'] = $this->dof->get_string('table_achievements_show', 'achievements');
                $cell .= $this->dof->modlib('ig')->icon(
                        'plus',
                        $this->dof->url_im('achievements', '/hide_achievement.php', $addvars),
                        $opt
                );
            }


            $data[] = $cell;
            $data[] = $item->name;
            $data[] = $item->type;
            $data[] = dof_userdate($item->createdate, '%d.%m.%Y', $usertimezone, false);
            if ( ! empty($system_rating_enabled) )
            {// Подсистема рейтинга включена
                $data[] = $item->points;
            }
            $data[] = $this->dof->workflow('achievements')->get_name($item->status);

            $table->data[] = $data;
        }

        if ( ! empty($table->data) )
        {// Есть доступные для отображения строки

            $html .= dof_html_writer::tag('h3', $this->dof->get_string('table_achievements_title', 'achievements'));

            $html .= $this->dof->modlib('widgets')->print_table($table, true);
        }
        return $html;
    }

    /**
     * Получение пользовательских достижений
     *
     * @param array $options - массив параметров для переопределения значений
     *  ['departmentid'] - ID подразделения
     *  ['personid'] - ID персоны, для которой производится построение таблицы
     *  ['sort'] - Поле , по которому происходит сортировка
     *              Доступные значения:
     *              'points' - По баллу
     *              'status' - По статусу
     *  ['dir'] - Направление сортировки
     *
     * @return string - HTML код таблицы
     */
    public function get_achievementins($options)
    {

        // НОРМАЛИЗАЦИЯ ЗНАЧЕНИЙ
        // Направление сортировки
        if ( ! isset($options['dir']) )
        {
            $options['dir'] = 'ASC';
        }
        if ( $options['dir'] != 'ASC' )
        {
            $options['dir'] = 'DESC';
        }
        // Сортировка
        if ( ! isset($options['sort']) )
        {
            $options['sort'] = NULL;
        } else
        {// Если передана сортировка - добавить в GET параметры ссылок
            $addvars['dir'] = $options['dir'];
            $addvars['sort'] = $options['sort'];
        }
        if ( ! isset($options['departmentid']) )
        {// Добавление подразделения
            // ID подразделения
            $options['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }

        // Определим текущую персону
        if ( isset($options['personid']) )
        {// Персона определена в опциях
            $personid = $options['personid'];
        } else
        {// Текущая персона
            $person = $this->dof->storage('persons')->get_bu();
            if ( isset($person->id) )
            {// Персона определена
                $personid = $person->id;
            } else
            {// Персона не определена
                $personid = 0;
            }
        }

        // ПОЛУЧЕНИЕ КОНФИГУРАЦИИ СИСТЕМЫ
        $person = $this->dof->storage('persons')->get($personid);
        $display_filter = $this->dof->storage('config')->get_config_value('display_filter', 'im', 'achievements', $options['departmentid']);

        // ПОЛУЧЕНИЕ СПИСКА ДОСТИЖЕНИЙ ПОЛЬЗОВАТЕЛЯ С УЧЕТОМ СОРТИРОВКИ И ФИЛЬТРАЦИИ
        $statuses = $this->dof->workflow('achievementins')->get_meta_list('real');

        if ( ! $this->dof->workflow('achievementins')->is_access('view:notavailable', $personid) )
        {// Пользователь не может видеть неподтвержденные достижения
            // Фильтровать все не-активные достижения
            $statuses = $this->dof->workflow('achievementins')->get_meta_list('active');
        }

        // Получение условий по фильтру
        $filter = (array)json_decode($options['filter']);
        // Соритровка
        switch ( $options['sort'] )
        {
            case 'points' :
                $sort =  ' userpoints '.$options['dir'].' ';
                break;
            case 'status' :
                $sort =  ' status '.$options['dir'].' ';
                break;
            default:
                $sort = '';
                break;
        }
        if ( empty($filter) || empty($display_filter))
        {// Фильтра нет
            $statuses = array_keys($statuses);
            $params = ['userid' => $personid, 'status' => $statuses];
        } else
        {// Фильтр определен
            $achievementids = [];
            if ( isset($filter['achievement']) )
            {// Указан шаблон достижения
                $achievementids[] = intval($filter['achievement']);
            } else
            {// Шаблон не указан
                if ( isset($filter['achievement_category']) )
                {// Указан раздел
                    $achievementfilter = $this->dof->storage('achievements')->get_records(['catid' => $filter['achievement_category']]);
                    if ( ! empty($achievementfilter) )
                    {// Добавление всех шаблонов указанного раздела
                        $achievementids = array_keys($achievementfilter);
                    }
                }
            }

            $aifilterfields = [];
            if( ! empty($filter['achievement_createdate_from']) )
            {
                $aifilterfields['createdate_from'] = $filter['achievement_createdate_from'];
            }
            if( ! empty($filter['achievement_createdate_to']) )
            {
                $aifilterfields['createdate_to'] = $filter['achievement_createdate_to'];
            }

            if ( isset($filter['status']) )
            {// Указан статус достижений
                if ( isset($statuses[$filter['status']]) )
                {// Фильтрация по статусу
                    $statuses = [$filter['status'] => ''];
                }
            }
            $statuses = array_keys($statuses);


            $params = ['userid' => $personid];
            if( ! empty($aifilterfields) )
            {
                $filteredachievementins = $this->dof->storage('achievementins')->get_filtered_data($aifilterfields);
                if( ! empty($filteredachievementins) )
                {
                    $params['id'] = array_keys($filteredachievementins);
                } else
                {
                    $params['id'] = 'null';
                }
            }
            if ( ! empty($statuses) )
            {// Доступна фильтрация по статусам
                $params['status'] = $statuses;
            }
            if ( ! empty($achievementids) )
            {// Доступна фильтрация по id шаблона
                $params['achievementid'] = $achievementids;
            }
        }

        // Получение списка пользовательских достижений
        $list = $this->dof->storage('achievementins')->get_records($params, $sort);

        if ( ! empty($list) )
        {
            $templist = $list;
            foreach ( $templist as $id => $item )
            {
                if ( isset($filter['pointsmin']) )
                {
                    if ( $item->userpoints < $filter['pointsmin'] )
                    {
                        unset($list[$id]);
                        continue;
                    }
                }
                if ( isset($filter['pointsmax']) )
                {
                    if ( $item->userpoints > $filter['pointsmax'] )
                    {
                        unset($list[$id]);
                        continue;
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Проверка на возможность просматривать достижения
     *
     * @param stdClass $achievementin - запись достижения из БД
     * @param int|null $achievementcatid - идентификатор категории, содержащей шаблон достижения (если null, будет получено запросом)
     * @throws dof_exception
     * @return boolean[] - ['canmoderate' => boolean, 'canapprove' => boolean]
     */
    public function is_access_view_achievementin($achievementin, $achievementcatid=null) {

        if (empty($achievementin))
        {// Информация по достижению не найдена
            throw new dof_exception('achievementin not found');
        }

        if (is_null($achievementcatid)) {
            // Поиск шаблона достижения
            $achievement = $this->dof->storage('achievements')->get($achievementin->achievementid);
            if (empty($achievement))
            {// Шаблон достижения не найден в системе
                throw new dof_exception('achievement not found');
            }
            $achievementcatid = $achievement->catid;
        }

        $owner = $this->dof->storage('persons')->get($achievementin->userid);
        if (empty($owner)) {
            throw new dof_exception('achievementin owner (person) not found');
        }

        // имеется ли право просматривать достижения (в т.ч. будет да, если настроено публичное портфолио)
        $hasaccessview = $this->is_access('achievementins/view', $achievementin->id);


        // принадлежит ли статус достижения списку реальных статусов достижений
        $achreal = array_key_exists($achievementin->status, $this->dof->workflow('achievementins')->get_meta_list('achievement_real'));
        // является ли модератором в подразделении владельца достижения
        $isdepmoderator = $this->dof->im('achievements')->is_access('moderation', $owner->departmentid);
        // является ли модератором категории
        $iscatmoderator = $this->dof->im('achievements')->is_access('achievementins/moderate_category', $achievementcatid);
        // имеет ли право модерировать достижение
        $isachievemtntmoderator = $this->dof->im('achievements')->is_access('achievementins/moderate', $achievementin->id);
        // имеет ли право модерировать достижение, если оно не его личное
        $exeptmyselfmoderator = $this->dof->im('achievements')->is_access('achievementins/moderate_except_myself', $achievementin->id);
        // комплексное право на модерацию
        $canmoderate = $achreal && ($isdepmoderator || ($iscatmoderator || $isachievemtntmoderator || $exeptmyselfmoderator));


        // имеет ли права на одобрение цели
        $canapprove = $achievementin->status == 'wait_approval' && $this->dof->storage('achievementins')->is_access('approve_goal', $achievementin->id);


        // текущая персона, взаимодействующая с объектами достижений
        $currentperson = $this->dof->storage('persons')->get_bu();
        // является ли текущая персона владельцем достижения
        $isowner = empty($currentperson) ? false : ($owner->id == $currentperson->id);


        // если нет права на просмотр портфолио (прямого или благодаря настройке публичности портфолио)
        // проверяется доп.логика, которая может дать право увидеть достижение, даже если нет прямого права на это
        // если по доп.логике ничего этого нет, то считается, что и просмотр должен быть не доступен
        // по доп.логике просмотр будет доступен, если есть право подтверждать цели или есть право модерировать достижения
        // или если пользователь является владельцем достижения
        if (!$hasaccessview && !$canapprove && !$canmoderate && !$isowner)
        {
            throw new dof_exception('achievementin view is forbidden');
        }

        return ['canmoderate' => $canmoderate, 'canapprove' => $canapprove];
    }

    /**
     * Сформировть таблицу пользовательских достижений
     *
     * @param array $list - массив пользовательских достижений
     * @param array $options - массив параметров для переопределения значений
     *  ['addvars'] - Массив GET-параметров
     *  ['personid'] - ID персоны, для которой производится построение таблицы
     *  ['sort'] - Поле , по которому происходит сортировка
     *              Доступные значения:
     *              'points' - По баллу
     *              'status' - По статусу
     *
     *  ['dir'] - Направление сортировки
     *  ['limitnum'] - Число достижений в таблице
     *  ['limitfrom'] - Смещение
     *
     * @return string - HTML код таблицы
     */
    public function get_achievementinstable($list, $options)
    {
        // Базовые параметры
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $currentperson = $this->dof->storage('persons')->get_bu();
        $html = '';

        // НОРМАЛИЗАЦИЯ ЗНАЧЕНИЙ
        if ( ! isset($options['dir']) )
        {
            $options['dir'] = 'ASC';
        }
        if ( $options['dir'] != 'ASC' )
        {
            $options['dir'] = 'DESC';
        }
        if ( ! isset($options['sort']) )
        {
            $options['sort'] = NULL;
        } else
        {// Если передана сортировка - добавить в GET параметры ссылок
            $addvars['dir'] = $options['dir'];
            $addvars['sort'] = $options['sort'];
        }
        if ( ! isset($options['limitnum']) || $options['limitnum'] < 1 )
        {
            $options['limitnum'] = 50;
        }
        if ( ! isset($options['limitfrom']) || $options['limitfrom'] < 0 )
        {
            $options['limitfrom'] = 1;
        }

        if( empty($options['filter']) )
        {
            $options['filter'] = [];
        }



        // ФОРМИРОВАНИЕ МАССИВА GET ПАРАМЕТРОВ
        if ( isset($options['addvars']) )
        {// Массив передан в опциях
            $addvars = $options['addvars'];
        } else
        {
            $addvars = [];
        }
        if ( ! isset($options['addvars']['departmentid']) )
        {// Добавление подразделения
            // ID подразделения
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        // Определим текущую персону
        if ( isset($options['personid']) )
        {// Персона определена в опциях
            $personid = $options['personid'];
            $addvars['personid'] = $options['personid'];
        } else
        {// Текущая персона
            $person = $currentperson;
            if ( isset($person->id) )
            {// Персона определена
                $personid = $person->id;
            } else
            {// Персона не определена
                $personid = 0;
            }
        }

        // ПОЛУЧЕНИЕ КОНФИГУРАЦИИ СИСТЕМЫ
        $person = $this->dof->storage('persons')->get($personid);

        if (!empty($currentperson)) {
            $display_userrating = $this->dof->im('achievements')->is_access('user_rating_view', $person->id, $currentperson->mdluser);
        } else {
            $display_userrating = false;
        }
        $system_rating_enabled = ( $display_userrating && $this->dof->storage('config')->
            get_config_value('system_rating_enabled', 'im', 'achievements', $addvars['departmentid']) );

        $display_mode = $this->dof->storage('config')->
            get_config_value('achievements_display_mode', 'im', 'achievements', $addvars['departmentid']);
        $display_single_table = $this->dof->storage('config')->
            get_config_value('display_single_table', 'im', 'achievements', $addvars['departmentid']);

        // получение реальных статусов

        $goalrealstatuses = $this->dof->workflow('achievementins')->get_meta_list('goal_real');

        // ПОЛУЧЕНИЕ СРЕЗА ДОСТИЖЕНИЙ
        $list = array_slice($list , $options['limitfrom'] - 1, $options['limitnum'], true);

        // ФОРМИРОВАНИЕ РЕЗУЛЬТИРУЮЩЕГО МАССИВА
        $tabledata = $this->get_achievementins_grouping_data($list);
        if ( empty($tabledata) )
        {// Данные не определены
            return $html;
        }

        // ФОРМИРОВАНИЕ ЗАГОЛОВКОВ ТАБЛИЦЫ С УЧЕТОМ СОРТИРОВКИ
        if ( $options['sort'] == 'points' )
        {
            $addvars['sort']= 'points';
            if ( $options['dir'] == 'ASC' )
            {
                $cell = $this->dof->modlib('ig')->icon(
                        'arrow_down'
                        );
                $addvars['dir']= 'DESC';
            } else
            {
                $cell = $this->dof->modlib('ig')->icon(
                        'arrow_up'
                        );
                $addvars['dir']= 'ASC';
            }
            $pointstitle = '<i>'. $cell.$this->dof->get_string('table_achievementins_points', 'achievements') . '</i>';
        } else
        {
            $addvars['sort']= 'points';
            $addvars['dir']= 'ASC';
            $pointstitle = $this->dof->get_string('table_achievementins_points', 'achievements');
        }
        $sorturl = $this->dof->url_im('achievements', '/my.php', $addvars);
        $pointstitle = dof_html_writer::link($sorturl, $pointstitle);

        if ( $options['sort'] == 'status' )
        {
            $addvars['sort'] = 'status';
            if ( $options['dir'] == 'ASC' )
            {
                $cell = $this->dof->modlib('ig')->icon(
                        'arrow_down'
                        );
                $addvars['dir']= 'DESC';
            } else
            {
                $cell = $this->dof->modlib('ig')->icon(
                        'arrow_up'
                        );
                $addvars['dir']= 'ASC';
            }

            $statustitle = '<i>'. $cell.$this->dof->get_string('table_achievementins_criteria_status', 'achievements') . '</i>';
        } else
        {
            $addvars['sort']= 'status';
            $addvars['dir']= 'ASC';
            $statustitle = $this->dof->get_string('table_achievementins_criteria_status', 'achievements');
        }
        $sorturl = $this->dof->url_im('achievements', '/my.php', $addvars);
        $statustitle = dof_html_writer::link($sorturl, $statustitle);

        $sortedachievementcats = [];
        foreach (array_keys($tabledata) as $achievementcatid)
        {
            $sortedachievementcats[$achievementcatid] = $this->dof->storage('achievementcats')->get_sortorder_fullpath($achievementcatid);
        }
        asort($sortedachievementcats);
        $tabledata = array_replace($sortedachievementcats, $tabledata);

        if( $display_mode == 'blocks' )
        {
            $portfolio = '';
            if( ! empty($tabledata) )
            {
                $achievementcat = '';
                foreach($tabledata as $categoryid => $achievements)
                {// Обработка каждой категории
                    if ( empty($achievements) )
                    {// Шаблонов нет
                        continue;
                    }
                    $category = $this->dof->storage('achievementcats')->get($categoryid);
                    $achievementids = array_keys($achievements);
                    $achievementsuserrating = $this->get_userrating_info($personid, $achievementids);

                    $achievementcatinner = '';

                    $achievementcatscorepointsinner = '';
                    $achievementcatscorepointsinner .= dof_html_writer::div($this->dof->get_string('table_achievements_points', 'achievements'), 'achievementcat-score-points-label');
                    $achievementcatscorepointsinner .= dof_html_writer::div('', 'achievementcat-score-points-divider');
                    $achievementcatscorepointsinner .= dof_html_writer::div($achievementsuserrating->alluserpoints, 'achievementcat-score-points-value');
                    $achievementcatscorepoints = dof_html_writer::div($achievementcatscorepointsinner, 'achievementcat-score-points');

                    $achievementcatheaderinner = '';
                    $achievementcatheaderinner .= dof_html_writer::div(count($achievements, COUNT_RECURSIVE) - count($achievements), 'achievementcat-counter');
                    $achievementcatheaderinner .= dof_html_writer::div('', 'achievementcat-divider');
                    $achievementcatheaderinner .= dof_html_writer::div('', 'achievementcat-switcher', ['data-state' => 'expanded']);
                    $achievementcatheaderinner .= dof_html_writer::div($category->name, 'achievementcat-name');
                    $achievementcatheaderinner .= $achievementcatscorepoints;
                    $achievementcatheader = dof_html_writer::div($achievementcatheaderinner, 'achievementcat-header');
                    $achievementcatinner .= $achievementcatheader;

                    $achievementinsinner = '';

                    foreach($achievements as $achievementid => $achievementins)
                    {// Обработка каждого шаблона
                        if( empty($achievementins) )
                        {// Достижений не найдено
                            continue;
                        }
                        $achievement = $this->dof->storage('achievements')->get($achievementid);

                        // получение объекта класса шаблона
                        $achievementobj = $this->dof->storage('achievements')->object($achievementid);

                        if( ! $display_single_table )
                        {
                            $achievementinsinner .= dof_html_writer::div($achievement->name, 'achievement-name');
                        }

                        foreach($achievementins as $achievementinid => $item)
                        {// Обработка каждого достижения

                            // Проверка на возможность просматривать достижения
                            try {
                                list('canmoderate' => $canmoderate, ) = $this->is_access_view_achievementin($item, $category->id);
                            } catch(dof_exception $ex) {
                                continue;
                            }

                            // флаг о том, что достижение было в одном из статусов цели
                            $hasgoalstatus = false;
                            if ( $this->dof->storage('statushistory')->has_status('storage', 'achievementins', $item->id, array_keys($goalrealstatuses)) )
                            {
                                $hasgoalstatus = true;
                            }


                            $opts = [];
                            $opts['rating_enabled'] = (bool)($system_rating_enabled);
                            $opts['moderation_enabled'] = (bool)($canmoderate);
                            $udata = $this->dof->storage('achievementins')->get_formatted_data($item->id, $opts);

                            $achievementininner = '';

                            if( $display_single_table )
                            {
                                $achievementininner .= dof_html_writer::div($achievement->name, 'achievement-name');
                            }

                            $achievementincardinner = '';
                            $achievementinstoolsinner = '';

                            $achievementinstoolsinfowrapperinner = '';
                            $achievementinstoolsinfowrapperinner .= dof_html_writer::div('', 'achievementins-tools-image');

                            $achievementinstoolsinfowrapperinnerside = '';
                            $achievementinstoolsinfowrapperinnerside .= dof_html_writer::div($this->dof->workflow('achievementins')->get_name($item->status), 'achievementins-tools-status');
                            $achievementinstoolsinfowrapperinnerside .= dof_html_writer::div('', 'achievementins-tools-divider');
                            // если цель, то дата создания
                            // если одобрена, то дата одобрения
                            // если достижение требует подтверждения, то дата достижения цели
                            // если подтверждено, то дату подтверждения
                            // если требует актуализации, то дату последнего изменения
                            $achievementinstoolsinfowrapperinnerside .= dof_html_writer::div(dof_userdate($item->timecreated, '%d.%m.%Y %H:%m', $this->dof->storage('persons')->get_usertimezone_as_number(), false), 'achievementins-tools-date');
                            $achievementinstoolsinfowrapperinner .= dof_html_writer::div($achievementinstoolsinfowrapperinnerside, 'achievementins-tools-statusdate');

                            $achievementinstoolsinfowrapper = dof_html_writer::div($achievementinstoolsinfowrapperinner, 'achievementins-tools-info-wrapper');
                            $achievementinstoolsinner .= $achievementinstoolsinfowrapper;

                            $achievementinstoolsactionswrapperinner = '';
                            //комментарии к достижению
                            $comments = $this->dof->storage('comments')->get_comments_by_object('storage',
                                'achievementins', $item->id, NULL);
                            //право просмотра комментариев
                            $accessviewcomments = $this->dof->storage('achievementins')->is_access('view_comments', $item->id);
                            //право комментирования
                            $accesscreatecomments = $this->dof->storage('achievementins')->is_access('create_comments', $item->id);

                            $addvars['id'] = $item->id;

                            $availablestatuses = array_keys($this->dof->workflow('achievementins')->get_available($item->id));

                            if( ($accessviewcomments && !empty($comments)) || $accesscreatecomments )
                            {
                                $opt['title'] = $this->dof->get_string('table_achievementins_comment', 'achievements');
                                $commentslink = $this->dof->modlib('ig')->icon(
                                    'feedback',
                                    $this->dof->url_im('achievements', '/comment_achievementinst.php', $addvars),
                                    $opt
                                );
                                $achievementinstoolscomments = dof_html_writer::div($commentslink, 'achievementins-tools-comments');
                                $achievementinstoolsactionswrapperinner .= $achievementinstoolscomments;
                            }

                            if( $item->status != 'archived' &&
                                $this->dof->storage('achievementins')->is_access('edit', $item->id) )
                            {// Ссылка на редактирование достижения
                                $opt['title'] = $this->dof->get_string('table_achievementins_edit', 'achievements');
                                $editlink = $this->dof->modlib('ig')->icon(
                                    'edit',
                                    $this->dof->url_im('achievements', '/edit_achievementinst.php', $addvars),
                                    $opt
                                );
                                $achievementinstoolsedit = dof_html_writer::div($editlink, 'achievementins-tools-edit');
                                $achievementinstoolsactionswrapperinner .= $achievementinstoolsedit;
                            }

                            if ( in_array('archived', $availablestatuses) &&
                                $this->dof->storage('achievementins')->is_access('archive', $item->id) )
                            {// Ссылка на архивацию достижения
                                $opt['title'] = $this->dof->get_string('table_achievementins_archive', 'achievements');
                                $archivelink = $this->dof->modlib('ig')->icon(
                                    'archive',
                                    $this->dof->url_im('achievements', '/archive_achievementinst.php', $addvars),
                                    $opt
                                    );
                                $achievementinstoolsarchive = dof_html_writer::div($archivelink, 'achievementins-tools-archive');
                                $achievementinstoolsactionswrapperinner .= $achievementinstoolsarchive;
                            }

                            if ( $this->dof->storage('achievementins')->is_access('delete', $item->id) )
                            {// Ссылка на удаление достижения
                                $opt['title'] = $this->dof->get_string('table_achievementins_delete', 'achievements');
                                $deletelink = $this->dof->modlib('ig')->icon(
                                    'delete',
                                    $this->dof->url_im('achievements', '/delete_achievementinst.php', $addvars),
                                    $opt
                                );
                                $achievementinstoolsdelete = dof_html_writer::div($deletelink, 'achievementins-tools-delete');
                                $achievementinstoolsactionswrapperinner .= $achievementinstoolsdelete;
                            }

                            if ( $item->status == 'notavailable' &&
                                    $canmoderate &&
                                    $hasgoalstatus )
                            {// Ссылка на архивацию достижения
                                $opt['title'] = $this->dof->get_string('table_achievementins_returntogoal', 'achievements');
                                $returntogoallink = $this->dof->modlib('ig')->icon(
                                        'cancelcompletion',
                                        $this->dof->url_im('achievements', '/returntogoal_achievementinst.php', $addvars),
                                        $opt
                                        );
                                $achievementinstoolsreturntogoal = dof_html_writer::div($returntogoallink, 'achievementins-tools-returntogoal');
                                $achievementinstoolsactionswrapperinner .= $achievementinstoolsreturntogoal;
                            }

                            $achievementinstoolsactionswrapperinner .=  dof_html_writer::div('', 'achievementins-tools-divider');

                            if( $item->status == 'wait_approval' &&
                                    $this->dof->storage('achievementins')->is_access('approve_goal', $item->id) )
                            {// Ссылка на одобрение цели
                                $opt['title'] = $this->dof->get_string('table_achievementins_goal_approve', 'achievements');
                                $goalapproveaddvars = $addvars;
                                $goalapproveaddvars['action'] = 'approve';
                                $approvelink = $this->dof->modlib('ig')->icon(
                                    'fromdraft',
                                    $this->dof->url_im('achievements', '/edit_goal.php', $goalapproveaddvars),
                                    $opt
                                );
                                $achievementinstoolsapprove = dof_html_writer::div($approvelink, 'achievementins-tools-approve');
                                $achievementinstoolsactionswrapperinner .= $achievementinstoolsapprove;
                            }


                            if( $item->status == 'wait_completion' &&
                                    ! $achievementobj->is_autocompletion() &&
                                    $this->dof->storage('achievementins')->is_access('achieve_goal', $item->id) )
                            {// Ссылка на достижение цели

                                $opt['title'] = $this->dof->get_string('table_achievementins_goal_achieve', 'achievements');
                                $goalachieveaddvars = $addvars;
                                $goalachieveaddvars['action'] = 'achieve';
                                $achievelink = $this->dof->modlib('ig')->icon(
                                    'submititem',
                                    $this->dof->url_im('achievements', '/edit_goal.php', $goalachieveaddvars),
                                    $opt
                                );
                                $achievementinstoolsachieve = dof_html_writer::div($achievelink, 'achievementins-tools-goalachieve');
                                $achievementinstoolsactionswrapperinner .= $achievementinstoolsachieve;
                            }

                            if( ! empty($udata->stat['tomoderate']) && $canmoderate )
                            {// ссылка на подтверждение всех критериев сразу
                                $opt['title'] = $this->dof->get_string('table_achievementins_confirm_all_criterias', 'achievements');
                                $confirmallcriteriasaddvars = $addvars;
                                $confirmallcriteriasaddvars['confirmall'] = true;
                                $achievelink = $this->dof->modlib('ig')->icon(
                                        'confirmall',
                                        $this->dof->url_im('achievements', '/moderation.php', $confirmallcriteriasaddvars),
                                        $opt
                                        );
                                $achievementinstoolsachieve = dof_html_writer::div($achievelink, 'achievementins-tools-goalachieve');
                                $achievementinstoolsactionswrapperinner .= $achievementinstoolsachieve;
                            }

                            $achievementinstoolsactionswrapper = dof_html_writer::div($achievementinstoolsactionswrapperinner, 'achievementins-tools-actions-wrapper');
                            $achievementinstoolsinner .= $achievementinstoolsactionswrapper;

                            $achievementinstools = dof_html_writer::div($achievementinstoolsinner, 'achievementins-tools');
                            $achievementincardinner .= $achievementinstools;

                            $achievementindatawrapperinner = '';

                            $actionoptions = [];
                            $actionoptions['addvars'] = array_merge($addvars,[
                                'filter' => $options['filter']
                            ]);
                            $actionoptions['rating_enabled'] = (bool)($system_rating_enabled);
                            $actionoptions['moderation_enabled'] = (bool)($canmoderate);


                            if( ! empty($item->goaldeadline) && array_key_exists($item->status, $goalrealstatuses) )
                            {
                                $achievementindatainner = '';

                                $achievementindatalabel = dof_html_writer::div(
                                    $this->dof->get_string('goaldeadline', 'achievements'),
                                    'achievementin-data-label'
                                );
                                $achievementindatalabelwrapper = dof_html_writer::div($achievementindatalabel, 'achievementin-data-label-wrapper');
                                $achievementindatainner .= $achievementindatalabelwrapper;

                                $achievementindatavalue = dof_html_writer::div(
                                    dof_userdate($item->goaldeadline, '%d.%m.%Y', $usertimezone),
                                    'achievementin-data-value'
                                );
                                $achievementindatavaluewrapper = dof_html_writer::div($achievementindatavalue, 'achievementin-data-value-wrapper');
                                $achievementindatainner .= $achievementindatavaluewrapper;

                                $achievementindata = dof_html_writer::div($achievementindatainner, 'achievementin-data');
                                $achievementindatawrapperinner .= $achievementindata;
                            }

                            // Добавление иконок действий к ячейкам
                            if( ! empty($udata->data) )
                            {// Есть строки
                                foreach($udata->data as $rownum => $row)
                                {// Обработка каждой строки
                                    if( ! empty($row) )
                                    {
                                        foreach($row as $cellnum => $cell)
                                        {
                                            if( $item->status != 'archived' && isset( $udata->do[$rownum][$cellnum] ) && is_array($udata->do[$rownum][$cellnum])  )
                                            {// Действия для ячейки определены
                                                foreach ( $udata->do[$rownum][$cellnum] as $action )
                                                {// Добавить иконку с действием по элементу
                                                    $udata->data[$rownum][$cellnum] = $this->instance_render_action_icon($achievementinid, $action, $actionoptions) . $udata->data[$rownum][$cellnum];
                                                }
                                            }
                                            if( ! empty($udata->data[$rownum][$cellnum]) || ! empty($udata->head[$cellnum]) )
                                            {
                                                $achievementindatainner = '';

                                                $achievementindatalabel = dof_html_writer::div($udata->head[$cellnum], 'achievementin-data-label');
                                                $achievementindatalabelwrapper = dof_html_writer::div($achievementindatalabel, 'achievementin-data-label-wrapper');
                                                $achievementindatainner .= $achievementindatalabelwrapper;

                                                $achievementindatavalue = dof_html_writer::div($udata->data[$rownum][$cellnum], 'achievementin-data-value');
                                                $achievementindatavaluewrapper = dof_html_writer::div($achievementindatavalue, 'achievementin-data-value-wrapper');
                                                $achievementindatainner .= $achievementindatavaluewrapper;

                                                $achievementindata = dof_html_writer::div($achievementindatainner, 'achievementin-data');
                                                $achievementindatawrapperinner .= $achievementindata;
                                            }
                                        }
                                    }
                                }
                            }

                            $achievementindatawrapper = dof_html_writer::div($achievementindatawrapperinner, 'achievementin-data-wrapper');
                            $achievementincardinner .= $achievementindatawrapper;

                            $scorepointsvalue = 0;
                            if( ! empty($system_rating_enabled) )
                            {// Подсистема рейтинга включена
                                if( is_null($item->userpoints) )
                                {
                                    $scorepointsvalue = $this->dof->get_string('table_achievementins_userpoints_in_progress', 'achievements');
                                } else
                                {
                                    $scorepointsvalue = $this->points_format($item->userpoints);
                                }
                            }

                            $achievementinscorepointsinner = '';
                            $achievementinscorepointsinner .= dof_html_writer::div($this->dof->get_string('table_achievementins_points', 'achievements'), 'achievementin-score-points-label');
                            $achievementinscorepointsinner .= dof_html_writer::div('', 'achievementin-score-points-divider');
                            $achievementinscorepointsinner .= dof_html_writer::div($scorepointsvalue, 'achievementin-score-points-value');

                            $achievementinscorepoints = dof_html_writer::div($achievementinscorepointsinner, 'achievementin-score-points');
                            $achievementincardinner .= $achievementinscorepoints;

                            $achievementincard = dof_html_writer::div($achievementincardinner, 'achievementin-card');
                            $achievementininner .= $achievementincard;

                            // если достижение было или является целью, то используем другие иконки
                            $status = empty($hasgoalstatus) ? $item->status : $item->status . '-goal';

                            $achievementin = dof_html_writer::div($achievementininner, 'achievementin', ['data-status' => $status]);
                            $achievementinsinner .= $achievementin;
                        }
                        if( ! $display_single_table )
                        {
                            $achievementins = dof_html_writer::div($achievementinsinner, 'achievementins', ['data-achievementins-view' => 'groupped']);
                        }
                    }
                    if( $display_single_table )
                    {
                        $achievementins = dof_html_writer::div($achievementinsinner, 'achievementins', ['data-achievementins-view' => 'groupped']);
                    }
                    $achievementcatinner .= $achievementins;
                    $achievementcat .= dof_html_writer::div($achievementcatinner, 'achievementcat');
                }
                $portfolio .= dof_html_writer::div($achievementcat, 'portfolio');
            }
            $html .= $portfolio;
        } else
        {
            // ФОРМИРОВАНИЕ ТАБЛИЦЫ
            if ( $display_single_table )
            {// Отображение в единой таблице
                // Формируем таблицу
                $table = new stdClass;
                $table->tablealign = "center";
                $table->cellpadding = 0;
                $table->cellspacing = 0;

                // Заносим данные
                $table->data = [];
                $additional_header = [];
                foreach ( $tabledata as $categoryid => $achievements )
                {// Обработка каждой категории
                    if ( empty($achievements) )
                    {// Шаблонов нет
                        continue;
                    }
                    $category = $this->dof->storage('achievementcats')->get($categoryid);
                    $catdisplayed = false;
                    foreach ( $achievements as $achievementid => $achievementins )
                    {// Обработка каждого шаблона
                        if ( empty($achievementins) )
                        {// Достижений не найдено
                            continue;
                        }
                        $achievement = $this->dof->storage('achievements')->get($achievementid);

                        // получение объекта класса шаблона
                        $achievementobj = $this->dof->storage('achievements')->object($achievementid);

                        $achdisplayed = false;
                        foreach ( $achievementins as $achievementinid => $item )
                        {// Обработка каждого достижения

                            // Проверка на возможность просматривать достижения
                            try {
                                list('canmoderate' => $canmoderate, ) = $this->is_access_view_achievementin($item, $category->id);
                            } catch(dof_exception $ex) {
                                continue;
                            }

                            $data = [];

                            if ( empty($catdisplayed) )
                            {// Отображение категории
                                $catdisplayed = true;
                                $data[] = $category->name;
                            } else
                            {
                                $data[] = '';
                            }
                            if ( empty($achdisplayed) )
                            {// Отображение имени шаблона достижения
                                $achdisplayed = true;
                                $data[] = $achievement->name;
                            } else
                            {
                                $data[] = '';
                            }
                            // Поле действия над достижениями
                            $cell = '';
                            //комментарии к достижению
                            $comments = $this->dof->storage('comments')->get_comments_by_object('storage',
                                'achievementins', $item->id, NULL);
                            //право просмотра комментариев
                            $accessviewcomments = $this->dof->storage('achievementins')->is_access('view_comments', $item->id);
                            //право комментирования
                            $accesscreatecomments = $this->dof->storage('achievementins')->is_access('create_comments', $item->id);

                            $availablestatuses = array_keys($this->dof->workflow('achievementins')->get_available($item->id));

                            if ( ($accessviewcomments && !empty($comments)) || $accesscreatecomments )
                            {// Ссылка на редактирование достижения
                                $addvars['id'] = $item->id;
                                $opt['title'] = $this->dof->get_string('table_achievementins_comment', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                    'feedback',
                                    $this->dof->url_im('achievements', '/comment_achievementinst.php', $addvars),
                                    $opt
                                    );
                            }
                            if ( in_array($item->status, ['available', 'notavailable', 'suspend']) &&
                                $this->dof->storage('achievementins')->is_access('edit', $item->id) )
                            {// Ссылка на редактирование достижения
                                $addvars['id'] = $item->id;
                                $opt['title'] = $this->dof->get_string('table_achievementins_edit', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                    'edit',
                                    $this->dof->url_im('achievements', '/edit_achievementinst.php', $addvars),
                                    $opt
                                    );
                            }
                            if ( in_array('archived', $availablestatuses) &&
                                $this->dof->storage('achievementins')->is_access('archive', $item->id) )
                            {// Ссылка на архивацию достижения
                                $addvars['id'] = $item->id;
                                $opt['title'] = $this->dof->get_string('table_achievementins_archive', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                    'archive',
                                    $this->dof->url_im('achievements', '/archive_achievementinst.php', $addvars),
                                    $opt
                                    );
                            }
                            if ( $this->dof->storage('achievementins')->is_access('delete', $item->id) )
                            {// Ссылка на удаление достижения
                                $addvars['id'] = $item->id;
                                $opt['title'] = $this->dof->get_string('table_achievementins_delete', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                    'delete',
                                    $this->dof->url_im('achievements', '/delete_achievementinst.php', $addvars),
                                    $opt
                                    );
                            }

                            // флаг о том, что достижение было в одном из статусов цели
                            $hasgoalstatus = false;
                            if ( $this->dof->storage('statushistory')->has_status('storage', 'achievementins', $item->id, array_keys($goalrealstatuses)) )
                            {
                                $hasgoalstatus = true;
                            }
                            if ( $item->status == 'notavailable' &&
                                    $canmoderate &&
                                    $hasgoalstatus )
                            {// ссылка на отмену достижения
                                $addvars['id'] = $item->id;
                                $opt['title'] = $this->dof->get_string('table_achievementins_returntogoal', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                        'cancelcompletion',
                                        $this->dof->url_im('achievements', '/returntogoal_achievementinst.php', $addvars),
                                        $opt
                                        );
                            }

                            if( $item->status == 'wait_approval' &&
                                    $this->dof->storage('achievementins')->is_access('approve_goal', $item->id) )
                            {// Ссылка на одобрение цели
                                $addvars['id'] = $item->id;
                                $addvars['action'] = 'approve';
                                $opt['title'] = $this->dof->get_string('table_achievementins_goal_approve', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                        'fromdraft',
                                        $this->dof->url_im('achievements', '/edit_goal.php', $addvars),
                                        $opt
                                        );
                            }


                            if( $item->status == 'wait_completion' &&
                                    ! $achievementobj->is_autocompletion() &&
                                    $this->dof->storage('achievementins')->is_access('achieve_goal', $item->id) )
                            {// Ссылка на достижение цели
                                $addvars['id'] = $item->id;
                                $addvars['action'] = 'achieve';
                                $opt['title'] = $this->dof->get_string('table_achievementins_goal_achieve', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                        'submititem',
                                        $this->dof->url_im('achievements', '/edit_goal.php', $addvars),
                                        $opt
                                        );
                            }

                            if( ! empty($udata->stat['tomoderate']) && $canmoderate )
                            {// ссылка на подтверждение всех критериев сразу
                                $addvars['id'] = $item->id;
                                $addvars['confirmall'] = true;
                                $opt['title'] = $this->dof->get_string('table_achievementins_confirm_all_criterias', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                        'confirmall',
                                        $this->dof->url_im('achievements', '/moderation.php', $addvars),
                                        $opt
                                        );
                            }
                            $data[] = $cell;

                            $opts = [];
                            $opts['rating_enabled'] = (bool)($system_rating_enabled);
                            $opts['moderation_enabled'] = (bool)($canmoderate);
                            $udata = $this->dof->storage('achievementins')->get_formatted_data($item->id, $opts);

                            if ( empty($additional_header) )
                            {// Дополнительные заголовки не определены
                                $additional_header = $udata->head;
                            }

                            $actionoptions = [];
                            $actionoptions['addvars'] = array_merge($addvars,[
                                'filter' => $options['filter']
                            ]);
                            $actionoptions['rating_enabled'] = (bool)($system_rating_enabled);
                            $actionoptions['moderation_enabled'] = (bool)($canmoderate);
                            // Добавление иконок действий к ячейкам
                            if ( ! empty($udata->data) )
                            {// Есть строки
                            foreach ( $udata->data as $rownum => $row )
                            {// Обраюотка каждой строки
                                if ( ! empty($row) )
                                {
                                    foreach ( $row as $cellnum => $cell )
                                    {
                                        if ( $item->status != 'archived' && isset( $udata->do[$rownum][$cellnum] ) && is_array($udata->do[$rownum][$cellnum])  )
                                        {// Действия для ячейки определены
                                            foreach ( $udata->do[$rownum][$cellnum] as $action )
                                            {// Добавить иконку с действием по элементу
                                                $udata->data[$rownum][$cellnum] .= $this->instance_render_action_icon($achievementinid, $action, $actionoptions);
                                            }
                                        }
                                    }
                                }
                                }
                            }
                            $equalheader = array_diff($additional_header, $udata->head);
                            if ( empty($equalheader) && ! empty($udata->data) )
                            {// Набор полей не изменился
                                $tempdata = array_shift($udata->data);
                                $span = count($data);
                                $data = array_merge($data, $tempdata);
                            } else
                            {// Другой набор полей - вывод таблицы
                                $cell = new html_table_cell();
                                $cell->colspan = count($additional_header);
                                $cell->text = $this->dof->modlib('widgets')->print_table($udata, true);
                                $data[] = $cell;
                                $udata = NULL;
                            }

                            if ( ! empty($system_rating_enabled) )
                            {// Подсистема рейтинга включена
                                if ( is_null($item->userpoints) )
                                {
                                    $data[] = $this->dof->get_string('table_achievementins_userpoints_in_progress', 'achievements');
                                } else
                                {
                                    $data[] = $this->points_format($item->userpoints);
                                }
                            }
                            $statusstr = $this->dof->workflow('achievementins')->get_name($item->status);
                            $data[] = $statusstr;
                            $row = new html_table_row($data);
                            $row->attributes['class'] = 'status_'.$item->status;
                            $table->data[] = $row;
                            if ( ! empty($udata->data) )
                            {// Дополнительные строки данных
                                foreach ( $udata->data as $tempdata )
                                {
                                    $table->data[] = array_merge(['', '', ''], $tempdata);
                                }
                            }
                        }
                    }
                }
                if ( empty($additional_header) )
                {// Дополнительных полей нет
                    $additional_header = [''];
                }
                $table->align = ["center", "center", "center"];
                $table->size = ["10%", "10%", "10%"];
                $table->head = [];
                $table->head[] = $this->dof->get_string('table_achievementins_category', 'achievements');
                $table->head[] = $this->dof->get_string('table_achievementins_achievementin', 'achievements');
                $table->head[] = $this->dof->get_string('table_achievementins_actions', 'achievements');
                foreach ( $additional_header as $header )
                {
                    $table->align[] = "center";
                    $table->size[] = "200px";
                    $table->head[] = $header;
                }
                if ( ! empty($system_rating_enabled) )
                {// Подсистема рейтинга включена
                    $table->align = array_merge($table->align, ["center"]);
                    $table->size = array_merge($table->size, ["10%"]);
                    $table->head[] = $this->dof->get_string('table_achievementins_points', 'achievements');
                }
                $table->align = array_merge($table->align, ["center"]);
                $table->size = array_merge($table->size, ["10%"]);
                $table->head[] = $this->dof->get_string('table_achievementins_criteria_status', 'achievements');

                $html .= $this->dof->modlib('widgets')->print_table($table, true);
            } else
            {// Отображение в разделенной таблице
                // Формируем таблицу
                $table = new stdClass;
                $table->tablealign = "center";
                $table->cellpadding = 0;
                $table->cellspacing = 0;

                $firstcat = true;
                // Заносим данные
                foreach ( $tabledata as $categoryid => $achievements )
                {// Обработка каждой категории
                    if ( empty($achievements) )
                    {// Шаблонов нет
                        continue;
                    }
                    $category = $this->dof->storage('achievementcats')->get($categoryid);

                    // Формирование html категории
                    $categoryhtml = '';
                    foreach ( $achievements as $achievementid => $achievementins )
                    {// Обработка каждого шаблона
                        if ( empty($achievementins) )
                        {// Достижений не найдено
                            continue;
                        }

                        $achievement = $this->dof->storage('achievements')->get($achievementid);

                        // получение объекта класса шаблона
                        $achievementobj = $this->dof->storage('achievements')->object($achievementid);

                        $table->data = [];
                        $additional_header = [];
                        $additional_rows = [];
                        foreach ( $achievementins as $achievementinid => $item )
                        {// Обработка каждого достижения

                            // Проверка на возможность просматривать достижения
                            try {
                                list('canmoderate' => $canmoderate, ) = $this->is_access_view_achievementin($item, $category->id);
                            } catch(dof_exception $ex) {
                                continue;
                            }

                            $data = [];
                            // Поле действия над достижениями
                            $cell = '';

                            //комментарии к достижению
                            $comments = $this->dof->storage('comments')->get_comments_by_object('storage',
                                'achievementins', $item->id, NULL);
                            //право просмотра комментариев
                            $accessviewcomments = $this->dof->storage('achievementins')->is_access('view_comments', $item->id);
                            //право комментирования
                            $accesscreatecomments = $this->dof->storage('achievementins')->is_access('create_comments', $item->id);

                            $availablestatuses = array_keys($this->dof->workflow('achievementins')->get_available($item->id));

                            if ( ($accessviewcomments && !empty($comments)) || $accesscreatecomments )
                            {// Ссылка на редактирование достижения
                                $addvars['id'] = $item->id;
                                $opt['title'] = $this->dof->get_string('table_achievementins_comment', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                    'feedback',
                                    $this->dof->url_im('achievements', '/comment_achievementinst.php', $addvars),
                                    $opt
                                    );
                            }
                            if ( in_array($item->status, ['available', 'notavailable', 'suspend']) &&
                                $this->dof->storage('achievementins')->is_access('edit', $item->id) )
                            {// Ссылка на редактирование достижения
                                $addvars['id'] = $item->id;
                                $opt['title'] = $this->dof->get_string('table_achievementins_edit', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                    'edit',
                                    $this->dof->url_im('achievements', '/edit_achievementinst.php', $addvars),
                                    $opt
                                    );
                            }
                            if ( in_array('archived', $availablestatuses) &&
                                $this->dof->storage('achievementins')->is_access('archive', $item->id) )
                            {// Ссылка на архивацию достижения
                                $addvars['id'] = $item->id;
                                $opt['title'] = $this->dof->get_string('table_achievementins_archive', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                    'archive',
                                    $this->dof->url_im('achievements', '/archive_achievementinst.php', $addvars),
                                    $opt
                                    );
                            }
                            if ( $this->dof->storage('achievementins')->is_access('delete', $item->id) )
                            {// Ссылка на удаление достижения
                                $addvars['id'] = $item->id;
                                $opt['title'] = $this->dof->get_string('table_achievementins_delete', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                    'delete',
                                    $this->dof->url_im('achievements', '/delete_achievementinst.php', $addvars),
                                    $opt
                                    );
                            }

                            // флаг о том, что достижение было в одном из статусов цели
                            $hasgoalstatus = false;
                            if ( $this->dof->storage('statushistory')->has_status('storage', 'achievementins', $item->id, array_keys($goalrealstatuses)) )
                            {
                                $hasgoalstatus = true;
                            }
                            if ( $item->status == 'notavailable' &&
                                    $canmoderate &&
                                    $hasgoalstatus )
                            {// Ссылка на архивацию достижения
                                $addvars['id'] = $item->id;
                                $opt['title'] = $this->dof->get_string('table_achievementins_returntogoal', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                        'cancelcompletion',
                                        $this->dof->url_im('achievements', '/returntogoal_achievementinst.php', $addvars),
                                        $opt
                                        );
                            }

                            if( $item->status == 'wait_approval' &&
                                    $this->dof->storage('achievementins')->is_access('approve_goal', $item->id) )
                            {// Ссылка на одобрение цели
                                $addvars['id'] = $item->id;
                                $addvars['action'] = 'approve';
                                $opt['title'] = $this->dof->get_string('table_achievementins_goal_approve', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                        'fromdraft',
                                        $this->dof->url_im('achievements', '/edit_goal.php', $addvars),
                                        $opt
                                        );
                            }


                            if( $item->status == 'wait_completion' &&
                                    ! $achievementobj->is_autocompletion() &&
                                    $this->dof->storage('achievementins')->is_access('achieve_goal', $item->id) )
                            {// Ссылка на достижение цели
                                $addvars['id'] = $item->id;
                                $addvars['action'] = 'achieve';
                                $opt['title'] = $this->dof->get_string('table_achievementins_goal_achieve', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                        'submititem',
                                        $this->dof->url_im('achievements', '/edit_goal.php', $addvars),
                                        $opt
                                        );
                            }

                            if( ! empty($udata->stat['tomoderate']) && $canmoderate )
                            {// ссылка на подтверждение всех критериев сразу
                                $addvars['id'] = $item->id;
                                $addvars['confirmall'] = true;
                                $opt['title'] = $this->dof->get_string('table_achievementins_confirm_all_criterias', 'achievements');
                                $cell .= $this->dof->modlib('ig')->icon(
                                        'confirmall',
                                        $this->dof->url_im('achievements', '/moderation.php', $addvars),
                                        $opt
                                        );
                            }

                            $data[] = $cell;

                            $opts = [];
                            $opts['rating_enabled'] = (bool)($system_rating_enabled);
                            $opts['moderation_enabled'] = (bool)($canmoderate);
                            $udata = $this->dof->storage('achievementins')->get_formatted_data($item->id, $opts);
                            if ( empty($additional_header) )
                            {
                                $additional_header = $udata->head;
                            }

                            $actionoptions = [];
                            $actionoptions['addvars'] = array_merge($addvars,[
                                'filter' => $options['filter']
                            ]);
                            $actionoptions['rating_enabled'] = (bool)($system_rating_enabled);
                            $actionoptions['moderation_enabled'] = (bool)($canmoderate);
                            // Добавление иконок действий к ячейкам
                            if ( ! empty($udata->data) )
                            {// Есть строки
                                foreach ( $udata->data as $rownum => $row )
                                {// Обраюотка каждой строки
                                    if ( ! empty($row) )
                                    {
                                        foreach ( $row as $cellnum => $cell )
                                        {
                                            if ( $item->status != 'archived' && isset( $udata->do[$rownum][$cellnum] ) && is_array($udata->do[$rownum][$cellnum])  )
                                            {// Действия для ячейки определены
                                                foreach ( $udata->do[$rownum][$cellnum] as $action )
                                                {// Добавить иконку с действием по элементу
                                                    $udata->data[$rownum][$cellnum] .= $this->instance_render_action_icon($achievementinid, $action, $actionoptions);
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $equalheader = array_diff($additional_header, $udata->head);
                            if ( empty($equalheader) && ! empty($udata->data) )
                            {// Набор полей не изменился
                                $tempdata = array_shift($udata->data);
                                $span = count($data);
                                $data = array_merge($data, $tempdata);
                            } else
                            {
                                $cell = new html_table_cell();
                                $cell->colspan = count($udata->head);
                                $cell->text = $this->dof->modlib('widgets')->print_table($udata, true);
                                $data[] = $cell;
                                $udata = NULL;
                            }

                            if ( ! empty($system_rating_enabled) )
                            {// Подсистема рейтинга включена
                                if ( is_null($item->userpoints) )
                                {
                                    $data[] = $this->dof->get_string('table_achievementins_userpoints_in_progress', 'achievements');
                                } else
                                {
                                    $data[] = $this->points_format($item->userpoints);
                                }
                            }
                            $statusstr = $this->dof->workflow('achievementins')->get_name($item->status);
                            $data[] = $statusstr;
                            $data[] = dof_userdate($item->timecreated, '%d.%m.%Y %H:%M', $usertimezone);
                            $row = new html_table_row($data);
                            $row->attributes['class'] = 'status_'.$item->status;
                            $table->data[] = $row;
                            if ( ! empty($udata->data) )
                            {// Дополнительные строки данных
                                foreach ( $udata->data as $tempdata )
                                {
                                    $table->data[] = array_merge([''], $tempdata);
                                }
                            }
                        }

                        if ( empty($table->data) )
                        {// Таблица пуста
                            continue;
                        }

                        $table->align = ["center"];
                        $table->size = ["5%"];
                        $table->head = [
                            $this->dof->get_string('table_achievementins_actions', 'achievements')
                        ];
                        if ( empty($additional_header) )
                        {// Дополнительных полей нет
                            $additional_header = [''];
                        }
                        foreach ( $additional_header as $header )
                        {
                            $table->align[] = "center";
                            $table->size = ["200px"];
                            $table->head[] = $header;
                        }
                        if ( ! empty($system_rating_enabled) )
                        {// Подсистема рейтинга включена
                            $table->align = array_merge($table->align, ["center"]);
                            $table->size = array_merge($table->size, ["10%"]);
                            $table->head[] = $pointstitle;
                        }
                        $table->align = array_merge($table->align, ["center", "5%"]);
                        $table->size = array_merge($table->size, ["10%", "5%"]);
                        $table->head[] = $statustitle;
                        $table->head[] = $this->dof->get_string('table_achievementins_createtime', 'achievements');

                        $categoryhtml .= dof_html_writer::tag('h3', $achievement->name);
                        $categoryhtml .= dof_html_writer::start_div('dof_tableachievementswrapper');
                        $categoryhtml .= $this->dof->modlib('widgets')->print_table($table, true);
                        $categoryhtml .= dof_html_writer::end_div();
                    }

                    // Вывод раздела
                    if ( ! empty($categoryhtml) )
                    {// Есть данные по разделу
                        $html .= dof_html_writer::start_div('dof_achievementcat dof_achievementcat'.$category->id);
                        if ( ! empty($system_rating_enabled) )
                        {// Подсистема рейтинга включена
                            $achievementids = array_keys($achievements);
                            $achievementsuserrating = $this->get_userrating_info($personid, $achievementids);
                            $html .= dof_html_writer::tag('h3', $this->dof->get_string('table_achievements_points', 'achievements').
                                ': '.$achievementsuserrating->points, ['style' => 'float: right;', 'class' => 'ratingpoints']);
                        }

                        $catname = dof_html_writer::tag('h2', $category->name);
                        $html .= html_writer::checkbox(
                            'hidetable'.$achievement->id,
                            '',
                            false,
                            $catname,
                            [
                                'class' => 'dof_hidecat dof_hidecat'.$category->id,
                                'style' => 'display: none;'
                            ]
                            );
                        $html .= dof_html_writer::div('', '', ['style' => 'clear: both;']);
                        $html .= html_writer::div($categoryhtml, 'dof_categorycontent');
                        $html .= dof_html_writer::end_div();
                    }
                }
            }
        }

        return $html;
    }

    /**
     * Напечатать таблицу достижений пользователя
     *
     * @param int $personid - ID персоны
     * @param array $options - массив дополнительных параметров
     *      ['limitnum'] - Число выводимых записей
     * @return string - HTML код таблицы
     */
    public function get_clear_myachievementstable($personid, $options = [])
    {
        // Базовые параметры
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $html = '';

        // ПОЛУЧЕНИЕ КОНФИГУРАЦИИ СИСТЕМЫ
        $person = $this->dof->storage('persons')->get($personid);
        $persondepid = 0;
        if ( ! empty($person) )
        {// Персона определена
            $persondepid = $person->departmentid;
        }

        $ismoderator = ( $this->dof->im('achievements')->is_access('moderation', $persondepid) );
        $system_rating_enabled = $this->dof->storage('config')->
            get_config_value('system_rating_enabled', 'im', 'achievements', $persondepid);
        $display_mode = $this->dof->storage('config')->
            get_config_value('achievements_display_mode', 'im', 'achievements', $persondepid);
        $display_single_table = $this->dof->storage('config')->
            get_config_value('display_single_table', 'im', 'achievements', $persondepid);

        // ПОЛУЧЕНИЕ СПИСКА ДОСТИЖЕНИЙ ПОЛЬЗОВАТЕЛЯ С УЧЕТОМ СОРТИРОВКИ И ФИЛЬТРАЦИИ
        $statuses = $this->dof->workflow('achievementins')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $achrealstatuses = $this->dof->workflow('achievementins')->get_meta_list('achievement_real');
        $goalrealstatuses = $this->dof->workflow('achievementins')->get_meta_list('goal_real');

        if ( ! isset($options['limitnum']) )
        {// Лимит не установлен
            $options['limitnum'] = 0;
        }
        $list = $this->dof->storage('achievementins')->
            get_records(['userid' => $personid, 'status' => $statuses], ' timecreated DESC ', '*', 0, $options['limitnum']);

        // ФОРМИРОВАНИЕ РЕЗУЛЬТИРУЮЩЕГО МАССИВА
        $tabledata = $this->get_achievementins_grouping_data($list);

        if ( empty($tabledata) )
        {// Данные не определены
            return $html;
        }

        if( $display_mode == 'blocks' )
        {
            $portfolio = '';
            if( ! empty($tabledata) )
            {
                $achievementcat = '';
                foreach($tabledata as $categoryid => $achievements)
                {// Обработка каждой категории
                    if ( empty($achievements) )
                    {// Шаблонов нет
                        continue;
                    }
                    $category = $this->dof->storage('achievementcats')->get($categoryid);
                    $achievementids = array_keys($achievements);
                    $achievementsuserrating = $this->get_userrating_info($personid, $achievementids);

                    $achievementcatinner = '';

                    $achievementcatscorepointsinner = '';
                    $achievementcatscorepointsinner .= dof_html_writer::div($this->dof->get_string('table_achievements_points', 'achievements'), 'achievementcat-score-points-label');
                    $achievementcatscorepointsinner .= dof_html_writer::div('', 'achievementcat-score-points-divider');
                    $achievementcatscorepointsinner .= dof_html_writer::div($achievementsuserrating->alluserpoints, 'achievementcat-score-points-value');
                    $achievementcatscorepoints = dof_html_writer::div($achievementcatscorepointsinner, 'achievementcat-score-points');

                    $achievementcatheaderinner = '';
                    $achievementcatheaderinner .= dof_html_writer::div(count($achievements, COUNT_RECURSIVE) - count($achievements), 'achievementcat-counter');
                    $achievementcatheaderinner .= dof_html_writer::div('', 'achievementcat-divider');
                    $achievementcatheaderinner .= dof_html_writer::div('', 'achievementcat-switcher', ['data-state' => 'expanded']);
                    $achievementcatheaderinner .= dof_html_writer::div($category->name, 'achievementcat-name');
                    $achievementcatheaderinner .= $achievementcatscorepoints;
                    $achievementcatheader = dof_html_writer::div($achievementcatheaderinner, 'achievementcat-header');
                    $achievementcatinner .= $achievementcatheader;

                    $achievementinsinner = '';

                    foreach($achievements as $achievementid => $achievementins)
                    {// Обработка каждого шаблона
                        if( empty($achievementins) )
                        {// Достижений не найдено
                            continue;
                        }
                        $achievement = $this->dof->storage('achievements')->get($achievementid);

                        if( ! $display_single_table )
                        {
                            $achievementinsinner .= dof_html_writer::div($achievement->name, 'achievement-name');
                        }

                        foreach($achievementins as $achievementinid => $item)
                        {// Обработка каждого достижения

                            // Проверка на возможность просматривать достижение
                            try {
                                $this->is_access_view_achievementin($item, $category->id);
                            } catch(dof_exception $ex) {
                                continue;
                            }

                            // флаг о том, что достижение было в одном из статусов цели
                            $hasgoalstatus = false;
                            if ( $this->dof->storage('statushistory')->has_status('storage', 'achievementins', $item->id, array_keys($goalrealstatuses)) )
                            {
                                $hasgoalstatus = true;
                            }

                            $achievementininner = '';

                            if( $display_single_table )
                            {
                                $achievementininner .= dof_html_writer::div($achievement->name, 'achievement-name');
                            }

                            $achievementincardinner = '';
                            $achievementinstoolsinner = '';

                            $achievementinstoolsinfowrapperinner = '';
                            $achievementinstoolsinfowrapperinner .= dof_html_writer::div('', 'achievementins-tools-image');

                            $achievementinstoolsinfowrapperinnerside = '';
                            $achievementinstoolsinfowrapperinnerside .= dof_html_writer::div($this->dof->workflow('achievementins')->get_name($item->status), 'achievementins-tools-status');
                            $achievementinstoolsinfowrapperinnerside .= dof_html_writer::div('', 'achievementins-tools-divider');
                            // если цель, то дата создания
                            // если одобрена, то дата одобрения
                            // если достижение требует подтверждения, то дата достижения цели
                            // если подтверждено, то дату подтверждения
                            // если требует актуализации, то дату последнего изменения
                            $achievementinstoolsinfowrapperinnerside .= dof_html_writer::div(dof_userdate($item->timecreated, '%d.%m.%Y %H:%m', $this->dof->storage('persons')->get_usertimezone_as_number(), false), 'achievementins-tools-date');
                            $achievementinstoolsinfowrapperinner .= dof_html_writer::div($achievementinstoolsinfowrapperinnerside, 'achievementins-tools-statusdate');

                            $achievementinstoolsinfowrapper = dof_html_writer::div($achievementinstoolsinfowrapperinner, 'achievementins-tools-info-wrapper');
                            $achievementinstoolsinner .= $achievementinstoolsinfowrapper;

                            $achievementinstoolsactionswrapperinner = '';

                            $achievementinstoolsactionswrapper = dof_html_writer::div($achievementinstoolsactionswrapperinner, 'achievementins-tools-actions-wrapper');
                            $achievementinstoolsinner .= $achievementinstoolsactionswrapper;

                            $achievementinstools = dof_html_writer::div($achievementinstoolsinner, 'achievementins-tools');
                            $achievementincardinner .= $achievementinstools;

                            $achievementindatawrapperinner = '';

                            $opts = [];
                            $opts['rating_enabled'] = (bool)($system_rating_enabled);
                            $udata = $this->dof->storage('achievementins')->get_formatted_data($item->id, $opts);

                            // Добавление иконок действий к ячейкам
                            if( ! empty($udata->data) )
                            {// Есть строки
                                foreach($udata->data as $rownum => $row)
                                {// Обработка каждой строки
                                    if( ! empty($row) )
                                    {
                                        foreach($row as $cellnum => $cell)
                                        {
                                            if( ! empty($udata->data[$rownum][$cellnum]) || ! empty($udata->head[$cellnum]) )
                                            {
                                                $achievementindatainner = '';

                                                $achievementindatalabel = dof_html_writer::div($udata->head[$cellnum], 'achievementin-data-label');
                                                $achievementindatalabelwrapper = dof_html_writer::div($achievementindatalabel, 'achievementin-data-label-wrapper');
                                                $achievementindatainner .= $achievementindatalabelwrapper;

                                                $achievementindatavalue = dof_html_writer::div($udata->data[$rownum][$cellnum], 'achievementin-data-value');
                                                $achievementindatavaluewrapper = dof_html_writer::div($achievementindatavalue, 'achievementin-data-value-wrapper');
                                                $achievementindatainner .= $achievementindatavaluewrapper;

                                                $achievementindata = dof_html_writer::div($achievementindatainner, 'achievementin-data');
                                                $achievementindatawrapperinner .= $achievementindata;
                                            }
                                        }
                                    }
                                }
                            }

                            $achievementindatawrapper = dof_html_writer::div($achievementindatawrapperinner, 'achievementin-data-wrapper');
                            $achievementincardinner .= $achievementindatawrapper;

                            if( ! empty($system_rating_enabled) )
                            {// Подсистема рейтинга включена
                                if( is_null($item->userpoints) )
                                {
                                    $scorepointsvalue = $this->dof->get_string('table_achievementins_userpoints_in_progress', 'achievements');
                                } else
                                {
                                    $scorepointsvalue = $this->points_format($item->userpoints);
                                }
                            }

                            $achievementinscorepointsinner = '';
                            $achievementinscorepointsinner .= dof_html_writer::div($this->dof->get_string('table_achievementins_points', 'achievements'), 'achievementin-score-points-label');
                            $achievementinscorepointsinner .= dof_html_writer::div('', 'achievementin-score-points-divider');
                            $achievementinscorepointsinner .= dof_html_writer::div($scorepointsvalue, 'achievementin-score-points-value');

                            $achievementinscorepoints = dof_html_writer::div($achievementinscorepointsinner, 'achievementin-score-points');
                            $achievementincardinner .= $achievementinscorepoints;

                            $achievementincard = dof_html_writer::div($achievementincardinner, 'achievementin-card');
                            $achievementininner .= $achievementincard;

                            // если достижение было или является целью, то используем другие иконки
                            $status = empty($hasgoalstatus) ? $item->status : $item->status . '-goal';

                            $achievementin = dof_html_writer::div($achievementininner, 'achievementin', ['data-status' => $status]);
                            $achievementinsinner .= $achievementin;
                        }
                        if( ! $display_single_table )
                        {
                            $achievementins = dof_html_writer::div($achievementinsinner, 'achievementins', ['data-achievementins-view' => 'groupped']);
                        }
                    }
                    if( $display_single_table )
                    {
                        $achievementins = dof_html_writer::div($achievementinsinner, 'achievementins', ['data-achievementins-view' => 'groupped']);
                    }
                    $achievementcatinner .= $achievementins;
                    $achievementcat .= dof_html_writer::div($achievementcatinner, 'achievementcat');
                }
                $portfolio .= dof_html_writer::div($achievementcat, 'portfolio');
            }
            $html .= $portfolio;
        } elseif( $display_mode == 'table' )
        {
            // Формируем таблицу
            $table = new stdClass;
            $table->tablealign = "center";
            $table->cellpadding = 0;
            $table->cellspacing = 0;

            // Заносим данные
            $table->data = [];
            $additional_header = [];

            foreach ( $tabledata as $categoryid => $achievements )
            {// Обработка каждой категории
                if ( empty($achievements) )
                {// Шаблонов нет
                    continue;
                }
                $category = $this->dof->storage('achievementcats')->get($categoryid);
                $catdisplayed = false;
                foreach ( $achievements as $achievementid => $achievementins )
                {// Обработка каждого шаблона
                    if ( empty($achievementins) )
                    {// Достижений не найдено
                        continue;
                    }
                    $achievement = $this->dof->storage('achievements')->get($achievementid);
                    $achdisplayed = false;
                    foreach ( $achievementins as $achievementinid => $item )
                    {// Обработка каждого достижения

                        // Проверка на возможность просматривать достижение
                        try {
                            $this->is_access_view_achievementin($item, $category->id);
                        } catch(dof_exception $ex) {
                            continue;
                        }

                        $data = [];
                        if ( empty($catdisplayed) )
                        {// Отображение категории
                            $catdisplayed = true;
                            $data[] = $category->name;
                        } else
                        {// Достижение той же категории
                            $data[] = '';
                        }
                        if ( empty($achdisplayed) )
                        {// Отображение имени шаблона достижения
                            $achdisplayed = true;
                            $data[] = $achievement->name;
                        } else
                        {// Продолжение шаблона
                            $data[] = '';
                        }

                        $opts = [];
                        $opts['rating_enabled'] = (bool)($system_rating_enabled);
                        $udata = $this->dof->storage('achievementins')->get_formatted_data($item->id, $opts);
                        if ( empty($additional_header) )
                        {// Дополнительные заголовки не определены
                            $additional_header = $udata->head;
                        }
                        $equalheader = array_diff($additional_header, $udata->head);
                        if ( empty($equalheader) && ! empty($udata->data) )
                        {// Набор полей не изменился
                            $tempdata = array_shift($udata->data);
                            $span = count($data);
                            $data = array_merge($data, $tempdata);
                        } else
                        {// Другой набор полей - вывод таблицы
                            $cell = new html_table_cell();
                            $cell->colspan = count($additional_header);
                            $cell->text = $this->dof->modlib('widgets')->print_table($udata, true);
                            $data[] = $cell;
                            $udata = NULL;
                        }

                        if ( ! empty($system_rating_enabled) )
                        {// Подсистема рейтинга включена
                            if ( is_null($item->userpoints) )
                            {
                                $data[] = $this->dof->get_string('table_achievementins_userpoints_in_progress', 'achievements');
                            } else
                            {
                                $data[] = $this->points_format($item->userpoints);
                            }
                        }
                        $statusstr = $this->dof->workflow('achievementins')->get_name($item->status);
                        $data[] = $statusstr;
                        $table->data[] = $data;
                        if ( ! empty($udata->data) )
                        {// Дополнительные строки данных
                            foreach ( $udata->data as $tempdata )
                            {
                                $table->data[] = array_merge(['', ''], $tempdata);
                            }
                        }
                    }
                }
            }

            if ( empty($additional_header) )
            {// Дополнительных полей нет
                $additional_header = [''];
            }
            $table->align = ["center", "center"];
            $table->size = ["10%", "10%"];
            $table->head = [];
            $table->head[] = $this->dof->get_string('table_achievementins_category', 'achievements');
            $table->head[] = $this->dof->get_string('table_achievementins_achievementin', 'achievements');
            foreach ( $additional_header as $header )
            {
                $table->align[] = "center";
                $table->size[] = "200px";
                $table->head[] = $header;
            }
            if ( ! empty($system_rating_enabled) )
            {// Подсистема рейтинга включена
                $table->align = array_merge($table->align, ["center"]);
                $table->size = array_merge($table->size, ["10%"]);
                $table->head[] = $this->dof->get_string('table_achievementins_points', 'achievements');
            }
            $table->align = array_merge($table->align, ["center"]);
            $table->size = array_merge($table->size, ["10%"]);
            $table->head[] = $this->dof->get_string('table_achievementins_criteria_status', 'achievements');

            $html .= $this->dof->modlib('widgets')->print_table($table, true);
        }

        return $html;
    }

    /**
     * Получить список персон, требуемых модерации
     *
     * @param array $options - массив параметров для переопределения значений
     *  ['addvars'] - Массив GET-параметров
     *
     * @return array - Данные по пользователям
     */
    public function get_moderation_data(&$options)
    {
        // Базовые параметры
        $data = [];
        $list = [];
        $filteredpersons = [];
        // Сформируем массив GET параметров
        if ( isset($options['addvars']) ) {
            // Массив передан в опциях
            $addvars = $options['addvars'];
        } else {
            $addvars = [];
        }
        if ( ! isset($options['addvars']['departmentid']) ) {
            // Добавление подразделения
            // ID подразделения
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        // Получение массива подразделений
        $statuses = $this->dof->workflow('departments')->get_meta_list('active');
        $statuses = array_keys($statuses);
        // Получим дочерние подразделения
        $departments = $this->dof->storage('departments')->get_departments(
            $addvars['departmentid'], ['statuses' => $statuses]
            );
        // Выполним проверку на наличие права control_panel
        $moderationdepartments = [];
        if ($this->dof->im('achievements')->is_access('control_panel', $addvars['departmentid'])) {
            $moderationdepartments[] = $addvars['departmentid'];
        }
        if (!empty($departments)) {
            foreach ( $departments as $department ) {
                if ($this->dof->im('achievements')->is_access('control_panel', $department->id)) {
                    $moderationdepartments[] = (int)$department->id;
                }
            }
        }
        // Подразделений к отображению пользователей нет
        if (empty($moderationdepartments)) {
            return $data;
        };
        // Получение массива персон, достижения которых модератор имеет право подтверждать
        $statuses = $this->dof->workflow('persons')->get_meta_list('active');
        $statuses = array_keys($statuses);
        $persons = $this->dof->storage('persons')->get_records(
            ['status' => $statuses, 'departmentid' => $moderationdepartments],
            '',
            'id,mdluser'
            );
        $personsids = array_keys($persons);

        if ( isset($options['persons']) )
        {// Доступные персоны
            if ( empty($options['persons']) )
            {
                return $data;
            }
            $filteredpersons = array_keys($options['persons']);
            $personsids = array_intersect($personsids, $filteredpersons);
        }
        // Настройки полей
        $fieldssettings = [];
        // Результатирующий массив категорий к отображению
        $filteredcategoriesconfig = [];
        // Для метода get_user_rating_category это значит все категории
        $filtercategory = 0;
        if (!empty($options['additional']['filter'])) {
            $currentfilter = json_decode($options['additional']['filter']);
            if (property_exists($currentfilter, 'achievement_category')) {
                $filtercategory = $currentfilter->achievement_category;
                // Получение категории из конфига являющихся дочерними указанной в фильтре.
                $filteredcategoriesconfig = array_keys(
                    $this->dof->im('achievements')->filtering_config_categories($filtercategory));
                $fieldssettingslist = [
                    'confirmed',
                    'unconfirmed',
                    'approved',
                    'notapproved',
                    'lastcreatedtime',
                    'lastchecktime',
                    'sumalluserpoints',
                    'sumalluserpointsselectedcats',
                    'sumpointsselectedcats',

                    'fieldssettings_points', 'fieldssettings_alluserpoints', 'fieldssettings_childrenamount'
                ];
                // Получение настроек полей
                foreach ($fieldssettingslist as $field) {
                    $fieldsetting = $this->dof->storage('achievementcats')->get_config_value(
                        $filtercategory, $field);
                    if (empty($fieldsetting)) continue;
                    $fieldssettings[$field] = $fieldsetting;
                }
            }
        }
        // Зададим дефолтные настройки полей если отсутствуют
        if (empty($fieldssettings)) {
            $fieldssettings = [
                'confirmed' => 1,
                'unconfirmed' => 1,
                'approved' => 1,
                'notapproved' => 1,
                'lastcreatedtime' => 1
            ];
        }

        // Получение активных статусов
        $statuses_active = $this->dof->workflow('achievementins')->get_meta_list('active');
        $statuses_real = $this->dof->workflow('achievementins')->get_meta_list('real');

        $statuses_achievement_real = $this->dof->workflow('achievementins')->get_meta_list('achievement_real');
        $statuses_moderation = array_diff($statuses_achievement_real, $statuses_active);
        $statuses_notapproved = ['wait_approval' => 'wait_approval', 'fail_approve' => 'fail_approve'];
        $statuses_approved = ['wait_completion' => 'wait_completion'];

        if ( isset($options['achievementins']) )
        {// Доступные персоны
            $list = $options['achievementins'];
        } else {
            $params = ['personids' => $personsids, 'statuses' => array_keys($statuses_real)];
            $list = $this->dof->storage('achievementins')->get_filtered_data($params);
        }
        // Проверим, есть ли право на модерирование и скроем колонку, если нет права
        $canmoderate = $this->dof->im('achievements')->is_access('moderation', $addvars['departmentid']);
        // может одобрять цель
        $canapprove = $this->dof->storage('achievementins')->is_access('approve_goal_by_template', null, null , $addvars['departmentid'])
        || $this->dof->storage('achievementins')->is_access('approve_goal_to_person', null, null, $addvars['departmentid']);

        // уникальные дочерние категории + родители от фильтрованных категорий из конфига
        $allconfigtreecategories =[];
        if (isset($fieldssettings['fieldssettings_childrenamount'])) {
            $allconfigtreecategories = $filteredcategoriesconfig;
            foreach ($filteredcategoriesconfig as $catid) {
                $childcategorieslist = $this->dof->storage('achievementcats')->get_categories_list($catid,0,['metalist' => 'active']);
                $allconfigtreecategories = array_merge($allconfigtreecategories, array_keys($childcategorieslist));
            }
            $allconfigtreecategories = array_unique($allconfigtreecategories);
        }

        // Заполним массив пользователями с счетчиками
        foreach ($personsids as $personid)
        {
            $obj = new stdClass();
            $obj->id = $personid;
            // Количество неодобренных целей
            if ( $canapprove ) {
                if (isset($fieldssettings['notapproved'])) {
                    $obj->notapproved = 0;
                }
            }
            // Количество одобренных целей
            if ( $canapprove ) {
                if (isset($fieldssettings['approved'])) {
                    $obj->approved = 0;
                }
            }
            // Количество неподтвержденных достижений
            if (isset($fieldssettings['unconfirmed']) && $canmoderate) {
                $obj->unconfirmed = 0;
            }
            // Количество подтвержденных достижений
            if (isset($fieldssettings['confirmed'])) {
                $obj->confirmed = 0;
            }
            // Дата последней активности
            if (isset($fieldssettings['lastcreatedtime'])) {
                $obj->lastcreatedtime = 0;
            }
            // Дата последней проверки
            if (isset($fieldssettings['lastchecktime'])) {
                $obj->lastchecktime = 0;
            }
            // Заполним рейтинг пользователей
            if (!empty($filteredcategoriesconfig)) {
                // Итого по выбранным разделам alluserpoint
                $saupsc = 0;
                // Итого по выбранным разделам point (только из рейтинга)
                $spsc = 0;
                foreach ($filteredcategoriesconfig as $catid) {
                    $rating = $this->get_user_rating_category(
                        $personid,
                        $catid,
                        isset($fieldssettings['fieldssettings_childrenamount']) ? true : false
                        );
                    if ( isset($fieldssettings['fieldssettings_points'])
                        || isset($fieldssettings['sumalluserpointsselectedcats']) )
                    {
                        $spsc += $rp = isset($rating->points) ? $rating->points : 0;
                        if ( isset($fieldssettings['fieldssettings_points']) ) {
                            $obj->{'cat_' . $catid . '_points'} = $rp;
                        }
                    }
                    if (isset($fieldssettings['fieldssettings_alluserpoints'])
                        || isset($fieldssettings['sumalluserpointsselectedcats']))
                    {
                        $saupsc += $rap = isset($rating->alluserpoints) ? $rating->alluserpoints : 0;
                        if ( isset($fieldssettings['fieldssettings_alluserpoints']) ) {
                            $obj->{'cat_' . $catid . '_alluserpoints'} = $rap;
                        }
                    }
                }
                // Итого по выбранным разделам alluserpoint
                if (isset($fieldssettings['sumalluserpointsselectedcats'])) {
                    if (isset($fieldssettings['fieldssettings_childrenamount'])) {
                        $obj->sumalluserpointsselectedcats = 0;
                        foreach ($allconfigtreecategories as $treecatid) {
                            $rating = $this->get_user_rating_category($personid, $treecatid, false);
                            $obj->sumalluserpointsselectedcats += isset($rating->alluserpoints) ? $rating->alluserpoints : 0;
                        }
                    } else {
                        $obj->sumalluserpointsselectedcats = $saupsc;
                    }
                }
                // Итого по выбранным разделам point
                if (isset($fieldssettings['sumpointsselectedcats'])) {
                    if (isset($fieldssettings['fieldssettings_childrenamount'])) {
                        $obj->sumpointsselectedcats = 0;
                        foreach ($allconfigtreecategories as $treecatid) {
                            $rating = $this->get_user_rating_category($personid, $treecatid, false);
                            $obj->sumpointsselectedcats += isset($rating->points) ? $rating->points : 0;
                        }
                    } else {
                        $obj->sumpointsselectedcats = $spsc;
                    }
                }
            }
            // Итого по всем разделам
            if (isset($fieldssettings['sumalluserpoints'])) {
                $rating = $this->get_user_rating_category($personid, $filtercategory);
                $obj->sumalluserpoints = isset($rating->alluserpoints) ? $rating->alluserpoints : 0;
            }
            $data[$personid] = $obj;
        }
        // Пользовательские достижения
        foreach ($list as $item)
        {// Формирование таблицы достижений
            if ( ! $this->dof->im('achievements')->is_access('achievementins/view', $item->id) )
            {// Доступа к просмотру достижения нет
                continue;
            }
            // Шаблон
            if ( ! isset($data[$item->userid]) )
            {// Пользователь не в кэше, скорее всего персона в неактивном статусе (удалена?)
                continue;
            }
            if ( property_exists($data[$item->userid], 'confirmed')
                && isset($statuses_active[$item->status]) )
            {
                $data[$item->userid]->confirmed++;
            } elseif ( property_exists($data[$item->userid], 'unconfirmed')
                && isset($statuses_moderation[$item->status]) )
            {
                $data[$item->userid]->unconfirmed++;
            } elseif ( property_exists($data[$item->userid], 'notapproved')
                && isset($statuses_notapproved[$item->status]) )
            {
                $data[$item->userid]->notapproved++;
            } elseif( property_exists($data[$item->userid], 'approved')
                && isset($statuses_approved[$item->status]) )
            {
                $data[$item->userid]->approved++;
            }
            if ( property_exists($data[$item->userid], 'lastcreatedtime')
                && $item->timecreated >= $data[$item->userid]->lastcreatedtime )
            {
                $data[$item->userid]->lastcreatedtime = $item->timecreated;
            }
            if ( property_exists($data[$item->userid], 'lastchecktime')
                && $item->timechecked >= $data[$item->userid]->lastchecktime )
            {
                $data[$item->userid]->lastchecktime = $item->timechecked;
            }
        }
        // Определим поле по которому требуется отсортировать
        $existingfields = array_keys((array)current($data));
        if (empty($options['sort']))
        {
            if ($this->dof->im('achievements')->is_access('control_panel', $addvars['departmentid'])
                && in_array('unconfirmed', $existingfields))
            {
                $options['sort'] = 'unconfirmed';
            } elseif (in_array('confirmed', $existingfields)) {
                $options['sort'] = 'confirmed';
            } elseif (isset($existingfields[1])) {
                $options['sort'] = $existingfields[1];
            } else {
                $options['sort'] = $existingfields[0];
            }
        }
        // Определим направление сортировки
        if (empty($options['direct']) ) {
            $options['direct'] = 'DESC';
        } elseif ($options['direct'] != 'DESC') {
            $options['direct'] = 'ASC';
        } else {
            $options['direct'] = 'DESC';
        }
        // Отсортируем массив данных
        $sort = $options['sort'];
        $direct = $options['direct'];
        usort($data, function ( $a, $b ) use ($sort, $direct) {
            if ( $a->$sort == $b->$sort ) {
                if ( $direct === 'ASC' ) {
                    return ($a->id < $b->id) ? -1 : 1;
                } else {
                    return ($a->id < $b->id) ? 1 : -1;
                }
            }
            if ( $direct === 'ASC' ) {
                return ($a->$sort < $b->$sort) ? -1 : 1;
            } else {
                return ($a->$sort < $b->$sort) ? 1 : -1;
            }
        });

        return $data;
    }

    /**
     * Получить информацию о рейтинге пользователя по разделу или по разделу и всем дочерним разделам
     *
     * @param int $personid
     * @param int $achievementcatid
     * @param bool $childcats - учитывать рейтинг подкатегорий
     * @return stdClass|boolean  - Информация о рейтинге пользователя или false
     */
    public function get_user_rating_category($personid, $achievementcatid, $childcats = true) {
        $catids = [(int)$achievementcatid];
        if ($childcats) {
            $subcats = $this->dof->storage('achievementcats')->get_categories((int)$achievementcatid);
            $catids = array_merge($catids, array_keys($subcats));
        }
        $statuses = $this->dof->workflow('achievements')->get_meta_list('active');

        $achievementids = $this->dof->storage('achievements')->get_records([
            'catid' => $catids,
            'status' => array_keys($statuses)
        ]);
        if( ! empty($achievementids) )
        {
            // Получение данных о рейтинге пользователя
            return $this->get_userrating_info(
                $personid,
                array_keys($achievementids)
                );
        }
        return false;
    }

    /**
     * Напечатать таблицу достижений, требуемых модерации
     *
     * @param array $personachievements - Данные пользователей
     * @param array $options - массив параметров для переопределения значений
     *  ['addvars'] - Массив GET-параметров
     *
     * @return string - HTML код таблицы
     */
    public function get_moderation_table($personachievements, $options)
    {
        // Базовые параметры
        $html = dof_html_writer::tag('h3', $this->dof->get_string('table_moderation_title', 'achievements'));
        $defdirect = 'DESC';
        if (empty($personachievements)) {
            $html .= $this->dof->get_string('empty_data', 'achievements');
            return $html;
        }
        // Сформируем массив GET параметров
        if ( isset($options['addvars']) )
        {// Массив передан в опциях
            $addvars = $options['addvars'];
        } else
        {
            $addvars = [];
        }
        if ( isset($options['additional']) && ! empty($options['additional']) )
        {
            $addvars = array_merge($addvars, $options['additional']);
        }
        if ( ! isset($options['addvars']['departmentid']) )
        {// Добавление подразделения
            // ID подразделения
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        // Формируем таблицу
        $table = new stdClass;
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->wrap = [];
        $table->id = 'moderator_panel_table';

        $tableheadstandardfields = [
            'confirmed', 'unconfirmed', 'approved', 'notapproved', 'lastcreatedtime',
            'lastchecktime', 'sumalluserpointsselectedcats', 'sumpointsselectedcats', 'sumalluserpoints'
        ];
        // первая ячейка таблицы с языковой строкой - имя персоны
        $sellpersonname = new html_table_cell();
        $sellpersonname->rowspan = 2;
        $sellpersonname->header = true;
        $sellpersonname->style = 'vertical-align: middle;';
        $sellpersonname->text = $this->dof->get_string('table_moderation_name', 'achievements');
        // названия стандартных полей (обьединение 2 строк) и названия разделов
        $header_1 = new html_table_row();
        $header_1->cells = [$sellpersonname];
        // пустые ячейки для стандартных балы учавствующие рейтинге и все баллы для каждого раздела
        $header_2 = new html_table_row();
        $header_2->cells = [];
        // определим размеры для первой ячейки далее в переборе
        $table->align = ["left"];

        $prevcatid = null;
        reset($personachievements);
        $fieldsname = array_keys((array)current($personachievements));
        foreach ($fieldsname as $key => $name) {
            // Параметры для сортировки и иконка
            list($direct,$icon) = $this->dof->modlib('ig')->get_icon_sort(
                $name,
                isset($options['sort']) ? $options['sort'] : '',
                isset($options['direct']) ? $options['direct'] : $defdirect,
                $defdirect
                );
            if (in_array($name, $tableheadstandardfields)) {
                $table->align[] = "center";
                // стандартные поля таблицы
                $sellstandard = new html_table_cell();
                $sellstandard->rowspan = 2;
                $sellstandard->style = 'vertical-align: middle;';
                $sellstandard->header = true;
                $sellstandard->text = dof_html_writer::link(
                    $this->dof->url_im(
                        'achievements',
                        '/moderator_panel.php',
                        array_merge($addvars, ['sort' => $name, 'direct' => strtoupper($direct)])
                        ),
                    $this->dof->get_string('table_moderation_' . $name, 'achievements')
                    .dof_html_writer::div($icon, 'sort-indicator')
                    );
                $header_1->cells[] = $sellstandard;
            } else if(preg_match('/cat_([0-9]+)_([a-z]+)/', $name, $matches)) {
                $table->align[] = "center";
                if ($prevcatid != $matches[1]) {
                    // поля категорий
                    $selladition_1 = new html_table_cell();
                    if (isset($fieldsname[$key + 1])
                        && (stripos ($fieldsname[$key + 1], 'cat_' . $matches[1] . '_') === 0))
                    {
                        $selladition_1->colspan = 2;
                    }
                    $selladition_1->header = true;
                    $selladition_1->text = $this->dof->storage('achievementcats')->get($matches[1])->name;
                    $header_1->cells[] = $selladition_1;
                }
                $selladition_2 = new html_table_cell();
                $selladition_2->attributes = ['class' => 'header-cell'];
                $selladition_2->text = dof_html_writer::link(
                    $this->dof->url_im(
                        'achievements',
                        '/moderator_panel.php',
                        array_merge($addvars, ['sort' => $matches[0], 'direct' => strtoupper($direct)])
                        ),
                    $this->dof->get_string('table_moderation_short_' . $matches[2], 'achievements')
                    .dof_html_writer::div($icon, 'sort-indicator')
                    );
                $header_2->cells[] = $selladition_2;
                $prevcatid = $matches[1];
            }
        }
        // Заносим данные
        $table->data = [];
        $table->data[] = $header_1;
        $table->data[] = $header_2;
        foreach ( $personachievements as $obj ) {
            $item = [];
            $fullname = $this->dof->storage('persons')->get_fullname($obj->id);
            $myaddvars = [];
            $myaddvars['personid'] = $obj->id;
            if( ! empty($addvars['filter']) ) {
                $myaddvars['filter'] = $addvars['filter'];
            }
            if( ! empty($addvars['departmentid']) ) {
                $myaddvars['departmentid'] = $addvars['departmentid'];
            }
            $item[] = dof_html_writer::link(
                $this->dof->url_im('achievements', '/my.php', $myaddvars),
                $fullname,
                ['style' => 'white-space: nowrap;']
            );
            foreach ($fieldsname as $name) {
                if (in_array($name, $tableheadstandardfields) || (stripos ($name, 'cat_') === 0)) {
                    if ($name == 'lastcreatedtime') {
                        $item[] = ((!empty($obj->lastcreatedtime)) ? date('d-m-y',$obj->lastcreatedtime) : '-');
                    } elseif ($name == 'lastchecktime') {
                        $item[] = ((!empty($obj->lastchecktime)) ? date('d-m-y',$obj->lastchecktime) : '-');
                    } else {
                        $item[] = $obj->$name;
                    }
                }
            }
            $table->data[] = $item;
        }
        $html .= $this->dof->modlib('widgets')->print_table($table, true);
        return $html;
    }

    /**
     * Напечатать таблицу достижений, требуемых модерации
     *
     * @param array $options - массив параметров для переопределения значений
     *  ['addvars'] - Массив GET-параметров,
     *  ['page'] - Страница,
     *  ['limit'] - Число записей на странице
     *
     * @return string - HTML код таблицы
     */
    public function get_ratingtable($options)
    {
        // Базовые параметры
        $html = '';

        // Получение текущего пользователя
        $currentperson = $this->dof->storage('persons')->get_bu();

        // Сформируем массив GET параметров
        if ( isset($options['addvars']) )
        {// Массив передан в опциях
            $addvars = $options['addvars'];
        } else
        {
            $addvars = [];
        }

        // Нормализация значений
        if ( ! isset($options['addvars']['departmentid']) )
        {// Добавление подразделения
            // ID подразделения
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }

        if ( ! isset($options['limitfrom']) || $options['limitfrom'] < 1 )
        {// Смещение не указано
            $options['limitfrom'] = 1;
        }
        if ( ! isset($options['limitnum']) || $options['limitnum'] < 1 )
        {// Число записей не указано
            $options['limitnum'] = 50;
        }

        // Статусы

        $statuses = $this->get_statuses_for_allpoints();
        $addoptions = ['status' => $statuses];

        if ( isset($options['persons']) )
        {// Указаны персоны, по которым следует построить рейтинг
            $addoptions['persons'] = $options['persons'];
        }
        if ( isset($options['achievementins']) )
        {// Указаны достижения, по которым следует построить рейтинг
            $addoptions['achievementins'] = $options['achievementins'];
        }

        // Получение рейтинга пользователей
        $items = $this->dof->storage('achievementins')->get_rating($options['limitfrom'] - 1, $options['limitnum'], $addoptions);

        if ( empty($items) )
        {// Подразделений нет
            return $html;
        }

        // Формируем таблицу
        $table = new stdClass();
        $table->tablealign = 'center';
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = ['center', 'left', 'left', 'center'];
        $table->size = ['10%', '5%', '85%', '20%'];
        $table->wrap = [true, true, true, true];

        // Шапка таблицы
        $table->head = [
                        $this->dof->get_string('table_rating_point', 'achievements'),
                        '',
                        $this->dof->get_string('table_rating_name', 'achievements'),
                        $this->dof->get_string('table_rating_num', 'achievements')
        ];

        // Заносим данные
        $table->data = [];

        foreach ( $items as $item )
        {
            // Получение права на просмотр рейтинга пользователя
            if (isset($currentperson->mdluser)){
                $display_userrating = $this->dof->im('achievements')->
                    is_access('user_rating_view',
                        $item->userid,
                        $currentperson->mdluser);
            }else{
                $display_userrating = false;
            }
            $row = [];
            $row[] = $item->rating;

            $myaddvars = [];
            $myaddvars['personid'] = $item->userid;
            if( ! empty($addvars['filter']) )
            {
                $myaddvars['filter'] = $addvars['filter'];
            }
            if( ! empty($addvars['departmentid']) )
            {
                $myaddvars['departmentid'] = $addvars['departmentid'];
            }
            $portfoliourl = $this->dof->url_im('achievements', '/my.php', $myaddvars);

            $mdluserid = $this->dof->storage('persons')->get_field($item->userid, 'mdluser');
            $mdluserpicture = $this->dof->modlib('ama')->user($mdluserid)->get_user_picture_html(['size' => '35']);
            $row[] = dof_html_writer::link(
                $portfoliourl,
                $mdluserpicture
            );

            $addvars['personid'] = $item->userid;
            $personfullname = $this->dof->storage('persons')->get_fullname($item->userid);
            $row[] = dof_html_writer::link(
                    $portfoliourl,
                    $personfullname
            );
            if ( $display_userrating )
            {// Есть доступ к просмотру рейтинга пользователя
                $row[] = $this->points_format($item->points);
            } else
            {// Доступа нет
                $row[] = '';
            }

            $table->data[] = $row;
        }

        $html .= dof_html_writer::tag('h3', $this->dof->get_string('table_rating_title', 'achievements'));
        $html .= $this->dof->modlib('widgets')->print_table($table, true);

        return $html;
    }

    /**
     * Получить данные для экспорта
     *
     * @param array $options - массив параметров для переопределения значений
     *  ['getratingoptions'] - Массив опция для выборки рейтинга
     *      ['persons'] - Пользователи для выборки рейтинга,
     *      ['achievementins'] - Достижения для выборки рейтинга
     *  ['additionaloptions'] - Массив дополнительных опций
     *      ['achievement_category'] - категория, по которой формируется выборка
     *
     * @return object - объект с данными для экспорта
     */
    public function get_rating_exportdata($options)
    {
        // Получение текущего пользователя
        $currentperson = $this->dof->storage('persons')->get_bu();
        // Получение текущего подразделения
        $departmentid = optional_param('departmentid', 0, PARAM_INT);
        // Получение дополнительных полей для отображения в экспортируемом рейтинге
        $extrafieldsjson = $this->dof->storage('config')->get_config_value(
            'rating_extrafields',
            'im',
            'achievements',
            $departmentid
        );
        $extrafields = json_decode($extrafieldsjson);

        // Отчет по рейтингу портфолио
        $report = new stdClass();

        // Массив строк экспортируемого отчета
        $report->table = [];

        // Строка с информацией о названии категории рейтинга
        if( ! empty($options['additionaloptions']['achievement_category']) )
        {
            $category = $this->dof->storage('achievementcats')->get(
                $options['additionaloptions']['achievement_category']
            );
            if( ! empty($category) )
            {
                $report->table[] = [
                    'report_rating_category_caption' => $this->dof->get_string('report_rating_category','achievements'),
                    'report_rating_category' => $category->name
                ];
            }
        }
        // Строка с информацией о дате формирования отчета
        $report->table[] = [
            'report_rating_date_caption' => $this->dof->get_string('report_rating_date','achievements'),
            'report_rating_date' => userdate(time(),'%d.%m.%Y %H:%M')
        ];

        // Строка заголовков основной таблицы
        $headerrows = [];
        // Место в рейтинге
        $headerrows['report_rating_position_caption'] = $this->dof->get_string('report_rating_position','achievements');
        if( ! empty($extrafields) && is_array($extrafields) )
        {
            // Дополнительные поля, задающиеся через настройку rating_extrafields
            foreach( $extrafields as $extrafield )
            {

                $headerrows[$extrafield->code.'_caption'] = $extrafield->caption;
            }
        }
        // Баллы в портфолио
        $headerrows['report_rating_sumpoints_caption'] = $this->dof->get_string('report_rating_sumpoints', 'achievements');
        // Строка с заголовками
        $report->table[] = $headerrows;



        // Массив с опциями для выполнения функции получения данных по рейтингу
        $getratingoptions = [];
        // Статусы
        $statuses = $this->dof->workflow('achievementins')->get_meta_list('active');
        $getratingoptions['status'] = array_keys($statuses);
        if ( isset($options['getratingoptions']['persons']) )
        {// Указаны персоны, по которым следует построить рейтинг
            $getratingoptions['persons'] = $options['getratingoptions']['persons'];
        }
        if ( isset($options['getratingoptions']['achievementins']) )
        {// Указаны достижения, по которым следует построить рейтинг
            $getratingoptions['achievementins'] = $options['getratingoptions']['achievementins'];
        }
        // Получение рейтинга пользователей
        $items = $this->dof->storage('achievementins')->get_rating(0, NULL, $getratingoptions);


        // В рейтинге есть пользователи
        if ( ! empty($items) )
        {
            foreach ( $items as $item )
            {
                $amauser = null;
                $person = null;
                $mdluser = null;
                // Строка с данными
                $datarow = [];
                // Место в рейтинге
                $datarow['report_rating_position'] = $item->rating;
                // Персона деканата
                $person = $this->dof->storage('persons')->get($item->userid);
                // Пользователь moodle
                if ( ! empty($person->sync2moodle) && $person->mdluser > 0 )
                {
                    $amauser = $this->dof->modlib('ama')->user($person->mdluser);
                    $mdluser = $amauser->get();
                }
                if( ! empty($extrafields) && is_array($extrafields) )
                {
                    // Обработка дополнительных полей, задающихся в настройке rating_extrafields
                    foreach( $extrafields as $extrafield )
                    {
                        // По умолчанию ставим пустую строку, на случай, если не удастся получить
                        $datarow[$extrafield->code] = '';
                        if( strpos($extrafield->code, 'custom_') !== false )
                        {   // Дополнительное поле профиля пользователя в moodle
                            if( ! empty($amauser) )
                            {
                                $customfieldshortname = str_replace(
                                    "custom_",
                                    "",
                                    $extrafield->code
                                );
                                // Получение поля по короткому названию
                                $customextrafield = $amauser->get_user_custom_field($customfieldshortname);
                                // На текущий момент обрабатываем только поля типа текст
                                if( ! empty($customextrafield) && $customextrafield->datatype == "text")
                                {
                                    // Получение значения поля
                                    $customextrafielddata = $amauser->get_user_customfield_data($customextrafield->id);
                                    if( ! empty($customextrafielddata) )
                                    {
                                        $datarow[$extrafield->code] = $customextrafielddata->data;
                                    }
                                }
                            }
                        } elseif(strpos($extrafield->code, 'dofperson_') !== false)
                        {   // Поле профиля пользователя деканата
                            $doffieldname = str_replace(
                                "dofperson_",
                                "",
                                $extrafield->code
                            );
                            if( ! empty($person->{$doffieldname}) )
                            {
                                $datarow[$extrafield->code] = $person->{$doffieldname};
                            }
                        }else
                        {   // Поле профиля пользователя moodle
                            if( ! empty($mdluser->{$extrafield->code}) )
                            {
                                $datarow[$extrafield->code] = $mdluser->{$extrafield->code};
                            }
                        }
                    }
                }

                // Получение права на просмотр рейтинга пользователя
                $display_userrating = $this->dof->im('achievements')->is_access(
                    'user_rating_view',
                    $item->userid,
                    $currentperson->mdluser
                );
                // Баллы в рейтинге пользователя
                $sumpoints = '';
                if ( $display_userrating )
                {// Есть доступ к просмотру рейтинга пользователя
                    $sumpoints = $this->points_format($item->points);
                }
                $datarow['report_rating_sumpoints'] = $sumpoints;

                // Добавление новой строки рейтинга
                $report->table[] = $datarow;
            }
        }

        return $report;
    }
    /**
     * Удаление раздела
     *
     * Вместе с разделом удаляются все дочерние разделы, шаблоны,
     * а также пользовательские достижения, связанные с этими шаблонами
     *
     * @param int $id - ID раздела для удаления
     * @param array $options - массив параметров удаления
     *
     * @return array $errors - Массив ошибок, полученных во время удаления раздела
     */
    public function delete_category($id, $options = [] )
    {
        // Результирующий массив ошибок
        $errors = [];

        // Проверка доступа
        $access = $this->is_access('category/delete', $id);
        if ( empty($access) )
        {// Нет доступа к удалению раздела
            $errors[] = $this->dof->get_string('error_achievementcat_deleting_access_error', 'achievements').': ID '.$id;
            return $errors;
        }

        // Смена статуса раздела
        $result = $this->dof->workflow('achievementcats')->change($id, 'deleted');
        if ( empty($result) )
        {// Ошибка
            $errors[] = $this->dof->get_string('error_achievementcat_deleting_error', 'achievements').': ID '.$id;
            return $errors;
        }

        // Удаление всех шаблонов достижений раздела
        $suberrors = $this->delete_achievements_by_categoryid($id);
        // Ошибки во время удаления шаблонов
        $errors = array_merge($errors, $suberrors);

        // Получение дочерних категорий раздела
        $params['parentid'] = $id;
        $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $params['status'] = $statuses;
        $categories = $this->dof->storage('achievementcats')->get_records($params);

        if ( ! empty($categories) )
        {// Найдены дочерние категории
            foreach( $categories as $category )
            {
                $suberrors = $this->delete_category($category->id);
                // Ошибки во время удаления раздела
                $errors = array_merge($errors, $suberrors);
            }
        }

        return $errors;
    }

    /**
     * Удаление шаблонов по ID раздела
     *
     * Вместе с шаблонами также удаляются все пользовательские достижения, связанные с этим шаблонами
     *
     * @param int $id - ID раздела для удаления его шаблонов
     * @param array $options - массив параметров удаления
     *
     * @return array $errors - Массив ошибок, полученных во время удаления шаблонов
     */
    public function delete_achievements_by_categoryid($id, $options = [] )
    {
        // Результирующий массив ошибок
        $errors = [];

        // Получение шаблонов
        $params['catid'] = $id;
        $statuses = $this->dof->workflow('achievements')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $params['status'] = $statuses;
        $achievements = $this->dof->storage('achievements')->get_records($params);

        if ( ! empty($achievements) )
        {// Найдены шаблоны
            foreach( $achievements as $achievement )
            {
                // Удаление шаблона
                $suberrors = $this->delete_achievement($achievement->id);
                // Ошибки во время удаления пользовательских достижений
                $errors = array_merge($errors, $suberrors);
            }
        }

        return $errors;
    }

    /**
     * Удаление шаблона
     *
     * Вместе с шаблоном также удаляются все пользовательские достижения, связанные с этим шаблоном
     *
     * @param int $id - ID шаблона
     * @param array $options - массив параметров удаления
     *
     * @return array $errors - Массив ошибок, полученных во время удаления шаблонов
     */
    public function delete_achievement($id, $options = [] )
    {
        // Результирующий массив ошибок
        $errors = [];

        // Проверка доступа
        $access = $this->is_access('achievement/delete', $id);
        if ( empty($access) )
        {// Нет доступа к удалению шаблона
            $errors[] = $this->dof->get_string('error_achievement_deleting_access_error', 'achievements').': ID '.$id;
            return $errors;
        }

        // Смена статуса шаблона
        $result = $this->dof->workflow('achievements')->change($id, 'deleted');
        if ( empty($result) )
        {// Ошибка
            $errors[] = $this->dof->get_string('error_achievement_deleting_error', 'achievements').': ID '.$id;
        }

        // Удаление всех пользователских достижений
        $suberrors = $this->delete_achievementins_by_achievementid($id);
        // Ошибки во время удаления пользовательских достижений
        $errors = array_merge($errors, $suberrors);

        return $errors;
    }

    /**
     * Удаление пользовательских достижений по ID шаблона
     *
     * Вместе с шаблоном также удаляются все пользовательские достижения, связанные с ним
     *
     * @param int $id - ID шаблона для удаления его пользовательских достижений
     * @param array $options - массив параметров удаления
     *
     * @return array $errors - Массив ошибок, полученных во время удаления достижений
     */
    public function delete_achievementins_by_achievementid($id, $options = [] )
    {
        // Результирующий массив ошибок
        $errors = [];

        // Получение шаблонов
        $params['achievementid'] = $id;
        $statuses = $this->dof->workflow('achievementins')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $params['status'] = $statuses;
        $achievementins = $this->dof->storage('achievementins')->get_records($params);

        if ( ! empty($achievementins) )
        {// Найдены достижения
            foreach( $achievementins as $achievementin )
            {
                // Удаление шаблона
                $suberrors = $this->dof->storage('achievementins')->delete_achievementin($achievementin->id);
                // Ошибки во время удаления пользовательских достижений
                $errors = array_merge($errors, $suberrors);
            }
        }

        return $errors;
    }

    /**
     * Архивация пользовательского достижения
     *
     * @param int $id - ID достижения
     * @param array $options - массив параметров архивации
     *
     * @return array $errors - Массив ошибок, полученных во время архивации достижения
     */
    public function archive_achievementin($id, $options = [] )
    {
        // Результирующий массив ошибок
        $errors = [];

        // Проверка доступа
        $access = $this->dof->storage('achievementins')->is_access('archive', $id);
        if ( empty($access) )
        {// Нет доступа к удалению шаблона
            $errors[] = $this->dof->get_string('error_achievementins_archiving_access_error', 'achievements').': ID '.$id;
            return $errors;
        }

        // Получим объект достижения
        $achievementin = $this->dof->storage('achievementins')->get($id);
        if( ! empty($achievementin) && $achievementin->status == 'notavailable' )
        {// Если переводим в архив неподтвержденное достижение - подтвердим его
            $options = [];
            $achievement = $this->dof->storage('achievements')->get($achievementin->achievementid);
            if( ! empty($achievement) )
            {// Получили объект шаблона достижения
                $adata = unserialize($achievement->data);
                // Получаем счетчик критериев
                $criteriacount = count($adata['simple_data']);
                for($i = 0; $i < $criteriacount; $i++)
                {// Модерируем каждый критерий
                    $options['additionalid'] = $i;
                    $this->dof->storage('achievementins')->moderate_confirm($id, $options);
                }
            }
        }
        // Смена статуса шаблона
        $result = $this->dof->workflow('achievementins')->change($id, 'archived');
        if ( empty($result) )
        {// Ошибка
            $errors[] = $this->dof->get_string('error_achievementins_archiving_error', 'achievements').': ID '.$id;
        }

        return $errors;
    }

    /**
     * Удаление пользовательского достижения
     *
     * @param int $id - ID достижения
     * @param array $options - массив параметров удаления
     *
     * @return array $errors - Массив ошибок, полученных во время удаления достижения
     */
    public function delete_achievementin($id, $options = [] )
    {
        // Результирующий массив ошибок
        $errors = [];

        // Проверка доступа
        $access = $this->dof->storage('achievementins')->is_access('delete', $id);
        if ( empty($access) )
        {// Нет доступа к удалению шаблона
            $errors[] = $this->dof->get_string('error_achievementins_deleting_access_error', 'achievements').': ID '.$id;
            return $errors;
        }

        // Смена статуса шаблона
        $result = $this->dof->workflow('achievementins')->change($id, 'deleted');
        if ( empty($result) )
        {// Ошибка
            $errors[] = $this->dof->get_string('error_achievementins_deleting_error', 'achievements').': ID '.$id;
        }

        return $errors;
    }

    /**
     * Получить информацию о рейтинге пользователя
     *
     * @param int $personid - ID персоны
     * @param array|int achievementids -  ID шаблонов достижения, по которым будет строиться рейтинг
     *
     * @return stdClass $info - Информация о рейтинге пользователя
     */
    public function get_userrating_info($personid = NULL, $achievementids = NULL)
    {
        if ( empty($personid) )
        {// Персона не передана
            $currentperson = $this->dof->storage('persons')->get_bu();
            if ( isset($currentperson->id) )
            {// Персона найдена
                $personid = $currentperson->id;
            } else
            {// Персона не определена
                return NULL;
            }
        }
        $statuses = $this->get_statuses_for_allpoints();

        $options = ['status' => $statuses];
        if ( ! empty($achievementids) )
        {
            if ( is_array($achievementids) )
            {
                $options['achievementids'] = $achievementids;
            } else
            {
                $options['achievementids'] = [$achievementids];
            }
        }

        $rating = $this->dof->storage('achievementins')->get_userrating_info($personid, $options);
        $options['status'] = $this->get_statuses_for_allpoints();
        $ratingallpoints = $this->dof->storage('achievementins')->get_userrating_info($personid,
            array_merge($options, [
                'alluserpoints' => true
            ]));
        if ( isset($rating->points) )
        {
            $rating->points = $this->points_format($rating->points);
            if ( $rating->points == 0 )
            {
                $rating->rating = '-';
            }
        }
        if ( isset($ratingallpoints->points) )
        {
            $rating->alluserpoints = $this->points_format($ratingallpoints->points);
            if ( $rating->alluserpoints == 0 )
            {
                $rating->alluserpoints = '-';
            }
        }
        return $rating;
    }

    public function get_user_rating_category_blocks($personid, $options=[], $return_html = false)
    {
        $addvars = [];
        $ratingcatblocks = [];
        $achievementcat = 0;
        $achievementids = [];
        $filtersearchparams = [];

        if( ! empty($options['addvars']) )
        {
            $addvars = $options['addvars'];
            if( ! empty($addvars['filter']) )
            {
                $filtersearchparams = (array)json_decode($addvars['filter']);
            }
        }
        if ( ! empty($personid) )
        {
            $addvars['personid'] = $personid;
        }

        $defaultachievementcat = $this->dof->storage('config')->get_config_value(
            'default_achievementcat',
            'storage',
            'achievementcats',
            $addvars['departmentid']
        );
        if( ! empty($defaultachievementcat) )
        {// Конфигурация найдена
            $achievementcat = $defaultachievementcat;
        }

        // формирование списка ближайшего нижестоящего уровня категорий
        $achievementcats = (array) $this->dof->storage('achievementcats')->get_categories_list(
            $achievementcat,
            0,
            [
                'metalist' => 'active',
                'affectrating'=>'1',
                'maxdepth'=>'1',
                'sortorder' => 'sortorder ASC, id ASC'
            ]
        );

        if ( ! empty($achievementcats) )
        {// нет активных категорий в ближайшем уровне - значит нет и кнопок, пусть выбирают из полного списка
            foreach($achievementcats as $achievementcatid=>$achievementcatname) {
                // Получение данных о рейтинге пользователя
                $rating = $this->get_user_rating_category($personid, $achievementcatid);
                if ($rating) {
                    $hideempty = $this->dof->storage('config')->get_config_value(
                        'hide_empty_rating_category_block',
                        'im',
                        'achievements',
                        $addvars['departmentid']
                    );
                    // Информация о месте в рейтинге
                    if (( $rating->alluserpoints > 0 || ! $hideempty ) && ! empty($rating)) {
                        $ratingcatname = html_writer::div(
                            $this->dof->get_string(
                                'dof_portfolio_ratingcat_name',
                                'achievements',
                                dof_html_writer::span(
                                    $achievementcatname
                                )
                            ),
                            'dof_portfolio_ratingcat_name'
                        );
                        $ratingcatallpoints = html_writer::div(
                            dof_html_writer::start_span('caption') .
                            $this->dof->get_string(
                                'dof_portfolio_ratingcat_allpoints',
                                'achievements',
                                dof_html_writer::end_span() .
                                dof_html_writer::span(
                                    round($rating->alluserpoints, 2),
                                    'value'
                                )
                            ),
                            'dof_portfolio_ratingcat_allpoints'
                        );
                        $ratingcatpoints = html_writer::div(
                            dof_html_writer::start_span('caption') .
                            $this->dof->get_string(
                                'dof_portfolio_ratingcat_points',
                                'achievements',
                                dof_html_writer::end_span() .
                                dof_html_writer::span(
                                    round($rating->points, 2),
                                    'value'
                                )
                            ),
                            'dof_portfolio_ratingcat_points'
                        );
                        $ratingcatposition = html_writer::div(
                            dof_html_writer::start_span('caption') .
                            $this->dof->get_string(
                                'dof_portfolio_ratingcat_position',
                                'achievements',
                                dof_html_writer::end_span() .
                                dof_html_writer::span(
                                    $rating->rating,
                                    'value'
                                )
                            ),
                            'dof_portfolio_ratingcat_position'
                        );

                        // Ссылка на рейтинг
                        $ratingcatlink = '';
                        if ( $this->dof->im('achievements')->is_access('rating_view', $addvars['departmentid']) )
                        {
                            $filtersearchparams['achievement_category'] = $achievementcatid;
                            $addvars['filter'] = json_encode($filtersearchparams);
                            $addvars['limitfrom'] = (floor($rating->rating/$addvars['limitnum']) * $addvars['limitnum']);
                            // Ссылка на рейтинг
                            $ratingcatlink = dof_html_writer::div(
                                dof_html_writer::link(
                                    $this->dof->url_im('achievements', '/rating.php', $addvars),
                                    $this->dof->get_string('user_rating_pagelink', 'achievements'),
                                    ['class' => 'btn btn-primary dof_button ']
                                ),
                                'dof_portfolio_ratingcat_link'
                            );
                        }
                        $ratingcatblocks[] = dof_html_writer::div(
                            $ratingcatname .
                            $ratingcatallpoints .
                            $ratingcatpoints .
                            $ratingcatposition .
                            $ratingcatlink,
                            'dof_portfolio_ratingcat'
                        );
                    }
                }
            }
        }
        if ( ! empty($ratingcatblocks) )
        {
            if ( $return_html )
            {
                return dof_html_writer::div(implode($ratingcatblocks), 'dof_portfolio_ratingcats');
            } else
            {
                echo dof_html_writer::div(
                    implode($ratingcatblocks),
                    'dof_portfolio_ratingcats'
                );
            }
        } else
        {
            return '';
        }
    }

    /**
     * Получить информацию о рейтинге пользователя
     *
     * @param int $personid - ID персоны
     *
     * @return stdClass $info - Информация о рейтинге пользователя
     */
    public function get_user_info($personid = NULL, $addvars)
    {
        global $PAGE;

        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();

        if ( ! isset($addvars['departmentid']) )
        {
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( empty($personid) )
        {// Персона не передана
            $currentperson = $this->dof->storage('persons')->get_bu();
        } else
        {
            $currentperson = $this->dof->storage('persons')->get($personid);
        }

        // Код базовой информации
        $mainhtml = '';
        // Код дополнительной информации
        $hiddenhtml = '';

        $mdluser = NULL;
        if ( ! empty($currentperson) && ! empty($currentperson->mdluser) )
        {// Данных не получено
            $mdluser = $this->dof->modlib('ama')->user($currentperson->mdluser)->get_user_profile();

            if ( ! empty($mdluser) )
            {
                $hasinfo = false;

                // Получение конфигурации фильтра
                $params = [
                    'departmentid' => $addvars['departmentid'],
                    'code' => 'userinfo_fields',
                    'plugintype' => 'im',
                    'plugincode' => 'achievements'
                ];
                $configrecords = $this->dof->storage('config')->get_records($params);
                if ( empty($configrecords) )
                {
                    return $mainhtml.$hiddenhtml;
                }

                // Получение значения конфигурации
                $configvalue = array_pop($configrecords)->value;
                $configvalue = unserialize($configvalue);

                foreach ( $configvalue as $position => $elements )
                {
                    if ( $position == 'main' )
                    {// Имеется информация по основным полям

                        $mainhtml .= dof_html_writer::start_div('photo');
                        // Фотография
                        if ( isset($mdluser->picture) )
                        {
                            $hasinfo = true;
                            $pic = new user_picture($mdluser);
                            $pic->size = 200;
                            $url = $pic->get_url($PAGE);
                            $mainhtml .= dof_html_writer::img($url, '', ['width' => '200px', 'height' => 'auto', 'style' => 'padding: 0 10px 0 0;']);
                        }
                        $mainhtml .= dof_html_writer::end_div();
                        $mainhtml .= dof_html_writer::start_div('maininfo');

                        $maintable = new html_table();
                        $maintable->data = [];
                    }
                    if ( $position == 'hidden' )
                    {// Имеется информация по основным полям
                        $hiddentable = new html_table();
                        $hiddentable->data = [];
                    }

                    foreach ( $elements as $groupfield => $fields )
                    {
                        if ( $groupfield == 'personfields' )
                        {// Поля персоны
                            if ( ! empty($fields) && is_array($fields) )
                            {
                                $personfields = $this->dof->storage('persons')->get_person_fieldnames();
                                foreach ( $fields as $fieldcode => $fielddata )
                                {
                                    if ( isset($currentperson->$fieldcode) )
                                    {
                                        $name = $fieldcode;
                                        if ( isset($personfields[$fieldcode]) )
                                        {
                                            $name = $personfields[$fieldcode];
                                        }
                                        switch ( $fieldcode )
                                        {
                                            case 'gender' :
                                                $value = $this->dof->get_string($currentperson->$fieldcode,'persons');
                                                $tableval = $position.'table';
                                                if( array_key_exists($fielddata, $$tableval->data) )
                                                {
                                                    $$tableval->data[] = [
                                                        $name,
                                                        dof_html_writer::div($value, $fieldcode)
                                                    ];
                                                } else
                                                {
                                                    $$tableval->data[$fielddata] = [
                                                        $name,
                                                        dof_html_writer::div($value, $fieldcode)
                                                    ];
                                                }
                                                break;
                                            case 'dateofbirth' :
                                                $value = dof_userdate($currentperson->$fieldcode, '%d.%m.%Y', $usertimezone);
                                                $tableval = $position.'table';
                                                if( array_key_exists($fielddata, $$tableval->data) )
                                                {
                                                    $$tableval->data[] = [
                                                        $name,
                                                        dof_html_writer::div($value, $fieldcode)
                                                    ];
                                                } else
                                                {
                                                    $$tableval->data[$fielddata] = [
                                                        $name,
                                                        dof_html_writer::div($value, $fieldcode)
                                                    ];
                                                }
                                                break;
                                            case 'addressid' :
                                                if ( is_array($fielddata) )
                                                {// Нужно получить данные адреса
                                                    $address = $this->dof->storage('addresses')->get($currentperson->$fieldcode);
                                                    foreach ( $fielddata as $addressnamefield => $datafield )
                                                    {
                                                        if ( isset($address->$addressnamefield) )
                                                        {
                                                            if (isset($address->region))
                                                            {
                                                                $region = $this->dof->modlib('refbook')->region($address->country, $address->region);
                                                                if ( !empty($region))
                                                                {
                                                                    $address->region = $region;
                                                                }
                                                            }

                                                            if (isset($address->country))
                                                            {
                                                                $countries = get_string_manager()->get_list_of_countries(false);
                                                                if ( isset($countries[$address->country]))
                                                                {
                                                                    $address->country = $countries[$address->country];
                                                                }
                                                            }

                                                            $tableval = $position.'table';
                                                            $name = $this->dof->get_string($addressnamefield, 'addresses', NULL, 'storage');
                                                            if( array_key_exists($datafield, $$tableval->data) )
                                                            {
                                                                $$tableval->data[] = [
                                                                    $name,
                                                                    dof_html_writer::div($address->$addressnamefield, $addressnamefield)
                                                                ];
                                                            } else
                                                            {
                                                                $$tableval->data[$datafield] = [
                                                                    $name,
                                                                    dof_html_writer::div($address->$addressnamefield, $addressnamefield)
                                                                ];
                                                            }
                                                        }
                                                    }
                                                }
                                                break;
                                            default :
                                                $value = $currentperson->$fieldcode;
                                                $tableval = $position.'table';
                                                if( array_key_exists($fielddata, $$tableval->data) )
                                                {
                                                    $$tableval->data[] = [
                                                        $name,
                                                        dof_html_writer::div($value, $fieldcode)
                                                    ];
                                                } else
                                                {
                                                    $$tableval->data[$fielddata] = [
                                                        $name,
                                                        dof_html_writer::div($value, $fieldcode)
                                                    ];
                                                }
                                                break;
                                        }
                                    }
                                }
                            }
                        }
                        if ( $groupfield == 'customfields' && ! empty($mdluser) )
                        {// Поля персоны
                            if ( ! empty($fields) && is_array($fields) )
                            {
                                foreach ( $fields as $fieldcode => $fielddata )
                                {
                                    if ( isset($mdluser->profile[$fieldcode]) )
                                    {
                                        $customfield = $this->dof->modlib('ama')->user(false)->get_user_custom_field($fieldcode);
                                        switch ( $customfield->datatype )
                                        {
                                            case 'datetime' :
                                                $value = dof_userdate($mdluser->profile[$fieldcode], '%d.%m.%Y', $usertimezone);
                                                break;
                                            default :
                                                $value = $mdluser->profile[$fieldcode];
                                                break;
                                        }
                                        $name = $fieldcode;
                                        if ( isset($customfield->name) )
                                        {
                                            $name = $customfield->name;
                                        }
                                        $tableval = $position.'table';
                                        if( array_key_exists($fielddata, $$tableval->data) )
                                        {
                                            $$tableval->data[] = [
                                                $name,
                                                dof_html_writer::div($value, 'customfield custom_'.$fieldcode)
                                            ];
                                        } else
                                        {
                                            $$tableval->data[$fielddata] = [
                                                $name,
                                                dof_html_writer::div($value, 'customfield custom_'.$fieldcode)
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        if( $groupfield == 'dofcustomfields' && ! empty($mdluser) )
                        {// Если есть кастомные поля персоны деканата
                            if( ! empty($fields) && is_array($fields) )
                            {
                                foreach( $fields as $fieldcode => $fielddata )
                                {
                                    if( $dofcustomfield = $this->dof->storage('customfields')->get_customfields($addvars['departmentid'], ['code' => $fieldcode]) )
                                    {// Получаем объект кастомного поля
                                        $dofcustomfield = array_shift($dofcustomfield);
                                        // Подключаем базовый класс кастомных полей
                                        require_once $this->dof->plugin_path('modlib', 'formbuilder', '/classes/customfieldtypes/base.php');
                                        $fieldsdir = $this->dof->plugin_path('modlib', 'formbuilder', '/classes/customfieldtypes');
                                        if( ! empty($dofcustomfield->type) )
                                        {// Если тип поля указан
                                            $fieldpath = $fieldsdir . '/' . $dofcustomfield->type . '/init.php';
                                            if ( file_exists($fieldpath) )
                                            {// Класс дополнительного поля найден
                                                // Подключаем класс нужного типа поля
                                                require_once($fieldpath);

                                                // Название класса дополнительного поля
                                                $classname = 'dof_customfields_' . $dofcustomfield->type;
                                                if ( class_exists($classname) )
                                                {
                                                    // Создаем объект класса поля
                                                    $customfieldobj = new $classname($this->dof, $dofcustomfield);
                                                    // Получаем значение поля для персоны
                                                    $value = $customfieldobj->render_data($currentperson->id);
                                                } else
                                                {
                                                    $value = '';
                                                }
                                            }

                                            $name = $fieldcode;
                                            if( isset($dofcustomfield->name) )
                                            {// Если имя поля задано - используем его, если нет - код поля
                                                $name = $dofcustomfield->name;
                                            }

                                            // Добавляем значения полей в таблицу
                                            $tableval = $position.'table';
                                            if( array_key_exists($fielddata, $$tableval->data) )
                                            {
                                                $$tableval->data[] = [
                                                    $name,
                                                    dof_html_writer::div($value, 'dofcustomfield custom_'.$fieldcode)
                                                ];
                                            } else
                                            {
                                                $$tableval->data[$fielddata] = [
                                                    $name,
                                                    dof_html_writer::div($value, 'dofcustomfield custom_'.$fieldcode)
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if ( $groupfield == 'recordbookfields' &&  ! empty($mdluser) )
                        {// Если есть поля зачетной книжки
                            if( ! empty($fields) && is_array($fields) )
                            {
                                foreach( $fields as $fieldcode => $fielddata )
                                {
                                    switch($fieldcode)
                                    {
                                        // Ссылка на зачетную книжку
                                        case 'link':
                                            // Получаем контракты персоны
                                            $contracts = $this->dof->storage('contracts')->get_list_by_student($currentperson->id);
                                            if( ! empty($contracts) )
                                            {
                                                foreach($contracts as $contract)
                                                {
                                                    // Получаем подписки по контракту
                                                    $programmsbcs = $this->dof->storage('programmsbcs')->get_programmsbcs_by_contractid($contract->id);
                                                    if( ! empty($programmsbcs) )
                                                    {
                                                        foreach($programmsbcs as $pbcsid => $pbcs)
                                                        {
                                                            $programm = $this->dof->storage('programms')->get($pbcs->programmid);
                                                            // Проверяем права
                                                            if( ! empty($programm) && $this->dof->im('recordbook')->is_access('view_recordbook', $pbcsid, $mdluser->id) )
                                                            {
                                                                // Добавляем значения полей в таблицу
                                                                $tableval = $position.'table';
                                                                $a = new stdClass();
                                                                // Ссылка на зачетку
                                                                $a->recordbook = dof_html_writer::link(
                                                                    $this->dof->im('recordbook')->url('/program.php', ['programmsbcid' => $pbcsid, 'departmentid' => $currentperson->departmentid]),
                                                                    $this->dof->modlib('ig')->icon('recordbook') . $this->dof->get_string('recordbook', 'achievements'),
                                                                    ['class' => 'portfolio_recordbook_link']);
                                                                // Ссылка на контракт
                                                                if( $this->dof->storage('contracts')->is_access('view', $contract->id) )
                                                                {// Если есть право на просмотр контракта - дадим ссылку
                                                                    $a->contract = dof_html_writer::link(
                                                                        $this->dof->im('sel')->url('/contracts/view.php', ['id' => $currentperson->id, 'departmentid' => $contracts[$contract->id]->departmentid]),
                                                                        $this->dof->get_string('contract_num', 'achievements') . $contracts[$contract->id]->num);
                                                                } else
                                                                {// Если нет - укажем текст
                                                                    $a->contract = $this->dof->get_string('contract_num', 'achievements') . $contracts[$contract->id]->num;
                                                                }

                                                                // Название программы
                                                                $a->programm = $programm->name;
                                                                if( array_key_exists($fielddata, $$tableval->data) )
                                                                {
                                                                    $$tableval->data[] = [
                                                                        $this->dof->get_string('recordbook_on_programm_by_contractid', 'achievements', $a),
                                                                        ''
                                                                    ];
                                                                } else
                                                                {
                                                                    $$tableval->data[$fielddata] = [
                                                                        $this->dof->get_string('recordbook_on_programm_by_contractid', 'achievements', $a),
                                                                        ''
                                                                    ];
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }
                        }
                    }
                }

                if( isset($maintable) )
                {
                    ksort($maintable->data);
                    $mainhtml .= dof_html_writer::table($maintable);
                }
                $mainhtml .= dof_html_writer::end_div();
                $mainhtml .= dof_html_writer::div('', 'clearboth', ['style' => 'clear: both;']);

                $hiddenhtml .= dof_html_writer::start_div('hiddeninfo');
                if( isset($hiddentable) )
                {
                    ksort($hiddentable->data);
                    $hiddenblockname = dof_html_writer::div(
                        dof_html_writer::div('','dof_hidecat_header_switcher') .
                        dof_html_writer::div(
                            $this->dof->get_string('additional_fields', 'achievements'),
                            'dof_hidecat_header_name'
                        ),
                        'dof_hidecat_header'
                    );
                    $hiddenhtml .= html_writer::checkbox(
                        'hidetablehiddeninfo',
                        '',
                        true,
                        $hiddenblockname,
                        [
                            'class' => 'dof_hidecat dof_hidecathiddeninfo',
                            'style' => 'display: none;'
                        ]
                    );
                    $hiddenhtml .= html_writer::div(dof_html_writer::table($hiddentable), 'dof_categorycontent');
                }
                $hiddenhtml .= dof_html_writer::end_div();
            }
        }
        $result = $mainhtml;

        $result .= $hiddenhtml;

        return $result;
    }

    /**
     * Получить данные по фильтру
     *
     * @param unknown $filterstr
     * @return mixed[]
     */
    public function get_filter($filterstr = NULL)
    {

        $filter = [];

        if ( ! empty($filterstr) )
        {
            // Разбиваем строку поиска на части
            $searchparts = explode('|', $filterstr);
            if ( ! empty($searchparts) )
            { // Значения есть
                $achievementids = [];
                foreach ( $searchparts as $searchelement )
                {
                    $searchvar = explode('=', $searchelement, 2);
                    if ( count($searchvar) > 1 )
                    {
                        $filter[$searchvar[0]] = $searchvar[1];
                    }
                }
            }
        }

        return $filter;
    }



    /**
     * Разделить по классам пользовательские достижения
     *
     * @param array $list - Массив достижений
     *
     * @return array - Массив достижений, разделенных по классуам
     */
    private function get_achievementins_classes_data($list)
    {
        if ( empty($list) )
        {// Данные не прееданы
            return [];
        }

        // Подготовкв итоговых данных
        $classes = [];
        $cache = [];

        foreach ( $list as $item )
        {
            if ( ! isset($cache[$item->achievementid]) )
            {// Добавление информации по шаблону в кэш
                $achievement = $this->dof->storage('achievements')->get($item->achievementid);
                $cache[$item->achievementid] = $achievement->type;
            }

            // Добавление данных в результирующий массив
            $classes[$cache[$item->achievementid]][$item->id] = $item;
        }
        // Очистка данных кэша
        $cache = NULL;
        return $classes;
    }

    /**
     * Разделить пользовательские достижения с учетом группировки по категориям
     * и шаблонам
     *
     * @param array $list - Массив достижений
     *
     * @return array - Массив достижений, разделенных по классуам
     */
    private function get_achievementins_grouping_data($list)
    {
        if ( empty($list) )
        {// Данные не прееданы
            return [];
        }

        // Подготовкв итоговых данных
        $group = [];
        $cache = [];

        foreach ( $list as $item )
        {
            if ( ! isset($cache[$item->achievementid]) )
            {// Добавление информации по шаблону в кэш
                $achievement = $this->dof->storage('achievements')->get($item->achievementid);
                $cache[$item->achievementid] = $achievement->catid;
            }

            // Добавление данных в результирующий массив
            $group[$cache[$item->achievementid]][$item->achievementid][$item->id] = $item;
        }
        // Очистка данных кэша
        $cache = NULL;
        return $group;
    }

    /**
     * Вернуть HTML иконки со ссылкой на действие
     *
     * @param int $instanceid - ID пользовательского достижения
     * @param array $action - Действие
     * @param array $options - Опции
     *      [addvars] - Массив GET параметров
     *      [moderation_enabled] - Доступность подсистемы модерации
     *      [rating_enabled] - Доступность подсистемы рейтинга
     */
    private function instance_render_action_icon($instanceid, $action, $options = [])
    {
        // Нормализация значений
        $addvars = [];
        if ( isset($options['addvars']) )
        {// GET параметры определены
            $addvars = $options['addvars'];
        }
        $moderation_enabled = [];
        if ( isset($options['moderation_enabled']) )
        {// GET параметры определены
            $moderation_enabled = $options['moderation_enabled'];
        }
        $rating_enabled = [];
        if ( isset($options['rating_enabled']) )
        {// GET параметры определены
            $rating_enabled = $options['rating_enabled'];
        }
        $html = '';

        if ( isset($action['do']) )
        {// Действие определено
            switch ($action['do'])
            {
                case 'confirm' :
                    if ( isset($action['hash']) && $moderation_enabled )
                    {// Установлен
                        $addvars['additionalid'] = $action['hash'];
                        $addvars['id'] = $instanceid;
                        $opt['title'] = $this->dof->get_string('table_achievementins_moderator_submit', 'achievements');
                        $opt['id'] = 'moderate_achievementin_'.$instanceid;
                        $opt['style'] = 'display: inline-block';
                        $opt['class'] = 'criteria-' . $action['do'];
                        $html .= $this->dof->modlib('ig')->icon(
                                'moderation_need',
                                $this->dof->url_im('achievements', '/moderation.php', $addvars),
                                $opt
                        );
                    }
                    break;
                case 'deconfirm' :
                    if ( isset($action['hash']) && $moderation_enabled )
                    {// Установлен
                        $addvars['additionalid'] = $action['hash'];
                        $addvars['additionalid2'] = 1;
                        $addvars['id'] = $instanceid;
                        $opt['title'] = $this->dof->get_string('table_achievementins_moderator_deconfirm', 'achievements');
                        $opt['id'] = 'moderate_achievementin_'.$instanceid;
                        $opt['style'] = 'display: inline-block';
                        $opt['class'] = 'criteria-' . $action['do'];
                        $html .= $this->dof->modlib('ig')->icon(
                                'moderation_confirm',
                                $this->dof->url_im('achievements', '/moderation.php', $addvars),
                                $opt
                        );
                    }
                    break;
                default :
                    break;
            }

        }

        return $html;
    }

    /**
     * Отформатировать значение рейтинга
     *
     * @param float $points - Значение рейтинга пользователя
     *
     * @return float - Отформатированное число
     */
    public function points_format($points)
    {
        $float = (float)$points;
        return round($float, 2);
    }

    /**
     * Получить блок рейтинга пользователя
     *
     * @param int $id - ID целевого пользователя или текущего, если NULL
     * @param array $addvars - Массив GET-параметров для ссылки
     *
     * @return string - HTML-код блока
     */
    public function get_my_rating_block($personid = NULL, $addvars = [], $options = [])
    {
        $html = '';
        // Текущий URL
        $url = $this->dof->modlib('nvg')->get_url();
        // Текущий пользователь
        $currentperson = $this->dof->storage('persons')->get_bu(NULL, true);
        // Целевой пользователь
        if ( $personid )
        {// Целевой пользователь передан
            $targetperson = $this->dof->storage('persons')->get($personid);
        } else
        {// Целевой пользователь - Текущий
            $targetperson = $currentperson;
        }
        if ( empty($targetperson) )
        {// Не удалось получить целевого пользователя
            return false;
        }
        $addvars['departmentid'] = (int)$targetperson->departmentid;
        if ( ! isset($addvars['limitnum']) )
        {
            $addvars['limitnum'] = (int)$this->dof->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
        }
        $addvars['limitfrom'] = optional_param('limitfrom', 1, PARAM_INT);

        // Получение глобальной настройки рейтинга в подразделении пользователя
        $system_rating_enabled = $this->dof->storage('config')->
            get_config_value('system_rating_enabled', 'im', 'achievements', $addvars['departmentid']);
        if ( $system_rating_enabled )
        {// Рейтинг включен в подразделении пользователя

            $html .= dof_html_writer::start_div('im_achievements_rating_wrapper');
            if ( $this->dof->im('achievements')->is_access('user_rating_view', $targetperson->id, $currentperson->mdluser) )
            {// Текущий пользователь может видеть рейтинг целевого пользователя
                // Получение данных о рейтинге пользователя
                $rating = $this->dof->im('achievements')->get_userrating_info($targetperson->id);
                if ( ! empty($targetperson->id) )
                {
                    $addvars['personid'] = $targetperson->id;
                }
                // Информация о месте в рейтинге
                if ( ! empty($rating) )
                {
                    $addvars['limitfrom'] = (floor($rating->rating/$addvars['limitnum']) * $addvars['limitnum']) + 1;
                    $rating->points = round($rating->points, 2);
                    $rating->alluserpoints = round($rating->alluserpoints, 2);
                    $ratigstr = $this->dof->get_string('user_rating_userrating', 'achievements', $rating);
                    $html .= dof_html_writer::tag('h5', $ratigstr);
                }
            }
            // Ссылка на рейтинг пользователей системы
            if ( $this->dof->im('achievements')->is_access('rating_view', $addvars['departmentid']) )
            {
                // Ссылка на рейтинг
                $ratiglink = dof_html_writer::link(
                    $this->dof->url_im('achievements', '/rating.php', $addvars),
                    $this->dof->get_string('user_rating_pagelink', 'achievements'),
                    ['class' => 'btn btn-primary dof_button']
                );
                $html .= dof_html_writer::div($ratiglink);
            }
            // Кнопка управления доступностью рейтинга
            if ( $this->dof->im('achievements')->is_access('rating_availability_edit', $targetperson->id, $currentperson->mdluser) )
            {// Есть доступ к изменению уровня отображения рейтинга
                // Обработчик изменения настройки
                $userrating_available = optional_param('rating_availability', NULL, PARAM_INT);
                if ( is_int($userrating_available) )
                {// Смена значения
                    $result = $this->dof->storage('cov')->
                        save_option('im', 'achievements', $targetperson->id, 'user_rating_availability', $userrating_available);
                    // Отображение сообщения
                    if ( $result )
                    {
                        $this->dof->messages->add(
                            $this->dof->get_string('message_form_achievementins_user_rating_available_saving', 'achievements'),
                            'message'
                        );
                    } else
                    {
                        $this->dof->messages->add(
                            $this->dof->get_string('error_form_achievementins_user_rating_available_saving', 'achievements'),
                            'error'
                        );
                    }
                }

                $userrating_available = (int)$this->dof->storage('cov')->
                    get_option('im', 'achievements', $targetperson->id, 'user_rating_availability');
                $labelattributes =
                [
                    'data-tooltip' => $this->dof->get_string('form_achievementins_user_rating_available_desc_yes', 'achievements'),
                    'class' => 'dof_tooltip',
                ];
                if ( empty($userrating_available) )
                {
                    $labelattributes['data-tooltip'] = $this->dof->get_string('form_achievementins_user_rating_available_desc_no', 'achievements');
                }
                // Интрерфейс изменения настройки
                $html .= $this->dof->modlib('widgets')->single_select(
                    $url,
                    'rating_availability',
                    [
                        1 => $this->dof->get_string('filter_form_yes', 'achievements'),
                        0 => $this->dof->get_string('filter_form_no', 'achievements')
                    ],
                    $userrating_available,
                    [
                        'label' => $this->dof->get_string('form_achievementins_user_rating_available', 'achievements'),
                        'labelattributes' => $labelattributes
                    ]
                );
            }
            $html .= dof_html_writer::end_div();
        }

        return $html;
    }

    /**
     * Функция, добавляющая на страницу дополнительные селекты
     *
     * @param int $personid
     * @param stdClass $currentperson
     * @param array $addvars
     * @param stdClass $url
     * @param string $return_html
     *
     * @return string|void
     */
    public function get_rating_selects($personid = null, $currentperson = null, $addvars = null, $url = null, $return_html = false)
    {
        // HTML код для отображения
        $html = '';
        $html_selects = '';
        $html .= dof_html_writer::start_div('im_achievements_rating_wrapper');
        if ( ! empty($currentperson) && ! empty($addvars) && ! empty($url) )
        {
            // Получение целевого пользователя
            $target_personid = $personid;
            if ( empty($personid) )
            {
                $target_personid = $currentperson->id;
            }
            // Кнопка управления доступностью рейтинга
            if ( $this->dof->im('achievements')->is_access('rating_availability_edit', $personid, $currentperson->mdluser) )
            {// Есть доступ к изменению уровня рейтинга
                // Обработчик изменения настройки
                $userrating_available = optional_param('rating_availability', NULL, PARAM_INT);
                if ( is_int($userrating_available) )
                {// Смена значения
                    $result = $this->dof->storage('cov')->save_option(
                            'im',
                            'achievements',
                            $target_personid,
                            'user_rating_availability',
                            $userrating_available
                            );
                    // Отображение сообщения
                    if ( $result )
                    {
                        $this->dof->messages->add(
                                $this->dof->get_string('message_form_achievementins_user_rating_available_saving', 'achievements'),
                                'message'
                                );
                    } else
                    {
                        $this->dof->messages->add(
                                $this->dof->get_string('error_form_achievementins_user_rating_available_saving', 'achievements'),
                                'error'
                                );
                    }
                }

                $userrating_available = (int)$this->dof->storage('cov')->
                get_option('im', 'achievements', $target_personid, 'user_rating_availability');
                $labelattributes_availability =
                [
                                'data-tooltip' => $this->dof->get_string('form_achievementins_user_rating_available_desc_yes', 'achievements'),
                                'class' => 'dof_tooltip',
                ];
                if ( empty($userrating_available) )
                {
                    $labelattributes_availability['data-tooltip'] = $this->dof->get_string('form_achievementins_user_rating_available_desc_no', 'achievements');
                }

                // Интрерфейс изменения настройки доступности рейтинга
                $html_selects.= $this->dof->modlib('widgets')->single_select(
                        $url,
                        'rating_availability',
                        [
                                        1 => $this->dof->get_string('filter_form_yes', 'achievements'),
                                        0 => $this->dof->get_string('filter_form_no', 'achievements')
                        ],
                        $userrating_available,
                        [
                                        'label' => $this->dof->get_string('form_achievementins_user_rating_available', 'achievements'),
                                        'labelattributes' => $labelattributes_availability
                        ]
                        );

                // Нстройка включения в список рейтинга
                $userrating_rated = optional_param('rating_included', NULL, PARAM_INT);
                if ( is_int($userrating_rated) )
                {// Смена значения
                    $result = $this->dof->storage('cov')->save_option(
                            'im',
                            'achievements',
                            $target_personid,
                            'rating_included',
                            $userrating_rated
                            );
                    // Отображение сообщения
                    if ( $result )
                    {
                        $this->dof->messages->add(
                                $this->dof->get_string('message_form_achievementins_user_rating_included_saving', 'achievements'),
                                'message'
                                );
                    }
                }

                $labelattributes_rated =
                [
                                'data-tooltip' => $this->dof->get_string('form_achievementins_user_rating_included_desc_yes', 'achievements'),
                                'class' => 'dof_tooltip',
                ];
                if ( empty($userrating_rated) )
                {
                    $labelattributes_rated['data-tooltip'] = $this->dof->get_string('form_achievementins_user_rating_included_desc_no', 'achievements');
                }

                $userrating_rated = $this->dof->storage('cov')->
                get_option('im', 'achievements', $target_personid, 'rating_included', null, ['emptyreturn' => 'not_set']);
                if ( $userrating_rated === 'not_set' )
                {
                    $userrating_rated = 1;
                }

                // Интрерфейс изменения настройки включения в список рейтинга
                $html_selects .= $this->dof->modlib('widgets')->single_select(
                        $url,
                        'rating_included',
                        [
                                        1 => $this->dof->get_string('filter_form_yes', 'achievements'),
                                        0 => $this->dof->get_string('filter_form_no', 'achievements')
                        ],
                        $userrating_rated,
                        [
                                        'label' => $this->dof->get_string('form_achievementins_user_rating_included', 'achievements'),
                                        'labelattributes' => $labelattributes_rated
                        ]
                        );
            }
            // Проверим, включил ли студент участие в рейтинге
            $check = $this->dof->storage('cov')->get_option(
                    'im',
                    'achievements',
                    $target_personid,
                    'rating_included',
                    null,
                    ['emptyreturn' => 'not_set']
                    );

            if ( ($check === 'not_set') ||
                    ($this->dof->im('achievements')->is_access('user_rating_view', $personid, $currentperson->mdluser) &&
                            ($check !== 'not_set') &&
                            ! empty($check)) )
            {// Текущий пользователь может видеть рейтинг выбранного пользователя
                $html .= $this->dof->im('achievements')->get_user_rating_category_blocks($personid, [
                                'addvars' => $addvars], true);
            }
        }
        $html .= $html_selects;
        $html .= dof_html_writer::end_div();

        if ( $return_html )
        {
            return $html;
        }
        else
        {
            print($html);
        }
    }

    /**
     * Получение списка отчетов
     *
     * @param int $departmentid - идентификатор подразделения
     *
     * @return string[] - массив сверстанных html-элементов
     */
    public function get_achievements_reports_links($departmentid=null)
    {
        $reportselements = [];

        if ( is_null($departmentid) )
        {// Подразделение не задано - ищем в GET/POST
            $departmentid = optional_param('departmentid', 0, PARAM_INT);
        }

        // общие параметры для отчетов
        // по умолчанию будет автоматически добавлено текущее подразделение, здесь его добавлять не нужно
        // если потребуется указать какое-то конкретное - делать это надо в кастомных настройках отчета
        $options = [
            'pt' => $this->type(),
            'pc' => $this->code()
        ];

        // массив отчетов по индивидуальным планам развития (idp)
        $idpreports = [
            // Сводная статистика по текущему и дочерним подразделениям
            'idp_departments_summary' => [
                'report_name' => $this->dof->get_string('report__idp_departments_summary', 'achievements'),
                'type' => 'idp_summary',
                'displaysubdepartments' => true
            ],
//             // Персонализированная статистика по текущему подразделению
//             'idp_department_personalized' => [
//                 'report_name' => $this->dof->get_string('report__idp_department_personalized', 'achievements'),
//                 'type' => 'idp_personalized',
//                 'displaysubdepartments' => false
//             ],
//             // Персонализированная статистика по текущему и дочерним подразделениям
//             'idp_departments_personalized' => [
//                 'report_name' => $this->dof->get_string('report__idp_departments_personalized', 'achievements'),
//                 'type' => 'idp_personalized',
//                 'displaysubdepartments' => true
//             ]
        ];

        // группы отчетов
        $reportgroups = [
            'idp' => $idpreports
        ];

        foreach($reportgroups as $groupname => $reports)
        {
            // заголовок группы отчетов
            $reportselements[] = dof_html_writer::div(
                $this->dof->get_string('report_group__'.$groupname, 'achievements'),
                'report-group-name'
            );
            foreach($reports as $reportname => $customoptions)
            {
                if ( $this->is_access('view:rtreport/'.$customoptions['type']) )
                {// есть право просмотра отчета - выводим ссылку
                    $reportselements[] = dof_html_writer::link(
                        $this->dof->url_im(
                            'achievements',
                            '/reports.php',
                            [
                                'departmentid' => $departmentid,
                                'rtroptions' => json_encode(array_merge(
                                    $options,
                                    $customoptions
                                ))
                            ]
                        ),
                        $customoptions['report_name']
                    );
                }
            }
        }

        return $reportselements;
    }

    /**
     * Подсистема отображения слайдера в портфолио
     *
     * @param int $personid
     * @return string
     */
    public function get_otslider_photo_by_criteria($personid, $departmentid) {
        if (empty($personid)) {// Персона не передана
            $person = $this->dof->storage('persons')->get_bu();
        } else {
            $person = $this->dof->storage('persons')->get($personid);
        }
        // Экземпляр cfg
        $config = $this->dof->storage('config');
        // Проверка на включенность слайдера в подразделении и наличии локального плагина opentechnology
        if (!$config->get_config_value('slider_enabled','im','achievements', $departmentid)) {
            return '';
        } elseif (!class_exists('otcomponent_otslider\slider')) {
            if ($this->dof->is_access('admin')) {
                $this->dof->messages->add(
                    'otslider is enabled but no opentechnology component',
                    DOF_MESSAGE_ERROR,
                    'no_opentechnology_component'
                    );
            }
            return '';
        }
        // Параметры генерации слайда
        $slideconfig = ['title','description','captionalign','summary',
            'captiontop','captionright','captionbottom','captionleft',
            'parallax','backgroundpositiontop', 'zoomview'
        ];
        $slideconfigvalue = [];
        foreach ($slideconfig as $configname) {
            $slideconfigvalue[$configname] = $config->get_config_value(
                'slide_image_' . $configname,'im','achievements', $departmentid
                );
        }
        // настройки слайдера
        $sliderconfig = ['enabled', 'slidetype', 'slidespeed', 'navigation', 'navigationpoints',
            'slidescroll', 'arrowtype', 'proportionalheight', 'height'];
        foreach ($sliderconfig as $configname) {
            $attributes[$configname] = $config->get_config_value(
                'slider_' . $configname, 'im', 'achievements', $departmentid
                );
        }
        // статусы активных достижений
        $statuses = $this->dof->workflow('achievementins')->get_meta_list('active');
        // Экземпляр класса слайдера
        $slider = new otcomponent_otslider\slider($attributes);
        $userachievementins = $this->dof->storage('achievementins')->get_achievementins(0, $person->id);
        foreach ($userachievementins as $userachievementin) {
            if (!array_key_exists($userachievementin->status, $statuses)) {
                continue;
            }
            $achievement = $this->dof->storage('achievements')->get($userachievementin->achievementid);
            try {
                $this->is_access_view_achievementin($userachievementin, $achievement->catid);
            } catch(dof_exception $ex) {
                continue;
            }

            $userdata = unserialize($userachievementin->data);
            if (property_exists($achievement, 'data')) {
                $adata = unserialize($achievement->data);
                if (isset($adata['simple_data'])) {
                    foreach ($adata['simple_data'] as $key => $criteria) {
                        if (property_exists($criteria, 'type')
                            && ($criteria->type == 'file')
                            && isset($userdata['simple' . $key . '_value']))
                        {// Критерий определен
                            if($areafilesparms = $this->get_image_area_params(
                                $userdata['simple' . $key . '_value'])
                                )
                            {
                                $slide = $slider->get_slide_class('image');
                                // Установим настройки с использованием магического метода слайда
                                foreach ($slideconfigvalue as $key => $val) {
                                    $slide->{$key} = $val;
                                }
                                $slide->image = $areafilesparms;
                            }
                        }
                    }
                }
            }
        }
        // Если слайдов нет вернем пустую строку
        if ($slider->count_slides() == 0) {
            return '';
        }
        // Установим слайды изображений без картинки чтобы отобразить заглушку.
        // Требуется для режима по 3 в ряд если количество изображений меньше трех.
        if ((($j = $slider->count_slides()) < 3) && ($attributes['slidetype'] == 'triple')) {
            for ($i = 0; $i < (3 - $j); $i++) {
                $slide = $slider->get_slide_class('image');
                // Установим настройки с использованием магического метода слайда
                foreach ($slideconfigvalue as $key => $val) {
                    $slide->{$key} = $val;
                }
            }
        }
        return $slider->get_slider_html();
    }
    /**
     * Получение параметров изображения для публичной зоны block_dof
     *
     * @param int $itemid
     * @return stdClass|boolean - false если не изображение или не фаил.
     */
    private function get_image_area_params($itemid) {
        $files = $this->dof->modlib('filestorage')->get_files_by_filearea(
            'public', $itemid
            );
        if (!empty($files) && key_exists(0, $files) && $files[0]->is_valid_image()) {
            // Изображение слайда
            $areafilesparms = new stdClass();
            $areafilesparms->contextid = context_block::instance($this->dof->instance->id)->id;
            $areafilesparms->component = 'block_dof';
            $areafilesparms->filearea = 'public';
            $areafilesparms->itemid = $itemid;
            return $areafilesparms;
        }
        return false;
    }


    public function get_statuses_for_allpoints()
    {
        return ['available', 'archived', 'notavailable'];
    }

    public function sortfields_panel($departmentid = null)
    {
        if( is_null($departmentid) )
        {
            $departmentid = optional_param('departmentid', $this->dof->storage('departments')->get_default(), PARAM_INT);
        }

        // Получение конфигурации фильтра
        $params = [
            'departmentid' => $departmentid,
            'code' => 'userinfo_fields',
            'plugintype' => 'im',
            'plugincode' => 'achievements'
        ];
        $configrecords = $this->dof->storage('config')->get_records($params);
        if ( empty($configrecords) )
        {
            return $this->dof->get_string('no_fields_for_order', 'achievements');
        }
        // Получение значения конфигурации
        $configvalue = array_pop($configrecords)->value;
        $configvalue = unserialize($configvalue);

        if ( empty($configvalue) )
        {
            return $this->dof->get_string('no_fields_for_order', 'achievements');
        }

        $html = '';
        $customdata = new stdClass();
        $personfields = $this->dof->storage('persons')->get_person_fieldnames();

        foreach($configvalue as $position => $elements)
        {
            $items = [];
            $html .= dof_html_writer::tag('h2', $this->dof->get_string($position . '_section_capture', 'achievements'));
            foreach($elements as $groupfield => $fields)
            {
                foreach($fields as $fieldcode => $fielddata)
                {
                    switch($groupfield)
                    {
                        case 'personfields':
                            $name = ! empty($personfields[$fieldcode]) ? $personfields[$fieldcode] : $fieldcode;
                            break;
                        case 'customfields':
                            $name = $this->dof->modlib('ama')->user(false)->get_user_custom_field($fieldcode)->name;
                            break;
                        case 'recordbookfields':
                            $name = $this->dof->get_string('recordbook_' . $fieldcode, 'achievements');
                            break;
                        default:
                            $name = $fieldcode;
                            break;
                    }
                    if( $fieldcode == 'addressid' )
                    {
                        if( ! empty($fielddata) && is_array($fielddata) )
                        {
                            foreach($fielddata as $fielddataname => $fielddatavalue)
                            {
                                $name = $this->dof->get_string($fielddataname, 'addresses', NULL, 'storage');
                                $sortedfields[$position][$fielddatavalue][] = ['code' => $fieldcode . '_' . $fielddataname, 'group' => $groupfield, 'name' => $name];
                            }
                        }
                    } else
                    {
                        $sortedfields[$position][$fielddata][] = ['code' => $fieldcode, 'group' => $groupfield, 'name' => $name];
                    }
                }
            }

            ksort($sortedfields[$position]);

            foreach($sortedfields[$position] as $index => $values)
            {
                $itemsforsort = [];
                foreach($values as $value)
                {
                    $itemsforsort[] = $value['name'];
                }
                asort($itemsforsort);
                $temparray = [];
                foreach($itemsforsort as $key => $itemforsort)
                {
                    $temparray[] = $sortedfields[$position][$index][$key];
                }

                foreach($temparray as $value)
                {
                    $li = new stdClass();
                    $li->name = $value['name'];
                    $li->code = $value['code'];
                    $li->group = $value['group'];
                    $li->index = $index;
                    $items[] = $li;
                }
            }
            $html .= dof_html_writer::start_tag('ul', ['class' => 'sortable', 'data-position' => $position])."\n";
            foreach($items as $item)
            {
                $attr = [
                    'data-code' => $item->code,
                    'data-group' => $item->group,
                    'data-index' => $item->index,
                    'id' => $item->group . '_' . $item->code
                ];
                $html .= dof_html_writer::tag('li', $item->name, $attr)."\n";
            }
            $html .= dof_html_writer::end_tag('ul');
        }

        return dof_html_writer::div($html, 'fields_sort_area', ['data-departmentid' => $departmentid]);
    }
    /**
     * Фильтрует категории из конфига оставляя только дочерние переданой
     *
     * @param int $categoryid
     * @return array ид категории => название
     */
    public function filtering_config_categories($categoryid)
    {
        $result = [];
        $configvalue = $this->dof->storage('achievementcats')->get_config_value(
            $categoryid, 'categorieslist');
        if (@unserialize($configvalue) !== false) {
            $configvalue = unserialize($configvalue);
            // Получим активные статусы категорий
            $catactivestatuses = $this->dof->workflow('achievementcats')->get_meta_list('active');
            $catactivestatuses = array_keys($catactivestatuses);
            // Уберем из отображения категории не в активных статусах
            foreach ($configvalue as $key => $catid) {
                $catstatus = $this->dof->storage('achievementcats')->get($catid, 'status');
                if (!in_array($catstatus->status, $catactivestatuses)) {
                    unset($configvalue[$key]);
                }
            }
            $categorieslist = $this->dof->storage('achievementcats')->get_categories_list(
                $categoryid, 0, ['metalist' => 'active']
                );
            foreach($configvalue as $catid) {
                if (!empty($categorieslist[$catid])) {
                    $result[$catid] = $categorieslist[$catid];
                }
            }
        }
        return $result;
    }
}