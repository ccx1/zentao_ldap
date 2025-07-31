<?php
public function update($userID)
{
    $password1 = isset($_POST['password1']) ? trim($_POST['password1']) : '';
    $password2 = isset($_POST['password2']) ? trim($_POST['password2']) : '';
    
    if(($password1 !== '' || $password2 !== '') && isset($this->app->user->fromldap) && $this->app->user->fromldap === true)
    {
        dao::$errors['originalPassword'][] = "LDAP/AD 用户不能修改密码";
        return false;
    }

    return parent::update($userID);
}
