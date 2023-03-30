<?php

/**
 * Поделиться ссылкой
 *
 * @package    block
 * @subpackage otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otshare\publication_types;

use stdClass;
use moodle_url;
use core_competency\api;
use DateTime;
use IntlDateFormatter;
use context_system;
use context_course;
use core_plugin_manager;
use grade_item;
use html_writer;
use html_table;
use html_table_row;
use html_table_cell;
use block_otshare\publication_types\sn_publication_interface as sn_interface;
use block_otshare\publication_types\publication_interface as publication_interface;
use block_otshare\exception\publication as publication_exception;
use local_learninghistory\local\completion_tracker;
use local_learninghistory\local\grades_manager;
use block_mylearninghistory\local\utilities;
use stored_file;

require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once ($CFG->libdir . '/moodlelib.php');

abstract class publication_base implements publication_interface
{
    protected $name;
    
    protected $sharer;
    protected $userid;
    
    protected $courseid;
    protected $course;
    protected $finalgrade;
    protected $maxgrade;
    
    protected $hash;
    protected $timecreated;
    protected $save_status = false;
    
    protected $block;
    protected $id;
    
   
    
    abstract protected function check_properties();
    abstract protected function get_insert_data();
    abstract protected function get_grade_text($short = false);
    abstract public function set_meta_properties();
    abstract public function set_data($data);
    
    protected function get_insert_row()
    {
        // Формируем объект для апдейта/вставки в БД
        $insert_obj = new stdClass();
        $insert_obj->userid = $this->userid;
        $insert_obj->timecreated = time();
        
        // Кладем сериализованные данные в объект
        $insert_obj->data = serialize($this->get_insert_data());
        $insert_obj->hash = $this->get_hash($insert_obj->data);
        
        // Установим хэш для последующего использования
        $this->hash = $insert_obj->hash;
        
        return $insert_obj;
    }
    
    protected function set_sn_url($url)
    {
        $this->sharer->set_url($url);
    }
    
    protected function get_sharer_name()
    {
        return $this->sharer->get_serviceshortname();
    }
    
    protected function share_sn()
    {
        redirect($this->sharer->get_share_url());
    }
    
    protected function get_course_grade($course)
    {
        // Отображение оценки
        if ( empty($course->finalgrade) ) 
        {
            $grade = get_string('nograde', 'block_otshare');
        }
        else
        {// Вытаскиваем оценку за курс
            // Параметры для поиска grade_item
            $gradeparams = [
                    'courseid' => $course->id,
                    'itemtype' => 'course'
            ];
            
            // Получаем grade_item
            $gradeitem = new grade_item($gradeparams, true);
            
            // Получаем отформатированную оценку
            $grade = grade_format_gradevalue($course->finalgrade, $gradeitem, true, null, 0);
            
            // Если оценка - число, округляем, иначе оставляем как есть
            if ( is_numeric($grade) )
            {
                $grade = round($grade);
            }
        }
        
        return $grade;
    }
    
    /**
     * Получить файл логотипа для наложения при альтернативном шаринге
     * @return stored_file|boolean
     */
    protected function get_logo()
    {
        // Получим первое попавшееся изображение из описания курса
        // Подключение менеджера файлов
        $fs = get_file_storage();
        
        // Получение файлов описания курса
        $files = $fs->get_area_files(
            context_system::instance()->id,
            'block_otshare',
            'logo',
            $this->courseid
            );
        
        if ( ! empty($files) )
        {
            foreach ( $files  as $file)
            {
                if ( $file->is_valid_image() )
                {
                    return $file;
                    break;
                }
            }
        }
        return false;
    }
    
    protected function get_image_url()
    {
        // Дефолтные параметры
        $url_image = '';

        // Получим первое попавшееся изображение из описания курса
        // Подключение менеджера файлов
        $fs = get_file_storage();
        
        // Получение файлов описания курса
        $files = $fs->get_area_files(
            context_system::instance()->id,
            'block_otshare',
            'public',
            $this->courseid
        );
        
        if ( ! empty($files) )
        {
            foreach ( $files  as $file)
            {
                if ( $file->is_valid_image() )
                {
                    $url_image = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                        );
                    break;
                }
            }
        }
        
        if( empty($url_image) )
        {
            // Получение файлов описания курса
            $files = $fs->get_area_files(
                context_course::instance($this->courseid)->id,
                'course',
                'overviewfiles',
                0
                );
            
            if ( ! empty($files) )
            {
                foreach ( $files  as $file)
                {
                    $flag = $file->is_valid_image();
                    if ( ! empty($flag) )
                    {
                        $url_image = moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            null,
                            $file->get_filepath(),
                            $file->get_filename()
                            );
                        break;
                    }
                }
            }
        }
        
        return $url_image;
    }
    
    protected function get_alter_img_url($caption, $coursename, $gradestr, $review)
    {
        $url_image = '';
        $options = [
            'caption' => $caption,
            'coursename' => $coursename,
            'gradestr' => $gradestr,
            'review' => $review
        ];
        
        list($width, $height) = $this->get_size();
        
        $recorddiff = new stdClass();
        $recorddiff->component = 'block_otshare';
        $recorddiff->filearea = 'og_image';
        $recorddiff->itemid = $this->id;
        
        // Получаем хранилище
        $fs = get_file_storage();
        
        // Получение файлов описания курса
        $files = $fs->get_area_files(
            context_system::instance()->id,
            'block_otshare',
            'public',
            $this->courseid
        );
        
        if ( ! empty($files) )
        {
            foreach ( $files  as $file)
            {
                if ( $file->is_valid_image() )
                {
                    $url_image = $this->get_alter_img($file, $recorddiff, $width, $height, 0xFFFFFF, 100, $options);
                    break;
                }
            }
        }
        
        if( empty($url_image) )
        {
            // Получение файлов описания курса
            $files = $fs->get_area_files(
                context_course::instance($this->courseid)->id,
                'course',
                'overviewfiles',
                0
                );
            
            if ( ! empty($files) )
            {
                foreach ( $files  as $file)
                {
                    if ( $file->is_valid_image() )
                    {
                        $url_image = $this->get_alter_img($file, $recorddiff, $width, $height, 0xFFFFFF, 100, $options);
                        break;
                    }
                }
            }
        }
        
        return $url_image;
    }
    
    protected function get_size()
    {
        switch($this->sharer->get_serviceshortname())
        {
            case 'fb':
                return [600,315];
                break;
            case 'vk':
                return [537,240];
                break;
            case 'tw':
                return [750,250];
                break;
            case 'ok':
                return [840,840];
                break;
            case 'gp':
                return [530,298];
                break;
            default:
                return [600,315];
                break;
        }
    }
    
    protected function get_overlay()
    {
        return [48, 182, 229];
    }
    
    protected function get_transparency()
    {
        return 50;
    }
    
    protected function image_add_text(&$srcimg, $srcimgwidth, $srcimgheight, $fontfile, $fontheight, $leftorigin, $toporigin, $leftoffset, $topoffset, $color, $text)
    {
        // размеры текст по координатам
        $fontsize = $srcimgheight * $fontheight * 0.75;
        
        $box = imagettfbbox($fontsize, 0, $fontfile, $text);
        
        switch($leftorigin)
        {
            case 'center':
                $boxwidth = $box[4]-$box[0];
                // размер отступа влево, чтобы текст оказался посередине заданной точки
                $left = $srcimgwidth/2-$boxwidth/2 + $srcimgwidth*$leftoffset;
                break;
            case 'left':
                $left = 0 + $srcimgwidth*$leftoffset;
                break;
        }
        
        switch($toporigin)
        {
            case 'center':
                $top = $srcimgheight/2 + $srcimgheight*$topoffset;
                break;
            case 'bottom':
                $top = $srcimgheight + $srcimgheight*$topoffset;
                break;
            case 'top':
                $boxheight = $box[3]-$box[7];
                $top = $boxheight + $srcimgheight*$topoffset;
                break;
        }
        
        imagettftext($srcimg, $fontsize, 0, $left, $top, $color, $fontfile, $text);
    }
    
    protected function create_alter_img(stored_file $file, stdClass $recorddiff, $width = NULL, $height = NULL, $rgb = 0xFFFFFF, $quality = 100, $options = [])
    {
        global $CFG;
        require_once ($CFG->libdir . '/filestorage/file_storage.php');
        
        if ( ! $file->is_valid_image() )
        {// Файл не является изображением
            return false;
        }
        
        // Копирование исходного файла изображения
        $thumbnailpath = $file->copy_content_to_temp('otshare_temp');
        if ( empty($thumbnailpath) )
        {// Файл превью не создался
            return false;
        }
        
        // Получение информации об изображении
        $imageinfo = $file->get_imageinfo();
        if ( empty($imageinfo) )
        {// Данные о изображении не получены
            // Получение информации из превью
            $imageinfo = getimagesize($thumbnailpath);
            if ( empty($imageinfo) )
            {// Данные невозможно получить
                return false;
            }
            // Нормализация
            $imageinfo['mimetype'] = $imageinfo['mime'];
        }
        
        // Формат изображения
        $format = strtolower(substr($imageinfo['mimetype'], strpos($imageinfo['mimetype'], '/')+1));
        
        // Функции работы с типом изображения
        $icfunc = "imagecreatefrom" . $format;
        $imagefunc = "image" . $format;
        
        if ( ! function_exists($icfunc) || ! function_exists($imagefunc) )
        {// Функция работы с текущим типом изображения не найдена
            return false;
        }
        
        // Исходное ображение
        $srcimg = $icfunc($thumbnailpath);
        
        // Ширина исходного изображения
        $srcimgwidth = $imageinfo['width'];
        // Высота исходного изображения
        $srcimgheight = $imageinfo['height'];

        // Изображение-заливка
        // Цвет заливки
        list($r, $g, $b) = $this->get_overlay();
        $overlay = imagecolorallocatealpha($srcimg, $r, $g, $b, $this->get_transparency());
        // Наложение заливки на изображение
        imagefilledrectangle($srcimg, 0, 0, $srcimgwidth, $srcimgheight, $overlay);
        
        // Получим логотип для наложения
        $logo = $this->get_logo();
        if( ! empty($logo) )
        {// Если получили - работаем с ним
            // Скопируем логотип во временнную директорию и будем работать с временным файлом
            $logotemppath = $logo->copy_content_to_temp('otshare_logo_temp');
            
            // Получение информации об изображении
            $logoinfo = $logo->get_imageinfo();
            if ( empty($logoinfo) )
            {// Данные о изображении не получены
                // Получение информации из временного файла
                $logoinfo = getimagesize($logotemppath);
                if ( empty($imageinfo) )
                {// Данные невозможно получить
                    return false;
                }
                // Нормализация
                $logoinfo['mimetype'] = $logoinfo['mime'];
            }
            
            // Формат изображения
            $logoformat = strtolower(substr($logoinfo['mimetype'], strpos($logoinfo['mimetype'], '/')+1));
            // Функции для работы с изображением с учетом формата
            $lcfunc = "imagecreatefrom" . $logoformat;
            $imagelogofunc = "image" . $logoformat;
            // Создадим изображение
            $logoimg = $lcfunc($logotemppath);
            
            if( $logoinfo['height'] > $srcimgheight * 0.2 )
            {// Если логотип больше 20% от высоты изображения, на которое делаем наложение, уменьшим его
                $dstlogoimgheight = $srcimgheight * 0.2;
                $dstlogoimgwidth = $logoinfo['width'] * ($dstlogoimgheight / $logoinfo['height']);
            } else 
            {
                $dstlogoimgheight = $logoinfo['height'];
                $dstlogoimgwidth = $logoinfo['width'];
            }
            
            // Создадим под конечный логотип прямоугольник нужных размеров
            $resampledlogoimg = imagecreatetruecolor($dstlogoimgwidth, $dstlogoimgheight);
            // Выключим режима сопряжения цветов для изображения
            imagealphablending($resampledlogoimg, false);
            // Установим сохранение альфа канала
            imagesavealpha($resampledlogoimg, true);         
            // Уменьшаем логотип до заданных размеров
            imagecopyresampled($resampledlogoimg, $logoimg, 0, 0, 0, 0, $dstlogoimgwidth, $dstlogoimgheight, $logoinfo['width'], $logoinfo['height']);
            // Вычислим координаты наложения логотипа
            $logo_x = $srcimgwidth - $dstlogoimgwidth - $srcimgheight * 0.025;
            $logo_y = $srcimgheight * 0.025;
            // Наложим логотип на изображение
            imagecopyresampled($srcimg, $resampledlogoimg, $logo_x, $logo_y, 0, 0, $dstlogoimgwidth, $dstlogoimgheight, $dstlogoimgwidth, $dstlogoimgheight);
            // Уничтожим файл, с которым работали
            imagedestroy($logoimg);
        }
        
        // Цвет для текста
        $white = imagecolorallocate($srcimg, 0xFF, 0xFF, 0xFF);
        // Шрифт для текста
        $fontfile = $CFG->dirroot . '/blocks/otshare/fonts/OpenSans-Regular.ttf';

        if( ! empty($options['caption']) )
        {
            $this->image_add_text(
                $srcimg, 
                $srcimgwidth, 
                $srcimgheight, 
                $fontfile, 
                0.05, 
                'left', 
                'top',
                0.025, 
                0.025,
                $white, 
                $options['caption']
            );
        }
        
        if( ! empty($options['gradestr']) )
        {
            $this->image_add_text(
                $srcimg, 
                $srcimgwidth, 
                $srcimgheight, 
                $fontfile, 
                0.2, 
                'center', 
                'center',
                0, 
                0,
                $white, 
                $options['gradestr']
            );
        }
        
        if( ! empty($options['coursename']) )
        {
            $this->image_add_text(
                $srcimg,
                $srcimgwidth,
                $srcimgheight,
                $fontfile,
                0.12,
                'center',
                'center',
                0,
                0.16,
                $white,
                $options['coursename']
            );
        }
        
        if( ! empty($options['review']) )
        {
            $this->image_add_text(
                $srcimg,
                $srcimgwidth,
                $srcimgheight,
                $fontfile,
                0.075,
                'center',
                'bottom',
                0,
                -0.025,
                $white,
                $options['review']
            );
        }

        // Формирование размеров превью
        if ( ! empty($width) && (int)$width > 0 )
        {// Указана ширина изображения
            $dstimgwidth = (int)$width;
        } else
        {// Ширину не передали
            $dstimgwidth = 1200;
        }
        if ( ! empty($height) && (int)$height > 0 )
        {// Указана ширина изображения
            $dstimgheight = (int)$height;
        } else
        {// Ширину не передали
            $dstimgheight = 630;
        }

        // Основное изображение, которое будет являться результатом генерации
        $dstimg = imagecreatetruecolor($dstimgwidth, $dstimgheight);
        // Установка фона изображения
        imagefill($dstimg, 0, 0, $rgb);

        // Подготовка размеров для вписывания исходного изображения в результирующее
        if ( $srcimgwidth <= $dstimgwidth && $srcimgheight <= $dstimgheight )
        {// Исходное изображение полностью помещается в ожидаемый результат
            $rewidth = $srcimgwidth;
            $reheight = $srcimgheight;
        } else
        {
            if( $dstimgwidth / $srcimgwidth < $dstimgheight / $srcimgheight )
            {// Исходное изображение горизонтальное
                $rewidth = $dstimgwidth;
                $reheight = $srcimgheight * ( $dstimgwidth / $srcimgwidth );
            } else
            {// Исхоное изображение вертикальное
                $rewidth = $srcimgwidth * ( $dstimgheight / $srcimgheight );
                $reheight = $dstimgheight;
            }
        }
        
        imagecopyresampled(
            $dstimg,
            $srcimg,
            $dstimgwidth/2 - $rewidth/2,
            $dstimgheight/2 - $reheight/2,
            0,
            0,
            $rewidth,
            $reheight,
            $srcimgwidth,
            $srcimgheight
        );
              
        // Нормализация значений
        switch ( $format )
        {
            case 'png' :
                $quality = (integer)(( 100 - $quality ) / 9);
                $imagefunc($dstimg, $thumbnailpath, $quality);
                break;
            case 'gif' :
                $imagefunc($dstimg, $thumbnailpath);
                break;
            default:
                $imagefunc($dstimg, $thumbnailpath, $quality);
                break;
        }
        
        // Удаление temp изображений
        imagedestroy($dstimg);
        imagedestroy($srcimg);
        
        // Создание превью записи
        $thumbnailrecord = new stdClass();
        $thumbnailrecord->contextid = $file->get_contextid();
        $thumbnailrecord->component = $file->get_component();
        $thumbnailrecord->filearea = $file->get_filearea();
        $thumbnailrecord->itemid = $file->get_itemid();
        $thumbnailrecord->filepath = $file->get_filepath();
        $thumbnailrecord->filename = $file->get_filename();
        $thumbnailrecord->userid = $file->get_userid();
        $thumbnailrecord->filesize = $file->get_filesize();
        $thumbnailrecord->mimetype = $file->get_mimetype();
        $thumbnailrecord->status = $file->get_status();
        $thumbnailrecord->source = $file->get_source();
        $thumbnailrecord->author = $file->get_author();
        $thumbnailrecord->license = $file->get_license();
        $thumbnailrecord->timecreated = $file->get_timecreated();
        $thumbnailrecord->timemodified = $file->get_timemodified();
        $thumbnailrecord->sortorder = $file->get_sortorder();
        $thumbnailrecord->referencefileid = $file->get_referencefileid();
        $thumbnailrecord->reference = $file->get_reference();
        $thumbnailrecord->referencelastsync = $file->get_referencelastsync();
        if ( ! empty($recorddiff) )
        {// Получение значений, которые следует изменить в исходной записи
            foreach ( $recorddiff as $key => $value )
            {
                if ( isset($thumbnailrecord->$key) )
                {// Значение найдено
                    $thumbnailrecord->$key = $value;
                }
            }
        }
        
        // Получение хранилища
        $fs = get_file_storage();
        
        // Сохранение превью изображения
        //$thumbnail = $fs->create_file_from_pathname($thumbnailrecord, $thumbnailpath);
        
        //return true;
        return $fs->create_file_from_pathname($thumbnailrecord, $thumbnailpath);
    }
    
    protected function get_alter_img(stored_file $file, stdClass $recorddiff, $width = 500, $height = NULL, $rgb = 0xFFFFFF, $quality = 100, $options = [])
    {
        global $CFG;
        require_once ($CFG->libdir . '/filestorage/file_storage.php');
        
        // Формирование данных превью изображения
        $preview = new stdClass();
        $preview->contextid = $file->get_contextid();
        $preview->component = $file->get_component();
        $preview->filearea = $file->get_filearea();
        $preview->itemid = $file->get_itemid();
        $preview->filepath = $file->get_filepath();
        $preview->filename = $file->get_filename();
        if ( ! empty($recorddiff) )
        {// Получение значений, которые следует изменить в исходной записи
            foreach ( $recorddiff as $key => $value )
            {
                if ( isset($preview->$key) )
                {// Значение найдено
                    $preview->$key = $value;
                }
            }
        }
        
        // Получение хранилища
        $fs = get_file_storage();
        
        // Проверка наличия превью
        $exist = $fs->file_exists(
            $preview->contextid,
            $preview->component,
            $preview->filearea,
            $preview->itemid,
            $preview->filepath,
            $preview->filename
            );
        if( $exist )
        {
            $fs->delete_area_files($preview->contextid, $preview->component, $preview->filearea, $preview->itemid);
        }
        // Создание превью изображения
        $result = $this->create_alter_img($file, $recorddiff, $width, $height, $rgb, $quality, $options);
        if ( empty($result) )
        {// Создание не удалось
            return '';
        } else
        {
            // Вернуть url превью
            $url = moodle_url::make_pluginfile_url(
                $result->get_contextid(),
                $result->get_component(),
                $result->get_filearea(),
                $result->get_itemid(),
                $result->get_filepath(),
                $result->get_filename()
                );
            return $url;
        }
    }
    
    protected function set_course_grade()
    {
        $course_data = $this->get_course_data();
        
        $this->finalgrade = $this->get_course_grade($course_data);
        $this->maxgrade = $course_data->maxgrade;
    }
    
    public function __construct(sn_interface $sharer)
    {
        GLOBAL $USER, $CFG;
        
        if ( file_exists($CFG->dirroot . '/local/crw/lib.php') )
        {
            require_once ($CFG->dirroot . '/local/crw/lib.php');
        }
        $this->userid = $USER->id;
        $this->sharer = $sharer;
    }
    
    public function set_block()
    {
        global $DB;
        
        if ( empty($this->courseid) )
        {
            throw new publication_exception('empty_courseid');
        }
        $context = context_course::instance($this->courseid);
        $instance = $DB->get_record('block_instances', [
            'parentcontextid' => $context->id, 
            'blockname' => 'otshare',
            'pagetypepattern' => 'course-view-*'
        ]);
        if( ! empty($instance) )
        {
            $this->block = block_instance('otshare', $instance);
        }
    }
    
    public function get_block_config($name)
    {
        if( isset($this->block->config->$name) )
        {
            return $this->block->config->$name;
        } else 
        {
            return null;
        }
    }
    
    public function save_data()
    {
        GLOBAL $DB;
        
        // Получим объект с данными
        $insert_obj = $this->get_insert_row();
        
        if ( ! $this->check_properties() )
        {
            throw new publication_exception('invalid_properties');
        }
        
        if ( ! $insert_obj = $this->get_insert_row() )
        {
            throw new publication_exception('invalid_properties');
        }
        
        if ( $DB->record_exists('block_otshare_shared_data', ['hash' => $insert_obj->hash]) )
        {// Обновляем запись
            $record = $DB->get_record(
                    'block_otshare_shared_data', 
                    ['hash' => $insert_obj->hash],
                    '*',
                    IGNORE_MULTIPLE
                    );
           
            // Устанавливаем ID для обновления
            $insert_obj->id = $record->id;
            
            // Обновляем запись
            $DB->update_record('block_otshare_shared_data', $insert_obj);
        } else 
        {// Добавляем новую запись
            $DB->insert_record('block_otshare_shared_data', $insert_obj);
        }
        
        // Установис флаг, что слепок сохранен
        return $this->save_status = true;
    }
    
    public function share()
    {
        if ( empty($this->save_status) )
        {
            throw new publication_exception('function_save_data_wasnt_used');
        }

        $this->set_sn_url(new moodle_url('/blocks/otshare/lp/lp.php', ['hash' => $this->hash]));
        $this->share_sn();
    }
    
    public function get_hash($data)
    {
        return substr(md5($data), 0, 12);
    }
    
    public function get_user_info()
    {
        GLOBAL $OUTPUT;
        
        if ( empty($this->userid) ) 
        {
            throw new publication_exception('empty_userid');
        }
        
        // Дефолтные параметры
        $html = '';
        
        // Аватарка пользователя
        $userpicturewrapper = '';
        $counterswrapper = '';
        $profilefieldswrapper = '';
        
        // Получим объект пользователя
        $user = user_get_users_by_id([$this->userid]);
        $user = array_shift($user);
        
        $userpicture = html_writer::div(
                $OUTPUT->user_picture($user, [
                        'size' => 512,
                        'class' => 'block_otshare_lp_picture_img'
                ]),
                'block_otshare_lp_user_picture'
                );
        $html .= html_writer::div(
                $userpicture,
                'block_otshare_lp_picture_wrapper'
                );
        
        $html .= html_writer::div(
                fullname($user),
                'block_otshare_lp_fullname_wrapper'
                );
        
        $html .= html_writer::div(
                $this->get_grade_text(),
                'block_otshare_lp_grade_wrapper'
                );
        
        $result = html_writer::div(
                $html,
                'block_otshare_lp_user_info_wrapper'
                );
        
        return $result;
    }
    
    public function get_course_info()
    {
        if ( empty($this->courseid) || empty($this->userid) )
        {
            throw new publication_exception('empty_properties');
        }
        
        // Дефолтные параметры
        $html = '';

        $course = $this->get_course_data();
        $context = context_course::instance($course->id);

        $coursecategories = [];
        foreach(explode('/',\coursecat::get($course->category, MUST_EXIST, true)->path) as $categoryid)
        {
            if( is_number($categoryid) )
            {
                $coursecategories[] = \coursecat::get($categoryid, MUST_EXIST, true)->name;
            }
        }
        
        $urlparams = ['id' => $course->id];
        $url = new moodle_url('/course/view.php', $urlparams);
        $link = html_writer::link($url, $course->fullname, [
                'title' => implode(' / ',$coursecategories).' / '.$course->fullname
        ]);
        
        $coursename = new html_table_cell($link);
        $rowdata=[$coursename];

        $url = new moodle_url('/grade/report/user/index.php', ['id' => $course->id]);
        
        // Максимальная оценка
        if ( ! empty($course->maxgrade) )
        {
            $grade = html_writer::tag('a', $this->finalgrade . " / " . $this->maxgrade, ['href' => $url->out(false)]);
            $gradeandtotal = new html_table_cell($grade);
        } else
        {
            $grade = html_writer::tag('a', $this->finalgrade, ['href' => $url->out(false)]);
            $gradeandtotal = new html_table_cell($grade);
        }
        $rowdata[] = $gradeandtotal;
        
        // Отображение освоенных компетенций
        
        if ( ($course->visible || has_capability('moodle/course:viewhiddencourses', $context)) && api::is_enabled())
        {
            $coursecompetencies = api::count_competencies_in_course($course->id);
            $proficientcompetencies = api::count_proficient_competencies_in_course_for_user($course->id, $this->userid);
        
            $competenciesurl = new \moodle_url(
                    '/admin/tool/lp/coursecompetencies.php',
                    [
                            'courseid'=>$course->id
                    ]
                    );
            $competencieslink = html_writer::link(
                    $competenciesurl,
                    $proficientcompetencies."/".$coursecompetencies,
                    [
                            'class' => 'block_otshare_lp_page_progressbar_inner'
                    ]
                    );
            $competenciespercent = 0;
            if( $coursecompetencies > 0 )
            {
                $competenciespercent = $proficientcompetencies * 100 / $coursecompetencies;
            }
            $rowdata[] = html_writer::div(
                    $competencieslink,
                    'block_otshare_lp_page_progressbar',
                    [
                            'data-percent' => $competenciespercent
                    ]
                    );
        }
        
        // Отображение процента завершения курса
        if( is_number($course->progress) )
        {
            $courseprogresslabel = $course->progress."%";
            if ( (int)$course->progress == 100 )
            {
                $courseprogresslabel = get_string('course_completed','block_otshare');
            }
        } else
        {
            $courseprogresslabel = $course->progress;
        }
        
        // Имеется процентр завершения курса - нарисуем прогресс-бар
        $progressbarinner = html_writer::div(
                $courseprogresslabel,
                'block_otshare_lp_page_progressbar_inner'
                );
        $progressbar = html_writer::div(
                $progressbarinner,
                'block_otshare_lp_page_progressbar',
                [
                        'data-percent' => $course->progress
                ]
                );
        $progress = new html_table_cell($progressbar);
        $rowdata[] = $progress;

        // Класс морматирования даты (вывод месяца на русском)
        $formatter = new IntlDateFormatter('ru_RU', IntlDateFormatter::FULL, IntlDateFormatter::FULL);
        $formatter->setPattern('d MMMM YYYY');
        
        // Дата начала курса
        $start_date = new html_table_cell($formatter->format(new DateTime(date('d F y', $course->startdate))));
        $rowdata[] = $start_date;

        // Строка таблицы
        $student_data[] = new html_table_row($rowdata);

        // Таблица
        $table_student = new html_table();
        $table_student->size = ['30%', '10%', '10%', '15%', '15%'];
        $table_student->align = ['left', 'center', 'center', 'center', 'center'];
        $table_student->head = 
            [
                    get_string('course', 'block_otshare'),
                    get_string('rating', 'block_otshare'),
                    get_string('competencies', 'block_otshare'),
                    get_string('progress', 'block_otshare'),
                    get_string('date_start', 'block_otshare')
            ];

        // Таблица с курсом
        $table_student->data = $student_data;
        $html .= html_writer::table($table_student);

        // Вернем html разметку
        return $html;
    }
    
    public function get_course_description()
    {
        GLOBAL $PAGE;
        
        // Дефолтные параметры
        $html = '';
        
        // Сепаратор
        $html .= html_writer::div('', 'block_otshare_lp_page_separator');
        // Доп информация о курсе
        $additional = html_writer::div(get_string('course_info', 'block_otshare'), 'block_otshare_lp_page_add');
        $additional .= html_writer::div($this->course->fullname, 'block_otshare_lp_page_addsec');
        
        // Отобразить Блок информации о курсе
        $course_info = "";
        
        $installlist = core_plugin_manager::instance()->get_installed_plugins('local');
        if ( array_key_exists('crw', $installlist) )
        {
            // Получаем рендер локального плагина
            $courserenderer = $PAGE->get_renderer('local_crw');
            $course_info = $courserenderer->ci_courseblock($this->course);
        }
        
        $html .= html_writer::div($additional . $course_info, 'block_otshare_lp_page_course_description');
        
        return $html;
    }
    
    public function get_course_data()
    {
        // Получим объект курса
        $course = $this->course;
        
        // Добавим оценки пользователя к курсам
        $grade = grades_manager::get_course_finalgrade($course, $this->userid);
        
        if ($grade === false)
        {
            $course->finalgrade = '';
        }
        
        // Добавим максимально возможную оценку за курс
        $maxgrade = utilities::get_course_maxgrade($course, $this->courseid);
        $course->maxgrade = $maxgrade;
        
        // Добавим процент завершения курса
        $completiontracker = new completion_tracker($course);
        $completion = $completiontracker->get_user_completion_all($this->userid);
        if($completion AND $completion->criteriacount > 0)
        {
            $course->progress = (int)$completion->percentcompleted;
        }
        else
        {
            $course->progress = get_string('progressdoesnttracking', 'block_otshare');
        }

        return $course;
    }
    
    public function set_navbar()
    {
        GLOBAL $PAGE;

        if ( empty($this->timecreated) )
        {
            throw new publication_exception('empty_timecreated');
        }
        
        $PAGE->navbar->add(get_string('result_date','block_otshare', date('d.m.y', $this->timecreated)));
    }
}
