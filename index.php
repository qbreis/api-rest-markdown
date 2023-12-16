<?php
require_once __DIR__ . '/includes/index.php';

$get_markdown = get_markdown('posts/hola-world.md', MARKDOWN_OPTIONS);

echo '
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>title</title>
        <link rel="stylesheet" href="css/style.css">
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