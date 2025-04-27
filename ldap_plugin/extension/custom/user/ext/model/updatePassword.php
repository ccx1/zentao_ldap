<?php
public function updatePassword($userID)
{
  if( $this->app->user->fromldap == true ){
    dao::$errors['originalPassword'][] = "LDAP/AD用户禁止修改密码";
    return false;
  }
  return parent::updatePassword($userID);
}
