<?php

namespace Webman;

class Install
{
    const WEBMAN_PLUGIN = true;

    /**
     * @var array
     */
    protected static $pathRelation = [
        'start.php' => 'start.php',
        'windows.php' => 'windows.php',
        'support/bootstrap.php' => 'support/bootstrap.php',
    ];

    /**
     * Install
     * @return void
     */
    public static function install()
    {
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {

    }

    /**
     * InstallByRelation
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            $parentDir = base_path(dirname($dest));
            if (!is_dir($parentDir)) {
                mkdir($parentDir, 0777, true);
            }
            $sourceFile = __DIR__ . "/$source";
            copy_dir($sourceFile, base_path($dest), true);
            echo "Create $dest\r\n";
            if (is_file($sourceFile)) {
                @unlink($sourceFile);
            }
        }
        if (is_file($file = base_path('support/helpers.php'))) {
            file_put_contents($file, "<?php\n// This file is generated by Webman, please don't modify it.\n");
            echo "Clear helpers.php\r\n";
        }
    }

}
