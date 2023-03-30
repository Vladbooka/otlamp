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

global $CFG;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/form/submit.php');

/** Класс элемента, отображающий submit-кнопку одноразового нажатия.
 *
 * @package formslib
 */
class MoodleQuickForm_dof_single_use_submit extends MoodleQuickForm_submit
{
    /**
     * "One-time" javascript (containing functions), see bug #4611
     *
     * @var     string
     * @access  private
     */
    var $_js = '';
    /** Текст, который показывается во время обработки запроса
     * 
     */
    var $_pleaseWaitText = '';
    
    /** Конструктор класса
     * 
     * 
     * @param     string    Input field name attribute
     * @param     string    Input field value
     * @param     mixed     Either a typical HTML attribute string or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     */
    public function __construct($elementName=null, $value=null, $attributes=null)
    {
        global $DOF;
        parent::__construct($elementName, $value, $attributes);
        // Устанавливаем по умолчанию на кунопе надпись "пожалуйста подождите..."
        $this->_pleaseWaitText =  $DOF->modlib('ig')->igs('please_wait');
    }
    
    /** Установить 
     * 
     */
    public function setPleaseWaitText($text)
    {
        if ( $text )
        {
            $this->_pleaseWaitText = $text;
        }
    }
    
    /**
     * Returns Html and JS for the submit element
     *
     * @access      public
     * @return      string
     */
    public function toHtml()
    {
        // добавляем JS к нажатию кнопки
        $this->updateAttributes(array(
            'onclick' => 'this.disabled=true;
                          this.value="'.$this->_pleaseWaitText.'";
                          this.form.submit();
                          return true;'
        ));
        
        return parent::toHtml();
    }
    
} 
?>