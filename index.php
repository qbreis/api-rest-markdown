<?php
require_once __DIR__ . '/includes/index.php';

$get_markdown = get_markdown('posts/hola-world.md', MARKDOWN_OPTIONS);

echo $get_markdown['body'];

echo '<pre style="border: 2px #aaa solid;">';
print_r($get_markdown);
echo '</pre>';
?>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.9.0/build/styles/panda-syntax-dark.min.css">