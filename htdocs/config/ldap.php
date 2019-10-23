<?php

return [
    'debug' => false,
    'host'	=> env('LDAP_HOST', '127.0.0.1'),
    'rhost'	=> env('LDAP_HOST_R', ''),
    'whost'	=> env('LDAP_HOST_W', ''),
    'rdn'	=> 'dc=tp,dc=edu,dc=tw',
    'version'	=> '3',
    'rootdn'	=> env('LDAP_ROOTDN', 'cn=admin,dc=tp,dc=edu,dc=tw'),
    'rootpwd'	=> env('LDAP_ROOTPWD', 'test'),
    'authdn'	=> 'ou=account,dc=tp,dc=edu,dc=tw',
    'userdn'	=> 'ou=people,dc=tp,dc=edu,dc=tw',
    'groupdn'	=> 'ou=group,dc=tp,dc=edu,dc=tw',
];