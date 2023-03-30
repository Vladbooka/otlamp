<?php
class sitecall_form_enrollogin extends sitecall_form
{
    /**
     * Код формы (задается при наследовании)
     */
    protected $code='enrollogin';
    /**
   * Преобразовать данные для отправки
   * @return {Array} результат проверки
   */
    public function msgData($form)
    {
        $msg = '';
        $msg .= '<br />' . get_string('msg_message','enrol_sitecall') . '<br />' . $form['comment'];
        
        return $msg;
    }
    
    /**
   * Проверить данные формы
   * @return {Array} результат проверки
   */
    public function checkData($form)
    {
        $response = array();
        // Накопитель статуса
        $status = 'ok';
        
        $response['form']['status']=$status;
        $response['form']['text']=$status;
        $response['status']=$status;
        $response['text']=$status;
        
        // До переопределения нам нравятся все формы
        return $response;
    }
}
