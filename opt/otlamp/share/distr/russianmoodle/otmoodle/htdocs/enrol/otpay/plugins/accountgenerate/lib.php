<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Плагин записи на курс OTPAY. Основной класс псевдосабплагина генерации счета
 *
 * @package    enrol
 * @subpackage otpay
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . "/enrol/otpay/plugins/otpay.php");

// Подключение файла класса работы с PDF
require_once($CFG->libdir.'/pdflib.php');

class otpay_accountgenerate extends otpay
{
    /**
     * Массив сценариев
     *
     * @var array
     */
    protected $scenarios = null;

    /**
     * Массив форм
     *
     * @var array
     */
    protected $forms = null;

    /**
     * Массив шаблонов
     *
     * @var array
     */
    protected $templates = null;

    /**
     * Флаг присутствия цены у сценария
     *
     * @var bool
     */
    protected $hascost = true;

    /**
     * Инициализация провайдера
     *
     * @param otpay $plugin - Плагин подписки на курс
     */
    public function __construct(enrol_otpay_plugin $plugin)
    {
        GLOBAL $CFG;

        parent::__construct($plugin);

        if ( ! class_exists('otpay_accountgenerate_scenario_base') )
        {
            require($CFG->dirroot . '/enrol/otpay/plugins/accountgenerate/scenarios/base.php');
        }
        if ( ! class_exists('otpay_accountgenerate_form_base') )
        {
            require($CFG->dirroot . '/enrol/otpay/plugins/accountgenerate/forms/base.php');
        }
        if ( ! class_exists('otpay_accountgenerate_template_base') )
        {
            require($CFG->dirroot . '/enrol/otpay/plugins/accountgenerate/templates/base.php');
        }
    }

    /**
     * Получить версию псевдосабплагина
     *
     * @return int
     */
    public function version()
    {
        return 2017100900;
    }

    /**
     * Массив маршрутов статусов
     *
     * @return array
     */
    function get_statuses_route()
    {
        return [
            'draft' => ['confirmed'],
            'confirmed' => []
        ];
    }

    /**
     * Получение комментария
     *
     * @param stdClass $enrolnment
     *
     * @return string
     */
    public function get_comment($enrolnment)
    {
        // Сбор комментария
        $comment = '';

        if ( empty($enrolnment) || empty($enrolnment->options) )
        {
            return $comment;
        }
        $unserialized = unserialize($enrolnment->options);
        if ( empty($unserialized) || empty($unserialized['account']) )
        {
            return $comment;
        }

        // Данные для отображения в поле комментария
        $account = $unserialized['account'];
        unset($account->type_code);
        if ( empty($account) )
        {
            return $comment;
        }
        foreach ( $account as $field => $value )
        {
            $comment .= html_writer::div(get_string($field, 'enrol_otpay', (string)$value)).PHP_EOL;
        }

        return $comment;
    }

    /**
     * Получение сценариев
     *
     * @return array
     */
    public function get_available_scenarios()
    {
        GLOBAL $CFG;

        // Путь к сабплагинам
        $basedir = $CFG->dirroot . '/enrol/otpay/plugins/accountgenerate/scenarios';

        if ( is_null($this->scenarios) )
        {
            // Поиск и инициализация Сабплагинов
            foreach ( (array)scandir($basedir) as $pluginname )
            {
                if ( $pluginname == '.' || $pluginname == '..' )
                {
                    continue;
                }

                if ( is_dir($basedir.'/'.$pluginname) )
                {// Папка с сабплагином

                    // Путь к файлу с классом сабплагина
                    $pluginpath = $basedir . '/' . $pluginname . '/init.php';

                    if ( file_exists($pluginpath) )
                    {// Класс сабплагина найден
                        require_once($pluginpath);

                        $classname = 'otpay_accountgenerate_scenario_' . $pluginname;
                        if ( class_exists($classname) )
                        {// Инициализация провайдера

                            $subplugin = new $classname($this);

                            // Добавление сабплагина
                            $this->scenarios[$pluginname] = $subplugin;
                        }
                    }
                }
            }
        }

        return $this->scenarios;
    }

    /**
     * Получение форм
     *
     * @return array
     */
    public function get_available_forms()
    {
        GLOBAL $CFG;

        // Путь к сабплагинам
        $basedir = $CFG->dirroot . '/enrol/otpay/plugins/accountgenerate/forms';

        if ( is_null($this->forms) )
        {
            // Поиск и инициализация Сабплагинов
            foreach ( (array)scandir($basedir) as $pluginname )
            {
                if ( $pluginname == '.' || $pluginname == '..' )
                {
                    continue;
                }

                if ( is_dir($basedir.'/'.$pluginname) )
                {// Папка с сабплагином

                    // Путь к файлу с классом сабплагина
                    $pluginpath = $basedir . '/' . $pluginname . '/init.php';

                    if ( file_exists($pluginpath) )
                    {// Класс сабплагина найден
                        require_once($pluginpath);

                        $classname = 'otpay_accountgenerate_form_' . $pluginname;
                        if ( class_exists($classname) )
                        {// Инициализация провайдера

                            $subplugin = new $classname($this);

                            // Добавление сабплагина
                            $this->forms[$pluginname] = $subplugin;
                        }
                    }
                }
            }
        }

        return $this->forms;
    }

    /**
     * Получение шаблонов
     *
     * @return array
     */
    public function get_available_templates()
    {
        GLOBAL $CFG;

        // Путь к сабплагинам
        $basedir = $CFG->dirroot . '/enrol/otpay/plugins/accountgenerate/templates';

        if ( is_null($this->templates) )
        {
            // Поиск и инициализация Сабплагинов
            foreach ( (array)scandir($basedir) as $pluginname )
            {
                if ( $pluginname == '.' || $pluginname == '..' )
                {
                    continue;
                }

                if ( is_dir($basedir.'/'.$pluginname) )
                {// Папка с сабплагином

                    // Путь к файлу с классом сабплагина
                    $pluginpath = $basedir . '/' . $pluginname . '/init.php';

                    if ( file_exists($pluginpath) )
                    {// Класс сабплагина найден
                        require_once($pluginpath);

                        $classname = 'otpay_accountgenerate_template_' . $pluginname;
                        if ( class_exists($classname) )
                        {// Инициализация провайдера

                            $subplugin = new $classname($this);

                            // Добавление сабплагина
                            $this->templates[$pluginname] = $subplugin;
                        }
                    }
                }
            }
        }

        return $this->templates;
    }

    /**
     * Замена макроподстановок HTML формы сабплагина
     *
     * @param string $html
     * @param stdClass $account
     * @param stdClass $enrolnment
     *
     * @return string
     */
    public function prepare_html($html = '', stdClass $account, stdClass $enrolnment, $selected_form)
    {
        // Фильтруем поля для работы с составными строками вида "КПП 7776665554", где 7776665554 - данные, приходящие из формы
        $account = $selected_form->filter_fields($account);
        // Получение полей из настроек сабплагина
        $settings_fields = $this->get_settings_fields();

        // Получение служебных полей
        $service_fields = $this->get_service_fields($enrolnment);

        $matches = [];
        preg_match_all('/\${(.*?)\}/', $html, $matches);

        if ( ! empty($matches[0]) )
        {
            // Полные совпадения
            foreach ( $matches[0] as $id => $field )
            {
                if ( ! empty($account->{$matches[1][$id]}) )
                {
                    // Поиск в сформированной форме
                    $html = str_replace($field, $account->{$matches[1][$id]}, $html);
                } elseif ( array_key_exists($matches[1][$id], $settings_fields) )
                {
                    // Поиск в настройках сабплагина
                    $html = str_replace($field, $settings_fields[$matches[1][$id]], $html);
                } elseif ( array_key_exists($matches[1][$id], $service_fields) )
                {
                    // Обработка служебных полей
                    $html = str_replace($field, $service_fields[$matches[1][$id]], $html);
                } else
                {
                    // Ничего не найдено, выставление пустого поля
                    $html = str_replace($field, '', $html);
                }
            }
        }

        return $html;
    }

    /**
     * Обновление полей формы
     *
     * @param string $field
     *
     * @return number[]
     */
    public function prepare_form_element($field)
    {
        global $USER;
        $options = [];

        switch ( $field )
        {
            case 'payer':
            case 'payerfio':
                $options['value'] = fullname($USER);
                break;

            default:
                break;
        }

        // Добавление плейсхолдеров
        $placehorders = $this->get_placeholder_fields();
        if ( array_key_exists($field, $placehorders) && ! empty($placehorders[$field]))
        {
            $options['placeholder'] = $placehorders[$field];
        }

        return $options;
    }

    /**
     * Получить плейсхолдеры
     *
     * @return string[]
     */
    public function get_placeholder_fields()
    {

        return [
            'payeremail' => get_string('placeholder_payeremail', 'enrol_otpay'),
            'payeraddr' => get_string('placeholder_payeraddr', 'enrol_otpay'),
            'payeraddrmail' => get_string('placeholder_payeraddrmail', 'enrol_otpay'),
            'payerphone' => get_string('placeholder_payerphone', 'enrol_otpay'),
            'payerinn' => get_string('placeholder_payerinn', 'enrol_otpay'),
            'payerkpp' => get_string('placeholder_payerkpp', 'enrol_otpay'),
            'payername' => get_string('placeholder_payername', 'enrol_otpay'),
            'payerfio' => get_string('placeholder_fio', 'enrol_otpay'),
            'payer' => get_string('placeholder_payer', 'enrol_otpay')
        ];
    }

    /**
     * Получение полей из настроек
     *
     * @return array
     */
    public function get_settings_fields()
    {
        $settings = [];

        // Получение списка полей
        $fields =  [
            'recipient' => PARAM_RAW_TRIMMED,
            'kpp' => PARAM_RAW_TRIMMED,
            'inn' => PARAM_RAW_TRIMMED,
            'oktmo' => PARAM_RAW_TRIMMED,
            'raccount' => PARAM_RAW_TRIMMED,
            'rinn' => PARAM_RAW_TRIMMED,
            'bik' => PARAM_RAW_TRIMMED,
            'kaccount' => PARAM_RAW_TRIMMED,
            'kbk' => PARAM_RAW_TRIMMED,
            'account_number' => PARAM_RAW_TRIMMED
        ];

        foreach ( $fields as $field => $type )
        {
            $settings[$field] = get_config('enrol_otpay', 'accountgenerate_' . $field);
        }

        return $settings;
    }

    /**
     * Получение служебной информации о подписке
     *
     * @param stdClass $enrolnment
     *
     * @return array
     */
    public function get_service_fields(stdClass $enrolnment)
    {
        global $DB;

        $fields = [];

        // Разделение суммы на целую часть и дробную
        $exploded_amount = explode('.', $enrolnment->amount);

        // Получение способа записи
        $enrol = $DB->get_record('enrol', ['id' => $enrolnment->instanceid]);

        // Получение курса
        $course = get_course($enrolnment->courseid);

        $options = unserialize($enrolnment->options);

        // Текущее время
        $time = time();

        // Формирование объекта для языковой строки
        $sum = new stdClass();
        $sum->int = $exploded_amount[0];
        $sum->fract = $exploded_amount[1];

        $fields['amount'] = get_string('sum_amount_' . $enrolnment->currency, 'enrol_otpay', $sum);
        $fields['date_dot'] = date('d.m.Y', time());
        $fields['amount_int'] = $sum->int . ',' . $sum->fract;
        $fields['amount_string'] = $sum->int . ',' . $sum->fract . ' (' . $this->get_string_by_number($sum->int, $sum->fract) . ')';
        $fields['course_start'] = get_string('withyear', 'enrol_otpay', date('d.m.Y', $course->startdate));
        $fields['course_end'] = get_string('withyear', 'enrol_otpay', date('d.m.Y', $course->startdate + $enrol->enrolperiod));
        $fields['enrol_start'] = get_string('withyear', 'enrol_otpay', date('d.m.Y', $time));
        $fields['enrol_end'] = get_string('withyear', 'enrol_otpay', date('d.m.Y', $time + $enrol->enrolperiod));
        // Код курса
        $fields['course_code'] = get_string('for_payment', 'enrol_otpay', $course->shortname);
        // Получатель
        $fields['recipient_name'] = get_string('recipient_name', 'enrol_otpay');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $processed_months = [];
        foreach ( $months as $month )
        {
            $processed_months[$month] = get_string($month, 'enrol_otpay');
        }
        $cur_date = date('«d» M Y', time());

        $string_for_account_number_course_code = new stdClass();
        $string_for_account_number_course_code->course_code = $course->shortname;
        $string_for_account_number_course_code->account_number = $options['account']->account_number;
        $string_for_account_number_course_code->date = str_replace(array_keys($processed_months), array_values($processed_months), date('«d» M Y', time()));
        $fields['for_account_number_course_code'] = get_string('for_account_number_course_code', 'enrol_otpay', $string_for_account_number_course_code);
        // Адрес
        $fields['payeraddr'] = '________________________';
        $fields['payerlaccount'] = '________________________';

        return $fields;
    }

    /**
     * Преобразование числа в текст
     *
     * @param int $number - целая часть
     * @param int $kopecks - копейки
     *
     * @return string
     */
    public function get_string_by_number($number, $kopecks)
    {

        // обозначаем словарь в виде статической переменной функции, чтобы
        // при повторном использовании функции его не определять заново
        static $dic = [

            // словарь необходимых чисел
            [
                -2	=> 'две',
                -1	=> 'одна',
                1	=> 'один',
                2	=> 'два',
                3	=> 'три',
                4	=> 'четыре',
                5	=> 'пять',
                6	=> 'шесть',
                7	=> 'семь',
                8	=> 'восемь',
                9	=> 'девять',
                10	=> 'десять',
                11	=> 'одиннадцать',
                12	=> 'двенадцать',
                13	=> 'тринадцать',
                14	=> 'четырнадцать' ,
                15	=> 'пятнадцать',
                16	=> 'шестнадцать',
                17	=> 'семнадцать',
                18	=> 'восемнадцать',
                19	=> 'девятнадцать',
                20	=> 'двадцать',
                30	=> 'тридцать',
                40	=> 'сорок',
                50	=> 'пятьдесят',
                60	=> 'шестьдесят',
                70	=> 'семьдесят',
                80	=> 'восемьдесят',
                90	=> 'девяносто',
                100	=> 'сто',
                200	=> 'двести',
                300	=> 'триста',
                400	=> 'четыреста',
                500	=> 'пятьсот',
                600	=> 'шестьсот',
                700	=> 'семьсот',
                800	=> 'восемьсот',
                900	=> 'девятьсот'
            ],

            // словарь порядков со склонениями для плюрализации
            [
                ['рубль', 'рубля', 'рублей'],
                ['тысяча', 'тысячи', 'тысяч'],
                ['миллион', 'миллиона', 'миллионов'],
                ['миллиард', 'миллиарда', 'миллиардов'],
                ['триллион', 'триллиона', 'триллионов'],
                ['квадриллион', 'квадриллиона', 'квадриллионов'],
                // квинтиллион, секстиллион и т.д.
            ],

            // карта плюрализации
            [
                2, 0, 1, 1, 1, 2
            ]
        ];

        // обозначаем переменную в которую будем писать сгенерированный текст
        $string = [];

        // дополняем число нулями слева до количества цифр кратного трем,
        // например 1234, преобразуется в 001234
        $number = str_pad($number, ceil(strlen($number)/3)*3, 0, STR_PAD_LEFT);

        // разбиваем число на части из 3 цифр (порядки) и инвертируем порядок частей,
        // т.к. мы не знаем максимальный порядок числа и будем бежать снизу
        // единицы, тысячи, миллионы и т.д.
        $parts = array_reverse(str_split($number,3));

        // бежим по каждой части
        foreach( $parts as $i => $part )
        {

            // если часть не равна нулю, нам надо преобразовать ее в текст
            if( $part > 0 )
            {

                // обозначаем переменную в которую будем писать составные числа для текущей части
                $digits = [];

                // если число треххзначное, запоминаем количество сотен
                if( $part > 99 )
                {
                    $digits[] = floor($part/100)*100;
                }

                // если последние 2 цифры не равны нулю, продолжаем искать составные числа
                // (данный блок прокомментирую при необходимости)
                if($mod1 = $part%100)
                {
                    $mod2 = $part%10;
                    $flag = $i==1 && $mod1!=11 && $mod1!=12 && $mod2<3 ? -1 : 1;
                    if($mod1 < 20 || ! $mod2)
                    {
                        $digits[] = $flag*$mod1;
                    } else
                    {
                        $digits[] = floor($mod1/10)*10;
                        $digits[] = $flag*$mod2;
                    }
                }

                // берем последнее составное число, для плюрализации
                $last = abs(end($digits));

                // преобразуем все составные числа в слова
                foreach( $digits as $j => $digit )
                {
                    $digits[$j] = $dic[0][$digit];
                }

                // добавляем обозначение порядка или валюту
                $digits[] = $dic[1][$i][(($last%=100)>4 && $last<20) ? 2 : $dic[2][min($last%10,5)]];

                // объединяем составные числа в единый текст и добавляем в переменную, которую вернет функция
                array_unshift($string, join(' ', $digits));
            }
        }

        // преобразуем переменную в текст и возвращаем из функции, ура!
        return join(' ', $string) . ' ' . $kopecks . ' ' . get_string('kopecks', 'enrol_otpay');
    }


    /**
     * Экспорт формы
     *
     * @param array $pages
     * @param stdClass $account
     * @param string $html
     * @param array $options
     *
     * @throws moodle_exception
     * @return void
     */
    public function export_pdf($pages = [], stdClass $account, $options = [])
    {
        \core_form\util::form_download_complete();
        if ( empty($account) || empty($pages) )
        {
            // Некорректный сабплагин
            throw new moodle_exception('otpay_accountgenerate_incorrect_subplugin');
        }

        $default_fontsize = 13;
        if ( ! empty($options['fontsize']) )
        {
            $default_fontsize = intval($options['fontsize']);
        }

        // Прямое скачивание
        $dest = 'D';

        // Переведем в PDF и выведем окно сохранения файла
        $pdf = new pdf('P', 'mm', [240, 297], true, 'UTF-8');

        $pdf->SetTitle('account_' . date('d_m_Y', time()));
        $pdf->SetSubject('account_' . date('d_m_Y', time()));
        $pdf->SetFontSize($default_fontsize);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(5, 5, 5, true);
        foreach ( $pages as $html )
        {
            $pdf->AddPage();
            $pdf->writeHTML($html);
        }
        $pdf->Output('account_' . date('d_m_Y', time()) . '.pdf', $dest);
    }

    /**
     * Получить конфигурацию псевдосабплагина
     *
     * @return stdClass
     */
    public function otpay_config()
    {
        $config = new stdClass();

        // Доступные валюты по ISO 4217
        $config->currencycodes = [
            643 => 'RUB'
        ];
        // Валюта по умолчанию
        $config->defaultcurrencycode = 643;

        $config->newinstanceurl = '/enrol/otpay/edit.php';
        $config->editurl = '/enrol/otpay/edit.php';
        $config->pixicon = new pix_icon('accountgenerate', get_string('otpay_accountgenerate', 'enrol_otpay'),
            'enrol_otpay');
        $config->configcapability = 'enrol/otpay:config';
        $config->unenrolcapability = 'enrol/otpay:unenrol';
        $config->managecapability = 'enrol/otpay:manage';
        $config->couponsupports = true;
        $config->minamount = 1;
        $config->costsupports = $this->hascost;

        return $config;
    }

    /**
     * Дополнение формы сохранения способа записи курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param array $customdata
     *
     * @return void
     */
    public function form_edit_enrol_definition(enrol_otpay_edit_enrol_form &$form, $customdata)
    {
        parent::form_edit_enrol_definition($form, $customdata);

        $mform = $form->get_mform();
        $plugin = $form->get_plugin();

        // Заголовок раздела формы
        $mform->addElement('header', 'header', get_string('otpay_accountgenerate', 'enrol_otpay'));

        // Формирование селекта
        $select_options = [];

        foreach ( $this->get_available_scenarios() as $name => $obj )
        {
            // Название сценария
            $name = $obj->get_name();
            if ( empty($name) )
            {
                // Название отсутствует, поиск языковой строки
                $name = get_string('scenario_' . $obj->get_code(), 'enrol_otpay');
            }
            $select_options[$obj->get_code()] = $name;

            // проверка поддержки стоимости
            if ( ! $obj->has_cost() )
            {
                $mform->disabledIf('cost', 'scenario', 'eq', $obj->get_code());
                $mform->disabledIf('currency', 'scenario', 'eq', $obj->get_code());
                $mform->disabledIf('couponsupports', 'scenario', 'eq', $obj->get_code());
            }
        }

        $mform->addElement('select', 'scenario', get_string('accountgenerate_form_field_account_types', 'enrol_otpay'), $select_options);
        if ( ! empty($form->get_instance()->customchar2) )
        {
            $mform->setDefault('scenario', json_decode($form->get_instance()->customchar2));
        }

        // Стоимость
        $mform->addElement('text', 'cost', get_string('accountgenerate_form_field_cost', 'enrol_otpay'), ['size' => 4]);
        $mform->setType('cost', PARAM_RAW);

        // Валюта
        $currencies = $this->otpay_config()->currencycodes;
        foreach ( $currencies as &$currence )
        {
            $currence = get_string($currence, 'core_currencies');
        }
        $mform->addElement('select', 'currency', get_string('accountgenerate_form_field_currency', 'enrol_otpay'), $currencies);

        // Поддержка скидочных купонов
        $mform->addElement('checkbox', 'couponsupports', get_string('accountgenerate_form_field_couponsupports', 'enrol_otpay'));
        $mform->setDefault('couponsupports', true);
    }

    /**
     * Дополнительная валидация формы сохранения способа записи курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param array $errors - Массив ошибок исходной формы
     * @param array $data - Массив с переданными данными формы
     * @param array $files - Массив с переданными файлами формы
     *
     * @return void
     */
    public function form_edit_enrol_validation(enrol_otpay_edit_enrol_form &$form, &$errors, $data, $files)
    {
        parent::form_edit_enrol_validation($form, $errors, $data, $files);

        if ( ! array_key_exists($data['scenario'], $this->get_available_scenarios()) )
        {
            return ['scenario' =>  get_string('error_provider_accountgenerate_form_edit_enrol_validation_scnerious_doesnt_exists', 'enrol_otpay')];
        }

        // если сценарий поддерживает стоимость, то идет дополнительная валидация
        if ( $this->get_available_scenarios()[$data['scenario']]->has_cost() )
        {
            // Валидация стоимости
            $cost = str_replace(get_string('decsep', 'langconfig'), '.', $data['cost']);
            if ( ! is_numeric($cost) )
            {
                $errors['cost'] = get_string('error_provider_accountgenerate_form_edit_enrol_validation_cost', 'enrol_otpay');
            }
            $config = $this->otpay_config();
            if ( $data['cost'] < $config->minamount )
            {
                $errors['cost'] = get_string('error_provider_accountgenerate_form_edit_enrol_validation_costminamount',
                        'enrol_otpay', $config->minamount);
            }

            // Валидация валюты
            $currencies = $this->otpay_config()->currencycodes;
            if ( empty($data['currency']) || empty($currencies[$data['currency']]) )
            {// Указанная валюта не найдена среди доступных
                $errors['currency'] = get_string('error_provider_accountgenerate_form_edit_enrol_validation_currency', 'enrol_otpay');
            }
        }
    }

    /**
     * Предварительная обработка формы сохранения способа записи курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param stdClass $formdata - Объект с данными формы
     *
     * @return void
     */
    public function form_edit_enrol_preprocess(enrol_otpay_edit_enrol_form &$form, &$formdata)
    {
        parent::form_edit_enrol_preprocess($form, $formdata);
    }

    /**
     * Постобработка формы сохранения способа записи курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param stdClass $instance - Объект экземпляра подписки
     * @param stdClass $formdata - Объект с данными формы
     *
     * @return void
     */
    public function form_edit_enrol_postprocess(enrol_otpay_edit_enrol_form &$form, &$instance, &$formdata)
    {
        parent::form_edit_enrol_postprocess($form, $instance, $formdata);

        $config = $this->otpay_config();
        if ( isset($instance->id) )
        {
            // Сценарий
            $instance->customchar2 = json_encode($formdata->scenario);

            // если сценарий поддерживает стоимость, то идет дополнительная валидация
            if ( $this->get_available_scenarios()[$formdata->scenario]->has_cost() )
            {
                $instance->cost = unformat_float($formdata->cost);
                $instance->currency = $formdata->currency;

                if( isset($formdata->couponsupports) )
                {
                    $instance->customint6 = 1;
                } else
                {
                    $instance->customint6 = 0;
                }
            } else
            {
                $instance->cost = 0;
                $instance->currency = $config->defaultcurrencycode;
                $instance->customint6 = 0;
            }
        }
    }

    /**
     * Дополнение формы записи пользователя на курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param array $customdata
     *
     * @return void
     */
    public function form_add_user_enrolment_definition(enrol_otpay_add_user_enrolment_form &$form, $customdata)
    {
        global $PAGE;
        parent::form_add_user_enrolment_definition($form, $customdata);

        $mform = $form->get_mform();
        list($instance, $couponcodes) = $customdata;
        $mform->addElement('hidden', 'couponcodes', implode(',', $couponcodes));
        $mform->setType('couponcodes', PARAM_RAW);

        // Массив вкладок
        $tabs = [];
        $formsubmit = [];

        $config = $this->otpay_config();

        if ( ! empty($form->get_instance()->customchar2) )
        {
            // Получение сценариев
            $scenarios = $this->get_available_scenarios();
            $forms = $this->get_available_forms();

            $scenario = (string)json_decode($form->get_instance()->customchar2);
            if ( ! empty($scenario) && array_key_exists($scenario, $scenarios) )
            {
                $current_scenario_obj = $scenarios[$scenario];
                $current_forms = $current_scenario_obj->get_forms();

                // кнопка сабмита формы
                $formsubmit = $current_scenario_obj->get_field_submit();
                if ( ! empty($current_forms) )
                {
                    foreach ( $current_forms as $form_name )
                    {
                        if ( array_key_exists($form_name, $forms) )
                        {
                            $current_form = $forms[$form_name];

                            // Формирование вкладки для отображения
                            $tab = [
                                'header' => $current_scenario_obj->get_form_header($current_form),
                                'elements' => []
                            ];

                            foreach ( $current_form->get_fields() as $field => $type )
                            {
                                if ( is_array($type) )
                                {
                                    // массив настроек поля
                                    $tab['elements'][] = $mform->createElement(
                                        $type['fieldtype'],
                                        $field . '_' . $current_form->get_code(),
                                        get_string($field, 'enrol_otpay', ''),
                                        $this->prepare_form_element($field)
                                        );
                                    $mform->setType($field . '_' . $current_form->get_code(), $type['type']);
                                    if ( ! empty($type['rules']) )
                                    {
                                        foreach ( $type['rules'] as $rule )
                                        {
                                            $mform->addRule(
                                                $field . '_' . $current_form->get_code(),
                                                get_string($rule['message'], 'enrol_otpay'),
                                                $rule['type'],
                                                $rule['format'],
                                                $rule['validation'],
                                                $rule['reset'],
                                                $rule['force']
                                                );
                                        }
                                    }
                                } else
                                {
                                    // Получение полей сабплагина и отображение в форму
                                    $tab['elements'][] = $mform->createElement(
                                        'text',
                                        $field . '_' . $current_form->get_code(),
                                        get_string($field, 'enrol_otpay', ''),
                                        $this->prepare_form_element($field)
                                        );
                                    $mform->setType($field . '_' . $current_form->get_code(), $type);
                                }
                            }

                            $submit = $current_form->get_field_submit();
                            if ( ! empty($submit) )
                            {// кастомный сабмит формы
                                // Добавление сабмит элемента
                                $tab['elements'][] = $mform->createElement(
                                    'submit',
                                    'submit_' . $current_form->get_code(),
                                    $submit['text']
                                    );
                            } else
                            {
                                // Добавление сабмит элемента
                                $tab['elements'][] = $mform->createElement(
                                    'submit',
                                    'submit_' . $current_form->get_code(),
                                    get_string('accountgenerate_payform_field_submit', 'enrol_otpay')
                                    );
                            }

                            $tabs[] = $tab;
                        }
                    }
                }
            }
        }

        // обработка купонов если поддерживаются в инстансе
        if (!empty($form->get_instance()->customchar6))
        {
            // Обработка купонов
            $couponform = new enrol_otpay_coupon_form($PAGE->url->out(false), [
                'amount' => $instance->cost,
                'courseid' => $instance->courseid,
                'minamount' => $config->minamount
            ]);
            // Подсчет итоговой суммы с учетом купонов
            $amount = $couponform->get_amount($couponcodes);

            if ((int)$amount <= 0)
            {// Купон покрывает стоимость курса
                $current_scenario_obj->add_free_enrol_button($form, $mform, $tabs);
                return;
            }
        }


        if (!empty($formsubmit))
        {
            // кастомная кнопка сабмита у сценария
            $form->add_modal_button($formsubmit['text'], $tabs);
        } else
        {
            $form->add_modal_button(get_string('accountgenerate_payform_field_submit', 'enrol_otpay'), $tabs);
        }
    }

    /**
     * Дополнительная валидация формы записи пользователя на курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param array $errors - Массив ошибок исходной формы
     * @param array $data - Массив с переданными данными формы
     * @param array $files - Массив с переданными файлами формы
     *
     * @return void
     */
    public function form_add_user_enrolment_validation(enrol_otpay_add_user_enrolment_form &$form, &$errors, $data, $files)
    {
        parent::form_add_user_enrolment_validation($form, $errors, $data, $files);
    }

    /**
     * Обработка формы записи пользователя на курс
     *
     * @param enrol_otpay_edit_enrol_form $form - Объект формы
     * @param stdClass $instance - Объект экземпляра подписки
     * @param stdClass $formdata - Объект с данными формы
     *
     * @return void
     */
    public function form_add_user_enrolment_process(enrol_otpay_add_user_enrolment_form &$form, &$instance, &$formdata)
    {
        global $DB, $COURSE, $USER, $PAGE;

        // Базовый обработчик
        parent::form_add_user_enrolment_process($form, $instance, $files);

        // Получение сценариев
        $scenarios = $this->get_available_scenarios();
        $forms = $this->get_available_forms();

        $scenario = json_decode($form->get_instance()->customchar2);
        $current_scenario_obj = $scenarios[$scenario];
        $this->hascost = $current_scenario_obj->has_cost();
        $current_forms = $current_scenario_obj->get_forms();

        // Выбранная вкладка
        $selected_tab = '';
        $selected_form = '';

        foreach ( $current_forms as $form_name )
        {
            if ( array_key_exists($form_name, $forms) )
            {
                $current_form = $forms[$form_name];

                $submit_field = 'submit_' . $current_form->get_code();
                foreach ( $formdata as $name => $field)
                {
                    if ( $name === $submit_field )
                    {
                        $selected_tab = $current_form->get_code();
                        $selected_form = $current_form;
                        break;
                    }
                }
            }
        }

        if ( empty($selected_tab) )
        {
            // Выбрана неизвестная вкладка
            throw new moodle_exception('otpay_accountgenerate_invalid_selected_tab');
        }

        // Объект счета
        $account = new stdClass();

        // Добавление служебных полей
        $account->account_number = '';
        $account->type = get_string($selected_form->get_code(), 'enrol_otpay');

        foreach ( $selected_form->get_fields() as $field => $type )
        {
            $field_name = $field . '_' . $selected_form->get_code();
            if ( ! empty($formdata->{$field_name}) )
            {
                $account->{$field} = $formdata->{$field_name};
            }
        }

        $plugin = $form->get_plugin();

        // Получение экземпляра
        $instanceid = $formdata->instanceid;
        $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);

        // Конфигурация провайдера
        $config = $this->otpay_config();


        // Формирование данных платежа
        $defaultenrolotpay = new stdClass();
        $defaultenrolotpay->instanceid = $instanceid;
        $defaultenrolotpay->courseid = $instance->courseid;
        $defaultenrolotpay->userid = $USER->id;
        $defaultenrolotpayoptions = [];
        $defaultenrolotpayoptions['account'] = $account;

        $amount = $instance->cost;
        // обработка купонов если поддерживаются в инстансе
        $couponsupports = !empty($instance->customchar6);
        if ($couponsupports)
        {
            // Обработка купонов
            $couponcodes = explode(',', $formdata->couponcodes);
            $couponcodes = $couponcodes === false ? "" : $couponcodes;
            $couponform = new enrol_otpay_coupon_form($PAGE->url->out(false), [
                'amount' => $instance->cost,
                'courseid' => $instance->courseid,
                'minamount' => $config->minamount
            ]);
            // Подсчет итоговой суммы с учетом купонов
            $amount = $couponform->get_amount($couponcodes);
            $defaultenrolotpayoptions['couponcodes'] = $couponcodes;
//             if ((int)$amount > 0)
//             {
//                 $defaultenrolotpayoptions['account'] = $account;
//             }
        }

        $defaultenrolotpay->options = serialize($defaultenrolotpayoptions);

        // обязательное поле в таблице
        $defaultenrolotpay->currency = $instance->currency ?? $config->defaultcurrencycode;
        if ( $this->hascost )
        {
            // проверка поддержки стоимости
            $defaultenrolotpay->amount = (int)$amount < $config->minamount ? $config->minamount : $amount;
        } else
        {
            $defaultenrolotpay->amount = 0;
        }

        // Создание записи в БД
        $enrolotpayid = $plugin->add_draft_enrol_otpay('accountgenerate', $defaultenrolotpay);

        // Формирование счета
        $enrolotpay = $DB->get_record('enrol_otpay', ['id' => $enrolotpayid]);

        if ($this->hascost && (int)$amount <= 0)
        {// Скидка по купону покрывает стоимость курса - подписываем пользователя на курс
            if ($couponsupports)
            {
                // Обработка скидочных купонов
                $plugin->process_coupons($enrolotpay);
            }
            // Подписка на курс
            $plugin->process_payment($enrolotpay);
            // Редирект на страницу назначения
            $plugin->process_redirect($instance);
        } else
        {
            // Добавление уникального номера счета (ID + hash)
            $options = unserialize($enrolotpay->options);
            $accountnumber = $plugin->get_config('accountgenerate_account_number');
            if( $accountnumber == 'id' )
            {
                $options['account']->account_number = $enrolotpayid;
            } else
            {
                $options['account']->account_number = $enrolotpayid . substr(md5(time()), 0, 6);
            }

            $update_record = new stdClass();
            $update_record->id = $enrolotpayid;
            $update_record->options = serialize($options);
            $update_record->externalpaymentid = $options['account']->account_number;
            if (!$this->hascost)
            {
                $update_record->amount = 0;
            }
            $DB->update_record('enrol_otpay', $update_record);

            // Формирование счета
            $enrolotpay = $DB->get_record('enrol_otpay', ['id' => $enrolotpayid]);

            $account = $options['account'];

            // Страницы для экспорта в pdf
            $pages = [];

            // Получение шаблонов
            $templates = $this->get_available_templates();
            $current_templates = $selected_form->get_templates();

            if ( empty($current_templates) )
            {
                // у формы отсутствуют шаблоны, выводим нотис
                \core\notification::add(get_string('form_create_enrol_save_success', 'enrol_otpay'), 'success');
                return;
            }

            $current_template = null;
            foreach ( $current_templates as $template )
            {
                if ( array_key_exists($template, $templates) )
                {
                    $current_template = $templates[$template];
                    $pages[] = $this->prepare_html($current_template->get_html(), $account, $enrolotpay, $selected_form);
                }
            }

            // Отправление сформированной формы пользователю
            $this->export_pdf($pages, $account);
        }
    }

    /**
     * Опции записи на курс
     *
     * @param stdClass $instance - Экземпляр подписки на курс
     * @param stdClass $customdata - Данные о заявке на оплату пользователя
     *
     * @return string рендер формы
     */
    protected function get_enrol_page_hook_options($instance, $customdata) {

        $options = new stdClass();
        $options->localisedcost = format_float($customdata->cost, 2, true);
        $options->cost = $customdata->cost;
        $options->localisedamount = format_float($customdata->amount, 2, true);
        $options->amount = $customdata->amount;
        $options->currency = $instance->currency;
        $options->instancename = $instance->name;
        $options->instancedescription = $instance->customtext2 ?? '';
        $options->enrolstartdate = $instance->enrolstartdate;
        $options->enrolperiod = $instance->enrolperiod;
        $options->enrolenddate = $instance->enrolenddate;
        $options->class = ['otpay_accountgenerate'];
        return $options;

    }

    /**
     * Форма записи на курс
     *
     * @param stdClass $instance - Экземпляр подписки на курс
     * @param stdClass $customdata - Данные о заявке на оплату пользователя
     *
     * @return string рендер формы
     */
    protected function get_enrol_page_hook_form($instance, $customdata) {
        global $PAGE;

        $PAGE->requires->js('/enrol/otpay/plugins/accountgenerate/script.js');

        // Неизвестно, нужен ли этот кусок кода. Другие сабплагины не задают свойство hascost в этом методе
        // Возможно, неописанный костыль, а возможно ненужный кусок кода
        if (!empty($instance->customchar2)) {
            // Получение сценариев
            $scenarios = $this->get_available_scenarios();
            $forms = $this->get_available_forms();
            $scenario = (string)json_decode($instance->customchar2);
            if (!empty($scenario) && array_key_exists($scenario, $scenarios)) {
                $this->hascost = $scenarios[$scenario]->has_cost();
            }
        }

        $form = '';
        if (isloggedin() && !isguestuser()) {

            $couponcodes = [];
            if( ! empty($instance->customint6) && (int)$instance->customint6 == 1 ) {
                $couponcodes = $customdata->couponcodes;
            }

            $adduserenrolmentform = new enrol_otpay_add_user_enrolment_form($PAGE->url, [$instance, $couponcodes]);
            $adduserenrolmentform->process();
            $form = $adduserenrolmentform->render();

        }

        return $form;
    }
}