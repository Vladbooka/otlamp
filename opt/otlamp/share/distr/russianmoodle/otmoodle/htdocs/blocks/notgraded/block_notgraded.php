<?php  // $Id: block_notgraded.php 2097/11/15
/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Блок отображения непроверенных ответов на вопросы типа "задание" или "эссе".
 */ 
class block_notgraded extends block_base
{
    
	/**
	 * Нужна для инициализации блока
	 * согласно требованиям разработчиков
	 * @return ничего не возвращает
	 */
	function init()
	{
		$this->title = get_string('notgraded', 'block_notgraded');//название модуля
		$this->version = 2011011200;//дата создания модуля
	}
    /** Позволяем настраивать блок для каждого конкретного курса
     * 
     * @return 
     */
    function instance_allow_config()
    {// @todo разобраться с тем, нужно ли это делать
        return true;
    }
    
    /** 
     * Говорит, что у блока есть настроки, которые можно редактировать
     * 
     * @return 
     */
    function has_config()
    {
        return true;
    }
    
    /**
	 * Функция для обновления содержания блока от разработчиков
	 */
	function refresh_content()
	{
    	// Nothing special here, depends on content()
    	$this->content = NULL;
    	return $this->get_content();
	}
    
	/**
	 * Выводит содержание блока на экран
	 * @return object содержимое блока
	 */
	function get_content()
	{
	    global $CFG, $USER, $COURSE;
	    $context = context_course::instance($COURSE->id);
	    // создаем новый объект для вывода
		$this->content = new stdClass;
		if (!isset($CFG) OR !isset($USER) OR !isset($COURSE) OR ! has_capability('block/notgraded:view', $context))
		{//если не можем начать работу выводим пустую строку
			$this->content->text = '';
		}else
		{//если все в норме
		    if( file_exists($CFG->dirroot . '/blocks/dof/locallib.php') )
		    {
		        if (isset($CFG->config->viewmode) AND $CFG->config->viewmode == 'block' )
		        {// выводим сразу все задания внутри блока
		        // записываем в объект для вывода на экран
		          $this->content->text = $this->get_list_notgraded_html();
		        }else
		        {// @todo вставить сюда настройку, которая определяет, показывать все задания внутри
    		        // блока, или выводить для них ссылку на отдельную страницу
    		        // выводим отдельную ссылку
    		        $this->content->text = '<a href="'.$CFG->wwwroot.
    		        '/blocks/notgraded/workslist.php?courseid='.$COURSE->id.'" target="_blank">'.
    		        get_string('view_notgraded_list', 'block_notgraded').'</a>';
		        }
		    } else
		    {
		        $this->content->text = get_string('need_dof_library', 'block_notgraded');
		    }
		}
        // определяем подвал блока (обязательно)
		$this->content->footer = '';
		if ($this->content)
		{//если есть что показать - выводим
	        return $this->content;
	    }
        
	}
    
	/**
	 * Получить код списка всех непроверенных заданий в курсе
	 * @param boolean $external - если true - на отдельной странице, если false - внутри блока
	 * @return string
	 */
    public function get_list_notgraded_html($external=false)
    {
        global $CFG, $COURSE, $USER, $SITE;
        $contextcourse = context_course::instance($COURSE->id);
        // объявляем переменную, для всего html-кода который будет в блоке
		$str = ''; 
		$view = optional_param('view', 0, PARAM_INT);
		$groupid  = optional_param('group', 0, PARAM_INT);
		if ( $COURSE->id == $SITE->id || has_capability('moodle/site:viewreports', $contextcourse, $USER->id) )
		{//покажем данные только преподавателю этого курса
            require_once($CFG->dirroot.'/blocks/notgraded/lib.php');
            $courseitems = new block_notgraded_items($COURSE->id, $USER->id);
		    if ( empty($CFG->block_notgraded_list) ) 
		    {
		        $CFG->block_notgraded_list = 'all';
            }
            //получаем непроверенные задания
            if ( $view == 0 AND ($CFG->block_notgraded_list == 'none' OR 
               ( $CFG->block_notgraded_list == 'none_group' AND $COURSE->groupmode != 0 )) )
            {
                $notgraded = null;
            }else
            {
                $view = 1;
                $notgraded = $courseitems->get_course_all_items();
            }
			if ( empty($notgraded) )
			{//если непроверенных заданий нет
			    $str .= html_writer::div(get_string('not_notgraded', 'block_notgraded'), 'text-center');
			}else
            {// если они есть - то приведем их к окончательному виду
                // сортируем по дате
                usort($notgraded, 'sort_elements');
                $format = new block_notgraded_format_item;
                foreach ( $notgraded as $element )
                {// перебираем все элементы и делаем из них единую строку
                    $str .= $format->format_element($element);
                }
            }
            // показываем меню выбора группы
            if ( $external )
            {// если меню находится на отдельной странице
                $link = $CFG->wwwroot.'/course/view.php?id='.$COURSE->id;
            }else
            {// Если меню находится внутри блока
                $link = $CFG->wwwroot.'/blocks/notgraded/workslist.php?courseid='.$COURSE->id;
            }
            $str = $this->groups_print_course_menu($COURSE, $link.'&view='.$view, true).$str;
		
            if ( $view == 0 )
            {
                $str = "<a href=\"$link&view=1\" class='d-inline'>"
                                    .get_string('all_notgraded', 'block_notgraded')."</a><br />".$str;
            }
		}
        // узнаем контекст сайта
        $contextsite   = context_course::instance(SITEID);
        if ( has_capability('moodle/site:viewreports', $contextsite, $USER->id) )
        {//покажем админу ссылку на страницу 
            //списка курсов с непроверенными заданиями если они есть
            $url = new moodle_url('/blocks/notgraded/notgraded_courses.php');
            $linktoall = html_writer::link($url, get_string('notgraded_courses', 'block_notgraded'), [
                'target' => '_blank',
                'class' => 'd-inline'
            ]);
            $str = $linktoall . $str;
            
        }
        
        return $str;
    }
    
    /**
     * Print group menu selector for course level.
     * @param object $course course object
     * @param string $urlroot return address
     * @param boolean $return return as string instead of printing
     * @return mixed void or string depending on $return param
     * 
     * @todo переработать эту функцию, она полностью взята из moodle и изменена, с целью 
     * исправить форматирование 
     */
    function groups_print_course_menu($course, $urlroot, $return=false) {
        global $CFG, $USER, $SESSION;
    
        if (!$groupmode = $course->groupmode) {
            if ($return) {
                return '';
            } else {
                return;
            }
        }
    
        $context = context_course::instance($course->id);
        if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $context)) {
            $allowedgroups = groups_get_all_groups($course->id, 0);
            // detect changes related to groups and fix active group
            if (!empty($SESSION->activegroup[$course->id][VISIBLEGROUPS][0])) {
                if (!array_key_exists($SESSION->activegroup[$course->id][VISIBLEGROUPS][0], $allowedgroups)) {
                    // active does not exist anymore
                    unset($SESSION->activegroup[$course->id][VISIBLEGROUPS][0]);
                }
            }
            if (!empty($SESSION->activegroup[$course->id]['aag'][0])) {
                if (!array_key_exists($SESSION->activegroup[$course->id]['aag'][0], $allowedgroups)) {
                    // active group does not exist anymore
                    unset($SESSION->activegroup[$course->id]['aag'][0]);
                }
            }
    
        } else {
            $allowedgroups = groups_get_all_groups($course->id, $USER->id);
            // detect changes related to groups and fix active group
            if (isset($SESSION->activegroup[$course->id][SEPARATEGROUPS][0])) {
                if ($SESSION->activegroup[$course->id][SEPARATEGROUPS][0] == 0) {
                    if ($allowedgroups) {
                        // somebody must have assigned at least one group, we can select it now - yay!
                        unset($SESSION->activegroup[$course->id][SEPARATEGROUPS][0]);
                    }
                } else {
                    if (!array_key_exists($SESSION->activegroup[$course->id][SEPARATEGROUPS][0], $allowedgroups)) {
                        // active group not allowed or does not exist anymore
                        unset($SESSION->activegroup[$course->id][SEPARATEGROUPS][0]);
                    }
                }
            }
        }
        
        //получение групп, доступных проверяющему, и групп, в которые проверяющий числится
        $activegroup = groups_get_course_group($course, true);
        $markergroupsids = array_keys(groups_get_all_groups($course->id, $USER->id));
        
        //инициализация массивов для формирования результата
        $groupsmenu = [];
        $groupsmenumine = [];
        $groupsmenuother = [];
        
        if (!$allowedgroups or $groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $context)) {
            $groupsmenu[0] = get_string('allparticipants');
        }
    
        if ($allowedgroups) {
            //стартовые элементы массивов, указывающие на начало секций с группами
            $groupsmenumine['minestart'] = '--' . get_string('mygroupssection', 'block_notgraded');
            $groupsmenuother['otherstart'] = '--' . get_string('othergroupssection', 'block_notgraded');
            foreach ($allowedgroups as $group) {
                if (in_array($group->id, $markergroupsids)) {
                    $groupsmenumine[$group->id] = format_string($group->name);
                } else {
                    $groupsmenuother[$group->id] = format_string($group->name);
                }
            }
            //завершающие элементы массивов, указывающие на завершение секций с группами
            $groupsmenumine['mineend'] = '--';
            $groupsmenuother['otherend'] = '--';
            
            $groupsmenu += $groupsmenumine + $groupsmenuother;
        }
    
        if ($groupmode == VISIBLEGROUPS) {
            $grouplabel = get_string('groupsvisible');
        } else {
            $grouplabel = get_string('groupsseparate');
        }
    
        if (count($groupsmenu) == 1) {
            $groupname = reset($groupsmenu);
            $output = $grouplabel.': '.$groupname;
        } else {
            $output = $this->popup_form($urlroot.'&amp;group=', $groupsmenu, 'selectgroup', $activegroup, '', '', '', true, 'self', $grouplabel);
        }
    
        $output = '<div>'.$output.'</div>';
    
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
    
    /**
     * Implements a complete little popup form
     * 
     * @todo эта функция тоже полностью взята из moodle, в ней изменены только две строки.
     * Это сделано для того, чтобы элемент select не вылезал за границы блока
     *
     * @uses $CFG
     * @param string $common  The URL up to the point of the variable that changes
     * @param array $options  Alist of value-label pairs for the popup list
     * @param string $formid Id must be unique on the page (originaly $formname)
     * @param string $selected The option that is already selected
     * @param string $nothing The label for the "no choice" option
     * @param string $help The name of a help page if help is required
     * @param string $helptext The name of the label for the help button
     * @param boolean $return Indicates whether the function should return the text
     *         as a string or echo it directly to the page being rendered
     * @param string $targetwindow The name of the target page to open the linked page in.
     * @param string $selectlabel Text to place in a [label] element - preferred for accessibility.
     * @param array $optionsextra TODO, an array?
     * @return string If $return is true then the entire form is returned as a string.
     * @todo Finish documenting this function<br>
     */
    function popup_form($common, $options, $formid, $selected='', $nothing='choose', $help='', $helptext='', $return=false,
    $targetwindow='self', $selectlabel='', $optionsextra=NULL) {
    
        global $CFG;
        static $go, $choose;   /// Locally cached, in case there's lots on a page
    
        if (empty($options)) {
            return '';
        }
    
        if (!isset($go)) {
            $go = get_string('go');
        }
    
        if ($nothing == 'choose') {
            if (!isset($choose)) {
                $choose = get_string('choose');
            }
            $nothing = $choose.'...';
        }
    
        // changed reference to document.getElementById('id_abc') instead of document.abc
        // MDL-7861
        $output = '<form action="'.$CFG->wwwroot.'/course/jumpto.php"'.
                            ' method="get" '.
                            ' id="'.$formid.'"'.
                            ' class="popupform">';
        if ($help) {
            $button = helpbutton($help, $helptext, 'moodle', true, false, '', true);
        } else {
            $button = '';
        }
    
        if ($selectlabel) {
            $selectlabel = '<label for="'.$formid.'_jump">'.$selectlabel.'</label>';
        }
    
        //IE and Opera fire the onchange when ever you move into a dropdwown list with the keyboard. 
        //onfocus will call a function inside dropdown.js. It fixes this IE/Opera behavior.
        //Note: There is a bug on Opera+Linux with the javascript code (first mouse selection is inactive), 
        //so we do not fix the Opera behavior on Linux
        if (core_useragent::check_ie_version() || (core_useragent::check_opera_version('Opera') && !core_useragent::check_browser_operating_system("Linux"))) {
            $output .= '<div>'.$selectlabel.$button.'<select style=" width: 100%; " id="'.$formid.'_jump" onfocus="initSelect(\''.$formid.'\','.$targetwindow.')" name="jump">'."\n";
        }
        //Other browser
        else {
            $output .= '<div>'.$selectlabel.$button.'<select style=" width: 100%; " id="'.$formid.'_jump" name="jump" onchange="'.$targetwindow.'.location=document.getElementById(\''.$formid.'\').jump.options[document.getElementById(\''.$formid.'\').jump.selectedIndex].value;">'."\n";  
        }
        
        if ($nothing != '') {
            $output .= "   <option value=\"javascript:void(0)\">$nothing</option>\n";
        }
    
        $inoptgroup = false;
        foreach ($options as $value => $label) {
    
            if ($label == '--') { /// we are ending previous optgroup
                /// Check to see if we already have a valid open optgroup
                /// XHTML demands that there be at least 1 option within an optgroup
                if ($inoptgroup and (count($optgr) > 1) ) {
                    $output .= implode('', $optgr);
                    $output .= '   </optgroup>';
                }
                $optgr = array();
                $inoptgroup = false;
                continue;
            } else if (substr($label,0,2) == '--') { /// we are starting a new optgroup
    
                /// Check to see if we already have a valid open optgroup
                /// XHTML demands that there be at least 1 option within an optgroup
                if ($inoptgroup and (count($optgr) > 1) ) {
                    $output .= implode('', $optgr);
                    $output .= '   </optgroup>';
                }
    
                unset($optgr);
                $optgr = array();
    
                $optgr[]  = '   <optgroup label="'. s(format_string(substr($label,2))) .'">';   // Plain labels
    
                $inoptgroup = true; /// everything following will be in an optgroup
                continue;
    
            } else {
               if (!empty($CFG->usesid) && !isset($_COOKIE[session_name()]))
                {
                    $url=sid_process_url( $common . $value );
                } else
                {
                    $url=$common . $value;
                }
                $optstr = '   <option value="' . $url . '"';
    
                if ($value == $selected) {
                    $optstr .= ' selected="selected"';
                }
    
                if (!empty($optionsextra[$value])) {
                    $optstr .= ' '.$optionsextra[$value];
                }
    
                if ($label) {
                    $optstr .= '>'. $label .'</option>' . "\n";
                } else {
                    $optstr .= '>'. $value .'</option>' . "\n";
                }
    
                if ($inoptgroup) {
                    $optgr[] = $optstr;
                } else {
                    $output .= $optstr;
                }
            }
    
        }
    
        /// catch the final group if not closed
        if ($inoptgroup and count($optgr) > 1) {
            $output .= implode('', $optgr);
            $output .= '    </optgroup>';
        }
    
        $output .= '</select>';
        $output .= '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        $output .= '<div id="noscript'.$formid.'" style="display: inline;">';
        $output .= '<input type="submit" value="'.$go.'" /></div>';
        $output .= '<script type="text/javascript">'.
                   "\n//<![CDATA[\n".
                   'document.getElementById("noscript'.$formid.'").style.display = "none";'.
                   "\n//]]>\n".'</script>';
        $output .= '</div>';
        $output .= '</form>';
    
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
    
    /**
     * Возвращает html-блок с контентом
     */
    public function get_notgraded_html() {
        global $CFG;
        if (file_exists($CFG->dirroot . '/blocks/dof/locallib.php')) {
            $content = $this->get_list_notgraded_html();
        } else {
            $content = get_string('need_dof_library', 'block_notgraded');
        }
        $inner = html_writer::div($content, 'd-flex flex-column align-items-start');
        return html_writer::div($inner, 'w-100 d-flex justify-content-center');
    }
}

/**
 * Эта функция используется для сортировки 
 * массива непроверенных заданий по дате
 * Вызывается в функции usort из метода get_content
 * @param object $a - один элемент массива
 * @param object $b - другой элемент массива
 * @return int
 */
function sort_elements($a, $b)
{
    if ( $a->time > $b->time )
    {
        return 1;
    }elseif ( $a->time < $b->time )
    {
        return -1;
    }else
    {
        return 1;
    }
}
?>
