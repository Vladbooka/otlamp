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
 * Библиотека взаимодействия с API Руконтекст
 *
 * @package    plagiarism
 * @subpackage rucont
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_rucont;

defined('MOODLE_INTERNAL') || die();

use plagiarism_rucont\settings_form;
use moodle_exception;
use coding_exception;
use stdClass;
use mod_feedback\event\response_deleted;

require_once($CFG->dirroot . '/plagiarism/rucont/lib.php');

/**
 * Класс для соединения с API "Руконтекст".
 */
class connection 
{
    /**
     * @var array - Список доступных типов файлов для проверки на плагиаризм
     */
    const SUPPORTED_TYPES = '.txt;.doc;.docx;.html;.htm;.pdf;.rtf';
    /**
     * @var array Список доступных типов архивов для проверки на плагиаризм
     */
    const SUPPORTED_ARCHIVES = '';

    /**
     * Объект подключения к API
     */
    var $api = NULL;
    
    /**
     * Конструктор класса. Выполняет подключение к API.
     */
    public function __construct() 
    {
        // Создание объекта OTAPI
        $this->api = new otserial();
    }
    
    /**
     * Загрузка документа
     * 
     * @param array $document - Данные по документу
     *                      content - Содержимое документа
     *                      filename - Имя документа(test.txt, ...) 
     *                      autor - Автор документа
     *                      title - Заголовок
     *                      tester - ФИО проверяющего
     *                      comment - Комментарий
     * @param array $options - Дополнительные опции
     * @return string json-данные
     */
    public function upload_document($document, $options = NULL) 
    {
        // Нормализация
        $document = (array)$document;
        if ( ! isset($document['content']) )
        {// Контент документа не передан
            throw new coding_exception('plagiarism_rucont_empty_content');
        }
        if ( ! isset($document['filename']) )
        {// Имя документа не найдено
            throw new coding_exception('plagiarism_rucont_empty_filename');
        } else 
        {// Имя передано
            // Проверка на допустимые типы файлов
            $supportedtypes    = explode(';', self::SUPPORTED_TYPES);
            $supportedarchives = explode(';', self::SUPPORTED_ARCHIVES);
            $allowedtypes = array_merge($supportedtypes, $supportedarchives);
            $exploded = explode('.', $document['filename']);
            if ( ! is_array($exploded) )
            {
                throw new coding_exception('plagiarism_rucont_not_valid_filename');
            } else 
            {
                $filetype = end($exploded);
                $filetype = '.'.(string)$filetype;
                if ( array_search($filetype, $allowedtypes) === false )
                {// Тип файла не поддерживается
                    throw new coding_exception('plagiarism_rucont_filetype_not_alloved');
                }
            }
        }
        if ( ! isset($document['autor']) || ! is_string($document['autor']) )
        {// Автор не передан
            $document['autor'] = '';
        }
        if ( ! isset($document['title']) || ! is_string($document['title']) )
        {// Заголовок не передан
            $document['title'] = '';
        }
        if ( ! isset($document['tester']) || ! is_string($document['tester']) )
        {// Проверяющий не передан
            $document['tester'] = '';
        }
        if ( ! isset($document['comment']) || ! is_string($document['comment']) )
        {// Комментарий не передан
            $document['comment'] = '';
        }
        
        // Формирование данных для запроса
        $data = [];
        $data['method'] = 'like';
        $data['body'] = [];
        $data['body']['doc'] = [
            'filename' => $document['filename'],
            'body' => base64_encode($document['content'])
        ];
        $data['body']['metafields'] = [
            'author' => $document['autor'],
            'title' => $document['title'],
            'tester' => $document['tester'],
            'comment' => $document['comment'],
        ];
        $data['body']['parameters'] = [
            'year' => (int)date('Y')
        ];
        
        // Передача запроса с данными
        $response = $this->api->rest($data);
        return $response;
    }
    
    /**
     * Получить результат проверки документа
     * 
     * @param int $id - Идентификатор документа
     */
    public function get_result($id)
    {
        // Формирование данных для запроса
        $data = [];
        $data['method'] = 'getresult';
        $data['body'] = [];
        $data['body']['requestId'] = (integer)$id;
        $data['body']['format'] = 'json';

        // Передача запроса с данными
        return $this->api->rest($data);
    }
    
    /**
     * Получить url отчета о проверке
     *
     * @param int $id - Идентификатор документа
     */
    public function get_report_url($id)
    {
        $result = $this->get_result($id);
        $url = NULL;
        if ( isset($result->reponse->result->hash) )
        {// Данные для получения отчета найдены
            $url = 'http://text.rucont.ru/History/ReviewItem?h='.$result->result->hash;
        }
        return $url;
    }
    
    /**
     * Получить коэфициент-оригинальности документа
     *
     * @param int $id - Идентификатор документа
     */
    public function get_originality_rating($id)
    {
        $result = $this->get_result($id);
        $url = NULL;
        if ( isset($result->reponse->result->hash) )
        {// Данные для получения отчета найдены
            $url = 'http://text.rucont.ru/History/ReviewItem?h='.$result->result->hash;
        }
        return $url;
    }
}
