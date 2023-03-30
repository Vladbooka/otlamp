<?php
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

require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/enrollib.php');

/** Класс для работы с пользователем (Alternative Moodle Api)
 * @access public
 */
class ama_user extends ama_base
{
    
    /** Возвращает информацию о пользователе из БД
     * @access public
     * @param int $id - id курса
     * @return object массив типа параметр=>значение
     */
    public function get()
    {
        global $DB;
        if ( !$this->get_id() )
        {
            return false; // неизвестно какую запись извлекать
        }
        return $DB->get_record('user', array('id' => $this->get_id()));
    }
    
    /**
     * Существует ли пользователь с заданным $id (вернуть $id или false)
     * @param int $id - id пользователя, если не задан - берется из класса
     */
    public function is_exists($id = null)
    {
        global $DB;
        if ( is_null($id) )
        {
            $id = $this->get_id();
        }
        if ( !$id OR ! ama_utils_is_intstring($id) )
        {
            return false;
        }
        return $DB->record_exists('user', array('id' => intval($id), 'deleted' => 0));
    }
    
    /**
     * Проверяет наличие роли учитель или админ у пользователя
     * @param int $userid - id проверяемого пользователя
     * @param int $courseid - id курса на котором он, возможно преподает
     * @param bool $isadmin - если true, то проверим заодно является ли он админом
     * @return bool - true если учитель (и админ - в зависимости от $isadmin)
     */
    public function is_teacher($userid = null, $courseid = null, $isadmin = true)
    {
        global $DB;
        if ( is_null($userid) )
        {//берем текушего пользователя
            $userid = $this->get_id();
        }
        if ( !$userid OR ! ama_utils_is_intstring($userid) )
        {//неправильный id пользователя
            return false;
        }
        
        $contextid = 0;
        if ( $courseid )
        {//проверим - является ли пользователь учителем
            if ( class_exists('context_course') )
            {// начиная с moodle 2.6
                $context = context_course::instance($courseid);
            } else
            {// оставим совместимость с moodle 2.5 и менее
                $context = get_context_instance(CONTEXT_COURSE, $courseid);
            }
            //на переданном курсе
            if ( $context )
            {
                $contextid = $context->id;
            }
        }
        // Определим, какие роли являются учителями
        // @todo в будущем следует найти какой-то более надежный способ это определять
        $roles = array();
        $roles[] = $DB->get_record('role', array('shortname' => 'teacher'));
        $roles[] = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $roles[] = $DB->get_record('role', array('shortname' => 'coursecreator'));
        $roles[] = $DB->get_record('role', array('shortname' => 'manager'));
        
        // перебираем все учительские роли, и смотрим обладает ли ими пользователь
        foreach ( $roles as $role )
        {
            if ( !empty($role->id) AND user_has_role_assignment($userid, $role->id, $contextid) )
            {
                return true;
            }
        }
        
        return false;
    }
    
    /** Создает объект и возвращает его id
     * @param mixed $obj - параметры объекта или null для параметров по умолчанию
     * @return mixed
     */
    public function create($obj = null)
    {
        global $DB;
        // Если емайл не задан - шаблон создаст его сам, но мы должны отключить отправку писем
        if ( is_object($obj) AND ! isset($obj->email) or empty($obj->email) )
        {
            // Запрещаем отправку писем
            $obj->emailstop = 1;
        }
        
        // Всегда создаем нового пользователя через шаблон
        // если нужно оставить поле пустым - устанавливаем его в исходном $obj
        // добавление слешей учтено (в исходном $obj слеши должны быть, шаблон обрабатыват только свои данные)
        $obj = $this->template($obj);
        
        // Добиваемся уникальности логина
        $obj->username = $this->username_unique($obj->username, true);
        
        // Перед вставкой в базу, или ее обновлением, проверим, уникальна ли запись, чтобы не было конфликтов
        if ( $this->is_unique($obj) )
        {
            // вставляем или обновляем запись в базе
            return $DB->insert_record('user', $obj);
        } else
        {
            // запись не уникальна, вставить в базу не получится
            return false;
        }
    }
    
    /** Обновляет информацию о пользователе в БД
     * @param object $dateobject - информация о пользователе
     * @return id созданной записи в случае успеха, или false, если мы налажали
     * @access public
     */
    public function update($dateobject, $replace = false)
    {
        global $DB;
        $this->require_real();
        //echo 'bbb1';
        /*
         * Здесь этот фрагмент не нужен, наоборот, нужно принимать специальные меры
         * если нужно обновить полностью
         if ($replace !== true)
         {
         echo 'bbb2';
         // Merge new data with old data
         $old = $this->get();
         $dateobject = (object) array_merge((array)$old,(array)$dateobject);
         }
         */
        //echo 'bbb3';
        // addslashes_object($dateobject);
        $dateobject->id = $this->id; // добавляем в обьект для обновления id созданной в конструкторе записи
        // 		Добиваемся уникальности логина
        if ( isset($dateobject->username) )
        {
            $dateobject->username = $this->username_unique($dateobject->username, true, $dateobject->id);
        }
        // Перед вставкой в базу, или ее обновлением, проверим, уникальна ли запись, чтобы не было конфликтов
        if ( !$this->is_unique($dateobject) )
        {// запись не уникальна, вставить в базу не получится
            return false;
        } else
        {
            // вставляем или обновляем запись в базе
            if ( $DB->update_record('user', $dateobject) )
            {
                return $this->id;
            } else
            {// с обновлением возникли проблемы
                return false;
            }
        }
    }
    
    /** Удаляет запись о пользователе из таблицы _user
     * @access public
     * @return bool true - удаление прошло успешно
     * false в противном случае
     */
    public function delete()
    {
        if ( delete_user($this->get()) )
        {
            $this->id = 0;
            return true;
        } else
        {
            return false;
        }
    }
    
    /**
     * Функция проверяющая уникальность логина и email-а до записи в базу данных
     * @param stdClass object $dateobject - обьект, содержащий данные о пользователе.
     * Имена полей обьекта совпадают с именами полей в таблице mdl_user.
     *
     * @access public
     * @return true or false
     */
    public function is_unique($dateobject)
    {
        global $DB;
        // Формируем SQL-запрос
        $sql = '';
        
        // Фильтр по логину
        if ( isset($dateobject->username) and ! empty($dateobject->username) )
        {
            $sql = $sql . ' username = \'' . $dateobject->username . '\' ';
        }
        // Фильтр по адресу электронной почты
        if ( isset($dateobject->email) and ! empty($dateobject->email) )
        {
            if ( $sql )
            {
                $sql = $sql . ' OR email = \'' . $dateobject->email . '\' ';
            } else
            {
                $sql = $sql . ' email = \'' . $dateobject->email . '\' ';
            }
        }
        // Фильтр игнорирования существующей записи по id
        // без $sql не имеет смысла
        if ( $sql AND isset($dateobject->id) AND ! empty($dateobject->id) ) // исключил: and !($this->get_id() === false) зачем это было?
        {
            // если id передан вместе с обьектом, игнорируем его
            $sql = "({$sql}) AND id<>{$dateobject->id}";
        } elseif ( $sql AND $this->get_id() )
        {
            // если id присутствует здесь, но отсутствует в переданном объекте - игнорируем местный
            $sql = "({$sql}) AND id<>{$this->get_id()}";
        }
        if ( !$sql )
        {
            // запрашивать нечего, email и логин не переданы, значит мы их не меняем, значит все ОК
            return true;
        }
        // Отсекаем удаленные
        $sql = "($sql) AND deleted='0'";
        // Считаем, если записей ноль - все хорошо
        return $DB->count_records_select('user', $sql) == 0;
    }
    
    /**
     * Включение режима игнорирования ключа сессии
     *
     * @return void
     */
    public function ingnore_sesskey_enable()
    {
        global $USER;
        $USER->ignoresesskey = true;
    }
    
    /**
     * Выключение режима игнорирования ключа сессии
     *
     * @return void
     */
    public function ingnore_sesskey_disable()
    {
        global $USER;
        $USER->ignoresesskey = false;
    }
    
    /**
     * Сгенерировать уникальный логин по имени кириллицей
     */
    public function username_unique($username, $translit = true, $ignoreid = null)
    {
        global $DB;
        // Добиваемся уникальности короткого имени
        $i = 1;
        if ( $translit )
        {
            // Транслитерация и приведение символов к нижнему регистру
            $username = ama_utils_translit('ru', $username, true);
        }
        $username2 = $username;
        $sql = '';
        if ( $ignoreid )
        {    // Игнорируем id
            $sql .= " AND id<>'{$ignoreid}'";
        }
        while ( $DB->record_exists_select('user', " username='{$username2}' AND deleted='0' {$sql}") )
        {
            $username2 = "{$username}-{$i}";
            ++$i;
        }
        
        return $username2;
    }
    
    /**
     * функция для поиска пользователя по имени и фамилии. Используется поиск по маске (то есть шаблон LIKE)
     * @param stdClass Object $search - обьект, содержащий поля, по которым будет производиться поиск
     * @param string $sort
     * @param int $limitfrom
     * @param int $limitnum
     *
     * @return массив обьектов (Записи из базы. Названия полей обьектов совпадают с названиями полей в таблице mdl_user)
     * или false в случае неудачи
     * @todo Уточнить по каким параметрам производить поиск
     */
    public function search($search, $sort = 'lastname ASC', $limitfrom = 0, $limitnum = 0)
    {
        global $DB;
        if ( $this->id != false )
        {// из-за текущей архитектуры ama поиск невозможен при конкретном userid
            dof_debugging('ama_user::search() - user->id must be null to use search', DEBUG_DEVELOPER);
            return false;
        }
        
        if ( ! isset($search->firstname) && ! isset($search->lastname) && ! isset($search->middlename) )
        {// пришел пустой поисковый запрос
            dof_debugging('ama_user::search() empty search parameters got, object expected', DEBUG_DEVELOPER);
            return false;
        }
        
        // Не получаем удаленных пользователей
        $sqlparts = ['deleted = 0'];
        $sqlparameters = [];
        
        // Поиск по фамилии
        if ( isset($search->lastname) )
        {
            $sqlparts[] = $DB->sql_like('lastname', ':lastname', false);
            $sqlparameters['lastname'] = '%'.clean_param($search->lastname, PARAM_TEXT).'%';
        }
        
        // Поиск по имени
        if ( isset($search->firstname) )
        {
            $sqlparts[] = $DB->sql_like('firstname', ':firstname', false);
            $sqlparameters['firstname'] = '%'.clean_param($search->firstname, PARAM_TEXT).'%';
        }
        
        // Поиск по отчеству
        if ( isset($search->middlename) )
        {
            $sqlparts[] = $DB->sql_like('middlename', ':middlename', false);
            $sqlparameters['middlename'] = '%'.clean_param($search->middlename, PARAM_TEXT).'%';
        }
        
        $sql = implode(' AND ',$sqlparts);
        return $DB->get_records_select('user', $sql, $sqlparameters, $sort, '*', $limitfrom, $limitnum);
    }
    
    /** Возвращает информацию по умолчанию о пользователе
     * Это значения полей по умолчанию для таблицы _user
     * @access public
     * @param object $data - массив значений, которые переопределяют
     * соответствующие параметры по умолчанию
     * @return object параметры по умолчанию для нового пользователя
     */
    public function template($data = NULL)
    {
        global $CFG;
        $user = new stdClass();
        $user->username = 'new' . substr(md5(microtime()), 0, 7);
        $user->password = md5($this->generate_password_moodle());
        $user->email = $this->generate_email($user->username);
        $user->timemodified = time();
        $user->department = '';
        $user->firstname = 'firstname';
        $user->lastname = 'lastname';
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->lang = 'ru';
        if ( file_exists("{$CFG->dirroot}/auth/dof/auth.php") )
        {// если есть авторицация dof - ставим ее
            $user->auth = 'dof';
        }
        // По умолчанию делаем запись не подтвержденной
        $user->confirmed = 0;
        // Запрещаем отправку писем
        $user->emailstop = 1;
        // Отключаем подписку
        $user->autosubscribe = 0;
        // Не отображаем емайл
        $user->maildisplay = 0;
        // Сливаем данные
        if ( !is_null($data) )
        {
            foreach ( $data as $key => $val )
            {
                $user->$key = $val;
            }
        }
        return $user;
    }
    
    // Почтовые уведомления
    /**
     * Отправит сообщение текущему пользователю
     *
     * @param int $frommuser user id from
     * @param string $subject plain text subject line of the email
     * @param string $messagetext plain text version of the message
     * @param string $messagehtml complete html version of the message (optional)
     * @param string $attachment a file on the filesystem, relative to $CFG->dataroot
     * @param string $attachname the name of the file (extension indicates MIME)
     * @param bool $usetrueaddress determines whether $from email address should
     *          be sent out. Will be overruled by user profile setting for maildisplay
     * @param int $wordwrapwidth custom word wrap width
     * @return bool|string Returns "true" if mail was sent OK, "emailstop" if email
     *          was blocked by user and "false" if there was another sort of error.
     */
    public function send_email($subject, $messagetext, $from = '', $messagehtml = '', $attachment = '', $attachname = '', $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79)
    {
        $this->require_real();
        // Получаем пользователя
        if ( empty($from) )
        {
            $from = core_user::get_support_user();
        }
        
        return email_to_user($this->get(), $from, $subject, $messagetext, $messagehtml, $attachment, $attachname);
    }
    
    public function send_sms($messagetext)
    {
        global $CFG;
        $user = $this->get();
        $processors = get_message_processors(true);
        if ( ! empty($processors['otsms']->enabled) && is_object($processors['otsms']->object) )
        {
            $otsms = $processors['otsms']->object;
            $otsms->translit = false;
            $otsms->addsubject = false;
            return $otsms->send_message($messagetext, false);
        } else
        {
            return false;
        }
    }
    
    /**
     * Проверка, является ли переданный пароль текущим
     *
     * @param string $password - пароль в открытом виде
     * @return boolean
     */
    public function is_current_password($password)
    {
        $this->require_real();
        // Получаем пользователя
        $user = $this->get();
        
        return validate_internal_user_password($user, $password);
    }
    
    /**
     * Установка пароля пользователю
     *
     * @param string $newpassword - пароль в открытом виде
     *
     * @return boolean
     */
    public function set_new_password($newpassword)
    {
        global $DB;
        
        $this->require_real();
        // Получаем пользователя
        $user = $this->get();
        
        // Нужно обновить пароль в БД
        try {
            return update_internal_user_password($user, $newpassword);
            
        } catch(Exception $ex)
        {
            dof_mtrace(3, 'Could not set user password!');
            return false;
        }
    }
    
    /**
     * Изменить пароль и отправить стандартное уведомление о регистрации с паролем
     * @param $newpassword - новый пароль, null - сгенерировать
     * @param $update - обновлять учетную запись?
     * @return boll
     */
    public function send_setnew_notice($newpassword = null, $update = false, $sendmethods = null)
    {
        global $DOF;
        
        if ( is_null($sendmethods) )
        {
            $sendmethods = ['email'];
        }
        $this->require_real();
        // Получаем пользователя
        $user = $this->get();
        // return setnew_password_and_mail($this->get());
        global $CFG, $DB;
        
        $site = get_site();
        
        $supportuser = core_user::get_support_user();
        
        if ( empty($newpassword) )
        {
            // Нужно сгенерировать пароль
            $newpassword = $this->generate_password_pronounceable();
        }
        
        if ( $update )
        {
            $this->set_new_password($newpassword);
        }
        
        $a = new stdClass();
        $a->firstname = fullname($user, true);
        $a->sitename = format_string($site->fullname);
        $a->username = $user->username;
        $a->newpassword = $newpassword;
        $a->link = $CFG->wwwroot . '/login/';
        $a->signoff = generate_email_signoff();
        
        $sendresult = true;
        foreach($sendmethods as $sendmethod)
        {
            switch($sendmethod)
            {
                case 'otsms':
                    $message = new stdClass();
                    $message->component    = 'block_dof';
                    $message->name         = '';
                    $message->userto       = $user;
                    $message->smallmessage = $DOF->get_string('newusernewpasswordshorttext', 'ama', $a, 'modlib');
                    $sendresult = $sendresult && $this->send_sms($message);
                    break;
                case 'email':
                    $subject = format_string($site->fullname) . ': ' . get_string('newusernewpasswordsubj');
                    $message = get_string('newusernewpasswordtext', '', $a);
                    $sendresult = $sendresult && email_to_user($user, $supportuser, $subject, $message);
                    break;
                default:
                    break;
            }
        }
        return $sendresult;
    }
    
    // ****************************************
    //                Утилиты
    // ****************************************
    /**
     * * Создать пароль (без спецсимволов): скопиоровано из moodle
     *
     * @param int $maxlen  The maximum size of the password being generated.
     * @param number $minpassworddigits
     * @param number $minpasswordnonalphanum
     * @param number $minpasswordlower
     * @param number $minpasswordupper
     * @return string
     */
    public function generate_password_moodle($maxlen = 10, $minpassworddigits = null,
        $minpasswordnonalphanum = null, $minpasswordlower = null, $minpasswordupper = null) {
            
            global $CFG;
            
            if (!is_null($minpassworddigits) && !is_null($minpasswordnonalphanum) &&
                !is_null($minpasswordlower) && !is_null($minpasswordupper))
            {// Полностью передана желаемая политика генерации пароля - переопределяем стандартную политику,
                // не зависимо от того, включена она или нет
                
                $digits = (int)$minpassworddigits;
                $nonalphanum = (int)$minpasswordnonalphanum;
                $lower = (int)$minpasswordlower;
                $upper = (int)$minpasswordupper;
                
            } else
            {
                if ( empty($CFG->passwordpolicy))
                {// Политика паролей в Moodle отключена, а переопределения не заданы, используем собственную логику
                    
                    $fillers = PASSWORD_DIGITS;
                    $wordlist = file($CFG->wordlist);
                    $word1 = trim($wordlist[rand(0, count($wordlist) - 1)]);
                    $word2 = trim($wordlist[rand(0, count($wordlist) - 1)]);
                    $filler1 = $fillers[rand(0, strlen($fillers) - 1)];
                    $password = $word1 . $filler1 . $word2;
                    
                    // продолжение скрипта генерации, нас не интересует, сразу возвращаем результат
                    return substr($password, 0, $maxlen);
                    
                } else
                {// при генерации пароля, используем стандартную политику паролей Moodle,
                    // переопределяя те параметры, которые были переданы разработчиком
                    
                    $digits = (is_null($minpassworddigits) ? $CFG->minpassworddigits : (int)$minpassworddigits);
                    $nonalphanum = (is_null($minpasswordnonalphanum) ? $CFG->minpasswordnonalphanum : (int)$minpasswordnonalphanum);
                    $lower = (is_null($minpasswordlower) ? $CFG->minpasswordlower : (int)$minpasswordlower);
                    $upper = (is_null($minpasswordupper) ? $CFG->minpasswordupper : (int)$minpasswordupper);
                    
                }
            }
            
            // Отключаем переопределение $maxlen, иначе минимальная длинна приравнивается к максимальной
            // $maxlen = !empty($CFG->minpasswordlength) ? $CFG->minpasswordlength : 0;
            
            $additional = $maxlen - ($lower + $upper + $digits + $nonalphanum);
            // Make sure we have enough characters to fulfill
            // complexity requirements
            $passworddigits = PASSWORD_DIGITS;
            while ( $digits > strlen($passworddigits) )
            {
                $passworddigits .= PASSWORD_DIGITS;
            }
            $passwordlower = PASSWORD_LOWER;
            while ( $lower > strlen($passwordlower) )
            {
                $passwordlower .= PASSWORD_LOWER;
            }
            $passwordupper = PASSWORD_UPPER;
            while ( $upper > strlen($passwordupper) )
            {
                $passwordupper .= PASSWORD_UPPER;
            }
            $passwordnonalphanum = PASSWORD_NONALPHANUM;
            while ( $nonalphanum > strlen($passwordnonalphanum) )
            {
                $passwordnonalphanum .= PASSWORD_NONALPHANUM;
            }
            
            // Now mix and shuffle it all
            $password = str_shuffle(substr(str_shuffle($passwordlower), 0, $lower) .
                substr(str_shuffle($passwordupper), 0, $upper) .
                substr(str_shuffle($passworddigits), 0, $digits) .
                substr(str_shuffle($passwordnonalphanum), 0, $nonalphanum) .
                substr(str_shuffle($passwordlower .
                    $passwordupper .
                    $passworddigits .
                    $passwordnonalphanum), 0, $additional));
            
            return substr($password, 0, $maxlen);
    }
    
    /**
     * Create pronounceable password
     *
     * This method creates a string that consists of
     * vowels and consonats.
     *
     * @access private
     * @param  integer Length of the password
     * @return string  Returns the password
     */
    public function generate_password_pronounceable($maxlen = 10)
    {
        
        $retVal = '';
        
        /**
         * List of vowels and vowel sounds
         */
        $v = array('a', 'e', 'i', 'o', 'u',
            'ae', 'ou', 'io',
            'a', 'o', 'a', 'o', 'i',
            'ea', 'ou', 'ia', 'eu', 'au'
        );
        
        /**
         * List of consonants and consonant sounds
         */
        $c = array('b', 'd', 'g', 'h', 'k', 'l', 'm',
            'n', 'p', 'r', 's', 't', 'u', 'v', 'f',
            'tr', 'kr', 'fr', 'dr', 'vr', 'pr', 'tl',
            'gd', 'kt', 'ml', 'pt', 'hr',
            'kh', 'ph', 'st', 'sl', 'kl', 'kz', 'bz',
            'kn', 'pr', 'zk', 'zd', 'bz', 'br', 'bl',
            'dl', 'nd', 'vn', 'kv', 'gl', 'ps', 'sh'
        );
        
        $v_count = 12;
        $c_count = 29;
        
        $_Text_Password_NumberOfPossibleCharacters = $v_count + $c_count;
        
        for ( $i = 0; $i < $maxlen; $i++ )
        {
            $retVal .= $c[mt_rand(0, $c_count - 1)] . $v[mt_rand(0, $v_count - 1)];
        }
        
        return ucfirst(substr($retVal, 0, ($maxlen - 1)) . rand(1, 9));
    }
    
    /** Проверяет, что почтовый адрес допустим в системе. Иначе отображает ошибку
     *
     * @param string $email
     * @return bool|string false в случае, если email допустим, иначе string с описанием ошибки
     */
    public function email_is_not_allowed($email)
    {
        return email_is_not_allowed($email);
    }
    
    /**
     * Генерирует email по заданному логину
     */
    protected function generate_email($login)
    {
        $suffix = 'emailsuffix.su';
        return $login . '@' . $suffix;
    }
    
    /**
     * получить пользователя для отправки уведомления
     *
     * @return array
     */
    public function get_noreply_user()
    {
        return core_user::get_noreply_user();
    }
    
    /** Получить список записей критериям
     *
     * @return array|bool массив записей из таблицы mdl_user или false
     * @param array $options - массив условий в формате 'название_поля' => 'значение'
     * @param string $sort [optional] - в каком направлении и по каким полям производится сортировка
     * @param string $fields [optional] - поля, которые надо возвратить
     * @param int $limitfrom [optional] - id, начиная с которого надо искать
     * @param int $limitnum [optional] - максимальное количество записей, которое надо вернуть
     */
    public function get_list($options = null, $sort = '', $fields = '*', $limitfrom = '', $limitnum = '')
    {
        global $CFG, $DB;
        $select = '';
        if ( !is_null($options) AND ! is_array($options) )
        {// передан неправильный формат данных
            return false;
        }
        if ( !empty($options) )
        {// если у нас есть условия - подставим мх в запрос
            foreach ( $options as $field => $value )
            {// перебираем все условия и в цикле составляем запрос
                if ( !$select )
                {// если это первый фрагмент запроса - то не добавляем условие AND
                    $select .= $this->query_part_select($field, $value);
                } else
                {// для второго и последующих условий - добавим
                    $select .= ' AND ' . $this->query_part_select($field, $value);
                }
            }
        }
        
        return $DB->get_records_select('user', $select, null, $sort, $fields, $limitfrom, $limitnum);
    }
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE,
     * который определяет параметры выборки
     * @param string $field - название поля
     * @param mixed $value - null, string или array
     * @return mixed string - фрагмент sql-запроса
     * если $value - null, то пустая строка
     * если $value - строка, то "поле = значение"
     * если $value - массив, то "поле IN(знач1, знач2, ... значN)"
     * если массив пуст или это не массив и не строка и не null,
     * то вернется bool false
     *
     * @todo это дублирование функции из storage_base. Нужно будет потом найти способ от него избавится.
     */
    public function query_part_select($field, $value = null)
    {
        if ( !is_scalar($field) OR is_bool($field) )
        {//название поля неправильного типа';
            return false;
        }
        if ( is_null($value) )
        {//значение поля не передано';
            return '';
        }
        if ( is_scalar($value) AND ! is_bool($value) )
        {//значение только одно';
            return "{$field} = '{$value}'";
        }
        if ( is_array($value) AND ! empty($value) )
        {//значений несколько';
            $isnull = '';
            foreach ( $value as $k => $v )
            {//разберемся, что передано в массиве,
                if ( is_null($v) )
                {//передан элемент null
                    //сформируем фрагмент запроса IS NULL
                    $isnull = $field . ' IS NULL ';
                    //уберем null из массива во избежание ошибок
                    unset($value[$k]);
                } elseif ( is_scalar($v) )
                {//передано что надо - превращаем в строку
                    $value[$k] = '\'' . $v . '\'';
                } else
                {//передано то, что не надо было передавать
                    return false;
                }
            }
            if ( empty($value) )
            {//в массиве были только элементы null
                return $isnull;
            }
            //если в массиве еще что-то осталось
            $str = implode(',', $value);
            if ( $isnull )
            {// Нужно сравнивать с null-значением
                return "({$field} IN({$str}) OR {$isnull})";
            } else
            {// не нужно сравнивать с null-значением
                return "({$field} IN({$str}))";
            }
        } else
        {//не массив или пустой массив';
            return false;
        }
        //на всякий случай, если передали нечто неизвестное';
        return false;
    }
    
    /** Возвращает запись пользователя Moodle по его логину
     * @param string $username - логин пользователя
     * @return object - запись пользователя Moodle или false, если таковой не был найден
     */
    public function get_user_by_username($username)
    {
        global $DB;
        return $DB->get_record_select('user', " username='{$username}' AND deleted='0'");
    }
    
    /** Меняем метод авторизации на dof
     * @return int - id записи или bool false если что-то не так
     */
    public function replace_method_on_dof($changepassword = false)
    {
        global $CFG;
        if ( !$user = $this->get() )
        {// не нашли пользователя - ошибка
            return false;
        }
        if ( $user->auth === 'dof' )
        {
            // Уже все сменили
            return true;
        }
        if ( file_exists("{$CFG->dirroot}/auth/dof/auth.php") )
        {// если есть авторицация dof - ставим ее
            $user->auth = 'dof';
            if ( $changepassword )
            {
                // Надо сменить пароль
                $user->password = $this->generate_password();
                $this->send_setnew_notice($user->password, false);
                $user->password = hash_internal_user_password($user->password);
            }
            // обновляем запись
            return $this->update($user);
        }
        return false;
    }
    
    /** Возвращает последний вход персоны деканата на портал
     * @param int $personid - id персоны из деканата
     * @return string - дата последнего вхда на портал
     * или bool false, если персоны на портале никогда не было
     */
    public function get_lastaccess($personid)
    {
        global $DOF;
        if ( !$userid = $DOF->storage('persons')->get_field($personid, 'mdluser') )
        {// не получили id пользователя Moodle - значит его никогда не было на портале
            return false;
        }
        $this->set_id($userid);
        if ( !$user = $this->get() )
        {// не нашли пользователя - значит его никогда не было на портале
            return false;
        }
        if ( empty($user->lastaccess) )
        {//последный вход не указан - значит его никогда не было на портале
            return false;
        }
        // вернем последний вход пользователя
        return date('d.m.Y H:i', $user->lastaccess);
    }
    
    /** Возвращает количество входов на портал
     * @param int $personid - id персоны
     * @return int количество заходов или bool false
     */
    public function count_login($personid, $begindate = null, $enddate = null)
    {
        global $DOF, $DB;
        if ( !$userid = $DOF->storage('persons')->get_field($personid, 'mdluser') )
        {// не получили id пользователя Moodle - значит он никогда не входил на портал
            return 0;
        }
        // укажем временной интервал
        $days = '';
        $params = [];
        
        $select = 'userid = ?';
        $params[] = $userid;
        
        $logreader = get_logreader();
        
        if ($logreader instanceof logstore_legacy\log\store) {
            $logtable = 'log';
            $timefield = 'time';
            $targetselect = ' AND module = ? AND action = ? AND course = ?';
            array_push($params, 'user', 'login', 1);
        } else {
            $logtable = $logreader->get_internal_log_table_name();
            $timefield = 'timecreated';
            $targetselect = ' AND eventname = ?';
            $params[] = '\core\event\user_loggedin';
        }
        
        $select = 'userid = ?' . $targetselect;
        
        // дата начала
        if ( ! empty($begindate) )
        {// укажем с какой даты брать отчет
            $days .= ' AND ' . $timefield . ' > ?';
            $params[] = $begindate;
        }
        // дата клнца
        if ( ! empty($enddate) )
        {// укажем с какой даты брать отчет
            $days .= ' AND ' . $timefield . ' < ?';
            $params[] = $enddate;
        }
        
        $select .= $days;
        
        return $DB->count_records_select($logtable, $select, $params);
    }
    
    /** Определяет правильность буквенного написания email
     * @param string $email - email
     * @return bool true - если email указан верно или
     *         bool false - если найдены неккоректно введеные знаки
     */
    public function validate_email($email)
    {
        return validate_email($email);
    }
    
    /** Определяет правильность буквенного написания логина
     * @param string $username - логин
     * @return bool true - если логин указан верно или
     *         bool false - если найдены неккоректно введеные знаки
     */
    public function validate_username($username)
    {
        return !preg_match("/[^(-_\.[:alnum:])]/i", $username);
    }
    
    /**
     * Получить список курсов, на которые подписан пользователь
     *
     * @return array
     */
    public function get_courses($userid)
    {
        return enrol_get_users_courses($userid);
    }
    
    /**
     * Получить список стандартных обрабатываемых полей пользователей
     *
     * @param array $addfields - поля, которые необходимо добавить к списку стандартных
     *                           ключ - строка с кодом поля
     *                           значение - название поля для отображения пользователю
     *
     * @return array
     */
    function get_userfields_list($addfields=[], $withcustomlangs = true)
    {
        $userfields = [];
        $profilefields = [
            'username',
            'email',
            'firstname',
            'lastname',
            'middlename',
            'firstnamephonetic',
            'lastnamephonetic',
            'alternatename',
            'idnumber',
            'institution',
            'department',
            'phone1',
            'phone2',
            'city',
            'url',
            'icq',
            'skype',
            'aim',
            'yahoo',
            'msn',
            'country'
        ];
        if( $withcustomlangs )
        {
            foreach(array_merge($profilefields, $addfields) as $k => $v)
            {
                if( is_number($k) )
                {
                    $userfields[$v] = get_user_field_name($v);
                } else
                {
                    $userfields[$k] = $v;
                }
            }
            return $userfields;
        } else
        {
            return array_merge($profilefields, $addfields);
        }
    }
    
    /**
     * Получить данные по полям пользователей
     * Сложная обертка на функцию core_myprofile_navigation с валидацией и подготовкой полей.
     *
     * @param stdClass $user - объект пользователя
     * @param string $fields - массив кодов полей пользователя
     *                         если поле стандартное, должно иметь префикс user_field_
     *                         если поле настраиваемое, должно иметь префикс user_profilefield_
     *                         если ключ - число, то значение считается кодом поля
     *                         если ключ - строка, то ключ считается кодом поля, а значение - названием для отображения
     * @param int $courseid  - курс, если не передан будет использован $COURSE
     * @return boolean|stdClass - массив с объектами данных по полям
     */
    function get_fields_data($user, $fields, $courseid =null)
    {
        global $USER, $CFG, $DB, $COURSE, $DOF;
        
        $resultfields = [];
        
        if( empty($user) ) {
            return $resultfields;
        }
        
        $supportedfieldsbycmn = [
            'email' => 'email',
            'country' => 'country',
            'city' => 'city',
            'address' => 'address',
            'phone1' => 'phone1',
            'phone2' => 'phone2',
            'institution' => 'institution',
            'department' => 'department',
            'idnumber' => 'idnumber',
            'url' => 'webpage',
            'interests' => 'interests',
            'icq' => 'icqnumber',
            'skype' => 'skypeid',
            'yahoo' => 'yahooid',
            'aim' => 'aimid',
            'msn' => 'msnid'
        ];
        if (is_null($courseid)) {
            $courseid = $COURSE->id;
        }
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $iscurrentuser = ($user->id == $USER->id);
        $tree = new core_user\output\myprofile\tree();
        // Add core nodes.
        require_once($CFG->libdir . "/myprofilelib.php");
        core_myprofile_navigation($tree, $user, $iscurrentuser, $course);
        $nodes = $tree->nodes;
        
        $user->profile = profile_user_record($user->id, false);
        foreach ($fields as $k=>$v) {
            if ( is_number($k) ) {
                $fieldshortname = $v;
                $fielddisplayname = null;
            } else {
                $fieldshortname = $k;
                $fielddisplayname = $v;
            }
            
            $result = new stdClass();
            $result->displayvalue = $DOF->get_string('forbidden', 'ama', null, 'modlib');
            $result->name = $fielddisplayname;
            
            if ($isprofilefield = (substr($fieldshortname, 0, 18) == 'user_profilefield_')) {
                $cmncustomfieldname = str_replace('user_profilefield_', 'custom_field_', $fieldshortname);
                $result->shortname = substr($fieldshortname, 18);
            }
            
            if ($isuserfield = (substr($fieldshortname, 0, 11) == 'user_field_'))
            {// обычное поле профиля
                $fieldshortname = substr($fieldshortname, 11);
                $result->shortname = $fieldshortname;
            }
            
            if (isset($supportedfieldsbycmn[$fieldshortname]) || $isprofilefield) {
                if ($isuserfield && array_key_exists($supportedfieldsbycmn[$fieldshortname], $nodes)) {
                    $result->value = ! is_null($user->{$fieldshortname}) ? $user->{$fieldshortname} : '';
                    $result->displayvalue = $nodes[$supportedfieldsbycmn[$fieldshortname]]->content;
                    if ( is_null($fielddisplayname) ) {
                        $result->name = $nodes[$supportedfieldsbycmn[$fieldshortname]]->title;
                    }
                } elseif ($isprofilefield && array_key_exists($cmncustomfieldname, $nodes)) {
                    $result->value = $user->profile->{substr($fieldshortname, 18)};
                    $result->displayvalue = $nodes[$cmncustomfieldname]->content;
                    if ( is_null($fielddisplayname) ) {
                        $result->name = $nodes[$cmncustomfieldname]->title;
                    }
                } else {
                    if ($isprofilefield && isset($user->profile->{substr($fieldshortname, 18)})) {
                        if (empty($user->profile->{substr($fieldshortname, 18)})) {
                            $result->displayvalue = '';
                        }
                        if( ! empty($pfrecord = $DB->get_record('user_info_field', [
                            'shortname' => substr($fieldshortname, 18)
                        ]))) {
                            if (is_null($fielddisplayname)) {
                                $result->name = $pfrecord->name;
                            }
                        } else {
                            $result = false;
                        }
                    } elseif ($isuserfield && property_exists($user, $fieldshortname)) {
                        if (empty($user->{$fieldshortname})) {
                            $result->displayvalue = '';
                        }
                        if (is_null($fielddisplayname)) {
                            if ($fieldshortname == 'id') {
                                $result->name = $fieldshortname;
                            } else {
                                $result->name = get_user_field_name($fieldshortname);
                            }
                        }
                    } else {
                        $result = false;
                    }
                }
            } elseif (property_exists($user, $fieldshortname)) {
                $result->value = ! is_null($user->{$fieldshortname}) ? $user->{$fieldshortname} : '';
                if (is_null($fielddisplayname)) {
                    if ($fieldshortname == 'id') {
                        $result->name = $fieldshortname;
                    } else {
                        $result->name = get_user_field_name($fieldshortname);
                    }
                }
                $result->displayvalue = $result->value;
            } else {
                $result = false;
            }
            
            if ($isuserfield) {
                $resultfields['user_field_' . $fieldshortname] = $result;
            } elseif ($isprofilefield) {
                $resultfields[$fieldshortname] = $result;
            } else {
                throw new moodle_exception('Field is not supported');
            }
        }
        return $resultfields;
    }
    /**
     *  Получить данные по полям пользователей без валидации
     *
     * @param stdClass $user - объект пользователя
     * @param string $fields - массив кодов полей пользователя
     *                         если поле стандартное, должно иметь префикс user_field_
     *                         если поле настраиваемое, должно иметь префикс user_profilefield_
     * @return array
     */
    function get_not_validated_fields_data($user, $fields) {
        $userfields = $profilefields = [];
        foreach ($fields as $field) {
            if (substr($field, 0, 11) == 'user_field_') {
                $userfields[] = substr($field, 11);
            } elseif (substr($field, 0, 18) == 'user_profilefield_') {
                $profilefields[] = substr($field, 18);
            }
        }
        return array_merge(
            $this->get_user_profilefields($user, $profilefields),
            $this->get_user_fields($user, $userfields)
            ); 
    }
    /**
     * Получить массив объектов с данными настраиваемых полей пользователя
     * Поля не валидируются, если требуется валидация использовать get_fields_data
     *
     * @param stdClass $user - объект пользователя
     * @param array $fieldshortnames - массив имен настраиваемых полей пользователя
     * @return array - массив объектов настраиваемых полей пользователя где ключ содержит user_profilefield_
     */
    function get_user_profilefields($user, $fieldshortnames)
    {
        global $DB, $CFG;
        $resultfields = [];
        profile_load_custom_fields($user);
        foreach ($fieldshortnames as $fieldshortname) {
            if ( isset($user->profile[$fieldshortname]) ) {
                $result = new stdClass();
                $result->shortname = $fieldshortname;
                $result->value = $user->profile[$fieldshortname];
                $result->displayvalue = '';
                
                $pfrecord = $DB->get_record('user_info_field', [
                    'shortname' => $fieldshortname
                ]);
                if( ! empty($pfrecord) ) {// получено наименование, переопределяем
                    $result->name = $pfrecord->name;
                    $result->type = $pfrecord->datatype;
                    // путь к файлу класса кастомного поля данного типа
                    $pfclassfile = $CFG->dirroot . '/user/profile/field/' . $pfrecord->datatype . '/field.class.php';
                    
                    if ( file_exists($pfclassfile) ) {
                        //подключение файла класса плагина поля профиля
                        require_once ($pfclassfile);
                        // название класса
                        $pfclassname = 'profile_field_' . $pfrecord->datatype;
                        
                        if ( class_exists($pfclassname) ) {
                            // создание экземпляра класса
                            $pf = new $pfclassname($pfrecord->id, $user->id);
                            
                            if (! $pf->is_empty() && method_exists($pf, 'display_data') ) {
                                $result->displayvalue = $pf->display_data();
                            }
                        }
                    }
                }
                $resultfields['user_profilefield_' . $fieldshortname] = $result;
            }
        }
        return $resultfields;
    }
    /**
     * Получить массив объектов с данными стандартных полей пользователя
     * Поля не валидируются, если требуется валидация использовать get_fields_data
     *
     * @param stdClass $user - объект пользователя
     * @param array $fieldshortnames - массив имен стандартных полей пользователя
     * @return array - массив объектов стандартных полей пользователя
     */
    function get_user_fields($user, $fieldshortnames) {
        global $OUTPUT;
        $resultfields = [];
        foreach ($fieldshortnames as $fieldshortname) {
            if (property_exists($user, $fieldshortname)) {
                $result = new stdClass();
                $result->shortname = $fieldshortname;
                $result->value = $user->{$fieldshortname};
                if ( $fieldshortname == 'id' ) {
                    $result->name = $fieldshortname;
                } elseif ($fieldshortname == 'lang') {
                    $result->name = get_user_field_name('language');
                } else {
                    $result->name = get_user_field_name($fieldshortname);
                }
                $result->displayvalue = '';
                switch($fieldshortname) {
                    case 'email':
                        $result->displayvalue = obfuscate_mailto($user->email, '');
                        break;
                    case 'country':
                        if ($user->country) {
                            $result->displayvalue = get_string($user->country, 'countries');
                        }
                        break;
                    case 'url':
                        if ($user->url) {
                            $url = $user->url;
                            if (strpos($user->url, '://') === false) {
                                $url = 'http://'. $url;
                            }
                            $webpageurl = new moodle_url($url);
                            $result->displayvalue = html_writer::link($url, $webpageurl);
                        }
                        break;
                    case 'interests':
                        if ($interests = core_tag_tag::get_item_tags('core', 'user', $user->id)) {
                            $result->displayvalue = $OUTPUT->tag_list($interests, '');
                        }
                        break;
                    case 'icq':
                        if ($user->icq) {
                            $imurl = new moodle_url('http://web.icq.com/wwp', ['uin' => $user->icq] );
                            $iconurl = new moodle_url('http://web.icq.com/whitepages/online', ['icq' => $user->icq, 'img' => '5']);
                            $statusicon = html_writer::tag('img', '',[
                                'src' => $iconurl,
                                'class' => 'icon icon-post',
                                'alt' => get_string('status')
                            ]);
                            $result->displayvalue = html_writer::link($imurl, s($user->icq) . $statusicon);
                        }
                        break;
                    case 'skype':
                        if ($user->skype) {
                            $imurl = 'skype:'.urlencode($user->skype).'?call';
                            $iconurl = new moodle_url('http://mystatus.skype.com/smallicon/'.urlencode($user->skype));
                            $statusicon = '';
                            if ( ! is_https()) {
                                $statusicon = html_writer::empty_tag('img', [
                                    'src' => $iconurl,
                                    'class' => 'icon icon-post',
                                    'alt' => get_string('status')
                                ]);
                            }
                            $result->displayvalue = html_writer::link($imurl, s($user->skype) . $statusicon);
                        }
                        break;
                    case 'yahoo':
                        if ($user->yahoo) {
                            $imurl = new moodle_url('http://edit.yahoo.com/config/send_webmesg', [
                                '.target' => $user->yahoo,
                                '.src' => 'pg'
                            ]);
                            $iconurl = new moodle_url('http://opi.yahoo.com/online', [
                                'u' => $user->yahoo,
                                'm' => 'g',
                                't' => '0'
                            ]);
                            $statusicon = html_writer::tag('img', '', [
                                'src' => $iconurl,
                                'class' => 'iconsmall icon-post',
                                'alt' => get_string('status')
                            ]);
                            $result->displayvalue = html_writer::link($imurl, s($user->yahoo) . $statusicon);
                        }
                        break;
                    case 'aim':
                        if ($user->aim) {
                            $imurl = 'aim:goim?screenname='.urlencode($user->aim);
                            $result->displayvalue = html_writer::link($imurl, s($user->aim));
                        }
                        break;
                    case 'msn':
                        if ($user->msn) {
                            $result->displayvalue = s($user->msn);
                        }
                        break;
                    default:
                        $result->displayvalue = $user->{$fieldshortname};
                        break;   
                }
                $resultfields['user_field_' . $fieldshortname] = $result;
            }
        }
        return $resultfields;
    }
    
    /**
     * Получить перечень дополнительных полей пользователя
     */
    public function get_user_custom_fields()
    {
        global $DB;
        $fields = $DB->get_records('user_info_field', null, 'sortorder ASC');
        return $fields;
    }
    /**
     * Получить перечень дополнительных полей пользователя в виде списка
     * 
     * @return array
     */
    public function get_user_custom_fields_list() {
        $fieldslist = [];
        foreach ($this->get_user_custom_fields() as $field) {
            $fieldslist[] = $field->shortname;
        }
        return $fieldslist;
    }
    
    /**
     * Получить поле дополнительное пользователя по его shortname
     */
    public function get_user_custom_field($shortname)
    {
        global $DB;
        $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
        return $field;
    }
    
    /**
     * Получить перечень всех имеющихся значений поля
     *
     * @param string $name - Имя поля
     *
     * @return array - Массив значений
     */
    public function get_user_custom_field_options($name)
    {
        global $DB;
        
        $options = [];
        
        $field = $DB->get_record('user_info_field', ['shortname' => $name]);
        if ( ! empty($field) )
        {// Поле найдено
            $sql = " SELECT MIN(id), data
                    FROM {user_info_data}
                    WHERE fieldid = :fieldid
                    GROUP BY data ";
            $koptions = $DB->get_records_sql($sql, ['fieldid' => $field->id]);
            if ( ! empty($koptions) )
            {
                foreach ( $koptions as $id => $instance )
                {
                    $options[$id] = $instance->data;
                }
            }
        }
        
        return $options;
    }
    
    /**
     * Получить данные пользователя
     *
     * Возвращает данные пользователя вместе с дополнительными полями
     */
    public function get_user_profile()
    {
        global $CFG;
        require_once($CFG->dirroot.'/user/profile/lib.php');
        
        if ( ! empty($this->id) )
        {
            $user = $this->get();
            profile_load_data($user);
            profile_load_custom_fields($user);
            return $user;
        } else
        {
            return NULL;
        }
        
    }
    
    /**
     * Получить значение поля пользователя
     *
     * @param string $custonfieldvalueid - ID поля пользоательского значения
     *
     * @return object|NULL - Пользовательский ответ или NULL
     */
    public function get_user_customfield_value($customfieldvalueid)
    {
        global $DB;
        
        // Получение данных
        $data = $DB->get_record('user_info_data', ['id' => $customfieldvalueid]);
        
        if ( ! empty($data) )
        {
            return $data;
        }
        return NULL;
    }
    
    /**
     * Устанавливает значение кастомного поля для пользователя
     * после вызова метода необходимо бросать событие апдейта
     * core\event\user_updated::create_from_userid($userid)->trigger();
     * @param int|string|stdClass $field идентификатор, shortname или объект поля
     * @param mixed $value значение, которое необходимо установить
     * @param array $options дополнительные опции
     * по умолчанию выставлены опции:
     * $options['concat'] - требуется ли передаваемые данные объединить с имеющимися
     * $options['glue'] - символ объединения данных в поле
     * $options['repeat'] - разрешено ли добавлять при объединении повторяющиеся данные
     * @return boolean
     */
    public function set_user_customfield_value($field, $value, $options = [])
    {
        global $DB, $CFG;
        // Выставим опции по умолчанию, если не переданы
        if( ! isset($options['concat']) )
        {
            $options['concat'] = false;
        }
        if( ! isset($options['glue']) )
        {
            $options['glue'] = ',';
        }
        if( ! isset($options['repeat']) )
        {
            $options['repeat'] = true;
        }
        // Передавать можно id, shortname и объект поля - приведем все к объекту
        if( is_int($field) )
        {// Передан идентификатор кастомного поля
            $field = $htis->get_user_custom_field_by_id($field);
        } elseif( is_string($field) )
        {// Передан shortname поля
            $field = $this->get_user_custom_field($field);
        }
        
        if( ! is_object($field) )
        {
            return false;
        }
        if( ! $this->get_id() )
        {// Если идентификатор пользователя, для которого нужно установить значение не известен
            return false;
        }
        
        // Подключим библиотеки для работы с кастомными полями
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/profile/field/' . $field->datatype . '/field.class.php');
        $classname = 'profile_field_' . $field->datatype;
        $formfield = new $classname($field->id, $this->get_id());
        
        $data = new stdClass();
        
        // Обработка переданных данных перед сохранением
        $value = $formfield->edit_save_data_preprocess($value, $data);
        
        $data->userid  = $this->get_id();
        $data->fieldid = $field->id;
        $data->data    = $value;
        
        if( $old = $DB->get_record('user_info_data', ['userid' => $data->userid, 'fieldid' => $data->fieldid]))
        {// Если уже есть данные в базе, обновим их
            $data->id = $old->id;
            $old->data = trim($old->data);
            if( $options['concat'] )
            {// Если нужно добавить данные, а не заменить
                if( ! $options['repeat'] && ! empty($old->data) )
                {// Если не разрешены повторяющиеся данные
                    $olddata = explode($options['glue'], $old->data);
                    if( ! in_array($data->data, $olddata) )
                    {// Добавим только новые данные
                        $data->data = $old->data . $options['glue'] . $data->data;
                    }
                } elseif( ! empty($old->data) )
                {
                    $data->data = $old->data . $options['glue'] . $data->data;
                }
            }
            $DB->update_record('user_info_data', $data);
        } else
        {// Если данных в базе еще нет, создаем запись
            $DB->insert_record('user_info_data', $data);
        }
    }
    
    /**
     * Получить дополнительное поле по его id
     * @param int $id идентификатор дополнитеьного поля
     * @return stdClass|false
     */
    public function get_user_custom_field_by_id($id)
    {
        global $DB;
        return $DB->get_record('user_info_field', ['id' => $id]);
    }
    
    /**
     * Получить значение поля пользователя
     *
     * @param string $custonfieldid - ID пользоательского поля
     *
     * @return object|NULL - Пользовательский ответ или NULL
     */
    public function get_user_customfield_data($customfieldid)
    {
        global $DB;
        
        // Получение данных
        $data = $DB->get_record('user_info_data', [
            'fieldid' => $customfieldid,
            'userid' => $this->get_id()
        ]);
        
        if ( ! empty($data) )
        {
            return $data;
        }
        return NULL;
    }
    
    /**
     * Получить ID пользователей по значению поля
     *
     * Из за нестандартизированной работы различных полей пользователя
     * требуется различная обработка у типов полей
     *
     * @param mixed $shortname - Название дополнительного поля пользователя поля пользователя
     * @param mixed $value - Значение дополнительного поля пользователя
     *
     * @return array|NULL - Массив ID пользоаелей или NULL
     */
    public function get_userids_by_customfield_value($shortname, $value)
    {
        global $DB;
        
        // Поле не найдено
        $field = $this->get_user_custom_field($shortname);
        if ( empty($field) )
        {
            return NULL;
        }
        // Поиск пользователей в зависимости от типа поля
        switch ( $field->datatype )
        {
            case 'menu' :
                if ( isset($field->param1) )
                {
                    $options = explode("\n", $field->param1);
                } else {
                    $options = [];
                }
                if ( isset($options[(int)$value]) )
                {// Значение найдено
                    $value = $options[(int)$value];
                }
                break;
                
        }
        // Получение данных
        $select = ' fieldid = :fieldid AND data = :data ';
        $options = ['fieldid' => $field->id, 'data' => $value];
        $data = $DB->get_records_select('user_info_data', $select, $options);
        
        if ( ! empty($data) )
        {
            // Формирование массива пользователей
            $users = [];
            foreach ( $data as $field )
            {
                $users[$field->userid] = $field->userid;
            }
            $users = array_keys($users);
            return $users;
        }
        return NULL;
    }
    
    /**
     * Получить аватар пользователя
     *
     * @param array $options - Дополнительные параметры отображения
     *             int  'size' - Размер изображения в пикселях(200 по умолчанию)
     *             array 'attributes' - Атрибуты тега изображения
     *
     * @return string|NULL - HTML-код изображения пользователя или NULL
     */
    public function get_user_picture_html($options = [])
    {
        global $PAGE;
        
        // Получить идентификатор пользователя
        $id = $this->get_id();
        if ( ! empty($id) )
        {// Указан ID пользователя Moodle
            $mdluser = $this->get();
            $pic = new user_picture($mdluser);
            $pic->size = 200;
            if ( isset($options['size']) )
            {// Размер указан
                $pic->size = (int)$options['size'];
            }
            
            $url = $pic->get_url($PAGE);
            $attributes = ['width' => (string)$pic->size, 'height' => 'auto'];
            // Переопределение атрибутов из опций обработки
            if ( isset($options['attributes']) && ! empty($options['attributes']) )
            {
                $options['attributes'] = (array)$options['attributes'];
                foreach ( $options['attributes'] as $name => $value )
                {
                    $attributes[$name] = $value;
                }
            }
            return dof_html_writer::img($url, '', $attributes);
        }
        return null;
    }
    
    /**
     * Получает пользователя по email
     * @param string $email email пользователя
     */
    public function get_user_by_email($email)
    {
        $email = (string)$email;
        if( empty($email) )
        {
            return [];
        }
        global $DB;
        return $DB->get_records('user', ['email' => $email, 'deleted' => 0]);
    }
    
    /** Возвращает запись пользователя Moodle по его индивидуальному номеру (idnumber)
     * @param string $idnumber - индивидуальный номер пользователя (idnumber)
     * @return object|false - запись пользователя Moodle или false, если таковой не был найден
     */
    public function get_user_by_idnumber($idnumber)
    {
        global $DB;
        $guestid = guest_user()->id;
        return $DB->get_records_select('user', " idnumber='{$idnumber}' AND deleted='0' AND id != {$guestid}");
    }
    
    /** Определить, подлежит ли пользователь оцениванию
     *
     * @return bool
     * @param int $userid - id пользователя в таблице mdl_user
     * @param int $coursecontext - id контекста курса, в котором назначен пользователь
     * @param int $systemcontext - id системного контекста
     * @param array $roles - массив id ролей, подлежащих оцениванию
     */
    public function is_graded_user($coursecontext, $systemcontext, $roles)
    {
        $userid = $this->get_id();
        foreach ( $roles as $roleid )
        {
            if ( user_has_role_assignment($userid, $roleid, $systemcontext) )
            {// сначала проверяем системные роли
                return true;
            }
            if ( user_has_role_assignment($userid, $roleid, $coursecontext) )
            {// пототом проверяем роль в контексте курса
                return true;
            }
        }
        // пользователь не принадлежит ни к одной роли, подлежащей оцениванию - мы не выводим его задания
        return false;
    }
    
    /**
     * Назначение роли пользователю в контексте категории
     *
     * @param int $categoryid - идентификатор категории (coursecat)
     * @param int $roleid - идентификатор роли
     *
     * @return number|boolean - идентификатор назначения роли или false в случае ошибки
     */
    public function assign_role_to_category($categoryid, $roleid)
    {
        if ( class_exists('context_coursecat') )
        {// начиная с moodle 2.6
            $context = context_coursecat::instance($categoryid);
        } else
        {// оставим совместимость с moodle 2.5 и менее
            $context = get_context_instance(CONTEXT_COURSECAT, $categoryid);
        }
        
        if ( $context )
        {
            return role_assign($roleid, $this->get_id(), $context->id);
        }
        return false;
    }
    
    /**
     * Удаление назначения роли
     *
     * @param int $roleassignid - идентификатор назначения роли
     *
     * @return bool - результат операции удаления записи назначения роли
     */
    public function unassign_role($roleassignid)
    {
        global $DB;
        
        return $DB->delete_records('role_assignments', ['id' => $roleassignid]);
    }
    
    public function fullsearch($filters=[], $sort='u.lastname ASC', $limitfrom=0, $limitnum=0,
        $includingguest=false, $loadcustomfields=false) {
            global $DB, $CFG;
            
            $conditions = [];
            $parameters = [];
            $filterfields = [];
            if (!empty($filters))
            {
                foreach($filters as $filter)
                {
                    $filter = (array)$filter;
                    if (!in_array($filter['fieldname'], $filterfields))
                    {
                        $filterfields[] = $filter['fieldname'];
                    }
                    if (substr($filter['fieldname'], 0, 11) == 'user_field_')
                    {
                        $fieldname = substr($filter['fieldname'], 11);
                        $conditions[] = 'u.'.$fieldname.' '.$filter['operator'].' :mfv_'.$fieldname;
                        $parameters['mfv_'.$fieldname] = $filter['value'];
                    } elseif (substr($filter['fieldname'], 0, 18) == 'user_profilefield_')
                    {
                        $fieldname = substr($filter['fieldname'], 18);
                        $conditions[] = 'uif.shortname = :cff_'.$fieldname;
                        $parameters['cff_'.$fieldname] = $fieldname;
                        $conditions[] = 'uid.data ' . $filter['operator'] . ' :cfv_'.$fieldname;
                        $parameters['cfv_'.$fieldname] = $filter['value'];
                    }
                }
            } else
            {
                $conditions = ['1=1'];
            }
            
            // По умолчанию в выборку берем только неудаленных
            if (!in_array('user_field_deleted', $filterfields))
            {
                $conditions[] = 'u.deleted = 0';
            }
            // По умолчанию в выборку берем только подтвержденных
            if (!in_array('user_field_confirmed', $filterfields))
            {
                $conditions[] = 'u.confirmed = 1';
            }
            // По умолчанию в выборку не берем гостя
            if (empty($includingguest))
            {
                $conditions[] = 'u.id <> :mfv_guestid';
                $parameters['mfv_guestid'] = $CFG->siteguest;
            }
            
            $sql = "SELECT u.*
                  FROM {user} u
             LEFT JOIN {user_info_data} uid ON uid.userid = u.id
             LEFT JOIN {user_info_field} uif ON uid.fieldid = uif.id
                 WHERE ". implode(' AND ', $conditions) . "
              GROUP BY u.id
              ORDER BY ". $sort;
            
            $users = $DB->get_records_sql($sql, $parameters, $limitfrom, $limitnum);
            
            if (empty($users))
            {
                return [];
            }
            
            if ($loadcustomfields)
            {
                foreach($users as $u => $user)
                {
                    profile_load_custom_fields($users[$u]);
                }
            }
            
            return $users;
    }
    
    /**
     * Получить все подписки пользователя в курсе
     * @param int $courseid идентификатор курса
     */
    public function get_user_enrolments_in_course($courseid)
    {
        global $DB, $DOF;
        $params = [];
        $select = $sql = '';
        $enrolinstances = enrol_get_instances($courseid, true);
        if( $dofinstance = $DOF->modlib('ama')->course($courseid)->enrol_manager(false)->get_dof_enrol_instance() )
        {
            if( ! isset($enrolinstances[$dofinstance->id]) )
            {
                $enrolinstances += [$dofinstance->id => $dofinstance];
            }
        }
        if( empty($enrolinstances) )
        {
            return [];
        }
        list($eisql, $eiparams) = $DB->get_in_or_equal(array_keys($enrolinstances));
        $params = array_merge($params, $eiparams);
        $select = 'enrolid ' . $eisql . ' AND userid=?';
        $params[] = $this->get_id();
        $sql = 'SELECT ue.*, e.enrol as plugin
                  FROM {user_enrolments} ue
                  JOIN {enrol} e
                    ON ue.enrolid=e.id
                 WHERE ' . $select;
        return $DB->get_records_sql($sql, $params);
    }
    
    /**
     * Получить все роли пользователя в курсе
     * @param int $courseid идентификатор курса
     */
    public function get_user_roles_in_course($courseid)
    {
        $roles = [];
        $context = context_course::instance($courseid);
        $roleassignments = get_user_roles($context, $this->get_id());
        if( ! empty($roleassignments) )
        {
            foreach($roleassignments as $roleassignment)
            {
                $roles[$roleassignment->roleid] = $roleassignment->roleid;
            }
        }
        ksort($roles);
        return $roles;
    }
    
    /**
     * Returns a persons full name
     *
     * Given an object containing all of the users name values, this function returns a string with the full name of the person.
     * The result may depend on system settings or language.  'override' will force both names to be used even if system settings
     * specify one.
     *
     * @return string
     */
    public function fullname() {
        return fullname($this->get());
    }
    
    /**
     * Получение всех полей пользователя (стандартных полей + кастомных полей) с языковой строкой
     *
     * @param array $addfields - поля, которые необходимо добавить к списку стандартных ключ - строка с кодом
     * поля значение - название поля для отображения пользователю, будут с префиксом user_field_
     *
     * @return array возвращает поля с префиксами user_field_ или user_profilefield_
     */
    public function get_all_user_fields_list($addfields = [], $commonprefix = 'user_field_', $customprefix = 'user_profilefield_') {
        $fields = [];
        $userfields = $this->get_userfields_list($addfields);
        if (!empty($userfields)) {
            foreach ($userfields as $key => $userfield) {
                $fields[$commonprefix . $key] = $userfield;
            }
        }
        $customfields = $this->get_user_custom_fields();
        if (!empty($customfields)) {
            foreach ($customfields as $customfield) {
                $fields[$customprefix . $customfield->shortname] = $customfield->name;
            }
        }
        return $fields;
    }
    
    /**
     * Получить данные по всем полям пользователя
     *
     * @param array $addfields  - поля, которые необходимо добавить к списку стандартных ключ - строка с кодом
     * поля значение - название поля для отображения пользователю, будут с префиксом user_field_
     *
     * @return boolean|stdClass
     */
    public function get_all_user_fields_data($addfields = []) {
        return $this->get_fields_data($this->get(), $this->get_all_user_fields_list($addfields));
    }
}
