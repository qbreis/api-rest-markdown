<?php

/** Server URL */
define(
    'SERVER_URL', 
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http'. // Get the protocol
    '://'. // scheme delimiter
    $_SERVER['SERVER_NAME']. // Get the server name
    dirname($_SERVER['SCRIPT_NAME']) // Get the script path (folder or path within the project)
);

/** Need to check Server type */
define(
    'INTERNAL_HOST', 
    $_SERVER['SERVER_NAME'] === 'localhost'
    ?
    'localhost'
    :
    'www.qbreis.com'
);

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