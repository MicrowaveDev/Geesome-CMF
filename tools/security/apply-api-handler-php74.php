<?php
// Query Builder columns are null until an explicit SELECT is added.
// PHP 7.2+ warns on count(null), which Laravel converts to an exception.
function failApiHandlerCompatibilityPatch($message)
{
    fwrite(STDERR, 'API Handler PHP compatibility patch: '.$message.PHP_EOL);
    exit(1);
}

$root = dirname(dirname(__DIR__));
$packages = array('marcelgwerder/laravel-api-handler', 'jonybang/laravel-api-handler');
$changes = array(
    '$fields = $this->query->columns;' => '$fields = (array) $this->query->columns;',
    'count($this->query->columns) == 0' => 'count((array) $this->query->columns) == 0',
);
$found = false;
foreach ($packages as $package) {
    $path = $root.'/vendor/'.$package.'/src/Parser.php';
    if (!is_file($path)) { continue; }
    $found = true;
    $source = @file_get_contents($path);
    if ($source === false) { failApiHandlerCompatibilityPatch('cannot read '.$path); }
    $updated = $source;
    foreach ($changes as $old => $new) {
        $oldCount = substr_count($updated, $old);
        $newCount = substr_count($updated, $new);
        if ($oldCount === 0 && $newCount === 1) { continue; }
        if ($oldCount !== 1 || $newCount !== 0) {
            failApiHandlerCompatibilityPatch('unexpected Parser implementation in '.$package.'; file left unchanged.');
        }
        $updated = str_replace($old, $new, $updated);
    }
    if ($updated === $source) {
        echo 'API Handler PHP compatibility patch already applied: '.$package.PHP_EOL;
        continue;
    }
    $temporary = @tempnam(dirname($path), '.php74-api-');
    if ($temporary === false) { failApiHandlerCompatibilityPatch('cannot create a temporary Parser file.'); }
    if (file_put_contents($temporary, $updated) !== strlen($updated)
        || !chmod($temporary, fileperms($path) & 0777)
        || !rename($temporary, $path)) {
        @unlink($temporary);
        failApiHandlerCompatibilityPatch('cannot replace Parser atomically.');
    }
    echo 'Applied API Handler PHP compatibility patch: '.$package.PHP_EOL;
}
if (!$found) { failApiHandlerCompatibilityPatch('neither supported laravel-api-handler package is installed.'); }
