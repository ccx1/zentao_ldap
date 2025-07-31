<?php
public function resetPassword($user)
{
    if(isset($this->app->user->fromldap) && $this->app->user->fromldap === true)
    {
        dao::$errors['originalPassword'][] = "LDAP/AD 用户禁止修改密码";
        return false;
    }
    return parent::resetPassword($user);
}
