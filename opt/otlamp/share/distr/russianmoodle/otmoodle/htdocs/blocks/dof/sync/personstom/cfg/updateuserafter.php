<?php
// Меняем и высылаем пароль только когда пользователь найден и выставлена опция на смену пароля
if (!empty($mdluser))
{
    $amauser = $this->dof->modlib('ama')->user($mdluser);
    
    if (!empty($options['reset_password']))
    {// требуется сбросить пароль (сгенерировать новый) и отправить его пользователю
        
        $amauser->send_setnew_notice(null, true, ['email']);
        
    } elseif (!empty($options['newpassword']) && !empty($changepasswordallowed)
        && !$amauser->is_current_password($options['newpassword']))
    {// при обновлении прилетел пароль в открытом виде, надо сохранить его
        if ($changepasswordnotificate)
        {
            // сменить и уведомить
            $amauser->send_setnew_notice($options['newpassword'], true, ['email']);
        } else
        {
            //только сменить, уведомлять не надо
            $amauser->set_new_password($options['newpassword']);
        }
    }
    
}