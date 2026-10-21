<?php
// Laravel 5.3 passes an undefined variable to compact(); PHP 7.3+ reports it.
// Keep this standalone so Composer can run it before Laravel bootstraps.
function failLaravelCompatibilityPatch($message)
{
    fwrite(STDERR, 'Laravel PHP compatibility patch: '.$message.PHP_EOL);
    exit(1);
}

$root = dirname(dirname(__DIR__));
$path = $root.'/vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php';
$source = @file_get_contents($path);
if ($source === false) {
    failLaravelCompatibilityPatch('cannot read '.$path);
}

$pattern = '/public function addWhereExistsQuery\([^)]*\)\s*\{.*?\n    \}/s';
if (!preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
    failLaravelCompatibilityPatch('unknown Query Builder implementation; review the patch before proceeding.');
}

$method = $matches[0][0];
$offset = $matches[0][1];
$old = "compact('type', 'operator', 'query', 'boolean')";
$new = "compact('type', 'query', 'boolean')";
$oldCount = substr_count($method, $old);
$newCount = substr_count($method, $new);
if ($oldCount === 0 && $newCount === 1) {
    echo 'Laravel PHP compatibility patch already applied.'.PHP_EOL;
    exit(0);
}
if ($oldCount !== 1 || $newCount !== 0) {
    failLaravelCompatibilityPatch('unexpected compact() expression; vendor files were left unchanged.');
}

$updated = substr_replace($source, str_replace($old, $new, $method), $offset, strlen($method));
$temporary = @tempnam(dirname($path), '.php74-patch-');
if ($temporary === false) {
    failLaravelCompatibilityPatch('cannot create a temporary file beside Query Builder.');
}
$permissions = fileperms($path) & 0777;
if (file_put_contents($temporary, $updated) !== strlen($updated)
    || !chmod($temporary, $permissions)
    || !rename($temporary, $path)) {
    @unlink($temporary);
    failLaravelCompatibilityPatch('cannot replace Query Builder atomically.');
}
echo 'Applied Laravel PHP compatibility patch.'.PHP_EOL;
