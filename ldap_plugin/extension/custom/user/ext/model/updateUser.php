<?php
public function update($userID)
{
  if( !empty($_POST['password1']) or !empty($_POST['password2']) and $this->app->user->fromldap == true ){
    dao::$errors['originalPassword'][] = "LDAP/AD用户不能修改密码";
    return false;
  }
  return parent::update($userID);
}