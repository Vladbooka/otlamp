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
 * Тип вопроса Объекты на изображении. Описание механизма работы вопроса.
 *
 * @package    qtype
 * @subpackage otimagepointer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Класс, описывающий логику работы вопроса в системе
 * 
 */
class qtype_otimagepointer_question extends question_with_responses 
{
    /**
     * Экземпляр источника изображения, используемый вопросом
     * 
     * @var null|object
     */
    private $imagesource = null;
    
    /**
     * Получить источник изображения для текущего вопроса
     *
     * @return object - Экземпляр источника изображения
     */
    public function get_imagesource()
    {
        if ( $this->imagesource )
        {// Источник найден
            return $this->imagesource;
        }
        
        // Инициализация источника
        $this->imagesource = $this->qtype->imagesources_get_by_pluginname($this->imagesourcetype);

        return $this->imagesource;
    }
    
    /**
     * Получить базовое изображение вопроса из источника
     * 
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     *
     * @return null|stored_file - Файл основного изображения
     */
    public function get_image(question_attempt $qa)
    {
        // Получение источника
        $imagesource = $this->get_imagesource();
        
        if ( $imagesource )
        {
            return $imagesource->question_get_image($qa);
        }
        return null;
    }
    
    /**
     * Проверка наличия базового изображения для указанной попытки прохождения
     *
     * @param question_attempt $qa - Текущая попытка прохождения вопроса
     *
     * @return bool
     */
    public function has_image(question_attempt $qa)
    {
        // Получение источника
        $imagesource = $this->get_imagesource();
    
        if ( $imagesource )
        {
            return $imagesource->question_has_image($qa);
        }
        return false;
    }
    
    /**
     * Получить движок вопроса
     * 
     * @param question_attempt $qa - Объект попытки прохождения вопроса
     * @param string $preferredbehaviour - Необходимый тип движка
     * 
     * @return question_behaviour - Объект инициализированного движка вопроса
     */
    public function make_behaviour(question_attempt $qa, $preferredbehaviour) 
    {
        return question_engine::make_behaviour('manualgraded', $qa, $preferredbehaviour);
    }
    
    /**
     * Получить url для отображения базового изображения вопроса
     *
     * @param question_attempt $qa - Объект попытки прохождения вопроса
     * @param object $imagesource - Экземпляр источника изображения 
     *
     * @return moodle_url - URL для доступа к базовому изображению вопроса
     */
    public function get_image_url(question_attempt $qa, $imagesource = null)
    {
        if ( empty($imagesource) )
        {
            $imagesource = $this->get_imagesource();
        }
        
        // Получение ID набора
        $qubaid = $qa->get_usage_id();
        // Получение номера слота в наборе
        $slot = $qa->get_slot();
        // Генерация токена доступа
        $access_token = $imagesource->get_access_token($qubaid, $slot);
        // Формирование ссылки
        $imageurl = new moodle_url(
            '/question/type/otimagepointer/baseimage.php',
            [
                'quba' => $qubaid,
                'slot' => $slot,
                'token' => $access_token,
                'refresh' => uniqid()
            ]
        );
        return $imageurl;
    }
    
    /**
     * Получить массив обязательных полей данных ответа пользователя
     * 
     * @return array - Массив вида ['Идентификатор поля' => 'Используемый фильтр']
     */
    public function get_expected_data() 
    {
        // Массив обязательных данных ответа
        $expected = [
            'answer' => PARAM_RAW_TRIMMED, 
            'answer_baseimage' => PARAM_RAW_TRIMMED,
            'answer_baseimage_pathnamehash' => PARAM_RAW_TRIMMED
        ];
        return $expected;
    }
    
    /**
     * Проверить целостность ответа пользователя
     * 
     * @return bool - Результат проверки
     */
    public function is_complete_response(array $response)
    {
        // Проверка целостности по источнику
        $imagesource = $this->get_imagesource();
        if ( ! empty($imagesource) )
        {// Источник изображения определен
            // Валидация ответа в источнике
            $iscomplete = $imagesource->is_complete_response($this, $response);
            if ( $iscomplete && isset($response['answer']) )
            {// Дан полноценный ответ
                return true;
            }
        }
        return false;
    }
    
    /**
     * Проверить разницу между ответами пользователя
     * 
     * @return bool - Результат проверки
     */
    public function is_same_response(array $prevresponse, array $newresponse)
    {
        // Подготовка отпечатков
        $prevhash = sha1(serialize($prevresponse));
        $newhash = sha1(serialize($newresponse));
        
        // Проверка идентичности
        if ( $prevhash === $newhash )
        {// Ответы совпадают, не сохранять ответ пользователя
            return true;
        }
        
        // Процесс генерации результирующего файла
        $this->create_responsefile($newresponse);
        
        return false;
    }
    
    /**
     * Процесс предоставления ответа в виде текста для отчета
     * 
     */
    public function summarise_response(array $response) 
    {
        if ( isset($response['answer']) ) 
        {
            return '';
        } else 
        {
            return null;
        }
    }
    
    /**
     * Генерация результирующего файла
     * 
     * @param array $response - 
     */
    protected function create_responsefile($response)
    {
        // Проверка целостности по источнику
        $imagesource = $this->get_imagesource();
        
        // Подключение менеджера файлов
        $fs = get_file_storage();
        
        if ( ! empty($imagesource) )
        {// Источник изображения определен
            
            // Получение базового изображения
            $baseimagefile = $fs->get_file_by_hash($response['answer_baseimage_pathnamehash']);
            if ( ! $baseimagefile )
            {
                return null;    
            }
            
            $image = imagecreatefromstring($baseimagefile->get_content());
            imagesavealpha($image, true);
            
            // Подготовка результирующего изображения на основе базового
            $width = imagesx($image);
            $height = imagesy($image);
            
            if ( isset($response['answer']) )
            {// Ответ пользователя передан
                
                // Нормализация ответа
                $answer = trim($response['answer']);
                // Выделение контента изображения из ответа
                $exploded = explode(',', $answer);
                if ( isset($exploded[1]) )
                {// Контент изображения получен
                    $filecontent = base64_decode($exploded[1]);
                    if ( $filecontent )
                    {// Декодирование успешно
                        
                        // Генерация изображения для добавления к результату
                        $answer = imagecreatefromstring($filecontent);
                        
                        imagealphablending($answer, false);
                        ImageSaveAlpha($answer, true);
                        ImageFill($answer, 0, 0, IMG_COLOR_TRANSPARENT);
                        imagealphablending($answer, true);
                        
                        $answerwidth = imagesx($answer);
                        $answerheight = imagesy($answer);
                        
                        imagecopyresampled($image, $answer, 0, 0, 0, 0, $width, $height, $answerwidth, $answerheight);
                    }
                }
            }
            
            // Генерация имени результирующего изображения
            $filename = sha1($response['answer_baseimage'] . $response['answer']);
            
            // Генерация контента
            ob_start();
            imagepng($image);
            $contents = ob_get_contents();
            ob_end_clean();
            
            // Сохранение базового изображения для попытки прохождения вопроса
            $filerecord = new stdClass();
            $filerecord->contextid = $this->contextid;
            $filerecord->component = 'qtype_otimagepointer';
            $filerecord->filearea  = 'user_response';
            $filerecord->itemid    = $this->id;
            $filerecord->filepath  = '/';
            $filerecord->filename  = $filename;
            
            $exists = $fs->file_exists(
                $filerecord->contextid, 
                $filerecord->component, 
                $filerecord->filearea, 
                $filerecord->itemid, 
                $filerecord->filepath, 
                $filerecord->filename
            );
            if ( ! $exists )
            {
                return $fs->create_file_from_string($filerecord, $contents);
            }
            return true;
        }
    }
    
    public function get_responsefile($filename)
    {
        // Подключение менеджера файлов
        $fs = get_file_storage();
        
        return $fs->get_file(
            $this->contextid, 
            'qtype_otimagepointer',
            'user_response',
            $this->id,
            '/',
            $filename
        );
    }
    
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) 
    {
        return parent::check_file_access($qa, $options, $component,
                $filearea, $args, $forcedownload);
        
    }
    
    public function get_correct_response() 
    {
        return null;
    }
}