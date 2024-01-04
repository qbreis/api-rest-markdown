# 3 Refactoring

In this chapter I want to do some refactoring.

## 2.1 Config file

I create new `config.php` file:

```php
<?php
/** Posts folder name */
define('INTERNAL_HOST', 'localhost');

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

I still don't know from what server I will consume this REST API endpoint, when I will know I will change `INTERNAL_HOST` accordingly to make this work.

In my `index.php`:

```php{26}
<?php
require_once __DIR__ . '/config.php';

require_once __DIR__ . '/vendor/autoload.php';

/* ... Same as original. Omitted for brevity. */

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

/* ... Same as original. Omitted for brevity. */
```

## 2.2 Includes folder

I create new `includes/index.php`:

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

/* ... Same as original. Omitted for brevity. */
```

## 2.3 Get Markdown Function

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

/* Note #1
I choose using a constant MARKDOWN_OPTIONS inside the function as long as
environment configuration is unlikely to change across different calls,
otherwise I would pass this environment configuration as a parameter
in this same function.
*/

/* Note #2
When Markdown has no Front Matter I will return, for the moment, content in body as well with title: Untitled and the rest of meta data empty.
*/
function get_markdown($markdownFile) {

    // Configure the Environment with all the CommonMark parsers/renderers.
    $environment = new Environment(MARKDOWN_OPTIONS); /* Note #1 */
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
        /* Note #2 */
        // array()
        array(
            'title' => 'Untitled', 
            // 'date' => '0000-00-00', 
            // 'tags' => array(),
        )
        ;
    $frontMatter['body'] = $markdownConverted->getContent();
    return $frontMatter;
}
```

I update `includes/index.php`:

```php{4}
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/functions.php';
```

Finally in `index.php`:

```php{4}
<?php
require_once __DIR__ . '/includes/index.php';

$get_markdown = get_markdown('posts/api-rest-markdown-3.md');

echo '
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>'.$get_markdown['title'].'</title>
        <link rel="stylesheet" href="css/style.css">
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
```

### 2.3.1 Get Markdown Function - Add appropriate validation

I want to add appropriate validation, error handling and security measures to this function in `includes/functions.php` basically to ensure the file is safe and meets corresponding requirements:

```php
/* ... Same as original. Omitted for brevity. */
function get_markdown($markdownFile) {
    $checkFile = checkFile($markdownFile);
    if(isset($checkFile['error'])) {
        return $checkFile;
    } else {

        /* ... Same as original. Omitted for brevity. */

    }
}
```

And new file `includes\utils.php`:

```php
<?php
function checkFile($filePath) {
    // Check if the file path is provided
    if (empty($filePath)) {
        return ['error' => 'File path is empty'];
    }

    // Validate and sanitize the file path to prevent directory traversal attacks
    $filePath = normalizePath(
        // On windows realpath() will change unix style paths to windows style, that is why I want normalize path!
        realpath($filePath)
    );
    
    if ($filePath === false || strpos($filePath, $_SERVER['DOCUMENT_ROOT']) !== 0) {
        return ['error' => 'Invalid file path: '.$filePath.' ('.$_SERVER['DOCUMENT_ROOT'].').'];
    }

    // Check if the file exists
    if (!file_exists($filePath)) {
        return ['error' => 'File does not exist'];
    }

    // Check if the file is readable
    if (!is_readable($filePath)) {
        return ['Error: Unable to read the file'];
    }

    // Check file type (only allow .md files)
    $allowedFileTypes = ['md'];
    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
    if (!in_array(strtolower($fileExtension), $allowedFileTypes)) {
        return ['Error: Invalid file type. Only Markdown (.md) files are allowed'];
    }

    // Perform additional security checks, such as checking file size, MIME type, etc.

    // Check file size (in this example, limit to 10 MB)
    $maxFileSize = 10 * 1024 * 1024; // 10 MB in bytes
    if (filesize($filePath) > $maxFileSize) {
        return ['Error: File size exceeds the maximum allowed'];
    }

    // Verify MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $actualMimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    $allowedMimeTypes = ['text/markdown', 'text/plain']; // Adjust the allowed MIME types as needed
    if (!in_array($actualMimeType, $allowedMimeTypes)) {
        return ['Error: Invalid MIME type. Only Markdown files are allowed'];
    }

    // Return null or an empty array to indicate success
    return [];
}

/*
Converts all windows path to UNIX path using the following function.
It is based in a WordPress core function (wp_normalize_path) and well tested.
*/
function normalizePath($path) {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('|(?<=.)/+|', '/', $path);
    if ( ':' === substr($path, 1, 1)) {
        $path = ucfirst($path);
    }
    return $path;
}
```

I don't forget to update `includes\functions.php`:

```php
<?php
require_once __DIR__ . '/utils.php';

/* ... Same as original. Omitted for brevity. */
```


## 2.4 Need to check Server type

At this point I can see links in my posts are linked as external links when `internal_host` is set to `localhost` in `index.php` (see [Chapter #1 - Markdown to HTML - 1.5 Links](https://github.com/qbreis/api-rest-markdown/tree/dev-chapter-1-md-to-html)) but this will be wrong when project is in my online server [qbreis.com](http://www.qbreis.com).


To solve this I can use the server name (`$_SERVER['SERVER_NAME']`) or `.env` file for environment configuration in PHP.

Having into account that:

1. I don't want additional files or libraries (such as `dotenv`) to load variables from the `.env` file.
2. I choose simplicity and I don't want the application to have complex environment configurations.

I choose using server name so I update `config.php`:

```php{}
<?php

/** Need to check Server type */
define(
    'INTERNAL_HOST', 
    $_SERVER['SERVER_NAME'] === 'localhost'
    ?
    'localhost'
    :
    'www.qbreis.com'
);

/* ... Same as original. Omitted for brevity. */
```

 ## 2.5 Friendly URL - Htaccess

 I want to access any post in `posts` folder through friendly URLs like: 

- [http://localhost/api-rest-markdown/api-rest-markdown-1](http://localhost/api-rest-markdown/api-rest-markdown-1) - Single post - Returning data in JSON to be cosumed from whatever online server, I still don't know.
- [http://localhost/api-rest-markdown/preview/api-rest-markdown-1](http://localhost/api-rest-markdown/preview/api-rest-markdown-1) - Single post - Returning HTML preview content to be loaded from this same REST API.

> While it is generally not considered a good practice for a REST API to return both JSON and HTML responses I want to use this same project to get a simple fast preview in HTML for each Markdown post, so I will resolve this later with some kind of [Content Negotiation](https://restfulapi.net/content-negotiation/).

In my `.htaccess` file, I want to rewrite the URLs to pass the requested path as the "_REQUEST" parameter to index.php. So I create `.htaccess` file in root:

```bash
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?_REQUEST=$1 [L]
```

And then I update `index.php`:

```php{16-25}
/* ... Same as original. Omitted for brevity. */

echo '
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>'.$get_markdown['title'].'</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.9.0/build/styles/panda-syntax-dark.min.css">
    </head>
    <body>
';

// Get the _REQUEST parameter
$requestPath = isset($_GET['_REQUEST']) ? $_GET['_REQUEST'] : '';

echo '<pre>requestPath : ';print_r($requestPath);echo '</pre>';

$pathSegments = array_filter( // Remove empty elements
    explode('/', $requestPath) // Explode the path into an array
);

echo '<pre>pathSegments : ';print_r($pathSegments);echo '</pre>';

/* ... Same as original. Omitted for brevity. */
```

## 2.6 Getting the right server URL

Now when I try to reach [http://localhost/api-rest-markdown/preview/api-rest-markdown-3](http://localhost/api-rest-markdown/preview/api-rest-markdown-3) I still can see my post content but I realize I lost link to the external stylesheet `css/style.css` so, although I could use the absolute URL to [qbreis.com/api-rest-markdown/css/style.css](https://www.qbreis.com/api-rest-markdown/css/style.css),  I want to dynamically generate the correct path for stylesheet based on whether REST API is loaded from a local server or an online server.

So I update `config.php`:

```php{}
<?php

/** Server URL */
define(
    'SERVER_URL', 
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http'. // Get the protocol
    '://'. // scheme delimiter
    $_SERVER['SERVER_NAME']. // Get the server name
    dirname($_SERVER['SCRIPT_NAME']) // Get the script path (folder or path within the project)
);

/* ... Same as original. Omitted for brevity. */
```

And then I update `index.php`:

```php{10}
/* ... Same as original. Omitted for brevity. */

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

/* ... Same as original. Omitted for brevity. */
```




# ...

Following detailed resources I want to be available to access via this REST API:

- / - First 30 posts (JSON)
- /name-of-post - Single post (JSON)
- /2 - list of posts from 31 to 60, including (JSON)
- ?skip=20&limit=10 - list of posts from 21 to 30, including (JSON)
- /preview - First 30 posts (HTML)
- /preview/name-of-post - Single post (HTML)
- /preview/2 - list of posts from 31 to 60, including (HTML)
- /preview?skip=20&limit=10 - list of posts from 21 to 30, including (HTML)

Default value for skip will be i.e. 30 and for limit i.e. 30.

# add appropriate validation, error handling, and security measures

Check ChatGPT conversation on 17.12.2023 - Server URL Construction

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

 
 
## Reference links



## External links

- [Content Negotiation](https://restfulapi.net/content-negotiation/) - Content Negotiation in a REST API.