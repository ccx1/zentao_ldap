<?php
/**
 * 设置顶级导航条
 */
if(!isset($lang->ldap))     $lang->ldap     = new stdclass();
if(!isset($lang->mainNav))  $lang->mainNav  = new stdclass();
if(!isset($lang->mainNav->menuOrder)) $lang->mainNav->menuOrder = array();

$lang->ldap->common = 'LDAP';

$lang->mainNav->ldap = "<i class='icon icon-cog-outline'></i>{$lang->ldap->common}|ldap|setting|";
$lang->mainNav->menuOrder[90] = 'ldap';
