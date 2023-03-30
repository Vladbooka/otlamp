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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Отчет о пользовательских данных.
 * Внутренняя библиотека плагина.
 *
 * @package report
 * @subpackage userdata
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/enrol/locallib.php');
require_once ($CFG->dirroot . '/user/profile/lib.php');
require_once ($CFG->libdir . '/pdflib.php');
require_once ($CFG->libdir . '/excellib.class.php');

/**
 * Класс отчета
 */
class report_userdata
{

    /**
     * Данные отчета
     *
     * @var array
     */
    private $reportdata = [];

    /**
     * Данные заголовков
     *
     * @var array
     */
    private $headerdata = [];

    /**
     * Пользовательские поля для отчета
     *
     * @var array
     */
    private $userfields = [
        'firstname',
        'lastname',
        'username',
        'email',
        'city',
        'country',
        'description',
        'url',
        'idnumber',
        'institution',
        'department',
        'phone1',
        'phone2',
        'address',
        'firstnamephonetic',
        'lastnamephonetic',
        'middlename',
        'alternatename',
        'id'
    ];

    /**
     * Дополнительные поля пользователя для отчета
     *
     * @var array
     */
    private $customuserfields = [];

    /**
     * Консструктор класса
     *
     * @param array $options
     *            - Дополнительные опции
     */
    public function __construct( $options = [] )
    {
        global $DB, $PAGE, $CFG;
        
        // Добавление в отчет дополнительных полей пользователей
        $this->init_custom_user_fields();
        // Добавление в отчет заголовков
        $this->init_header_data();
        
        $where = [
            '1=1'
        ];
        if ( ! empty($options['courseid']) )
        {
            $course = get_course($options['courseid']);
            if ( ! empty($course) )
            {
                $cem = new course_enrolment_manager($PAGE, $course);
                $courseusers = $cem->get_users('u.lastname, u.firstname, u.middlename', 'ASC', 0, 
                    $cem->get_total_users());
                if ( ! empty($courseusers) )
                {
                    $courseuserids = [];
                    foreach ( $courseusers as $courseuser )
                    {
                        $courseuserids[] = $courseuser->id;
                    }
                    $where[] = 'id IN (' . implode(',', $courseuserids) . ')';
                }
            }
        }
        $where[] = 'deleted=0';
        
        // Добавление в отчет данных пользователя
        $users = (array) $DB->get_records_sql(
            '
            SELECT ' . implode(',', 
                array_merge([
                    'id'
                ], $this->userfields)) . ' 
            FROM {user} 
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY lastname, firstname');
        foreach ( $users as $user )
        { // Обрабтка каждого пользователя
            

            // Добавление в отчет данных пользователя
            foreach ( $this->userfields as $userfield )
            { // Добавление каждого поля в отчет
                if ( ! empty($user->{$userfield}) )
                { // Поле указано
                    $this->addUserCell($user->id, $user->{$userfield});
                } else
                { // Поле не указано
                    $this->addUserCell($user->id, '');
                }
            }
            
            // Добавление в отчет данных дополнительных полей пользователя
            $usercustomfieldsarray = [];
            $usercustomfields = $DB->get_records('user_info_data', 
                [
                    'userid' => $user->id
                ]);
            // Инициализация полей
            foreach ( $usercustomfields as $usercustomfield )
            {
                $usercustomfieldsarray[$usercustomfield->fieldid] = $usercustomfield;
            }
            // Добавление данных по пользователям в отчет
            foreach ( $this->customuserfields as $customuserfield )
            {
                if ( ! empty($usercustomfieldsarray[$customuserfield->id]) )
                { // Дополнительное поле определено
                    if ( $customuserfield->datatype == 'checkbox' )
                    { //плагин поля профиля сам отображает картинку - нам такого не надо
                        if ( $usercustomfieldsarray[$customuserfield->id]->data )
                        {
                            $this->addUserCell($user->id, get_string('yes'));
                        } else
                        {
                            $this->addUserCell($user->id, get_string('no'));
                        }
                    } else
                    { //остальные, нормальные плагины полей профиля отображают текстовую информацию через метод display_data в своих классах
                        //путь к файлу класса
                        $profilefieldclassfile = $CFG->dirroot . '/user/profile/field/' .
                             $customuserfield->datatype . '/field.class.php';
                        //название класса
                        $profilefieldclassname = 'profile_field_' . $customuserfield->datatype;
                        if ( file_exists($profilefieldclassfile) )
                        { //файл класса существует
                            //подключение файла класса плагина поля профиля
                            require_once ($profilefieldclassfile);
                            if ( class_exists($profilefieldclassname) )
                            { //класс существует
                                $profilefieldbytype = new $profilefieldclassname(
                                    $customuserfield->id, $user->id);
                                if ( $profilefieldbytype->is_visible() and
                                     ! $profilefieldbytype->is_empty() )
                                {
                                    $profilefieldddata = $profilefieldbytype->display_data();
                                    if ( $customuserfield->datatype == 'file' &&
                                         ! empty($profilefieldddata) )
                                    {
                                        $profilefieldplaintext = preg_replace_callback(
                                            "/<a\shref=\"([^\"]*)\">(.*)<\/a>/siU", 
                                            function ( $matches )
                                            {
                                                return $matches[1];
                                            }, $profilefieldddata);
                                        ;
                                        $this->addUserCell($user->id, $profilefieldddata, 
                                            $profilefieldplaintext);
                                    } else
                                    {
                                        $this->addUserCell($user->id, $profilefieldddata);
                                    }
                                } else
                                {
                                    $this->addUserCell($user->id, '');
                                }
                            } else
                            {
                                $this->addUserCell($user->id, '');
                            }
                        } else
                        {
                            $this->addUserCell($user->id, '');
                        }
                    }
                } else
                { // Дополнительное поле не определено
                    $this->addUserCell($user->id, '');
                }
            }
        }
    }

    private function addUserCell( $userid, $htmltext, $plaintext = null )
    {
        $data = [
            'htmltext' => $htmltext,
            'plaintext' => $htmltext
        ];
        if ( ! empty($plaintext) )
        {
            $data['plaintext'] = $plaintext;
        }
        $this->reportdata[$userid][] = $data;
    }

    /**
     * Сформировать HTML-таблицу с отчетом
     *
     * @return string - Код HTML-таблицы
     */
    public function get_html()
    {
        // Инициализация таблицы
        $table = new html_table();
        $table->head = $this->headerdata;
        $table->attributes = [
            'border' => '1'
        ];
        
        // Формирование данных таблицы
        $tabledata = [];
        foreach ( $this->reportdata as $datarow )
        {
            foreach ( $datarow as $k => $cell )
            {
                $datarow[$k] = $cell['htmltext'];
            }
            $tablerow = new html_table_row($datarow);
            $tablerow->attributes = [
                'nobr' => 'true',
                'class' => ''
            ];
            $tabledata[] = $tablerow;
        }
        $table->data = $tabledata;
        
        // Рендеринг таблицы
        return html_writer::table($table);
    }

    /**
     * Сформировать PDF-файл с отчетом
     *
     * @param string $name
     *            - Название файла отчета
     * @param string $dest
     *            - Ключ для отправки файла PDF
     *            
     * @return void
     */
    public function create_pdf( $name = 'report_userdata', $dest = 'I' )
    {
        // Получение HTML-кода таблицы
        $html = $this->get_html();
        
        // Генерация PDF
        ob_clean();
        $pdf = new pdf('L', 'mm', 'A1', true, 'UTF-8');
        $pdf->SetTitle($name);
        $pdf->SetSubject($name);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(20, 10, 10, true);
        $pdf->AddPage();
        $pdf->writeHTML($html);
        $pdf->Output($name . '.pdf', $dest);
    }

    /**
     * Сформировать XLS-файл с отчетом
     *
     * @param string $name
     *            - Название файла отчета
     *            
     * @return void
     */
    public function create_xls( $name = 'report_userdata' )
    {
        ob_clean();
        
        // Объявление файла отчета
        $workbook = new MoodleExcelWorkbook($name . '.xls');
        $workbook->send($name . '.xls');
        $worksheet = $workbook->add_worksheet('');
        
        // Генерация отчета
        $rownum = 0;
        $cellnum = 0;
        foreach ( $this->headerdata as $headerfield )
        { // Добавление заголовка
            $worksheet->write_string($rownum, $cellnum ++, $headerfield);
        }
        // Добавление данных
        foreach ( $this->reportdata as $datarow )
        {
            $rownum ++;
            $cellnum = 0;
            foreach ( $datarow as $datacell )
            {
                $worksheet->write_string($rownum, $cellnum ++, $datacell['plaintext']);
            }
        }
        
        // Завершение генерации
        $workbook->close();
    }

    /**
     * Добавление данных о заголовках отчета
     *
     * @return array - Массив заголовков
     */
    private function init_header_data()
    {
        // Заголовки полей пользователя
        foreach ( $this->userfields as $userfield )
        {
            if( $userfield === 'id' )
            {
                $this->headerdata[] = get_string('id', 'report_userdata');
            } else 
            {
                $this->headerdata[] = get_user_field_name($userfield);
            }
        }
        // Заголовки дополнительных полей пользователя
        foreach ( $this->customuserfields as $customuserfield )
        {
            $this->headerdata[] = $customuserfield->name;
        }
        // Массив заголовков
        return $this->headerdata;
    }

    /**
     * Добавление данных о дополнительных полях пользователей
     *
     * @return array - Массив дополнительных полей
     */
    private function init_custom_user_fields()
    {
        global $DB;
        
        if ( ! empty($this->customuserfields) )
        { // Дополнительные поля прежде были определены
            return $this->customuserfields;
        }
        
        // Добавление полей
        $this->customuserfields = [];
        $proffields = $DB->get_records('user_info_field');
        if ( ! empty($proffields) )
        {
            foreach ( $proffields as $proffield )
            {
                $this->customuserfields[] = $proffield;
            }
        }
        unset($proffields);
        
        // Массив дополнительных полей
        return $this->customuserfields;
    }
}