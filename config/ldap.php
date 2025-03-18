<?php
return [
    'server' => 'ldap://192.168.71.9:389',
    'bind_dn' => 'CN=Administrador,CN=Users,DC=Calidad,DC=Local',
    'bind_password' => 'Ingenes@24',
    'base_dn' => 'DC=Calidad,DC=Local',
    'user_id_key' => 'sAMAccountName',
    'attributes' => ['cn', 'mail', 'memberof', 'givenname', 'sn']
];
