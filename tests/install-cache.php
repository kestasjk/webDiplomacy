<?php
// Run with: php tests/install-cache.php
define('IN_CODE', true);
require dirname(__DIR__).'/variants/install.php';

function l_t($message, ...$args)
{
    return sprintf($message, ...$args);
}

// Only the database reads used by terrJSONData are needed for this regression.
class CacheTestDatabase
{
    public function sql_tabl($sql)
    {
        return new ArrayIterator(strpos($sql, 'FROM wD_Territories') !== false
            ? array(array('id' => 1, 'name' => 'Test territory')) : array());
    }

    public function tabl_hash($table)
    {
        if (!$table->valid()) return false;
        $row = $table->current();
        $table->next();
        return $row;
    }
}

function check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

function removeTestDirectory($directory)
{
    foreach (new DirectoryIterator($directory) as $entry) {
        if ($entry->isDot()) continue;
        if ($entry->isDir()) removeTestDirectory($entry->getPathname());
        else unlink($entry->getPathname());
    }
    rmdir($directory);
}

$DB = new CacheTestDatabase();
$root = sys_get_temp_dir().'/webdip-cache-test-'.bin2hex(random_bytes(8));
mkdir($root);
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity)
        throw new ErrorException($message, 0, $severity, $file, $line);
    return false;
});

try {
    $file = $root.'/variant/cache/territories.js';
    InstallCache::terrJSON($file, 1);
    check(is_file($file), 'Missing nested cache directories must be created.');
    $expected = "function loadTerritories() {\n".
        'Territories = $H({"1":{"id":1,"name":"Test territory","Borders":[],"CoastalBorders":[]}});'."\n}\n";
    check(file_get_contents($file) === $expected, 'Generated territory data must be preserved.');
    file_put_contents($file, 'stale cache');
    InstallCache::terrJSON($file, 1);
    check(file_get_contents($file) === $expected, 'Existing cache files must be refreshed.');

    file_put_contents($root.'/blocked', 'keep this file');
    $failure = null;
    try {
        InstallCache::terrJSON($root.'/blocked/cache/territories.js', 1);
    } catch (Exception $exception) {
        $failure = $exception;
    }
    check($failure !== null && strpos($failure->getMessage(), "Couldn't make cache directory") !== false,
        'Directory creation failure must report a clear error.');
    check(file_get_contents($root.'/blocked') === 'keep this file', 'Failure must preserve existing files.');
    echo "PASS: missing directories, cache contents, cache refresh, and creation failure\n";
} finally {
    restore_error_handler();
    removeTestDirectory($root);
}
