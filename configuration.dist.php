<?php
define('DATABASE_HOST', 'localhost');
define('DATABASE_NAME', 'wall369');
define('DATABASE_PASSWORD', 'wall369');
define('DATABASE_PORT', 3306);
define('DATABASE_TYPE', 'mysql');
define('DATABASE_USER', 'wall369');
define('DEBUG', 0);//0, 1
define('DEMO', 1);//0, 1
define('GRAVATAR', 1);//0, 1
define('GRAVATAR_DEFAULT', 'identicon');//identicon, mm, monsterid, retro, wavatar
define('GRAVATAR_RATING', 'pg');//g, pg, r, x
define('GZHANDLER', 1);//0, 1

define('LDAP', 0);//0, 1
define('LDAP_SERVER', 'ldap://hostname');
define('LDAP_ADMIN_DN', 'YOURDOMAIN\\user');
define('LDAP_ADMINPW', 'password');
define('LDAP_DOMAINS', array('yourdomain.com'));
define('LDAP_DN', 'DC=yourdomain,DC=com');
define('LDAP_FILTER', 'userprincipalname=[email]');
define('LDAP_LASTNAME', 'sn');
define('LDAP_FIRSTNAME', 'givenname');

define('LIMIT_COMMENTS', 5);
define('LIMIT_POSTS', 10);
define('TABLE_ADDRESS', 'wall369_address');
define('TABLE_COMMENT', 'wall369_comment');
define('TABLE_LIKE', 'wall369_like');
define('TABLE_LINK', 'wall369_link');
define('TABLE_PHOTO', 'wall369_photo');
define('TABLE_POST', 'wall369_post');
define('TABLE_USER', 'wall369_user' );
define('UPLOAD_SIZE', 4);//mega
?>
