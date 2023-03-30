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

define('DOF_MESSAGE_SUCCESS', 1);
define('DOF_MESSAGE_ERROR', 2);
define('DOF_MESSAGE_WARNING', 3);
define('DOF_MESSAGE_INFO', 4);

/**
 * Экземпляр одного потока сообщения
 *
 * @package    dof
 * @subpackage messages
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_message_controller
{
    /**
     * Контейнер объектов сообщений
     *
     * @var dof_message_stack[]
     */
    protected $stacks = [];
    
    /**
     * Объект деканата
     *
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Отображать очередь
     *
     * @var bool
     */
    protected $display = true;
    
    /**
     * Конструктор класса
     *
     * @param dof_control $dof - объект деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
        $this->stacks['default'] = new dof_message_stack($dof);
        
        // Обработка уведомлений из сессии
        $this->process_session_messages_stacks();
    }
    
    /**
     * Отобразить сообщения
     *
     * @param string $stackcode
     *
     * @return string | void
     */
    public function display($stackcode = null)
    {
        if ( ! is_null($stackcode) )
        {
            if ( ! array_key_exists($stackcode, $this->stacks) )
            {
                throw new dof_exception_coding('invalid_stackcode');
            }
            
            $this->stacks[$stackcode]->display();
        } else
        {
            foreach ( $this->stacks as $stack )
            {
                $stack->display();
            }
        }
    }
    
    /**
     * Добавить сообщение в очередь
     *
     * @param string $text - Текст сообщения
     * @param string $type - Тип сообщения (message, notice, error, info)
     *
     * @throws dof_exception_coding - при неизвестном типе сообщения
     */
    public function add($text, $type = DOF_MESSAGE_WARNING, $stackcode = 'default')
    {
        if ( ! array_key_exists($stackcode, $this->stacks) )
        {
            $this->stacks[$stackcode] = new dof_message_stack($this->dof);
        }
        
        $this->stacks[$stackcode]->add($text, $type, $stackcode);
    }
    
    /**
     * Добавить сообщение в очередь
     *
     * @param string $text - Текст сообщения
     * @param string $type - Тип сообщения (message, notice, error, info)
     *
     * @return dof_message[]
     */
    public function get_stack_messages($stackcode = null, $type = null)
    {
        $processedmessages = [];
        if ( ! is_null($stackcode) )
        {
            if ( ! array_key_exists($stackcode, $this->stacks) )
            {
                return $processedmessages;
            } else
            {
                $processedmessages = $this->stacks[$stackcode]->get_messages($type);
            }
        } else
        {
            foreach ( $this->stacks as $stack )
            {
                $processedmessages = array_merge($processedmessages, $stack->get_messages($type));
            }
        }
        
        return $processedmessages;
    }
    
    /**
     * Обработка стеков сообщений из сессии при наличии
     *
     * @return void
     */
    public function process_session_messages_stacks()
    {
        if ( array_key_exists('dof_message_controller', $_SESSION) )
        {
            $stacks = $_SESSION['dof_message_controller'];
            unset($_SESSION['dof_message_controller']);
            foreach ( $stacks as $stackcode => $messages )
            {
                if ( ! empty($messages) )
                {
                    foreach ( $messages as $message )
                    {
                        $this->add($message['text'], $message['type'], $stackcode);
                    }
                }
            }
        }
    }
    
    /**
     * @deprecated используем get_stack_messages() и проверяем на пустоту
     *
     * Наличие ошибок в очереди сообщений
     *
     * @return bool
     */
    public function errors_exists()
    {
        return (bool)$this->get_stack_messages('default', DOF_MESSAGE_ERROR);
    }
    
    /**
     * @deprecated используем get_stack_messages() и проверяем на пустоту
     *
     * Наличие уведомлений в очереди сообщений
     *
     * @return bool - Наличие уведомлений
     */
    public function notices_exists()
    {
        return (bool)$this->get_stack_messages('default', DOF_MESSAGE_WARNING);
    }
    
    /**
     * @deprecated используем get_stack_messages() и проверяем на пустоту
     *
     * Наличие сообщений в очереди
     *
     * @return bool - Наличие сообщений
     */
    public function messages_exists()
    {
        return (bool)$this->get_stack_messages('default', DOF_MESSAGE_SUCCESS);
    }
}

/**
 * Экземпляр одного потока сообщения
 *
 * @package    dof
 * @subpackage messages
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_message_stack
{
    /**
     * Объект деканата
     *
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Массив уведомлений
     *
     * @var dof_message[]
     */
    protected $messages = [];
    
    /**
     * Конструктор класса
     *
     * @param dof_control $dof - объект деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    /**
     * Добавить сообщение в очередь
     *
     * @param string $text - Текст сообщения
     * @param string $type - Тип сообщения (message, notice, error, info)
     *
     * @throws dof_exception_coding - при неизвестном типе сообщения
     */
    public function add($text, $type, $stackcode)
    {
        $this->messages[] = new dof_message($text, $type, $stackcode);
    }
    
    /**
     * Отобразить сообщения
     *
     * @return void
     */
    public function display()
    {
        foreach ( $this->messages as $message )
        {
            $message->display();
        }
    }
    
    /**
     * Добавить сообщение в очередь
     *
     * @param string $text - Текст сообщения
     * @param string $type - Тип сообщения (message, notice, error, info)
     *
     * @throws dof_message[]
     */
    public function get_messages($type = null)
    {
        $processedmessages = [];
        foreach ( $this->messages as $message )
        {
            if ( is_null($type) || ($message->get_type() == $message->validate_type($type)) )
            {
                $processedmessages[] = $message;
            }
        }
        
        return $processedmessages;
    }
}

class dof_message
{
    /**
     * Уникальный идентификатор сообщения
     *
     * @param int
     */
    protected $id = 0;
    
    /**
     * Код стека уведомлений
     *
     * @param string
     */
    protected $stackcode = 'default';
    
    /**
     * Тип сообщения
     *
     * @var string
     */
    protected $type;
    
    /**
     * Флаг о том, что сообщение было отображено
     *
     * @var string
     */
    protected $displayed = false;
    
    /**
     * Текст сообщения
     *
     * @var string
     */
    protected $text = '';
    
    /**
     * CSS классы уведомлений
     *
     * @var array
     */
    protected $classcodes = [
        DOF_MESSAGE_SUCCESS => 'success',
        DOF_MESSAGE_ERROR => 'error',
        DOF_MESSAGE_WARNING => 'warning',
        DOF_MESSAGE_INFO => 'info'
    ];
    
    /**
     * Валидация типа сообщения
     *
     * @param string $type
     *
     * @throws dof_exception_coding
     *
     * @return string
     */
    public function validate_type($type)
    {
        switch ( $type )
        {
            case 'notice':
            case 'warning':
            case DOF_MESSAGE_WARNING:
                return DOF_MESSAGE_WARNING;
                break;
                
            case 'message':
            case 'success':
            case DOF_MESSAGE_SUCCESS:
                return DOF_MESSAGE_SUCCESS;
                break;
                
            case 'error':
            case DOF_MESSAGE_ERROR:
                return DOF_MESSAGE_ERROR;
                break;
                
            case 'info':
            case DOF_MESSAGE_INFO:
                return DOF_MESSAGE_INFO;
                break;
                
            default:
                throw new dof_exception_coding('invalid_message_type');
        }
    }
    
    /**
     * Конструктор сообщения
     *
     * @param string $text
     * @param string $type
     *
     * @throws dof_exception_coding
     */
    public function __construct($text, $type, $stackcode)
    {
        $this->type = $this->validate_type($type);
        if ( empty($text) )
        {
            throw new dof_exception_coding('invalid_message_text');
        }
        
        $this->text = $text;
        $this->id = uniqid();
        $this->stackcode = $stackcode;
        
        $this->set_message_in_session();
    }
    
    /**
     * Получение HTML кода сообщения
     * @return string
     */
    public function render()
    {
        if ( ! $this->displayed  )
        {
            return dof_html_writer::div(dof_html_writer::div($this->text, "dof_message block_dof_{$this->classcodes[$this->type]}_message"), 'dof_noticemessages');
        } else
        {
            return '';
        }
    }
    
    /**
     * Отображение сообщения
     *
     * @return void
     */
    public function display()
    {
        echo $this->render();
        $this->set_displayed();
    }
    
    /**
     * Установка флага о том, что сообщение было отображено
     *
     * @return void
     */
    public function set_displayed()
    {
        global $_SESSION;
        
        if ( ! empty($_SESSION['dof_message_controller'][$this->stackcode][$this->id]) )
        {
            unset($_SESSION['dof_message_controller'][$this->stackcode][$this->id]);
        }
        
        $this->displayed = true;
    }
    
    /**
     * Получение типа сообщения
     *
     * @return string
     */
    public function get_type()
    {
        return $this->type;
    }
    
    /**
     * Добавляем текущее сообщение в сессию
     *
     * @return void
     */
    public function set_message_in_session()
    {
        global $_SESSION;
        
        if ( ! array_key_exists('dof_message_controller', $_SESSION) )
        {
            $_SESSION['dof_message_controller'] = [];
        }
        if ( ! array_key_exists($this->stackcode, $_SESSION['dof_message_controller']) )
        {
            $_SESSION['dof_message_controller'][$this->stackcode] = [];
        }
        
        $_SESSION['dof_message_controller'][$this->stackcode][$this->id] = ['type' => $this->type, 'text' => $this->text];
    }
}
