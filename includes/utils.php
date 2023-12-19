<?php
function checkFile($filePath) {
    // Check if the file path is provided
    if (empty($filePath)) {
        return ['error' => 'File path is empty'];
    }

    // Validate and sanitize the file path to prevent directory traversal attacks
    $filePath = realpath($filePath);
    if ($filePath === false || strpos($filePath, $_SERVER['DOCUMENT_ROOT']) !== 0) {
        return ['error' => 'Invalid file path'];
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

}