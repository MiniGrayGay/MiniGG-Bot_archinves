<?php

$reqRet = file_get_contents(APP_DIR_CONFIG . "app.config.json");
$resJson = json_decode($reqRet);

define('DEFAULT_UA', "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36");
define('CONFIG_VERSION', $resJson->version);
define('CONFIG_ADMIN', $resJson->admin);
define('CONFIG_ROBOT', $resJson->robot);

$event = $resJson->event;
define('CONFIG_EVENT_ROBOT', json_encode($event->robot));
define('CONFIG_EVENT_GROUP', json_encode($event->group));
