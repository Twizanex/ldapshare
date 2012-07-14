<?php
define('DATABASE_TYPE', 'mysql');
define('DATABASE_NAME', 'ldapshare');
define('DATABASE_HOST', 'localhost');
define('DATABASE_PORT', 3306);
define('DATABASE_USER', 'ldapshare');
define('DATABASE_PASSWORD', 'ldapshare');
define('DEBUG', 0);//0, 1
define('DEMO', 0);//0, 1
define('GRAVATAR', 1);//0, 1
define('GRAVATAR_DEFAULT', 'identicon');//identicon, mm, monsterid, retro, wavatar
define('GRAVATAR_RATING', 'pg');//g, pg, r, x
define('GZHANDLER', 1);//0, 1
define('LDAP_SERVER', 'ldap://localhost');
define('LDAP_PORT', 389);
define('LDAP_PROTOCOL', 3);
define('LDAP_ROOTDN', 'cn=Manager,dc=my-domain,dc=com');
define('LDAP_ROOTPW', 'secret');
define('LDAP_BASEDN', 'dc=my-domain,dc=com');
define('LDAP_FILTER', 'mail=[email]');
define('LDAP_LASTNAME', 'sn');
define('LDAP_FIRSTNAME', 'givenname');
define('LIMIT_POSTS', 10);
define('LIMIT_COMMENTS', 5);
define('TABLE_COMMENT', 'ldapshare_comment');
define('TABLE_LIKE', 'ldapshare_like');
define('TABLE_LINK', 'ldapshare_link');
define('TABLE_POST', 'ldapshare_post');
define('TABLE_USER', 'ldapshare_user' );
