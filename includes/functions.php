<?php
require_once __DIR__ . '/utils.php';

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

    $checkFile = checkFile($markdownFile);
    echo '<pre>checkFile('.$markdownFile.')';print_r(checkFile($markdownFile));echo '</pre>';

    if($checkFile['error']) {
        return $checkFile;

    } else {
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
}