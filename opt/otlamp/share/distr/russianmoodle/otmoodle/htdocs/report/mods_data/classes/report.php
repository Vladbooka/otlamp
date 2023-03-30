<?php
class report_mods_data_report
{
    /**
     * Динамические заголовки отчета
     * @var array
     */
    private $uniondata = [];
    /**
     * Поддерживаемые форматы отчета
     * @var array
     */
    private $supportedformats = ['pdf', 'html', 'xls'];
    /**
     * Ориентация отчета (вертикальный, горизонтальный)
     * @var array
     */
    private $supportedorientations = ['v', 'h'];
    /**
     * Формат по умолчанию
     * @var string
     */
    private $exportformat = 'pdf';
    /**
     * Ориентация по умолчанию
     * @var string
     */
    private $reportorientation = 'v';
    /**
     * Пользователи, которых необходимо включить в отчет
     * @var array
     */
    private $users;
    /**
     * Критерий отбора по выполнению элементов
     * @var string
     */
    private $completion;
    /**
     * Критерий отбора по попыткам прохождения элементов
     * @var string
     */
    private $attempts;
    /**
     * Начало периода, за который необходимо собрать данные
     * @var int timestamp
     */
    private $startdate;
    /**
     * Конец периода, за которые необходимо собрать данные
     * @var int timestamp
     */
    private $enddate;
    /**
     * Критерий отбора попыток в периоде по завершению попытки
     * @var string
     */
    private $attemptsinperiod;
    /**
     * Формат представления даты
     * @var string
     */
    private $strtimeformat;
    /**
     * Настройка включения кеширования отчета
     * @var int|null
     */
    private $enablecron;
    
    private $attemptreportformat = 'pdf';
    
    private $maxelements;
    
    /**
     * 
     * @param array $uniondata - массив данных для формирования отчета
     * @param string $exportformat - формат отчета, возможны варианты pdf, xls, html
     * @param string $reportorientation - вертикальное ('v') или горизонтальное ('h') отображение
     * @param array $users - массив идентификаторов пользователей, которые должны попасть в отчет (по умолчанию все)
     * @param array $groups - массив идентификаторов локальных групп, пользователи из которых должны попасть в отчет (по умолчанию все)
     * @param string $completion - фильтрация элементов по выполнению (все, выполненные, не выполненные - all|completed|notcompleted)
     * @param string $attempts - фильтрация по попыткам прохождения (все, лучшие - all|best)
     * @param int $startdate - начало периода, за который необходимо предоставить данные (null - не ограничения снизу)
     * @param int $enddate - конец периода, за который необходимо предоставить данные (null - не ограничения сверху)
     * @param string $attemptsinperiod - какие попытки за период нужны: все или законченные
     */
    public function __construct(
        $uniondata, $exportformat = 'pdf', $reportorientation = null, 
        $users = [], $groups = [], $completion = 'all', $attempts = 'all', 
        $startdate = null, $enddate = null, $attemptsinperiod = 'all')
    {
        // Данные формы разбитые по каждому из отчетов

        if(!empty($uniondata))
        {
            $this->uniondata = $uniondata;
        }
        if (empty($groups)) {
            // Если фильтрации по группам нет, берем всех переданных пользователей
            $this->users = $users;
        } else {
            // Если есть фильтрация по группам, то берем только тех пользователей из переданных, которые находятся в переданных группах
            $this->users = $this->get_groups_members_array_intersect($groups, $users);
        }
        $this->completion = $completion;
        $this->attempts = $attempts;
        $this->startdate = $startdate;
        $this->enddate = $enddate;
        $this->attemptsinperiod = $attemptsinperiod;
        
        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));

        if(!empty($exportformat) && in_array($exportformat, $this->supportedformats))
        {//формат отчета
            $this->exportformat = $exportformat;
        }
        
        if(!empty($reportorientation) && in_array($reportorientation,$this->supportedorientations))
        {//указали принудительно ориентацию, которая поддерживается данным классом
            $this->reportorientation = $reportorientation;
        } else 
        {//установим дефолтную ориентацию для форматов
            switch($this->exportformat)
            {
                case 'xls': 
                    $xlsorientation = get_config('report_mods_data', 'xls_orientation');
                    if( $xlsorientation == 'h' )
                    {
                        $this->reportorientation = 'h';
                    } else 
                    {
                        $this->reportorientation = 'xls_v';
                    }
                    break;
                case 'html':
                case 'pdf': 
                default:
                    $this->reportorientation = 'v'; 
                    break;
            }
        }
        
        // Получение настройки включения кеширования
        $this->enablecron = get_config('report_mods_data', 'enablecron');
        
        $attemptreportformat = get_config('report_mods_data', 'quiz_attempt_report_default_format');
        $this->attemptreportformat = ! empty($attemptreportformat) ? $attemptreportformat : 'pdf';
        $this->maxelements = 0;
    }
    
    /**
     * Объединить отчеты в единый документ
     *
     * @param stdClass $uniondata - Данные о модулях и полях для объединения
     */
    public function get_report($userid=null, $download=false)
    {
        global $CFG;
    
        //получим данные для отображения таблицы согласно настройкам
        $reportdata = $this->get_reportdata($userid);
        if( ! empty($reportdata) )
        {
            $formatpath = $CFG->dirroot . '/report/mods_data/classes/format/' .$this->exportformat . '.php';
            if ( file_exists( $formatpath ) )
            {
                //подключение файла с классом требуемого формата
                require_once ($formatpath);
                $formatclass = 'report_mods_data_format_' . $this->exportformat;
                if ( class_exists($formatclass) )
                { // Подключение класса формата
                    $formatmanager = new $formatclass($reportdata);
                    
                    // обязательно нужно закрыть сессию
                    \core\session\manager::write_close();
                    
                    // запускается отображение/скачивание отчета, если формат не поддерживает возврат данных
                    //или если было запрошено отображение/скачивание
                    if ( ( $download || !method_exists($formatmanager, 'get_report') ) 
                        && method_exists($formatmanager, 'print_report'))
                    { 
                        ob_clean();
                        //распечатаем/выведем отчет
                        $formatmanager->print_report();
                        exit;
                    } else if( method_exists($formatmanager, 'get_report') )
                    {
                        //вернем данные отчета
                        $outputreport = $formatmanager->get_report();
                        return $outputreport;
                    }
                }
            }
        }
    }
    
    /**
     * Формирует данные для отчета в зависимости от полученных настроек
     *
     * @param array $uniondata
     * @param string $exportformat
     * @return boolean|array
     */
    protected function get_reportdata($userid=null)
    {
        global $CFG;
        require_once ($CFG->dirroot . '/report/mods_data/classes/subreport/userfields.php');
        require_once ($CFG->dirroot . '/report/mods_data/classes/subreport/customuserfields.php');
        require_once ($CFG->dirroot . '/report/mods_data/classes/subreport/dofpersonfields.php');
    
        // Данные не переданы
        if ( empty($this->uniondata) )
        {
            return false;
        }
        
        // массив с данными для отчета
        $rdata = [
            // Данные по пользователям
            'users' => [],
            // Заголовок первого уровня с названиями групп полей (названия модулей)
            'header1' => [],
            // Заголовок второго уровня с названиями полей
            'header2' => []
        ];
    
    
        // Нормализация переданных данных
        if ( ! isset($this->uniondata['userfields']) || ! is_array($this->uniondata['userfields']) )
        {
            $this->uniondata['userfields'] = [];
        }
        if ( ! isset($this->uniondata['customuserfields']) || ! is_array($this->uniondata['customuserfields']) )
        {
            $this->uniondata['customuserfields'] = [];
        }
        if ( ! isset($this->uniondata['dofpersonfields']) || ! is_array($this->uniondata['dofpersonfields']) )
        {
            $this->uniondata['dofpersonfields'] = [];
        }
        // Подключение класса суботчета по пользовательским полям
        $userfieldsmanager = new report_mods_data_userfields();
        // Добавление данных в отчет
        $userfieldsmanager->add_subreport_headers($this->uniondata['userfields'], $rdata);
        
        // Подключение класса суботчета по кастомным пользовательским полям
        $customuserfieldsmanager = new report_mods_data_customuserfields();
        // Добавление данных в отчет
        $customuserfieldsmanager->add_subreport_headers($this->uniondata['customuserfields'], $rdata);
    
        // Подключение класса суботчета по полям персоны деканата
        $dofpersonfieldsmanager = new report_mods_data_dofpersonfields();
        // Добавление данных в отчет
        $dofpersonfieldsmanager->add_subreport_headers($this->uniondata['dofpersonfields'], $rdata);
    
        // Получить поддерживаемые модули
        $supported_modules = report_mods_data_get_supported_modules();
        
        if( empty($this->enablecron) )
        {// Если настройка не включена - сброс кешей
            require_once($CFG->dirroot . '/report/mods_data/classes/report_helper.php');
            report_mods_data\report_helper::purgecaches();
        }
        
        // Получение данных из кеша
        $cache = cache::make('report_mods_data', 'fullreportdata');
        $reportdata = $cache->get('fullreportdata');
        
        //получение данных из отчетов модулей
        foreach ( $supported_modules as $modulename => $data )
        {
            if ( isset($this->uniondata[$modulename]) )
            { // Требуется получить отчет модуля
                foreach ( $this->uniondata[$modulename] as $cmid => $cmdata )
                { // Обработка каждого экземпляра модуля
                    if( $reportdata !== false)
                    {// Если кеш собран - используем его
                        foreach($reportdata as $cid => $data)
                        {
                            // Собираем данные из кеша в нужную структуру
                            if( ! isset($data[$cmid]) )
                            {
                                continue;
                            }
                            foreach($data[$cmid][$this->exportformat]['users'] as $uid => $udata)
                            {
                                if( ! empty($this->users) && ! in_array($uid, $this->users) )
                                {
                                    unset($data[$cmid][$this->exportformat]['users'][$uid]);
                                    continue;
                                }
                                if( ! empty($udata[$cid]['cms'][$this->completion][$cmid][$this->attempts]) )
                                {
                                    if( ! is_null($this->startdate) || ! is_null($this->enddate) )
                                    {
                                        foreach($udata[$cid]['cms'][$this->completion][$cmid][$this->attempts] as $attemptid => $attempt)
                                        {
                                            if( (! is_null($this->startdate) && ! is_null($attempt['timestart']) && $attempt['timestart'] < $this->startdate) ||
                                                (! is_null($this->enddate) && ! is_null($attempt['timefinish']) && $attempt['timefinish'] > $this->enddate) )
                                            {
                                                unset($udata[$cid]['cms'][$this->completion][$cmid][$this->attempts][$attemptid]);
                                                continue;
                                            } else
                                            {
                                                if( is_null($attempt['timestart']) )
                                                {
                                                    $udata[$cid]['cms'][$this->completion][$cmid][$this->attempts][$attemptid]['timestart'] =  '-';
                                                } else
                                                {
                                                    $udata[$cid]['cms'][$this->completion][$cmid][$this->attempts][$attemptid]['timestart'] =  userdate($attempt['timestart'], $this->strtimeformat);
                                                }
                                                if( is_null($attempt['timefinish']) )
                                                {
                                                    $udata[$cid]['cms'][$this->completion][$cmid][$this->attempts][$attemptid]['timefinish'] =  '-';
                                                } else
                                                {
                                                    $udata[$cid]['cms'][$this->completion][$cmid][$this->attempts][$attemptid]['timefinish'] =  userdate($attempt['timefinish'], $this->strtimeformat);
                                                }
                                            }
                                        }
                                    }
                                    if( ! empty($udata[$cid]['cms'][$this->completion][$cmid][$this->attempts]) )
                                    {
                                        $rdata['users'][$uid][$cid]['info'] = $udata[$cid]['info'];
                                        $rdata['users'][$uid][$cid]['cms'][$this->completion][$cmid][$this->attempts] = $udata[$cid]['cms'][$this->completion][$cmid][$this->attempts];
                                    }
                                }
                            }
                            if( isset($data[$cmid][$this->exportformat]['header1'][$cmid]) )
                            {
                                $rdata['header1'][$cmid] = $data[$cmid][$this->exportformat]['header1'][$cmid];
                            }
                            if( isset($data[$cmid][$this->exportformat]['header2'][$cmid]) )
                            {
                                $rdata['header2'][$cmid] = $data[$cmid][$this->exportformat]['header2'][$cmid];
                            }
                        }
                    } else 
                    {
                        $modulepath = $CFG->dirroot . '/report/mods_data/classes/subreport/modules/' .$modulename . '.php';
                        if ( file_exists( $modulepath ) )
                        {
                            require_once ($modulepath);
                            $subreportclass = 'report_mods_data_' . $modulename;
                            if ( class_exists($subreportclass) )
                            { // Подключение класса сбора отчета
                                $subreportmanager = new $subreportclass();
                                if ( method_exists($subreportmanager, 'add_subreport') )
                                { // Добавление данных отчета
                                    $subreportmanager->add_subreport(
                                        $cmid, 
                                        $rdata, 
                                        $this->exportformat, 
                                        $this->users, 
                                        $this->completion,
                                        $this->attempts, 
                                        $this->startdate, 
                                        $this->enddate,
                                        $this->attemptsinperiod
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        // Теперь, когда нам известны пользователи, по которым представлены данные в отчетах, добавление данных пользователей
        if ( ! empty($rdata['users']) )
        { // Необходимо собрать данные по пользователям
            foreach ( $rdata['users'] as $userid => &$data )
            { // Сбор данных по каждому пользователю
                $userfieldsmanager->add_userdata($data['userfields'], $this->uniondata['userfields'], $userid);
                $customuserfieldsmanager->add_userdata($data['customuserfields'], $this->uniondata['customuserfields'], $userid);
                $dofpersonfieldsmanager->add_userdata($data['dofpersonfields'],
                    $this->uniondata['dofpersonfields'], $userid);
            }
            switch($this->reportorientation)
            {
                case 'h':
                    $reportdata = $this->h_report_data($rdata);
                    break;
                case 'v':
                    $reportdata = $this->v_report_data($rdata);
                    break;
                case 'xls_v':
                    $reportdata = $this->xls_v_report_data($rdata);
                    break;
            }
        } else
        {//в отчет не попал ни один пользователь - отчета нет. Напишем информационное сообщение об этом
            $reportdata = [
                0 => [
                    0 => [
                        'class' => 'default', 
                        'data' => get_string('empty_report', 'report_mods_data')
                    ]
                ]
            ];
        }
        
        return $reportdata;
    }
    
    /**
     * Формирование данных для горизонтального отчета
     *
     * @param array $rdata исходные данные для отчета
     * @return array массив с данными для горизонтального отчета - как в результирующей таблице
     */
    protected function h_report_data($rdata)
    {
        $reportdata = [];
        // Указатель текущей ячейки
        $currentrow = 0;
        $currentcol = 0;
    
        // Заполнение заголовков
        if ( ! empty($rdata['header2']) )
        { // Есть поля отчета
            foreach ( $rdata['header2'] as $cmid => $cmheaders )
            { // Поля экземпляра
                $headerelements[$cmid] = [];
                if ( ! empty($cmheaders) )
                { // Есть поля экземпляра модуля
                    if ( isset($rdata['header1'][$cmid]) )
                    { // Добавление названия экземпляра модуля
                        if(!empty($rdata['header1'][$cmid]['coursename']))
                        {
                            $header1data = $rdata['header1'][$cmid]['coursename'].": ".$rdata['header1'][$cmid]['name'];
                        } else 
                        {
                            $header1data = $rdata['header1'][$cmid]['name'];
                        }
                        $reportdata[$currentrow][$currentcol] = [
                            'class' => 'header1',
                            'data' => $header1data
                        ];
                    }
                    foreach ( $cmheaders as $headername => $headerstrname )
                    { // Добавить каждое поле
                        $reportdata[$currentrow + 1][$currentcol] = [
                            'class' => 'header2',
                            'data' => $headerstrname
                        ];
                        $headerelements[$cmid][$headername] = $currentcol;
                        $currentcol ++;
                    }
                }
            }
        }
    
        // Указатель текущей ячейки
        $currentrow = 2;
        $currentcol = 0;
        foreach ( $rdata['users'] as $userid => $userdata )
        {
            if ( empty($userdata) )
            { // Данных нет
                continue;
            }
    
            $empty = true;
            foreach($userdata as $subreporttype => $subreportdata)
            {
                if( in_array($subreporttype,['userfields','customuserfields','dofpersonfields']) || ! isset($subreportdata['cms'][$this->completion]) )
                {
                    continue;
                }
            
                foreach($subreportdata['cms'][$this->completion] as $cmid => $attempts)
                {
                    foreach($attempts[$this->attempts] as $attempt)
                    {
                        if( ! empty($attempt) )
                        {
                            $empty = false;
                            break;
                        }
                    }
                    if( ! $empty )
                    {
                        break;
                    }
                }
            }
            
            if( $empty )
            {
                continue;
            }
            
            // Перевод текущей строки
            $userstartrow = $currentrow;
            $userfinalrow = $currentrow;
            foreach ( $userdata as $subreporttype => $subreportdata )
            { // Добавление данных пользователя по элементу(модуль или данные профиля)
                if( in_array($subreporttype,['userfields','customuserfields','dofpersonfields']) )
                {//обработка данных профиля
                    if (!empty($subreportdata)) {
                        foreach ( $subreportdata as $itemfield => $itemvalue )
                        {
                            if ( isset($headerelements[$subreporttype][$itemfield]) )
                            { // Заголоваок есть - добавление данных пользователя в первую строку
                                $reportdata[$userstartrow][$headerelements[$subreporttype][$itemfield]] = [
                                    'class' => 'value',
                                    'data' => $itemvalue
                                ];
                            }
                        }
                    }
                }
                else
                {
                    $courseid = $subreporttype;
                    $coursedata = $subreportdata;
                    foreach ( $coursedata['cms'][$this->completion] as $cmid => $cmdata )
                    { // Добавление данных пользователя по элементу(модуль или данные профиля)

                        if ( ! empty($cmdata[$this->attempts]) )
                        { // Данные по элементу есть
                            $userсurrentrow = $userstartrow;
                            foreach ( $cmdata[$this->attempts] as $key => $item )
                            {
                                if ( ! empty($item) )
                                { // Данные есть
                                    if ( is_array($item) )
                                    { // Элемент является экземпляром действий пользователя в модуле
                                        foreach ( $item as $itemfield => $itemvalue )
                                        { // Заполнение данных пользователя в экземпляре модуля
                                            if ( isset($headerelements[$cmid][$itemfield]) )
                                            { // Заголоваок есть
                                                $reportdata[$userсurrentrow][$headerelements[$cmid][$itemfield]] = [
                                                    'class' => 'value',
                                                    'data' => $itemvalue
                                                ];
                                            }
                                        }
                                        if ( $userсurrentrow > $userfinalrow )
                                        { // Последняя строка пользовательских данных
                                            $userfinalrow = $userсurrentrow;
                                        }
                                        $userсurrentrow ++;
                                    }
                                }
                            }
                        }
                    }
                }
                $currentrow = $userfinalrow + 1;
                
            }
        }
        return $this->add_missed_cells($reportdata);
    }
    
    /**
     * Формирование данных для вертикального отчета
     *
     * @param array $rdata исходные данные для отчета
     * @return array массив с данными для вертикального отчета - как в результирующей таблице
     */
    protected function v_report_data($rdata)
    {
        $reportdata = [];
        // Указатель текущей строки с данными
        $currentrow = 0;
    
        
        foreach ( $rdata['users'] as $userid => $userdata )
        {//данные по каждому пользователю

            if ( empty($userdata) )
            { // Данных нет
                continue;
            }
    
            //выстраиваем порядок отображения данных - как в заголовках
            $userdata = array_replace(array_flip(array_keys($rdata['header2'])), $userdata);

            $empty = true;
            foreach($userdata as $subreporttype => $subreportdata)
            {
                if( in_array($subreporttype,['userfields','customuserfields','dofpersonfields']) || ! isset($subreportdata['cms'][$this->completion]) )
                {
                    continue;
                }

                foreach($subreportdata['cms'][$this->completion] as $cmid => $attempts)
                {
                    foreach($attempts[$this->attempts] as $attempt)
                    {
                        if( ! empty($attempt) )
                        {
                            $empty = false;
                            break;
                        }
                    }
                    if( ! $empty )
                    {
                        break;
                    }
                }
            }
            
            if( $empty )
            {
                continue;
            }
            
            foreach ( $userdata as $subreporttype => $subreportdata )
            {
                if( in_array($subreporttype,['userfields','customuserfields','dofpersonfields']) )
                { // Элемент - данные профиля
                    if ( isset($rdata['header1'][$subreporttype]) )
                    { // Добавление заголовка первого уровня, название экземпляра модуля или поля профиля
                        $reportdata[$currentrow][0] = [
                            'class' => 'header1',
                            'data' => $rdata['header1'][$subreporttype]['name']
                        ];
                        $currentrow++;
                    }
                    if(!empty($subreportdata ) && is_array($subreportdata ))
                    {
                        foreach ( $subreportdata as $itemfield => $itemvalue )
                        {
                            if ( isset($rdata['header2'][$subreporttype][$itemfield]) )
                            { // Заголоваок есть
                                //добавляем заголовок второго уровня
                                $reportdata[$currentrow][1] = [
                                    'class' => 'header2',
                                    'data' => $rdata['header2'][$subreporttype][$itemfield]
                                ];
                                //добавляем значение для поля
                                $reportdata[$currentrow][2] = [
                                    'class' => 'value',
                                    'data' => $itemvalue
                                ];
                                $currentrow++;
                            }
                        }
                    }
                }
                else
                {
                    
                    $courseid = $subreporttype;
                    $coursedata = $subreportdata;
                    
                    if(!empty($coursedata) && !empty($coursedata['cms'][$this->completion]))
                    {
                        $anonymous = "";
                        if ( $userid == 0 )
                        {
                            $anonymous = get_string('anonymous','report_mods_data');
                        }
                        $reportdata[$currentrow][0] = [
                            'class' => 'header1',
                            'data' => $coursedata['info']->fullname.$anonymous
                        ];
                        $currentrow++;
                        
                        foreach ( $coursedata['cms'][$this->completion] as $cmid => $attempts )
                        { // Добавление данных пользователя по элементу(модуль или данные профиля)
                            if ( ! empty($attempts[$this->attempts]) )
                            { // Данные по элементу есть
                                if ( isset($rdata['header1'][$cmid]) )
                                { // Добавление заголовка первого уровня, название экземпляра модуля или поля профиля
                                    $reportdata[$currentrow][1] = [
                                        'class' => 'header1',
                                        'data' => $rdata['header1'][$cmid]['name']
                                    ];
                                    $currentrow++;
                                }
                                foreach ( $attempts[$this->attempts] as $attempt )
                                {
                                    if ( ! empty($attempt) )
                                    { // Данные есть
                                        if ( is_array($attempt) )
                                        { // Элемент является экземпляром модуля
                                            foreach ( $attempt as $itemfield => $itemvalue )
                                            { // Заполнение данных пользователя в экземпляре модуля
                                                if ( isset($rdata['header2'][$cmid][$itemfield]) )
                                                { // Заголовак есть
                                                    //добавляем заголовок второго уровня
                                                    $reportdata[$currentrow][2] = [
                                                        'class' => 'header2',
                                                        'data' => $rdata['header2'][$cmid][$itemfield]
                                                    ];
                                                    //добавляем значение для поля
                                                    $reportdata[$currentrow][3] = [
                                                        'class' => 'value',
                                                        'data' => $itemvalue
                                                    ];
                                                    $currentrow++;
                                                }
                                            }
                                            if( $this->exportformat == 'html' )
                                            {
                                                list($course, $cm_info) = get_course_and_cm_from_cmid($cmid);
                                                $context = context_course::instance($course->id);
                                                if( has_capability('report/mods_data:view_quiz_attempt_report', $context) )
                                                {
                                                    $cm = $cm_info->get_course_module_record(true);
                                                    if( $cm->modname == 'quiz' )
                                                    {
                                                        $attr = ['class' => 'btn btn-primary'];
                                                        if( $this->attemptreportformat == 'html' )
                                                        {
                                                            $attr['target'] = '_blank';
                                                        }
                                                        $reportdata[$currentrow][3] = [
                                                            'class' => 'value',
                                                            'data' => html_writer::link(
                                                                new moodle_url(
                                                                    '/report/mods_data/quiz_attempt.php',
                                                                    [
                                                                        'id' => $attempt['attempt'],
                                                                        'format' => $this->attemptreportformat
                                                                    ]),
                                                                get_string('quiz_attempt_' . $this->attemptreportformat .'_report_link_title', 'report_mods_data'),
                                                                $attr
                                                                ),
                                                        ];
                                                        $currentrow++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $this->add_missed_cells($reportdata);
    }
    
    protected function xls_v_report_data($rdata)
    {
        $reportdata = [];
        // Указатель текущей строки с данными
        $currentrow = 0;
        $currentcell = 0;
        
        $firstheadersfill = false;
        $headerprinted = false;
        foreach ( $rdata['users'] as $userid => $userdata )
        {//данные по каждому пользователю
            
            if ( empty($userdata) )
            { // Данных нет
                continue;
            }
        
            //выстраиваем порядок отображения данных - как в заголовках
            $userdata = array_replace(array_flip(array_keys($rdata['header2'])), $userdata);
            
            $empty = true;
            foreach($userdata as $subreporttype => $subreportdata)
            {
                if( in_array($subreporttype,['userfields','customuserfields','dofpersonfields']) || ! isset($subreportdata['cms'][$this->completion]) )
                {
                    continue;
                }
            
                foreach($subreportdata['cms'][$this->completion] as $cmid => $attempts)
                {
                    foreach($attempts[$this->attempts] as $attempt)
                    {
                        if( ! empty($attempt) )
                        {
                            $empty = false;
                            break;
                        }
                    }
                    if( ! $empty )
                    {
                        break;
                    }
                }
            }
        
            if( $empty )
            {
                continue;
            }
            
            $fields = [];
            foreach ( $userdata as $subreporttype => $subreportdata )
            {
                if( in_array($subreporttype,['userfields','customuserfields','dofpersonfields']) )
                { // Элемент - данные профиля
                    if(!empty($subreportdata ) && is_array($subreportdata) )
                    {
                        foreach ( $subreportdata as $itemfield => $itemvalue )
                        {
                            if ( isset($rdata['header2'][$subreporttype][$itemfield]) )
                            { // Заголоваок есть
                                //добавляем заголовок второго уровня
                                if( ! $firstheadersfill )
                                {
                                    $reportdata[$currentrow][$currentcell] = [
                                        'class' => 'header2',
                                        'data' => $rdata['header2'][$subreporttype][$itemfield]
                                    ];
                                }

                                $fields[$currentcell] = [
                                    'class' => 'value',
                                    'data' => $itemvalue
                                ];
                                $currentcell++;
                            }
                        }
                        if( ! $firstheadersfill )
                        {
                            $currentrow++;
                        }
                    }
                }
                else
                {
                    $courseid = $subreporttype;
                    $coursedata = $subreportdata;
            
                    if(!empty($coursedata) && !empty($coursedata['cms'][$this->completion]))
                    {
                        foreach ( $coursedata['cms'][$this->completion] as $cmid => $attempts )
                        { // Добавление данных пользователя по элементу(модуль или данные профиля)
                            if ( ! empty($attempts[$this->attempts]) )
                            { // Данные по элементу есть
                                
                                $tempcell = $currentcell;
                                foreach ( $attempts[$this->attempts] as $attempt )
                                {
                                    if ( ! empty($attempt) )
                                    { // Данные есть
                                        if ( is_array($attempt) )
                                        { // Элемент является экземпляром модуля
                                            foreach($fields as $cell => $field)
                                            {
                                                $reportdata[$currentrow][$cell] = $field;
                                            }
                                            if ( isset($rdata['header1'][$cmid]) )
                                            { // Добавление заголовка первого уровня, название экземпляра модуля или поля профиля
                                                if( ! $headerprinted )
                                                {
                                                    $reportdata[$currentrow-1][$currentcell] = [
                                                        'class' => 'header2',
                                                        'data' => 'Модуль'
                                                    ];;
                                                }
                                                $reportdata[$currentrow][$currentcell] = [
                                                    'class' => 'value',
                                                    'data' => $rdata['header1'][$cmid]['name']
                                                ];
                                                
                                                $currentcell++;
                                                
                                                if( ! $headerprinted )
                                                {
                                                    $reportdata[$currentrow-1][$currentcell] = [
                                                        'class' => 'header2',
                                                        'data' => 'Курс'
                                                    ];
                                                }
                                                $reportdata[$currentrow][$currentcell] = [
                                                    'class' => 'value',
                                                    'data' => $rdata['header1'][$cmid]['coursename']
                                                ];
                                                $currentcell++;
                                            }
                                            
                                            foreach ( $attempt as $itemfield => $itemvalue )
                                            { // Заполнение данных пользователя в экземпляре модуля
                                                if ( isset($rdata['header2'][$cmid][$itemfield]) )
                                                { // Заголовак есть
                                                    //добавляем заголовок второго уровня
                                                    if( ! $headerprinted )
                                                    {
                                                        $reportdata[$currentrow-1][$currentcell] = [
                                                            'class' => 'header2',
                                                            'data' => $rdata['header2'][$cmid][$itemfield]
                                                        ];
                                                    }
                                                    //добавляем значение для поля
                                                    $reportdata[$currentrow][$currentcell] = [
                                                        'class' => 'value',
                                                        'data' => $itemvalue
                                                    ];
                                                    $currentcell++;
                                                }
                                            }
                                            $this->maxelements = max($this->maxelements, $currentcell-1);
                                        }
                                    }
                                    if( ! $headerprinted )
                                    {
                                        $headerprinted = true;
                                    }
                                    $currentrow++;
                                    $currentcell = $tempcell;
                                }
                            }
                        }
                    }
                    $firstheadersfill = true;
                }
            }
            $fields = [];
            $currentcell = 0;
        }

        $reportdata = $this->add_missed_headers($reportdata);
        return $this->add_missed_cells($reportdata);
    }
    
    private function add_missed_headers($reportdata)
    {
        $all = count($reportdata[0]);
        if( $all < $this->maxelements )
        {
            $last = end($reportdata[0]);
            $index = explode(' ', $last['data']);
            $number = $index[2]+1;
            for($i=$all; $i<$this->maxelements; $i=$i+2)
            {
                $reportdata[0][$i] = [
                    'class' => 'header2',
                    'data' => get_string('questionx', 'question', $number)
                ];
                $reportdata[0][$i+1] = [
                    'class' => 'header2',
                    'data' => get_string('statex', 'report_mods_data', $number)
                ];
                $number++;
            }
        }
        return $reportdata;
    }
    
    /**
     * Добавление недостающих ячеек
     * При формировании данных для отчета, в определенные ячейки записывались требуемые значения
     * Некоторые ячейки могли оказаться пропущены, что негативно сказывается на формирование pdf
     * из html-таблицы с недостающими ячейками
     *
     * @param array $reportdata - массив с данными для отчета
     * @return array - массив с дополненными пустыми ячейками, отсортированный по ключам
     */
    private function add_missed_cells($reportdata)
    {
        //выясним максимальное количество ячеек на строку в массиве
        $maxcellsinrow = 0;
        for ($r = 0; $r <= max(array_keys($reportdata)); $r ++)
        {
            $cellsinrow = max(array_keys($reportdata[$r]));
            if($cellsinrow>$maxcellsinrow)
            {
                $maxcellsinrow=$cellsinrow;
            }
        }
        //недостающие ячейки наполним пустыми значениями
        for ($r = 0; $r <= max(array_keys($reportdata)); $r ++)
        {
            for ($c = 0; $c <= $maxcellsinrow; $c ++)
            {
                if ( ! isset($reportdata[$r][$c]) )
                {
                    $reportdata[$r][$c] = [
                        'class'=>'empty',
                        'data'=>''
                    ];
                }
            }
            ksort($reportdata[$r]);
        }
        ksort($reportdata);
    
        return $reportdata;
    }
    
    public function get_supported_formats()
    {
        return $this->supportedformats;
    }
    
    /**
     * Получить пересечение пользователей с локальными группами
     * @param array $groupsids массив идентификаторов локальных групп
     * @param array $userids массив идентификаторо пользователей
     * @param string $sort колонка и направление сортировки
     * @return array массив пользователей, которые есть в переданном массиве пользователей, которые состоят в переданных локальных группах
     */
    private function get_groups_members_array_intersect($groupsids, $userids, $sort = 'lastname ASC') {
        global $DB;
        $result = [];
        if (empty($userids) || empty($groupsids)) {
            return $result;
        }
        list($groupsinsql, $groupsparams) = $DB->get_in_or_equal($groupsids, SQL_PARAMS_NAMED);
        list($usersinsql, $usersparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params = array_merge($groupsparams, $usersparams);
        $sql = "SELECT u.id
                  FROM {user} u
             LEFT JOIN {groups_members} gm
                    ON u.id = gm.userid
                 WHERE u.id $usersinsql AND gm.groupid $groupsinsql
              GROUP BY u.id
              ORDER BY $sort";
        return $DB->get_fieldset_sql($sql, $params);
    }
}