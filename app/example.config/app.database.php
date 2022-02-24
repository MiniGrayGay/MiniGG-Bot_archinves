<?php

/**
 *
 * Redis - 如果Redis设置了访问密钥，填入下方数组的双引号内
 *
 */
$redisConfig = array(
    ""
);
define('APP_REDIS_CONFIG', $redisConfig);

/**
 *
 * MySQL/MariaDB数据库 - 无需配置
 *
 */

define('SQL_PREFIX', "minigg_"); //数据库前缀

$dbConfig = array(
    array("localhost", "dbusername", "dbpassword", "dbname", 3306)
);
define('APP_DB_CONFIG', $dbConfig);