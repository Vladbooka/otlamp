<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Форма для добавления/удаления предмета в список связей учителя с предметами
 *
 * Код частично позаимствован из формы назначения ролей (moodle/admin/roles/assign.html)
 */
// Подключаем библиотеки';
require_once('lib.php');
//проверяем доступ
if ( $DOF->storage('teachers')->is_access('create') )
{// если есть право покажем форму назначения предметов
    if ( empty($pitem) OR (isset($pitem) AND $pitem->status == 'deleted') )
    {
        $DOF->print_error('programmitem_not_found', null, null, 'im' ,'employees');
    }
    ?>
    <form id="apitems_assign_form" method="post" action="">
    <div style="text-align:center;">
    <label for="available"><?php $DOF->get_string('teachers_available', 'employees') ?></label> 
    <label for="toadd"><?php $DOF->get_string('teachers_to_add', 'employees') ?></label>
    
    <input type="hidden" name="id" value="<?php print($id); ?>" />
    <input type="hidden" name="sesskey" value="<?php print(sesskey()); ?>" />
      <table summary="" style="margin-left:auto;margin-right:auto" border="0" cellpadding="5" cellspacing="0">
        <tr>
          <td valign="top">
              <label for="removeselect"><?php echo $DOF->get_string('teachers_available', 'employees', '<br/>'); ?></label>
              <br />
              <select name="removeselect[]" size="20" id="removeselect" multiple="multiple"
                      onfocus="getElementById('apitems_assign_form').add.disabled=true;
                               getElementById('apitems_assign_form').remove.disabled=false;
                               getElementById('apitems_assign_form').addselect.selectedIndex=-1;">
              <?php
    		  	// СПИСОК УЖЕ СУЩЕСТВУЮЩМХ КУРСОВ';
                // те курсы, которые уже добавлены в список преподавателя
    			$signedteachers = $DOF->im('employees')->get_pitem_teachers($id,true);
    			if ( ! empty($signedteachers) )
    			{// список добавленных предметов не пуст - выведем его';
    				foreach ( $signedteachers as $teacher )
    				{// выводим название предмета вместе с его кодом
                        // определим статус учителя для этого предмета: если учитель пока не 
                        // преподает предмет, а только собирается его преподавать - то выделим предмет серым
                        $color = 'black';
                        //if ( $teacher->status == 'plan' )
                        //{// учитель только планирует преподавать этот предмет
                        //    $color = 'gray';
                        //}
    	                echo '<option style=" color:'.$color.'; " value="'.$teacher->appointmentid.'">'.$teacher->fullname.' ['.$teacher->enumber.'] ('.$teacher->worktime.')</option>\n';   
    	            }
    			}else
    			{// пустой select-элемент нарушает структуру xhtml
    				echo '<option/>'; 
    			}
              ?>
              
              </select></td>
          <td valign="top">
            <br />
    		<!-- СТРЕЛКИ "ДОБАВИТЬ" и "УДАЛИТЬ" -->
            <p>
                <?php
                    if ( $DOF->storage('programmitems')->get_field($id, 'status') == 'active' )
                    {// если табельный номер активен - то разрешим появление галочки "активировать немедленно"
                        $options = 'checked="checked"';
                    }else
                    {// в остальных случаях - запретим ставить галочку
                        $options = 'disabled="disabled"';
                    }
                ?>
                <label for="addselect">
                    <?php echo $DOF->get_string('activate_immediately', 'employees', '<br/>'); ?>
                </label>
                <br/>
                <input type="checkbox" name="activate" <?php echo $options; ?> />
            </p>
            <p class="arrow_button" align="center">
                <input name="add" id="add" type="submit" value="<?php echo '&lt;&nbsp;'.$DOF->modlib('ig')->igs('add'); ?>" title="<?php echo $DOF->modlib('ig')->igs('add'); ?>" /><br />
                <input name="remove" id="remove" type="submit" value="<?php echo $DOF->modlib('ig')->igs('delete').'&nbsp;&gt;' ?>" title="<?php echo $DOF->modlib('ig')->igs('remove'); ?>" />
            </p>
          </td>
          <td valign="top">
              <label for="addselect"><?php echo $DOF->get_string('teachers_to_add', 'employees'); ?></label>
              <br />
              <select name="addselect[]" size="20" id="addselect" multiple="multiple"
                      onfocus="getElementById('apitems_assign_form').add.disabled=false;
                               getElementById('apitems_assign_form').remove.disabled=true;
                               getElementById('apitems_assign_form').removeselect.selectedIndex=-1;">
              <?php
    		    // СПИСОК КУРСОВ ДЛЯ ДОБАВЛЕНИЯ
    			// собираем данные для формы добавления/удаления предмета:
    			// те курсы, которые можно добавить в список преподавателя
                $avalteachers = $DOF->im('employees')->get_pitem_teachers($id,false);
                
                // оставим в списке только те объекты, на использование которых есть право
                $permissions  = array(array('plugintype'=>'storage', 'plugincode'=>'appointments', 'code'=>'use'));
                $avalteachers = $DOF->storage('acl')->get_acl_filtered_list($avalteachers, $permissions);
                if ( ! empty($avalteachers) )
    			{
    				foreach ( $avalteachers as $teacher )
    				{// перебираем все предметы программы и выводим информацию о каждом
                        echo '<option value="'.$teacher->appointmentid.'">'.$teacher->fullname.' ['.$teacher->enumber.']</option>\n';
    				}
    				// закрываем список предметов группы
    				echo "</optgroup>\n";
                }else
    			{// пустой select-элемент нарушает структуру xhtml
    				echo '<option/>'; 
    			}
              ?>
             </select>
             <br />
             <p align="left">
             <label for="worktime" ><?php echo $DOF->get_string('worktimi_for_teaching', 'employees'); ?></label>
             <br />
             <input type="text" name="worktime" id="worktime" size="5" >
             </p>
    		 <!-- ПОИСК (БУДЕТ ДОБАВЛЕНО ПОЗЖЕ) 
             <label for="searchtext" class="accesshide"><?php /*p($strsearch)*/ ?></label>
             <input type="text" name="searchtext" id="searchtext" size="30" value="<?php /*p($strsearch)*/ ?>"
                      onfocus ="getElementById('apitems_assign_form').add.disabled=true;
                                getElementById('apitems_assign_form').remove.disabled=true;
                                getElementById('apitems_assign_form').removeselect.selectedIndex=-1;
                                getElementById('apitems_assign_form').addselect.selectedIndex=-1;"
                      onkeydown = "var keyCode = event.which ? event.which : event.keyCode;
                                   if (keyCode == 13) {
                                        getElementById('assignform').previoussearch.value=1;
                                        getElementById('assignform').submit();
                                   } " />
             <input name="search" id="search" type="submit" value="<?php /*p($strsearch)*/ ?>" />
    		 -->
             <?php
                  /*if (!empty($searchtext))
    			  {// если есть кнопка "поиск" - то покажем "кнопку "показать все"
                      echo '<input name="showall" id="showall" type="submit" value="'.$strshowall.'" />'."\n";
                  }*/
             ?>
           </td>
        </tr>
      </table>
    </div>
    </form>
    <?php 
}
?>