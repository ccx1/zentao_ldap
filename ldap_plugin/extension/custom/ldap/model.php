<?php
class ldapModel extends model
{
    /**
     * 获取用户 DN（绑定后通过 account 查找 DN）
     */
    public function getUserDn($config, $account)
    {
        $ret = null;
        $ds  = ldap_connect($config->host);

        if (!$ds) return null;

        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bind = @ldap_bind($ds, $config->bindDN, $config->bindPWD);
        if (!$bind) return null;

        $filter = "($config->uid=$account)";
        $result = ldap_search($ds, $config->baseDN, $filter);
        if (!$result) {
            ldap_unbind($ds);
            return null;
        }

        $count = ldap_count_entries($ds, $result);
        if ($count > 0) {
            $entries = ldap_get_entries($ds, $result);
            $ret     = $entries[0]['dn'];
        }

        ldap_unbind($ds);
        return $ret;
    }

    /**
     * 身份认证：根据 DN 和密码尝试绑定
     */
    public function identify($host, $dn, $pwd)
    {
        $ds = ldap_connect($host);
        if (!$ds) return '无法连接 LDAP 服务器';

        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bind = @ldap_bind($ds, $dn, $pwd);

        $error = ldap_error($ds);
        ldap_unbind($ds);
        return $error === 'Success' ? true : $error;
    }

    /**
     * 获取 LDAP 用户数据
     */
    public function getUsers($config)
    {
        $ds = ldap_connect($config->host);
        if (!$ds) return [];

        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bind = @ldap_bind($ds, $config->bindDN, $config->bindPWD);
        if (!$bind) return [];

        $attrs = [$config->uid, $config->mail, $config->name];
        $result = ldap_search($ds, $config->baseDN, $config->searchFilter, $attrs);
        if (!$result) return [];

        $entries = ldap_get_entries($ds, $result);
        ldap_unbind($ds);
        return $entries;
    }

    /**
     * 同步 LDAP 用户数据到禅道数据库
     */
    public function sync2db($config)
    {
        $ldapUsers = $this->getUsers($config);
        if (empty($ldapUsers) || $ldapUsers['count'] == 0) return 0;

        $insertCount = 0;

        for ($i = 0; $i < $ldapUsers['count']; $i++) {
            $user = new stdclass();
            $group = new stdclass();

            $user->account  = $ldapUsers[$i][$config->uid][0] ?? '';
            $user->email    = $ldapUsers[$i][$config->mail][0] ?? ($user->account . '@test.local');
            $user->realname = $ldapUsers[$i][$config->name][0] ?? $user->account;
            $user->gender   = 'm';
            $user->role     = 'others';
            $user->deleted  = 0;

            if (empty($user->account)) continue;

            $exist = $this->dao->select('account')->from(TABLE_USER)->where('account')->eq($user->account)->fetch();

            if ($exist) {
                $this->dao->update(TABLE_USER)->data($user)
                    ->where('account')->eq($user->account)
                    ->autoCheck()
                    ->exec();
            } else {
                $this->dao->insert(TABLE_USER)->data($user)->autoCheck()->exec();
            }

            if (dao::isError()) {
                error_log("LDAP同步用户 {$user->account} 出错：" . print_r(dao::getError(), true), 3, __DIR__ . '/ldapSync.log');
                continue;
            }

            $insertCount++;
        }

        return $insertCount;
    }
}
