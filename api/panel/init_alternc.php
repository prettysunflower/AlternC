<?php

include_once "/usr/share/alternc/panel/class/local.php";
include_once "/usr/share/alternc/panel/class/db_mysql.php";

class DB_system extends DB_Sql {
    function __construct() {
        global $L_MYSQL_HOST,$L_MYSQL_DATABASE,$L_MYSQL_LOGIN,$L_MYSQL_PWD;
        parent::__construct($L_MYSQL_DATABASE, $L_MYSQL_HOST, $L_MYSQL_LOGIN, $L_MYSQL_PWD);
    }
}

// we do both:
$db= new DB_system();
$dbh = new PDO("mysql:host=".$L_MYSQL_HOST.";dbname=".$L_MYSQL_DATABASE, $L_MYSQL_LOGIN,$L_MYSQL_PWD,
    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8;"),
);

include_once "/usr/share/alternc/panel/class/variables.php";
include_once "/usr/share/alternc/panel/class/local.php";
include_once "/usr/share/alternc/panel/class/functions.php";

include_once "/usr/share/alternc/panel/class/m_messages.php";
$msg = new m_messages();

include_once "/usr/share/alternc/panel/class/m_dom.php";