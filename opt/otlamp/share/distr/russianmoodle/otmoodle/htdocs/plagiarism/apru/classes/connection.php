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
 * Класс для соединения с API "Антиплагиат"
 *
 * @package    plagiarism
 * @subpackage apru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_apru;

defined('MOODLE_INTERNAL') || die();

use plagiarism_apru\otserial;
use plagiarism_apru\settings_form;
use moodle_exception;
use coding_exception;
use SoapClient;
use SoapFault;
use stdClass;
use cache;

require_once($CFG->dirroot . '/plagiarism/apru/lib.php');

/**
 * Класс для соединения с API "Антиплагиат".
 */
class connection
{
    /**
     * Объект для связи с сервером "Антиплагиата"
     *
     * @var SoapClient
     */
    protected $client;

    /**
     * Данные тарифа
     *
     * @var object
     */
    protected $tarif;

    /**
     * @var array список доступных типов файлов для проверки на плагиаризм
     */
    const SUPPORTED_TYPES = '.txt;.doc;.docx;.dot;.html;.htm;.pdf;.rtf;.odt;.ppt;.pptx;.fb2;.docm';
    /**
     * @var array список доступных типов архивов для проверки на плагиаризм
     */
    const SUPPORTED_ARCHIVES = '.7z;.rar;.zip;.tar;.gz;.gzip;.bz2;.bzip2';

    /**
     * Конструктор класса
     *
     * Выполняет подключение к API
     *
     * @throws \moodle_exception - При ошибке соединения с сервисом Антиплагиата
     */
    public function __construct()
    {
        // Объявление кэша
        $cache = cache::make('plagiarism_apru', 'otdata');
        
        // Получение данных
        $lastrequest = $cache->get('lastrequest');
        $tarif = $cache->get('reply');
        
        // Создание объекта OTAPI
        $otapi = new otserial();
        
        // Получение ключа и серийного кода установленной версии плагина
        $otserial = $otapi->get_config_otserial();
        $otkey = $otapi->get_config_otkey();
        
        // Определение необходимости запроса о данных тарифа с сервера OT
        $now = time();
        $serverrequest = false;
        if ( empty($lastrequest) || empty($tarif) )
        {// Данных в кэше не найдено
            $serverrequest = true;
        } else
        {// Данные в кэше имеются
            if ( $now - $lastrequest > 300 )
            {// Если со времени последнего запроса прошло больше 5 минут
                $serverrequest = true;
            }
        }
        
        if ( $serverrequest )
        {// Требуется запрос с сервера OT сведений о тарифе
            // Получение данных тарифа по серийному коду и ключу
            $serverreply = $otapi->otapi_get_otserial_status($otserial, $otkey);
            
            if ( isset($serverreply->status) && ( $serverreply->status == 'ok') )
            {// Запрос с сервера успешный
                // Запись ответа в кэш
                $cache->set('reply', $serverreply);
                $cache->set('lastrequest', $now);
                $tarif = $serverreply;
            }
        }
        
        // Установка данных о тарифе
        if ( empty($tarif) )
        {// Ошибка при формированиии информации о тарифе
            $this->tarif = NULL;
        } else
        {// Данные по тарифу получены
            $this->tarif = $tarif;
        }
        
        // Создание подключения с сервисом Антиплагиат
        $this->client = NULL;
        
        if ( isset($this->tarif->options->host) &&
             isset($this->tarif->options->login) &&
             isset($this->tarif->options->password) &&
             isset($this->tarif->options->companyname)
           )
        {// Данные для создания подключения переданы
            // Получить адрес wsdl файла
            $wsdlurl = $otapi->get_wsdl_url($otserial, $otkey);
    
            // Попытка подключения к сервису Антиплагиат
            try {
                $this->client = new SoapClient(
                    $wsdlurl,
                    [
                        "trace"        => 1,
                        "login"        => $this->tarif->options->login,
                        "password"     => $this->tarif->options->password,
                        "soap_version" => SOAP_1_1,
                        "features"     => SOAP_SINGLE_ELEMENT_ARRAYS,
                        "timeout"      => 300
                    ]
                );
            } catch ( SoapFault $ex )
            {// Ошибка подключения WSDL
                throw new \moodle_exception('error_service_connection', 'plagiarism_apru', NULL, NULL, $ex->getMessage());
            }
        } else {
            throw new \moodle_exception('empty_connection_parameters', 'plagiarism_apru');
        }
    }
    
    /**
     * Загрузка документа
     *
     * При загрузке происходит проверка на поддерживаемые типы данных
     *
     * @param \stdClass $uplaoddata - содержит следующие поля:
     *             ->Data     = $content
     *             ->FileName = 'filename.txt'
     *             ->FileType = '.txt' (с точкой)
     * @param array $attributes - Массив атрибутов документа в виде ['Атрибут' => 'Значение']
     *                            Список основных атрибутов:
     *                              'Name' - Название файла
     *                              'Url' - URL для перехода к файлу
     *                              'Author' - Автор документа
     * @param array $options - Массив дополнительных опций обработки
     *                              bool 'AddToIndex' - Добавить документ в индекс
     *
     * @return \stdClass - Объект с результатом загрузки документа в систему
     *
     * @throws \moodle_exception - При ошибке загрузки документа в сервис Антиплагиат
     *         \coding_exception - При недопустимом формате документа
     */
    public function upload_document($uplaoddata, $attributes = [], $options = [])
    {
        // Формирование запроса
        $request = [];
        
        // Проверка на допустимые типы файлов
        $supportedtypes    = explode(';', self::SUPPORTED_TYPES);
        $supportedarchives = explode(';', self::SUPPORTED_ARCHIVES);
        $allowedtypes = array_merge($supportedtypes, $supportedarchives);
        
        if (empty($uplaoddata->FileType) || array_search($uplaoddata->FileType, $allowedtypes ) === false) {
            // Недопустимый формат
            throw new \coding_exception('typenotallowed');
        }
        
        // Нормализация имени файла
        if (empty($uplaoddata->FileName)) {
            $uplaoddata->FileName = 'Unnamed';
        }
        
        if (empty($uplaoddata->ExternalUserID)) {
            // Если внутренний идентификатор пользователя не передали, выбросим исключение
            // т.к. передача является обязательной с 07.04.2020, а чей это файл не известно
            throw new \coding_exception('ExternalUserID_not_set');
        }
        $request['data'] = $uplaoddata;
        
        // Добавление данных по загружаемому документу
        $attributes = $this->prepare_doc_attributes($attributes);
        if (!empty($attributes['Custom'])) {
            unset($attributes['Custom']);
        }
        $request['attributes'] = $attributes;
        $request['options'] = $options;
        // Загрузка файла
        try
        {
            return $this->client->UploadDocument($request);
        } catch (\SoapFault $ex)
        {
            throw new \moodle_exception(
                'error_service_uploading_document', 'plagiarism_apru', NULL, NULL, $ex->getCode() . '---' . $ex->getMessage()
            );
        }
    }

    /**
     * Удалить документ из системы Антиплагиат
     *
     * @param int $id - Идентификатор документа в системе Антиплагиат
     *
     * @return bool - Результат удаления документа
     *
     * @throws \moodle_exception - При ошибке удаления
     */
    public function delete_document($id)
    {
        // Формирование запроса
        $request = [];
        
        // Добавление идентификатора документа
        $docid = $this->make_docid($id);
        $request['docId'] = $docid;
        
        // Отправка запроса
        try
        {
            $this->client->DeleteDocument($request);
            return TRUE;
        } catch ( SoapFault $ex )
        {
            throw new \moodle_exception(
                'error_service_deleting_document', 'plagiarism_apru', NULL, NULL, $ex->getMessage().' ExternalID: '.$id
            );
        }
        return FALSE;
    }
    
    /**
     * Запустить проверку документа на плагиат
     *
     * @param int $id - Идентификатор документа в системе Антиплагиат
     *
     * @return bool - Рехультат постановки на проверку
     *
     * @throws \moodle_exception - При ошибке постановки на проверку
     */
    public function check_document($id)
    {
        // Формирование запроса
        $request = [];
        
        // Добавление идентификатора документа
        $request['docId'] = $this->make_docid($id);
        
        // Добавление списка источников
        $services = $this->client->GetCheckServices()->GetCheckServicesResult->CheckServiceInfo;
        $servicelist = [];
        foreach ( $services as $service )
        {
            $servicelist[] = $service->Code;
        }
        if ( isset($this->tarif->options->checklist) && ! empty($this->tarif->options->checklist) )
        {// Чеклист передан с сервера
            $servicelist = explode(',', $this->tarif->options->checklist);
            $request['checkServicesList'] = $servicelist;
        } else
        {// Чеклист по умолчанию
            if ( ! empty($servicelist) )
            {
                $request['checkServicesList'] = $servicelist;
            }
        }
        
        // Отправка запроса в систему Антиплагиат
        try
        {
           $response = $this->client->CheckDocument($request);
           return TRUE;
        } catch ( SoapFault $ex )
        {
            throw new \moodle_exception(
                'error_service_checking_document', 'plagiarism_apru', NULL, NULL, $ex->getMessage().' ExternalID: '.$id
            );
        }
        return FALSE;
    }
    
    /**
     * Получить статус последней проверки документа на заимствования
     *
     * @param int $id - Идентификатор документа в системе Антиплагиат
     *
     * @return \stdClass - Объект с данными по проверке документа
     *
     * @throws \moodle_exception - При ошибке получения статуса проверки
     */
    public function get_check_status($id)
    {
         // Формирование запроса
        $request = [];
        // Добавление идентификатора документа
        $request['docId'] = $this->make_docid($id);
        
        // Отправка запроса
        try
        {
            // Получить текущий статус последней проверки
            $status = $this->client->GetCheckStatus($request);
            
            if ( $status->GetCheckStatusResult->Status === "Failed" )
            {// Проверка закончилась не удачно
                return $status->GetCheckStatusResult;
            }
            
            // Ссылка на отчёт
            $status->GetCheckStatusResult->reporturl = '';
            
            if (!empty($status->GetCheckStatusResult->Summary->ReportWebId))
            {// Установим ссылку на отчет
                if ( isset($this->tarif->options->siteurl) )
                {
                    $status->GetCheckStatusResult->reporturl = $this->tarif->options->siteurl . $status->GetCheckStatusResult->Summary->ReportWebId;
                } else
                {
                    $status->GetCheckStatusResult->reporturl = '';
                }
            }
            return $status->GetCheckStatusResult;
        } catch (SoapFault $ex)
        {
            throw new \moodle_exception(
                'error_service_getting_document_checkstatus', 'plagiarism_apru', NULL, NULL, $ex->getMessage().' ExternalID: '.$id
            );
        }
    }
    
    /**
     * Получить отчет о проверке документа
     *
     * @param int $id - Идентификатор документа в системе Антиплагиат
     * @param array $options - Массив опций получения отчета
     *                              bool 'FullReport' - Добавить дополнительную информацию в отчет
     *                              bool 'NeedText' - Добавить текст документа
     *                              bool 'NeedStats' - Добавить статистику по документу
     *                              bool 'NeedAttributes' - Добавить атрибуты документа
     *
     * @return \stdClass - Объект с отчетом по документу
     *
     * @throws \moodle_exception - При ошибке получения отчета
     */
    public function get_report_view($id, $optons = [])
    {
        // Формирование запроса
        $request = [];
        $request['docId'] = $this->make_docid($id);
        $request['options'] = [];
        if ( isset($optons['FullReport']) )
        {
            $request['options']['FullReport'] = (bool)$optons['FullReport'];
        }
        if ( isset($optons['NeedText']) )
        {
            $request['options']['NeedText'] = (bool)$optons['NeedText'];
        }
        if ( isset($optons['NeedStats']) )
        {
            $request['options']['NeedStats'] = (bool)$optons['NeedStats'];
        }
        if ( isset($optons['NeedAttributes']) )
        {
            $request['options']['NeedAttributes'] = (bool)$optons['NeedAttributes'];
        }
        
        // Отправка запроса
        try {
            // Получить отчет по документу
            return $this->client->GetReportView($request);
        } catch ( SoapFault $ex )
        {
            throw new \moodle_exception(
                'error_service_getting_document_report', 'plagiarism_apru', NULL, NULL, $ex->getMessage().' ExternalID: '.$id
            );
        }
    }
    
    /**
     * Получить набор идентификаторов документов, загруженных аккаунтом
     *
     * Иденификаторы перечисляются пачками - в options можно переопределить
     * размер пачки(по умолчанию 100).
     * Чтобы получить следующую пачку, нужно указать идентификатор последнего документа
     * в полученной пачке.
     *
     * @param int|NULL $afterdocid - Идентификатор документа, после которого должен
     *                               начинаться текущий набор. Если указан NULL
     *                               Набор сформируется с первого элемента
     * @param array $options - Массив дополнительных параметров
     *           bool 'AddedToIndex' - Фильтровать набор по только добавленным в индекс элементам
     *           int  'Count' - Число элементов в наборе. По умолчанию 100
     *
     * @return \stdClass - Объект со свойством EnumerateDocumentsResult - массивом
     * идентификаторов документов в формате:
     * stdClass Object
     * (
     *     [Id] =>
     *     [External] =>
     * )
     *
     * @throws \moodle_exception - При ошибке получения набора документов
     */
    public function enumerate_documents($afterdocid = NULL, $options = NULL)
    {
        // Формирование запроса
        $request = [];
        
        // Определить начало набора
        $afterdocid = (int)$afterdocid;
        if ( ! empty($afterdocid) )
        {// Указан идентификатор документа для сдвига набора
            $request['afterDocId'] = $this->make_docid($afterdocid);
        }
        
        // Определить дополнительные опции
        $additional_options = [];
        // Определить дополнительные опции получения набора
        if ( isset($options['Count']) && (int)$options['Count'] > 0 )
        {// Переопределено число элементов в наборе
            $additional_options['Count'] = (int)$options['Count'];
        }
        if ( isset($options['AddedToIndex']) )
        {// Требуется фильтр по добавленным в индекс документам
            $additional_options['AddedToIndex'] = (bool)$options['AddedToIndex'];
        }
        if ( ! empty($additional_options) )
        {
            $request['options'] = $additional_options;
        }
        
        // Отправка запроса
        try {
            // Получить набор документов
            return $this->client->EnumerateDocuments($request);
        } catch ( SoapFault $ex )
        {
            throw new \moodle_exception(
                'error_service_getting_enumerate_documents', 'plagiarism_apru', NULL, NULL, $ex->getMessage().' AfterdocID: '.$afterdocid
            );
        }
        
    }
    
    /**
     * Установить статус нахождения документа в индексе
     *
     * После удаления из индекса документ перестает находиться как источник в проверках
     * запущенных после удаления.
     * При удалении документа из индекса отчеты сделанные до удаления,
     * в которых документ фигурирует как источник, остаются без изменения.
     *
     * @param int $id - ID документа в базе Антиплагиата
     * @param boolean $addtoindex - Статус нахождения документа в индексе
     *
     * @return bool - Результат изменения статуса
     */
    public function set_indexed_status($id, $addtoindex = TRUE)
    {
        global $SITE, $COURSE, $DB, $USER;
        
        $docid = $this->make_docid($id);
        $params = ['docId' => $docid, 'addToIndex' => (bool)$addtoindex];
        
        // Сформировать данные по событию
        $eventdata = [];
        $eventdata['other'] = [
            'externalid' => $id,
            'addToIndex' => (bool)$addtoindex
        ];
        if ( isset($USER->id) )
        {// Пользователь определен
            $eventdata['userid'] = $USER->id;
        } else
        {// Пользователь не определен
            $eventdata['userid'] = 0;
        }
        if ( isset($COURSE->id) )
        {// Курс определен
            $eventdata['courseid'] = $COURSE->id;
            $eventdata['context'] = \context_course::instance($COURSE->id);
        } else
        {// Курс не определен
            $eventdata['courseid'] = $SITE->id;
            $eventdata['context'] = \context_system::instance();
        }
        // Получить документы по externalid
        $documents = $DB->get_records('plagiarism_apru_files', ['externalid' => $id]);
        if ( ! empty($document) )
        {// Найдены документы с указанным внешним идентификатором
            $document = array_pop($documents);
            $eventdata['objectid'] = $document->id;
            if ( ! empty($documents) )
            {// Найдено еще несколько файлов с указанным внешним идентификатором
                $eventdata['other']['ids'] = [$document->id];
                foreach ( $documents as $document )
                {// Добавленеи нескольких идентификаторов документа
                    $eventdata['other']['ids'][] = $document->id;
                }
            }
        } else
        {// Нет информации о документе
            $eventdata['objectid'] = 0;
        }
        
        // Смена статуса наличия документа в индексе
        try
        {
            $this->client->SetIndexedStatus($params);
            // Добавление в лог данных о произошедшем событии
            $event = \plagiarism_apru\event\set_indexed_status::create($eventdata);
            $event->trigger();
            return TRUE;
        } catch ( SoapFault $ex )
        {// Ошибка во время смены статуса
            $eventdata['other']['error'] = [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode()
            ];
            if ( isset($ex->faultcode) )
            {
                $eventdata['other']['error']['faultcode'] = $ex->faultcode;
            }
            // Добавление в лог данных о произошедшем событии
            $event = \plagiarism_apru\event\set_indexed_status::create($eventdata);
            $event->trigger();
            return FALSE;
        }
    }
    
    /**
     * Обновить атрибуты документа.
     *
     * Изменяются только заданные атрибуты. Чтобы удалить пользовательский
     * атрибут в его значении нужно указать NULL.
     *
     * @param int $id - Идентификатор документа
     * @param array $attributes - Массив атрибутов документа в виде ['Атрибут' => 'Значение']
     *                              Список основных атрибутов:
     *                                  'Name' - Название файла
     *                                  'Url' - URL для перехода к файлу
     *                                  'Author' - Автор документа
     *
     * @return bool
     */
    public function update_document_attributes($id, $attributes = [])
    {
        // Формирование запроса
        $request = [];
        
        // Получение идентификатора документа
        $request['docId'] = $this->make_docid($id);
        
        // Формирование атрибутов документа
        $request['attributes'] = $this->prepare_doc_attributes($attributes);
        
        // Отправка запроса на смену атрибутов
        try
        {
            $this->client->UpdateDocumentAttributes($request);
            return TRUE;
        } catch ( SoapFault $ex )
        {// Ошибка во время смены атрибутов
            return FALSE;
        }
    }
    
    /**
     * Обновить документ.
     *
     * Производит удаление старого документа из сервиса, после чео загружает новый документ
     *
     * @param int $id - Идентификатор документа в системе Антиплагиат
     * @param object $data - plagiarism_apru\connection->upload_document
     * @param array $attributes - plagiarism_apru\connection->upload_document
     * @param array $options - plagiarism_apru\connection->upload_document
     *
     * @return \stdClass - Объект с результатом загрузки документа в систему
     */
    public function update_document($id, $data, $attributes = [], $options = [])
    {
        // Удалить старый документ из системы
        $this->delete_document($id);
        // Загрузить новый документ в систему
        return $this->upload_document($data, $attributes, $options);
    }

    /**
     * Получить статистику по аккаунту
     */
    public function get_company_stats()
    {
        return $this->client->GetCompanyStats();
    }

    /**
     * Проверка доступности сервиса
     *
     * @return boolean
     */
    public function is_alive()
    {
        try
        {
            // Проверка доступности сервиса
            $ping = $this->client->Ping();
            if ( ! empty($ping) )
            {// Получено время отклика
                return TRUE;
            } else
            {// Сервис недоступен
                return FALSE;
            }
        } catch (SoapFault $ex)
        {// Сервис недостуен
            $message = (string)$ex->getMessage();
            debugging('Antiplagiat service connection is not alive:'.$message, DEBUG_DEVELOPER);
            return FALSE;
        }
    }
    
    /**
     * Создать объект docId для передачи в качестве параметра сервису
     *
     * @param int $id - ID документа в системе Антиплагиат
     * @param string $external - ID документа в системе Moodle
     *
     * @return \stdClass
     */
    private function make_docid($id, $external = NULL)
    {
        $docid = new \stdClass();
        $docid->Id = (int)$id;
        $docid->External = (string)$external;
        return $docid;
    }
    
    /**
     * Подготовить атирбуты документа
     *
     * Производит подготовку атрибутов документа с нормализацией
     *
     * @param array $attributes = Массив атрибутов документа
     *
     * @return array - Отфильтрованные атрибуты файла
     */
    private function prepare_doc_attributes($attributes = [])
    {
        // Формирование массива атрибутов
        $filtered = [];
        
        $attributes = (array)$attributes;
        
        // Имя документа
        if ( isset($attributes['Name']) )
        {// Атрибут определен
            $name = trim((string)$attributes['Name']);
            if ( strlen($name) > 255 )
            {// Лимит значения превышен
                $name = mb_strimwidth($name, 0, 252, '...');
            }
            $filtered['Name'] = $name;
            unset($attributes['Name']);
        } elseif ( isset($attributes['name']) )
        {// Нормализация атрибута
            $name = trim((string)$attributes['name']);
            if ( strlen($name) > 255 )
            {// Лимит значения превышен
                $name = mb_strimwidth($name, 0, 252, '...');
            }
            $filtered['Name'] = $name;
            unset($attributes['name']);
        }
        
        // Url документа
        if ( isset($attributes['Url']) )
        {// Атрибут определен
            $url = trim((string)$attributes['Url']);
            if ( strlen($url) > 4095 )
            {// Лимит значения превышен
                $url = mb_strimwidth($url, 0, 4092, '...');
            }
            $filtered['Url'] = $url;
            unset($attributes['Url']);
        } elseif ( isset($attributes['url']) )
        {// Нормализация атрибута
            $url = trim((string)$attributes['url']);
            if ( strlen($url) > 4095 )
            {// Лимит значения превышен
                $url = mb_strimwidth($url, 0, 4092, '...');
            }
            $filtered['Url'] = $url;
            unset($attributes['url']);
        }
        
        // Автор документа
        if ( isset($attributes['Author']) )
        {// Атрибут определен
            $author = trim((string)$attributes['Author']);
            if ( strlen($author) > 1023 )
            {// Лимит значения превышен
                $author = mb_strimwidth($author, 0, 1020, '...');
            }
            $filtered['Author'] = $author;
            unset($attributes['Author']);
        } elseif ( isset($attributes['author']) )
        {// Нормализация атрибута
            $author = trim((string)$attributes['author']);
            if ( strlen($author) > 1023 )
            {// Лимит значения превышен
                $author = mb_strimwidth($author, 0, 1020, '...');
            }
            $filtered['Author'] = $author;
            unset($attributes['author']);
        }
        
        // Дополнительные атирбуты
        $custom = [];
        foreach ( $attributes as $name => $value )
        {
            $AttrName = trim((string)$name);
            if ( strlen($AttrName) > 80 )
            {// Лимит значения превышен
                $AttrName = mb_strimwidth($AttrName, 0, 77, '...');
            }
            $AttrValue = trim((string)$value);
            if ( strlen($AttrValue) > 1023 )
            {// Лимит значения превышен
                $AttrValue = mb_strimwidth($AttrValue, 0, 1020, '...');
            }
            $custom[$AttrName] = $AttrValue;
        }
        if ( ! empty($custom) )
        {// Добавление дополнительных атрибутов
            $filtered['Custom'] = $custom;
        }
        return $filtered;
    }
    
    /**
     * Получение информации о тарифе
     * @throws \moodle_exception
     * @return \stdClass $result объект с информацией о тарифе
     */
    public function get_tarif_info()
    {
        try
        {
            $result = $this->client->GetTariffInfo();
        } catch(\SoapFault $e)
        {
            throw new \moodle_exception(
                'error_service_get_tariff_info', 'plagiarism_apru', NULL, NULL, $e->getMessage()
            );
        }
        return $result;
    }
}