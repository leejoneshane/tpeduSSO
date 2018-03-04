<?php

return [
    'host'	=> env('LDAP_HOST', '127.0.0.1'),
    'rdn'	=> 'dc=tp,dc=edu,dc=tw',
    'schattr'	=> 'dc',
    'version'	=> '3',
    'rootdn'	=> env('LDAP_ROOTDN'),
    'rootpwd'	=> env('LDAP_ROOTPWD'),
    'authdn'	=> 'ou=account,dc=tp,dc=edu,dc=tw',
    'authattr'	=> 'uid',
    'userdn'	=> 'ou=people,dc=tp,dc=edu,dc=tw',
    'userattr'	=> 'cn',
//    'groupdn'	=> 'ou=group,dc=tp,dc=edu,dc=tw',
];
