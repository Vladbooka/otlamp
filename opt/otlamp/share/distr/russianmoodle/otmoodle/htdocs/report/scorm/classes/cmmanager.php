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
 * Отчет по результатам SCORM. Класс работы с модулями курса scorm.
 * 
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_scorm;

defined('MOODLE_INTERNAL') || die();

use stdClass;

class cmmanager 
{
    /**
     * Обработчик данных
     * 
     * @var datamanager
     */
    protected $datamanager = null;
    
    /**
     * Массив модулей курса SCORM
     * 
     * @var array
     */
    protected $cms = [];
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        // Инициализация обработчика
        $this->datamanager = new datamanager();
    }
    
    /**
     * Получение модуля курса SCORM
     * 
     * @param int $cmid - ID модуля курса
     * 
     * @return stdClass|null
     */
    protected function get_cm($cmid)
    {
        if ( ! isset($this->cms[(int)$cmid]) )
        {// Модуль не был ранее получен
            
            $scorm = get_coursemodule_from_id('scorm', (int)$cmid);
            if ( ! $scorm )
            {// Модуль не найден
                return null;
            }
            $this->cms[(int)$cmid] = $scorm;
        }
        return $this->cms[(int)$cmid];
    }
    
    /**
     * Установка процента выполнения оцениваемых элементов SCORM для прохождения
     * 
     * @param int $cmid - ID модуля курса SCORM
     * @param float $passpersent - Процент выполнения
     * 
     * @return float|null $passpersent - Сохраненный процент прохождения или null
     */
    public function set_passpercent($cmid, $passpersent)
    {
        // Получение данных по модулю курса
        $scorm = $this->get_cm($cmid);
        
        if ( $scorm )
        {// Данные получены
            
            // Нормализация процента прохождения
            $passpersent = str_replace(',', '.', (string)$passpersent);
            $passpersent = floatval($passpersent);
            $passpersent = round($passpersent, 2);
            if ( $passpersent > 100 )
            {
                $passpersent = 100;
            } elseif ( $passpersent < 0 )
            {
                $passpersent = 0;
            }
            
            // Установка процента прохождения
            $option = $this->datamanager->set_cm_option((int)$cmid, 'passpersent', $passpersent);
            if ( $option )
            {// Установка опции прошла успешно
                return floatval($option->value);
            }
        }
        
        // Ошибка установки значения
        return null;
    }
    
    /**
     * Получение процента выполнения оцениваемых элементов SCORM для прохождения
     *
     * @param int $cmid - ID модуля курса SCORM
     *
     * @return float|null
     */
    public function get_passpercent($cmid)
    {
        // Получение данных по модулю курса
        $scorm = $this->get_cm($cmid);
    
        if ( $scorm )
        {// Данные получены
    
            // Установка процента прохождения
            $value = $this->datamanager->get_cm_option((int)$cmid, 'passpersent');
            if ( $value !== null )
            {// Установка получена
                return floatval($value);
            }
        }
    
        // Ошибка получения значения
        return null;
    }
    
    /**
     * Сохранение данных по оцениваемым элементам модуля курса SCORM
     * 
     * @param int $cmid - ID модуля курса SCORM
     * @param array $data - Массив данных в формате [identifier => ['weight' => ]]
     * 
     * @return bool - Результат сохранения
     */
    public function set_gradeelements_data($cmid, $data)
    {
        global $DB;
        
        // Получение данных по модулю курса
        $scorm = $this->get_cm((int)$cmid);
        
        // Проверка валидности идентификатора модуля курса
        if ( ! $scorm )
        {
            return false;
        }
        
        //$transaction = $DB->start_delegated_transaction();
        // Получение текущих данных по оцениваемым элементам
        if ( ! $this->remove_gradeelements_data($cmid) )
        {// Не удалось удалить информацию
            return false;
        }
        
        // Сохранение текущих данных по элементам
        $number = 0;
        foreach ( $data as $identifier => $gradeelementdata )
        {
            // Установка идентификатора
            if ( $this->datamanager->set_cm_option(
                (int)$cmid, 'gradeelement_'.$number.'_identifier', $identifier) )
            {// Идентификатор сохранен
                
                // Нормализация веса
                $weight = 0;
                if ( ! empty($gradeelementdata['weight']) )
                {
                    $weight = str_replace(',', '.', (string)$gradeelementdata['weight']);
                    $weight = floatval($weight);
                    $weight = round($weight, 2);
                }
                // Сохранение веса
                if ( ! $this->datamanager->set_cm_option(
                    (int)$cmid, 'gradeelement_'.$number.'_weight', $weight) )
                {
                    return false;
                }
                
                // Нормализация типа оценивания
                $gradetype = $gradeelementdata['gradetype'];
                if ( ! in_array($gradetype, [1,2]) )
                {
                    $gradetype = 2;
                }
                
                // Сохранение типа оценивания
                if ( ! $this->datamanager->set_cm_option(
                     (int)$cmid, 'gradeelement_'.$number.'_gradetype', $gradetype) )
                {
                    return false;
                }
            } else 
            {
                return false;
            }
            $number++;
        }
        //$transaction->allow_commit();
        
        return true;
    }
    
    /**
     * Получение данных по оцениваемым элементам модуля курса SCORM
     *
     * @param int $cmid - ID модуля курса SCORM
     *
     * @return array - Данные по оцениваемым элементам в формате [identifier => ['weight' => ]]
     */
    public function get_gradeelements_data($cmid)
    {
        // Дефолтные параметры
        $gradeelements = [];
        
        // Получение данных по модулю курса
        $scorm = $this->get_cm((int)$cmid);
    
        // Проверка валидности идентификатора модуля курса
        if ( ! $scorm )
        {
            return [];
        }
    
        // Получение всех данных о модуле курса
        $options = (array)$this->datamanager->get_cm_options((int)$cmid);
        
        // Формирование реестра всех идентификаторов
        foreach ( $options as $option )
        {
            // Разбиение полученной опции
            $nameparts = explode('_', $option->name);
            if ( isset($nameparts[0]) )
            {// Разбиение прошло удачно
                if ( count($nameparts) > 2 && $nameparts[0] == 'gradeelement' && $nameparts[2] == 'identifier' )
                {// Опция идентификатора оцениваемого элемента
                    // Идентификатор оцениваемого элемента
                    $gradeelements[$option->value]['identifier'] = $option->value;
                    // Добавление веса оцениваемого элемента
                    $optionname = 'gradeelement_'.$nameparts[1].'_weight';
                    $gradeelements[$option->value]['weight'] = (float)0;
                    // Поиск опций данного оцениваемого элемента
                    foreach ( $options as $row )
                    {
                        if ( $row->name == $optionname )
                        {// Вес найден
                            $gradeelements[$option->value]['weight'] = floatval($row->value);
                        }
                    }
                    // Добавление типа оценивания вопроса
                    $optionname = 'gradeelement_'.$nameparts[1].'_gradetype';
                    $gradeelements[$option->value]['gradetype'] = 2;
                    // Поиск опций данного оцениваемого элемента
                    foreach ( $options as $row )
                    {
                        if ( $row->name == $optionname )
                        {// Тип оценивания найден
                            $gradeelements[$option->value]['gradetype'] = intval($row->value);
                        }
                    }
                }
            }
        }

        return $gradeelements;
    }
    
    /**
     * Удаление всех данных по оцениваемым элементам модуля курса SCORM
     *
     * @param int $cmid - ID модуля курса SCORM
     *
     * @return bool - Результат удаления
     */
    public function remove_gradeelements_data($cmid)
    {
        global $DB;
        
        // Получение всех данных о модуле курса
        $options = (array)$this->datamanager->get_cm_options((int)$cmid);
        
        $transaction = $DB->start_delegated_transaction();
        foreach ( $options as $id => $option )
        {
            // Разбиение полученной опции
            $nameparts = explode('_', $option->name);
            if ( isset($nameparts[0]) && $nameparts[0] == 'gradeelement' )
            {// Опция оцениваемых элементов
                if ( ! $this->datamanager->remove_cm_option((int)$cmid, $option->name) )
                {
                    return false;
                }
            }
        }
        $transaction->allow_commit();
        
        return true;
    }
    
    public function get_user_info($cm, $sco, $userid)
    {
        // Получение данных по модулю
        $passpersent = $this->get_passpercent($cm->id);
        $gradeelementsdata = $this->get_gradeelements_data($cm->id);
        
        // Получение максимального веса по модулю
        $maxweight = 0;
        foreach ( $gradeelementsdata as $gradeelement )
        {
            if ( isset($gradeelement['weight']) )
            {
                $maxweight += $gradeelement['weight'];
            }
        }
        
        // Получение попыток прохождения
        $attempts = scorm_get_all_attempts($cm->instance, $userid);
        
        $bestweight = 0;
        $attempt = '';
        $scoreraw = '';
        $runtime = '';
        $totaltime = '';
        
        foreach ( $attempts as $attempt )
        {
            $attemptweight = [];
            
            // Получение трека прохождения
            $trackdata = scorm_get_tracks($sco->id, $userid, $attempt);
            if ( ! empty($trackdata->status) )
            {
                // Сбор данных о прохождении модуля
                foreach ( $trackdata as $step => $value )
                {
                    if ( strpos($step, 'cmi.interactions') !== false )
                    {
                        $stepnamedata = explode('_', $step, 2);
                        $stepnum = (int)$stepnamedata[1];
                        $stepname = str_replace($stepnum.'.', '', $stepnamedata[1]);
                        
                        if ( $stepname == 'id' )
                        {// Найден идентификатор шага
                            // Поиск веса в настройках отчета
                            if ( isset($gradeelementsdata[$value]) )
                            {// В настройках присутствует указанный идентификатор
                                if ( $gradeelementsdata[$value]['gradetype'] === 1 )
                                {// Тип оценивания - просмотр
                                    // Регистрация веса для итогового рассчета
                                    $attemptweight[$value] = $gradeelementsdata[$value]['weight'];
                                } else
                                {// Тип оценивания - правильный ответ
                                    // Поиск статуса ответа пользователя по данному вопросу
                                    $trackname = 'cmi.interactions_'.$stepnum.'.result';
                                    if ( isset($trackdata->$trackname) && $trackdata->$trackname == 'correct' )
                                    {// Регистрация веса для итогового рассчета
                                        $attemptweight[$value] = $gradeelementsdata[$value]['weight'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $weight = array_sum($attemptweight);
            if ( $weight >= $bestweight )
            {
                $attempt = $attempt;
                $scoreraw = ( isset($trackdata->{'cmi.core.score.raw'}) ? $trackdata->{'cmi.core.score.raw'} : '' );
                $runtime = ( isset($trackdata->timemodified) ? $trackdata->timemodified : '' );
                $totaltime = ( isset($trackdata->total_time) ? $trackdata->total_time : '' );
                $bestweight = $weight;
            }
        }
        
        if ( $maxweight > 0 )
        {
            $userpersent = $bestweight * 100 / $maxweight;
        } else 
        {
            $userpersent = 100;
        }
        return [
            'userpercent' => $userpersent,
            'scoreraw' => $scoreraw,
            'runtime' => $runtime,
            'totaltime' => $totaltime,
            'bestweight' => $bestweight,        
        ];
    }
}