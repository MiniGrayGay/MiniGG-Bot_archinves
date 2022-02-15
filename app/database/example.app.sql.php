<?php

define('SQL_PREFIX', "minigg_");
//数据库前缀

/**
 * 
 * 数据库配置
 * 
 */
$dbConfig = array(
  array("localhost", "minigg", "password", "minigg", 3306)
);
define('APP_DB_CONFIG', $dbConfig);

/**
 * 
 * Redis 配置
 * 
 */
$redisConfig = array(
  ""
);
define('APP_REDIS_CONFIG', $redisConfig);
