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
 * Condition main class.
 *
 * @package    availability_duration
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_duration;

require_once($CFG->dirroot.'/enrol/locallib.php');
use core\plugininfo\mod;

defined('MOODLE_INTERNAL') || die();

/**
 * Condition main class.
 *
 * @package availability_duration
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    /** @var string source of date ('coursedate','enrollmentdate') that this condition requires */
    protected $source;
    /** @var int duration that this condition requires */
    protected $duration;
    /** @var string durationmeasure ('d','h','m') that this condition requires */
    protected $durationmeasure;
    /** @var string courselogiccminstance */
    protected $courselogiccminstance;
    /** @var string instanceid */
    protected $instanceid;
    
    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        
        if( !empty($structure->instanceid) )
        {
            $this->instanceid = $structure->instanceid;
        } else
        {
            $this->instanceid = microtime();
        }
        
        if(is_number($structure->duration)) {
            $this->duration = $structure->duration;
        } else {
            throw new \coding_exception('Invalid duration');
        }
        
        if( in_array($structure->source, [
            'coursedate',
            'enrollmentdate',
            'unenrollmentdate',
            'courselastaccessdate',
            'sincecourselogicactivate'
        ]) )
        {
            $this->source = $structure->source;
        } else {
            throw new \coding_exception('Invalid source to get date');
        }
        
        if(  $this->source == "sincecourselogicactivate" &&
            !empty($structure->courselogiccminstance) &&
            is_number($structure->courselogiccminstance) )
        {
            $this->courselogiccminstance = $structure->courselogiccminstance;
        } else if ($this->source == "sincecourselogicactivate")
        {
            throw new \coding_exception('Invalid course logic instance');
        }
        
        
        if(in_array($structure->durationmeasure, [
            'w',
            'd',
            'h',
            'm'
        ]) )
        {
            $this->durationmeasure=$structure->durationmeasure;
        } else {
            throw new \coding_exception('Invalid duration measure');
        }
        
    }
    
    /**
     * JSON код ограничения доступа
     *
     * @return stdClass Object representing condition
     */
    public static function get_json($source, $duration, $durationmeasure, $courselogiccminstance, $instanceid)
    {
        
        return (object)[
            'type' => 'duration',
            'source' => $source,
            'duration' => $duration,
            'durationmeasure' => $durationmeasure,
            'courselogiccminstance' => $courselogiccminstance,
            'instanceid' => $instanceid
        ];
    }
    
    public function save() {
        $saveobj = new \stdClass();
        $saveobj->type = 'duration';
        $saveobj->instanceid = $this->instanceid;
        $saveobj->duration = $this->duration;
        $saveobj->durationmeasure = $this->durationmeasure;
        $saveobj->source = $this->source;
        
        if( !empty($this->courselogiccminstance) )
        {
            $saveobj->courselogiccminstance = $this->courselogiccminstance;
        }
        
        return $saveobj;
    }
    
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        global $USER, $PAGE, $DB;
        
        // по умолчанию доступ запрещен
        // если условия ниже будут выполнены, доступ будет разрешён
        $allow=false;
        
        // строгий запрет
        // требуется для случаев, когда исходных данных недостаточно для определения выполнения условий
        // и для обоих случаев (стандарт и инверсия) доступ должен быть запрещен
        $strictdisallow = false;
        
        if (!$userid)
        {
            $userid = $USER->id;
        }
        
        switch($this->durationmeasure) {
            case 'w':
                $settingdurationtime =  $this->duration*7*24*60*60;
                break;
            case 'd':
                $settingdurationtime =  $this->duration*24*60*60;
                break;
            case 'h':
                $settingdurationtime = $this->duration*60*60;
                break;
            case 'm':
                $settingdurationtime = $this->duration*60;
                break;
        }
        
        //Курс с ограничением
        $course = $info->get_course();
        
        //Выбранный в настройках источник в качестве стартовой точки продолжительности
        switch($this->source)
        {
            case "coursedate":
                //продолжительность активности курса в секундах на текущий момент
                $coursedurationtime = (time() - $course->startdate);
                $allow = ($coursedurationtime >= $settingdurationtime);
                break;
            case "enrollmentdate":
                
                // количество подписок, для которых не настроена дата начала
                $notset = 0;
                
                $manager = new \course_enrolment_manager($PAGE, $course);
                $userenrolments = $manager->get_user_enrolments($userid);
                //будем проверять каждую из подписок пользователя
                foreach($userenrolments as $userenrolment)
                {
                    if ($userenrolment->timestart == 0)
                    {
                        $notset++;
                        continue;
                    }
                    //продолжительность активности подписки в секундах на текущий момент
                    $enrolmentdurationtime = (time() - $userenrolment->timestart);
                    //также, статус подписки должен быть активным
                    if ($enrolmentdurationtime >= $settingdurationtime && $userenrolment->status == 0)
                    {
                        // условие выполнено
                        $allow=true;
                        break;
                    }
                }
                
                if (count($userenrolments) == $notset)
                {// все подписки были без настроенных дат начала подписки
                    // нет возможности вычислить выполнение условия
                    // применяем ограничение доступа и для обычного условия и для инвертированного
                    $strictdisallow = true;
                }
                break;
            case "courselastaccessdate":
                $courselastaccessdurationtime = null;
                
                $courselastaccess = $DB->get_record('user_lastaccess',[
                    'courseid' => $course->id,
                    'userid' => (int)$userid
                ], 'timeaccess', IGNORE_MULTIPLE );
                
                if ( ! empty($courselastaccess) )
                {// Имеется записи о дате последнего доступа к курсу
                    //интервал времени, прошедший с момента последнего обращения к курсу
                    $courselastaccessdurationtime = (time() - $courselastaccess->timeaccess);
                    $allow = ($courselastaccessdurationtime >= $settingdurationtime);
                } else
                {// попыток доступа к курсу не найдено
                    // поскольку нет возможности вычислить выполнение условия
                    // применяем ограничение доступа и для обычного условия и для инвертированного
                    $strictdisallow = true;
                    // для понимания
                    // пользователь к курсу ни разу не обращался
                    // невозможно ответить прошло ли сколько-то времени с неизвестного момента
                    // поэтому условие в обоих случах считаем не выполненным
                }
                
                break;
            case "sincecourselogicactivate":
                $instance = $DB->get_record('otcourselogic', ['id' => (int)$this->courselogiccminstance]);
                if( !empty($instance) )
                {
                    // Попытка получить данные из БД
                    $state = $DB->get_record(
                        'otcourselogic_state',
                        ['instanceid' => $instance->id, 'userid' => (int)$userid],
                        'id, status, changetime',
                        IGNORE_MULTIPLE
                        );
                    if ( ! empty($state) && $state->status == '1' )
                    {// Логика курса активна
                        //время в течение которого логика курса активна
                        $activatedurationtime = (time() - $state->changetime);
                        $allow = ($activatedurationtime >= $settingdurationtime);
                    } else {
                        // нет возможности вычислить выполнение условия
                        // применяем ограничение доступа и для обычного условия и для инвертированного
                        $strictdisallow = true;
                        // пример для понимания
                        // надо получить пятерку за тест, чтобы сработала логика курса
                        // условие доступа настроено на выполнение при условии, что прошла неделя с момента получения пятерки
                        // инверсия: не прошло пять дней с момента получения пятерки
                        // если логика курса не активна (пятерка не получена)
                        // прошло пять дней с момента получения пятерки? нет, пятерка не получена в принципе
                        // инверсия: не прошло пять дней с момента получения пятерки? нет, пятерка не получена в принципе
                        // в обоих случаях условие не должно выполняться
                    }
                } else {
                    // нет возможности вычислить выполнение условия
                    // применяем ограничение доступа и для обычного условия и для инвертированного
                    $strictdisallow = true;
                }
                break;
            case "unenrollmentdate":
                
                // количество подписок, для которых не настроена дата окончания
                $notset = 0;
                
                $manager = new \course_enrolment_manager($PAGE, $course);
                $userenrolments = $manager->get_user_enrolments($userid);
                //будем проверять каждую из подписок пользователя
                foreach($userenrolments as $userenrolment)
                {
                    if ($userenrolment->timeend == 0)
                    {
                        $notset++;
                        continue;
                    }
                    //оставшееся до конца подписки время
                    $tillend = $userenrolment->timeend-time();
                    
                    if( $tillend <= $settingdurationtime )
                    {
                        $allow=true;
                        break;
                    }
                }
                
                if (count($userenrolments) == $notset)
                {// все подписки были без настроенных дат окончания подписки
                    // нет возможности вычислить выполнение условия
                    // применяем ограничение доступа и для обычного условия и для инвертированного
                    $strictdisallow = true;
                    // несмотря на то, что дату окончания курса можно трактовать как бесконечность
                    // принято решение, что дата окончания подписки нам всё-таки неизвестна
                    // ведь завершать подписку возможно и другими способами
                    // лучше следовать строгой логике и в обоих случаях не выполнять условие
                }
                break;
        }
        
        //инвертируем, если пользовтель НЕ должен соответствовать условиям
        return ($strictdisallow ? false : ($not ? !$allow : $allow));
    }
    
    public function get_description($full, $not, \core_availability\info $info) {
        $lessmore = $not?'lessthan':'morethan';
        if($this->source == 'unenrollmentdate')
        {//для даты окончания подписки считаем сколько времени осталось
            //до конца, а не прошло с начала, поэтому инвертируем фразу
            $lessmore = $not?'morethan':'lessthan';
        }
        return get_string($lessmore,'availability_duration').
        ' '.
        $this->duration.
        ' '.
        get_string('durationmeasure'.$this->durationmeasure,'availability_duration').
        ' '.
        get_string($this->source.'description','availability_duration');
    }
    
    protected function get_debug_string() {
        return get_string($not?'lessthan':'morethan','availability_duration').
        ' '.
        $this->duration.
        ' '.
        get_string($this->source.'description','availability_duration');
    }
    
    /**
     * Восстановление из бэкапа
     * {@inheritDoc}
     * @see \core_availability\tree_node::update_after_restore()
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name)
    {
        global $DB;
        
        try
        {
            $cm_data = get_course_and_cm_from_instance($this->courselogiccminstance, 'otcourselogic');
            if ( empty($cm_data) )
            {
                return false;
            }
        } catch ( \moodle_exception $e )
        {
            return false;
        }
        $cm_instance = $cm_data[1]->get_course_module_record(true);
        if ( empty($cm_instance) )
        {
            return false;
        }
        
        if ( $this->source == 'sincecourselogicactivate' )
        {
            $rec = \restore_dbops::get_backup_ids_record($restoreid, 'course_module', $cm_instance->id);
            // Запись не найдена
            if ( empty($rec->newitemid) )
            {
                // If we are on the same course (e.g. duplicate) then we can just
                // use the existing one.
                if ( $DB->record_exists('course_modules', ['id' => $cm_instance->id, 'course' => $courseid]) )
                {
                    return false;
                }
                
                // Otherwise it's a warning.
                $this->courselogiccminstance = 0;
                $this->source = 'coursedate';
                $logger->process('Restored item (' . $name . ') has availability condition on module that was not restored', \backup::LOG_WARNING);
            } else
            {
                $this->courselogiccminstance = (int)$rec->newitemid;
            }
        }
        
        return true;
    }
}
?>