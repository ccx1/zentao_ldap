<?php
public function identify($account, $password, $passwordStrength = 0)
{
    if (0 == strcmp('$', substr($account, 0, 1))) {
        return parent::identify(ltrim($account, '$'), $password, $passwordStrength);
    } else {
        $user = false;
        $record = $this->dao->select('*')->from(TABLE_USER)
            ->where('account')->eq($account)
            ->andWhere('deleted')->eq(0)
            ->fetch();
        if ($record) {
            $ldap = $this->loadModel('ldap');
            $ldap_account = $ldap->getUserDn($this->config->ldap, $account);
            $pass = $ldap->identify($this->config->ldap->host, $ldap_account, $password);
            if (0 == strcmp('Success', $pass)) {
                $user = $record;
                $ip   = $this->server->remote_addr;
                $last = $this->server->request_time;
                
                $user->password = md5($password);
                $user->fromldap = true;

                $this->dao->update(TABLE_USER)
                    ->set('visits = visits + 1')
                    ->set('ip')->eq($ip)
                    ->set('last')->eq($last)
                    ->where('account')->eq($account)
                    ->exec();

                $user->last = date(DT_DATETIME1, $user->last);

                $todoList = $this->dao->select('*')->from(TABLE_TODO)
                    ->where('cycle')->eq(1)
                    ->andWhere('account')->eq($user->account)
                    ->fetchAll('id');
                $this->loadModel('todo')->createByCycle($todoList);
            }
        }
        return $user;
    }
}
