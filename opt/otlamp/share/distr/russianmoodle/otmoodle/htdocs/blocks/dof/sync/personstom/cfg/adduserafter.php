<?php
// Скрипт, который отрабатывается после добавления пользователя
// По умолчанию - отправка уведомления о смене пароля.
// Чтобы изменить - положите одноименный файл в moodledata/dof/cfg/sync/personstom/
if (!empty($mdluser))
{
    $amauser = $this->dof->modlib('ama')->user($mdluser);
    
    if (!empty($options['newpassword']))
    {// Пароль прилетел в открытом виде
        
        if ($setpasswordnotificate)
        {
            // сохранить и уведомить
            $amauser->send_setnew_notice($options['newpassword'], true, ['email']);
        } else
        {
            //только сменить, уведомлять не надо
            $amauser->set_new_password($options['newpassword']);
        }
        
    } elseif ($isnewuser || empty($user->password))
    {// Пароля нет (не прилетел ни в открытом виде, ни в md5), надо сгенерировать новый и сообщить пользователю
        // или создали нового пользователя
        $amauser->send_setnew_notice($user->password ?? null, true, ['email']);
    }
}
                
?>