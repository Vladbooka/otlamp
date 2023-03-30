<?php
// Класс для работы с формой

class sitecall_form_enrol extends sitecall_form
{
    /**
     * Код формы (задается при наследовании)
     */
    protected $code='enrol';
    /**
   * Преобразовать данные для отправки
   * @return {Array} результат проверки
   */
    public function msgData($form)
    {
        $msg = '';
        $msg .= '<br />' . get_string('msg_sender_name','enrol_sitecall') . ' ' . $form['name'];
        $msg .= '<br />' . get_string('msg_phone','enrol_sitecall') . ' ' . $form['phone'];
        $msg .= '<br />' . get_string('msg_message','enrol_sitecall') . '<br />' . $form['comment'];
        
        return $msg;
    }
    /**
   * Проверить данные формы
   * @return {Array} результат проверки
   */
    public function checkData($form)
    {
        // return array('status'=>'ok','text' => 'Ура');
        
        $response = array();
        // Накопитель статуса
        $status = 'ok';
        // Проверяем телефон (мягкая проверка - можно почти всё)
        $pattern = "/^.[a-zа-я0-9_\(\)\*\#\-\s\.]{6,30}$/";
        if (!preg_match($pattern,$form['phone']))
        {
            $status = 'error';
            $response['phone']=array
                                (
                                    'status' => 'error',
                                    'text' => get_string('phone_error_text','enrol_sitecall')
                                );
        }else
        {
            $response['phone']=array
                                (
                                    'status' => 'ok',
                                    'text' => get_string('phone_ok_text','enrol_sitecall')
                                );   
        }
        // Проверяем имя
        if (mb_strlen($form['firstname'], 'utf-8' ) < 2)
        {
            $status = 'error';
            $response['firstname']=array
                                (
                                    'status' => 'error',
                                    'text' => get_string('firstname_error_text','enrol_sitecall')
                                );
        }else
        {
            $response['firstname']=array
                                (
                                    'status' => 'ok',
                                    'text' => get_string('firstname_ok_text','enrol_sitecall')
                                );   
        }
        // Проверяем фамилию
        if (mb_strlen($form['lastname'], 'utf-8' ) < 2)
        {
            $status = 'error';
            $response['lastname']=array
            (
                    'status' => 'error',
                'text' => get_string('lastname_error_text','enrol_sitecall')
            );
        }else
        {
            $response['lastname']=array
            (
                    'status' => 'ok',
                'text' => get_string('lastname_ok_text','enrol_sitecall')
            );
        }
        if ( ! isset($response['form']['ip']) )
        {
            $ip = $_SERVER["REMOTE_ADDR"];
            $response['form']['ip'] = $ip;
        }
        $response['form']['status']=$status;
        $response['form']['text']=$status;
        $response['status']=$status;
        $response['text']=$status;
        
        // До переопределения нам нравятся все формы
        return $response;
    }
}
