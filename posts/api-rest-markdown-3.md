# 3 Refactoring

In this chapter I want to do some refactoring.

## 2.1 One detail

As I am deploying my project using my own server online `www.qbreis.com` I want to do small change in `posts/hola-world.md`:

```md
[...] Same as original. Omitted for brevity.
[local link](https://www.qbreis.com/api-rest-markdown/)
[...] Same as original. Omitted for brevity.
```

And also in `index.php`:

```php
[...] Same as original. Omitted for brevity.
// Define your configuration, if needed
$config = [
    'html_input' => 'escape', // How to handle raw HTML. 'strip' will ignore all html tags not allowed, while 'escape' will allow but escape them.
    'allow_unsafe_links' => false, // Whether unsafe links are permitted.

    'external_link' => [
        'internal_hosts' => 'www.qbreis.com',
        'open_in_new_window' => true,
        'html_class' => 'external-link',
        'nofollow' => '',
        'noopener' => 'external',
        'noreferrer' => 'external',
    ],
];
[...] Same as original. Omitted for brevity.
```

## 2.2 Config file

I create new `config.php`file:

```php
<?php
/** Posts folder name */
define('INTERNAL_HOST', 'www.qbreis.com');

/** Array of configuration options for the Environment or converter classes */
define('MARKDOWN_OPTIONS', [
    'html_input' => 'escape', // How to handle raw HTML. 'strip' will ignore all html tags not allowed, while 'escape' will allow but escape them.
    'allow_unsafe_links' => false, // Whether unsafe links are permitted.

    'external_link' => [
        'internal_hosts' => INTERNAL_HOST,
        'open_in_new_window' => true,
        'html_class' => 'external-link',
        'nofollow' => '',
        'noopener' => 'external',
        'noreferrer' => 'external',
    ],
]);
```

In my `index.php`:

```php
<?php
require_once __DIR__ . '/config.php';

require_once __DIR__ . '/vendor/autoload.php';
[...] Same as original. Omitted for brevity.
// Define your configuration, if needed
/*
$config = [
    'html_input' => 'escape', // How to handle raw HTML. 'strip' will ignore all html tags not allowed, while 'escape' will allow but escape them.
    'allow_unsafe_links' => false, // Whether unsafe links are permitted.

    'external_link' => [
        'internal_hosts' => 'localhost', // TODO: Don't forget to set this!
        'open_in_new_window' => true,
        'html_class' => 'external-link',
        'nofollow' => '',
        'noopener' => 'external',
        'noreferrer' => 'external',
    ],
];
*/

// Configure the Environment with all the CommonMark parsers/renderers.
$environment = new Environment(MARKDOWN_OPTIONS);
[...] Same as original. Omitted for brevity.
```

## 2.3 Includes folder

I create new `includes/index,php`:

```php
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';
```

And in `index.php`:

```php
<?php
require_once __DIR__ . '/includes/index.php';

// require_once __DIR__ . '/vendor/autoload.php';
[...] Same as original. Omitted for brevity.
```

# 2.4 Get Markdown Function

In `includes/functions.php`:

```php
<?php
// To use Environment with Extensions
use League\CommonMark\Environment\Environment;

// To use Extensions
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;

// Front Matter Extension
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;

// External Links Extension
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

// Highlight Extension
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;

use League\CommonMark\MarkdownConverter;

function get_markdown($markdownFile, $config) {

    // Configure the Environment with all the CommonMark parsers/renderers.
    $environment = new Environment($config);
    $environment->addExtension(new CommonMarkCoreExtension());
    
    // Add FrontMatterExtension
    $environment->addExtension(new FrontMatterExtension());
    
    // Add External Links Extension
    $environment->addExtension(new ExternalLinkExtension());
    
    // Add Highlight Renderer
    $environment->addRenderer(FencedCode::class, new FencedCodeRenderer(['html', 'php', 'js']));
    $environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer(['html', 'php', 'js']));
    
    // Instantiate the converter engine and start converting some Markdown!
    $converter = new MarkdownConverter($environment);
    
    $markdown = file_get_contents($markdownFile);
    
    $markdownConverted = $converter->convert($markdown);
 
    $frontMatter = $markdownConverted instanceof RenderedContentWithFrontMatter
        ?
        $markdownConverted->getFrontMatter()
        :
        '';

    if($frontMatter){
        $frontMatter['body'] = $markdownConverted->getContent();
    }

    return $frontMatter;
}
```

I update `includes/index.php`:

```php
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/functions.php';
```

Finally in `index.php`:

```php
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
 ```
 
 ### 2.4.1 Handeling possible errors
 
 In `includes\functions.php`:
 
 ```php
 // ... (code remains the same)
 function get_markdown($markdownFile, $config) {

    if(!file_exists($markdownFile)) {
        throw new Exception('The specified markdown file does not exist.');
    }
 // ... (code remains the same)
 ```
 
 In `index.php`:
 
 ```php
 <?php
require_once __DIR__ . '/includes/index.php';

try {
    $get_markdown = get_markdown('posts/hola-worldz.md', MARKDOWN_OPTIONS);

    echo isset($get_markdown['body']) 
        ? 
        $get_markdown['body'] 
        : 
        ''; // Undefined index: body in array

    // if Markdown content is not empty show it
    if(count($get_markdown)) {
        echo '<pre style="border: 2px #aaa solid;">';
        print_r($get_markdown);
        echo '</pre>';
    }

} catch (Exception $error) {
    echo '<p>Error: ' . $error->getMessage().'</p>';
}
?>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.9.0/build/styles/panda-syntax-dark.min.css">
```
 
 ```

 
 
