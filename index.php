<?php
require_once __DIR__ . '/vendor/autoload.php';

// use League\CommonMark\CommonMarkConverter;

//$converter = new CommonMarkConverter();

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

// Define your configuration, if needed
$config = [
    'html_input' => 'escape', // How to handle raw HTML. 'strip' will ignore all html tags not allowed, while 'escape' will allow but escape them.
    'allow_unsafe_links' => false, // Whether unsafe links are permitted.

    'external_link' => [
        // 'internal_hosts' => 'localhost',
        'internal_hosts' => $_SERVER['SERVER_NAME'], // ($_SERVER['SERVER_NAME'] === 'localhost') ? 'localhost' : 'qbreis.com',
        'open_in_new_window' => true,
        'html_class' => 'external-link',
        'nofollow' => '',
        'noopener' => 'external',
        'noreferrer' => 'external',
    ],
];


echo '<pre>config: ';print_r($config);echo '</pre>';
echo '<pre>_SERVER: ';print_r($_SERVER);echo '</pre>';

// Configure the Environment with all the CommonMark parsers/renderers.
$environment = new Environment($config);
$environment->addExtension(new CommonMarkCoreExtension());

// Add the extension
$environment->addExtension(new FrontMatterExtension());

// Add External Links Extension
$environment->addExtension(new ExternalLinkExtension());

// Add Highlight Renderer
$environment->addRenderer(FencedCode::class, new FencedCodeRenderer(['html', 'php', 'js']));
$environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer(['html', 'php', 'js']));

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
?>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.9.0/build/styles/panda-syntax-dark.min.css">