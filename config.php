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