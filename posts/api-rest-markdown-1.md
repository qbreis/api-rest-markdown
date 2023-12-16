GitHub Branch: [Chapter #1 - Markdown to HTML](https://github.com/qbreis/api-rest-markdown/tree/dev-chapter-1-md-to-html)

## Api Rest Markdown

I want to have an Api Rest endpoint to get all posts data from one Github repository with all posts in Markdown format. This Github repo will handle Rest Api source code as well as all posts in Markdown files.

This way, my Rest Api won't have to connect to any database and will only have GET method, as I want to manage my posts via GitHub repo.

### Wish list

I want Rest Api to:

- Convert Markdown into Html, taking into account:
    - Parse [YAML](https://en.wikipedia.org/wiki/YAML) `front matter` to get metadata for each post, as title, excerpt, date, categories, tags, repository, state or draft, order, etc.
    - Detect links to external sites and optionally adjust the markup accordingly, to open in new tabs/windows, add a `rel` attribute as well as optional custom HTML classes.
    - Apply some HTML classes and other attributes optionally.
    - I also want to use [Highlight](https://highlightjs.org/) for pieces of code.
- Rest Api will need to read all Markdown files in a folder to retrieve response in `JSON` format.
- I will want to update my posts via GitHub, that is, with each push I will want to deploy changes online.

### Documentation

I start documenting the process through [Dillinger](https://dillinger.io), an online cloud-enabled, HTML5, buzzword-filled Markdown editor.

At the end of this same chapter I will start using this same project to keep documenting itself.

### 1.1 Md to Html

In this chapter I want to get my Markdown into Html with all I need.

I create new empty folder `api-rest-markdown`.

I did find this library to convert Markdown into Html: [CommonMark for PHP](https://commonmark.thephpleague.com/) - Markdown done right, which can be installed via Composer.

So once I create new empty folder `api-rest-markdown` I just run:

```bash
composer require league/commonmark
```

New `vendor` subfolder appears in my project folder, it includes the Composer dependencies in the file autoload.php`, inside `vendor` subfolder. [Composer](https://getcomposer.org/) is a PHP based tool for dependency management.

I can also see new `composer.json` file in the root folder of my PHP project. Its purpose is to specify a common project properties, meta data and dependencies.

Also new file `composer.lock` file in the root folder of my PHP project, prevents from automatically getting the latest versions of current dependencies.

Now I create new PHP file `index.php` in new project folder:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use League\CommonMark\CommonMarkConverter;

$converter = new CommonMarkConverter();
echo $converter->convert('# Hello, World!')->getContent();

// <h1>Hello, World!</h1>
```

### 1.2 Reading from MD file in posts/

I want to get my Markdown from a folder `posts/hola-world.md`:

```md
# Hola, World!
```

In same folder i will have one `posts/index.php`:

```php 
<?php 
/** 
 * Intentionally empty file.
 * 
 * It exists to stop directory listings on poorly configured servers.
 */
```

And my `index.php` becomes:

```php{8-12} Do not leave spaces after commas, hyphens or the language specification and the opening curly braces
<?php
require_once __DIR__ . '/vendor/autoload.php';

use League\CommonMark\CommonMarkConverter;

$converter = new CommonMarkConverter();

// echo $converter->convert('# Hello, World!')->getContent();

echo $converter->convert(
    file_get_contents('posts/hola-world.md')
)->getContent();

// <h1>Hello, World!</h1>
```

### 1.3 YAML

I want some metadata in my `posts/hola-world.md` and I add also some raw HTML:

```md{1-8,10}
---
layout: post
title: I Love Markdown
tags:
    - test
    - example
 
---
# Hola World!

<strong>*Hard coded* strong HTML element inside MD!</strong>
```

Same library comes with [extensions](https://commonmark.thephpleague.com/2.4/extensions/overview/) providing a simple way to add new syntax and features to the CommonMark parser.

The [FrontMatterExtension](https://commonmark.thephpleague.com/2.4/extensions/front-matter/) adds the ability to parse YAML front matter from the Markdown document and include that in the return result.

I want to install `symfony/yaml` to use this extension via Composer:

```bash
composer require symfony/yaml
```

Now I update my `index.php` as follows:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

// use League\CommonMark\CommonMarkConverter;

// $converter = new CommonMarkConverter();

// To use Environment with Extensions
use League\CommonMark\Environment\Environment;

// To use Extensions
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;

// Front Matter Extension
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;

use League\CommonMark\MarkdownConverter;

// Configure the Environment with all the CommonMark parsers/renderers.
$environment = new Environment();
$environment->addExtension(new CommonMarkCoreExtension());

// Add the extension
$environment->addExtension(new FrontMatterExtension());

// Instantiate the converter engine and start converting some Markdown!
$converter = new MarkdownConverter($environment);

$markdown = file_get_contents('posts/hola-world.md');

$markdownConverted = $converter->convert($markdown);

$frontMatter = $markdownConverted instanceof RenderedContentWithFrontMatter
    ?
    $markdownConverted->getFrontMatter()
    :
    '';

echo $markdownConverted;

echo '<pre>';
print_r($frontMatter);
echo '</pre>';
```

### 1.4 Raw HTML, unsafe links and CSS support for Markdown

All CommonMark features in this library are supported by default, including raw HTML and unsafe links, which I may want to disable using the `html_input` and `allow_unsafe_links` options in `index.php`:

```php
/* ... Same as original. Omitted for brevity. */

// Define your configuration, if needed
$config = [
    'html_input' => 'escape', // How to handle raw HTML. 'strip' will ignore all html tags not allowed, while 'escape' will allow but escape them.
    'allow_unsafe_links' => false, // Whether unsafe links are permitted.
];

// Configure the Environment with all the CommonMark parsers/renderers.
$environment = new Environment($config);

/* ... Same as original. Omitted for brevity. */
```

I also want some CSS support in my Markdown HTML rendered, so I create new file `css/style.css`:

```css
/*
CSS support for Markdown
*/

img {
    max-width: 200px;
    margin: 0 auto;
    display: block;
}

pre+p em,
    /* Caption for pieces of code into Markdown, here is how I can use:

    ```js
    Code goes here
    ```
    *Caption goes here*
    */
img+em {
    /* Caption for pictures as well into Markdown, here is how I can use:

    ![Alternative text for image](https://blog-qbreis.vercel.app/images/logo-github.svg)
    *Caption goes here*
    */
    display: block;
    text-align: center;
    color: rgba(76, 86, 106, .8);
    margin: 0.5em 0;
}

h1+p em {
    /* Lead format after h1 into Markdown, here is how I can use:

    # Hola World!
    *Lead content goes here*
    */
    font-style: normal;
    font-size: 1.5em;
}

blockquote {
    padding: .1em 1em;
    background-color: rgba(0, 0, 0, .05);
    border-left: 5px solid rgba(0, 0, 0, .1);
    font-size: .9em;
    color: rgba(76, 86, 106, .8);
}


/*
Normalize a bit
*/

body {
    font-size: 16px;
    line-height: 1.5em;
    margin: 1em;
}

code {
    background-color: #f1f1f1;
    color: #ff3860;
}
```

To see my CSS support styles in action I want to update `posts/hola-world.md`:

``````md{10,13-33}
---
layout: post
title: I Love Markdown
tags:
    - test
    - example

---
# Hola World!
*Lead content.*

<strong>*Hard coded* strong HTML element inside MD!</strong>

[local link](http://localhost/aaa/posts/)

[Dillinger Markdown Editor](https://dillinger.io)

![Alternative text for image](https://blog-qbreis.vercel.app/images/logo-github.svg)
*I want some optional caption for some of my pictures.*

```js
// Love at first sight
if (me.getDistanceTo(you.position) < 200) {
    me.setFeelings({
        inLove: true,
    });
}
```
*Source code by [Learning To Code By Writing Code Poems — Smashing Magazine](https://www.smashingmagazine.com/2018/07/writing-code-poems/)*

>   This is blockquote eleemnt

I successfully installed the <https://github.com/thephpleague/commonmark> project!
``````

And I will include my `css/style.css` in `index.php`:

```php{3-13,21-24}
/* ... Same as original. Omitted for brevity. */

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

echo $markdownConverted;

echo '<pre>';
print_r($frontMatter);
echo '</pre>';

echo '
    </body>
</html>
';
```

### 1.5 Links

I will also use the [External Links Extension](https://commonmark.thephpleague.com/2.4/extensions/external-links/) to detect links to external sites and adjust the markup accordingly.

In `index.php` I will update following code:

```php
/* ... Same as original. Omitted for brevity. */

// External Links Extension
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

/* ... Same as original. Omitted for brevity. */

// Define your configuration, if needed
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

/* ... Same as original. Omitted for brevity. */

// Add External Links Extension
$environment->addExtension(new ExternalLinkExtension());

/* ... Same as original. Omitted for brevity. */
```

### 1.6 Highlight

To highlight code blocks in my Markdown I will use this other block renderer [commonmark-highlighter](https://github.com/spatie/commonmark-highlighter), which I can as well install via composer:

```bash
composer require spatie/commonmark-highlighter
```

Finally I update once again my `index.php`:

```php
/* ... Same as original. Omitted for brevity. */

// Highlight Extension
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;

/* ... Same as original. Omitted for brevity. */

// Add Highlight Renderer
$environment->addRenderer(FencedCode::class, new FencedCodeRenderer(['html', 'php', 'js']));
$environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer(['html', 'php', 'js']));

/* ... Same as original. Omitted for brevity. */
```

To see highlight in action, first I update `index.php`:

```php{11}
/* ... Same as original. Omitted for brevity. */

echo '
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>title</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.9.0/build/styles/panda-syntax-dark.min.css">
    </head>
    <body>
';

/* ... Same as original. Omitted for brevity. */
```

I also want to update `css/style.css`:

```css
/* ... Same as original. Omitted for brevity. */

/*
highlightjs
*/

pre code.hljs {
    padding: 1em;
    display: inline-block;
    width: calc(100% - 2em);
    font-size: 1.2em;
}

pre code.hljs .loc {
    width: 100%;
    display: inline-block;
}

.highlighted {
    display: inline-block;
    width: 100%;
    background-color: rgba(255, 255, 255, .2);
    margin-left: -1em;
    width: calc(100% + 1em) !important;
    padding-left: 1em;
    /* So I can select with the mouse pointer comfortably over highlighted pieces of code */
    pointer-events: none;
}

/* Selects a div with class "loc" only if it is empty, yes, I can do that */
.loc:empty {
    /* My own styles for empty .loc divs inside .hljs blocks of code */
    display: none !important;
}
```

To highlight specific lines into my code blocks in `posts/hola-world.md`:

``````md

/* ... Same as original. Omitted for brevity. */

```js{1,4-5} Do not leave spaces after commas, hyphens or the language specification and the opening curly braces
// Love at first sight
if (me.getDistanceTo(you.position) < 200) {
    me.setFeelings({
        inLove: true,
    });
}
```

/* ... Same as original. Omitted for brevity. */
``````

## Reference links

- [League\CommonMark](https://commonmark.thephpleague.com) - Robust, highly-extensible Markdown parser for PHP.
- [Front Matter Extension](https://commonmark.thephpleague.com/2.4/extensions/front-matter/) - Parse YAML front matter.
- [External Links Extension](https://commonmark.thephpleague.com/2.4/extensions/external-links/) - Parser for links.
- [spatie/commonmark-highlighter](https://github.com/spatie/commonmark-highlighter) - Highlight code blocks with League\CommonMark.
- [Dillinger](https://dillinger.io/) - An online cloud-enabled, HTML5, buzzword-filled Markdown editor.
- [Highlight.js Examples](https://highlightjs.org/examples) 
- [Highlightjs/cdn-release CDN files](https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.9.0/build/styles/)

## External links

- [Composer on Wikipedia](https://es.wikipedia.org/wiki/Composer) - I think it is importnat to know a bit what Composer is.
- [Composer site](https://getcomposer.org/) - A Dependency Manager for PHP.
- [Basic usage - Composer](https://getcomposer.org/doc/01-basic-usage.md) - About vendor subfolder and composer.json, composer.lock files, among other things.
- [Markdown - Wikipedia](https://en.wikipedia.org/wiki/Markdown).
- [YAML](https://en.wikipedia.org/wiki/YAML) - Yet Another Markup Language, repurposed as YAML Ain't Markup Language, a [recursive acronym](https://en.wikipedia.org/wiki/Recursive_acronym).
- [DummyJSON](https://dummyjson.com/) - Get dummy/fake JSON data to use as placeholder in development or in prototype testing.
- [JSON](https://es.wikipedia.org/wiki/JSON) - To know a bit about JavaScript Object Notation.