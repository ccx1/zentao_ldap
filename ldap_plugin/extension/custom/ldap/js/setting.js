function onClickTest() {
	$.post(createLink('ldap', 'test'),{
		host: $('#ldapHost').val(),
		dn: $('#ldapBindDN').val(),
		pwd: $('#ldapPassword').val(),
	}, function(data) {
		$('#testRlt').html(data);
	});
}

function sync() {
	$.get(createLink('ldap', 'sync'), function(ret){
		alert("成功同步"+ret+"位用户信息");
	});
}

function save() {
	$.get(createLink('ldap', 'save'), function(ret){
		alert("保存配置成功！");

	});
}