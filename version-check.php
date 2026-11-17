<?php
// Temporary: shows only the PHP version and the extensions Laravel needs. Delete after Step 1 reads it.
header('Content-Type: text/plain');
echo 'PHP ' . PHP_VERSION . "\n";
foreach (['pdo_pgsql', 'pgsql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'curl', 'fileinfo', 'bcmath', 'zip'] as $ext) {
    echo $ext . ': ' . (extension_loaded($ext) ? 'yes' : 'NO') . "\n";
}
