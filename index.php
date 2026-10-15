<?php
/**
 * Root Entry Point Wrapper
 * Forwards requests to public/index.php for XAMPP / Shared Hosting compatibility.
 */

// Change current working directory to public/
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
