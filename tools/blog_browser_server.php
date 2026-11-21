<?php

// Router for the isolated blog browser fixture; uploaded QA files never use the application's media directory.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
if (str_starts_with($path, '/storage/')) {
    $root = realpath(dirname(__DIR__).'/.audit/blog-qa/uploads');
    $file = $root ? realpath($root.'/'.substr($path, strlen('/storage/'))) : false;
    if ($root && $file && str_starts_with($file, $root.DIRECTORY_SEPARATOR) && is_file($file)) {
        header('Content-Type: '.mime_content_type($file));
        readfile($file);
        return;
    }
    http_response_code(404);
    return;
}
return require dirname(__DIR__).'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';
