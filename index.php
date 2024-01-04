<?php
require_once __DIR__ . '/includes/index.php';

// Get the _REQUEST parameter
$requestPath = isset($_GET['_REQUEST']) ? $_GET['_REQUEST'] : '';

echo '<pre>requestPath : ';print_r($requestPath);echo '</pre>';

$pathSegments = array_filter( // Remove empty elements
    explode('/', $requestPath) // Explode the path into an array
);

echo '<pre>pathSegments : ';print_r($pathSegments);echo '</pre>';

$get_markdown = get_markdown('posts/api-rest-markdown-3.md');

echo '
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>'.$get_markdown['title'].'</title>
        <link rel="stylesheet" href="'.SERVER_URL.'/css/style.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.9.0/build/styles/panda-syntax-dark.min.css">
    </head>
    <body>
';










echo $get_markdown['body'];

echo '<pre style="border: 2px #aaa solid;">';
print_r($get_markdown);
echo '</pre>';

echo '
    </body>
</html>
';