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