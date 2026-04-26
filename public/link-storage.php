<?php
/**
 * One-time storage symlink creator for shared hosting (Hostinger).
 * Upload to public/, visit once in browser, then DELETE immediately.
 */

$target = dirname(__DIR__) . '/storage/app/public';
$link   = __DIR__ . '/storage';

if (is_link($link)) {
    echo '✅ Storage link already exists. Nothing to do.<br>';
    echo 'Target: ' . readlink($link);
} elseif (file_exists($link) && !is_link($link)) {
    echo '⚠️ A folder named "storage" already exists in public/ — cannot create symlink over it.<br>';
    echo 'Delete the public/storage folder via File Manager, then visit this page again.';
} elseif (symlink($target, $link)) {
    echo '✅ Storage link created successfully!<br>';
    echo 'public/storage → ' . $target . '<br><br>';
    echo '<strong>⚠️ DELETE this file (public/link-storage.php) immediately from your server!</strong>';
} else {
    echo '❌ Failed to create symlink. Your host may not allow symlinks.<br>';
    echo 'Try running via SSH: <code>php artisan storage:link</code><br><br>';
    echo 'Alternative: See workaround below.';
}
