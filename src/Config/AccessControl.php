<?php
	
	// Roles
	AccessControl::addRole('read only');
	AccessControl::addRole('restricted', ['read only']);
	AccessControl::addRole('user', ['restricted']);
	AccessControl::addRole('owner');
	
	AccessControl::addRole('restricted superuser', ['user']);
	AccessControl::addRole('superuser', ['restricted superuser']);
	AccessControl::addRole('admin', ['user']);
	AccessControl::addRole('engineer', ['user']);
	
	// Everybody
	AccessControl::allow(null, ['user'], ['login']);
	
	// Read only
	AccessControl::allow('read only', ['projects'], ['index']);
	AccessControl::allow('read only', ['user'], [
		'settings', 'logout', 'changeback', 'act_as_substitute', 'restrict'
	]);
	
	AccessControl::allow(
		'read only',
		['tickets'],
		['feed', 'index', 'view', 'log', 'search']
	);
	
	// Restricted
	AccessControl::allow(
		'restricted',
		['tickets'],
		['comment', 'cut', 'uncut', 'check', 'uncheck']
	);
	
	// TODO: owner currently doesn't work with controllers
	// AccessControl::allow('owner', ['tickets'], ['delete_comment']);
	
	// User
	AccessControl::allow('user', ['tickets'], ['jobfile', 'edit', 'edit_multiple']);
	
	AccessControl::allow('user', ['encodingprofiles'], ['index', 'view']);
	AccessControl::allow('user', ['workers'], ['index', 'queue']);
	AccessControl::allow('user', ['projects'], ['settings']);
	
	// Restricted Superuser
	AccessControl::allow('restricted superuser', ['projects'], [
		'edit', 'properties', 'profiles', 'states', 'worker', 'edit_filter'
	]);
	AccessControl::allow('restricted superuser', ['tickets'], ['create', 'duplicate']);
	AccessControl::allow('restricted superuser', ['import']);
	
	// Superuser
	AccessControl::allow('superuser', ['projects'], [
		'create'
	]);
	AccessControl::allow('superuser', ['encodingprofiles']);
	AccessControl::allow('superuser', ['workers'], ['pause', 'unpause', 'edit_group']);
	
	AccessControl::deny('superuser', ['user'], ['restrict']);
	
	// Admin
	AccessControl::allow('admin');
	
	AccessControl::deny('admin', ['user'], ['act_as_substitute', 'restrict']);
	
	AccessControl::deny('admin', ['project'], ['delete']);
	AccessControl::deny('admin', ['encodingprofiles'], ['delete']);
	AccessControl::deny('admin', ['workers'], ['delete_group']);
	
	// Engineer
	AccessControl::allow('engineer');
	
	AccessControl::deny('engineer', ['user'], ['act_as_substitute']);
	
?>
