<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita530fdab98b95fc220fbc2685e9a36cb
{
    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'Bootpay\\BackendPhp\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Bootpay\\BackendPhp\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita530fdab98b95fc220fbc2685e9a36cb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita530fdab98b95fc220fbc2685e9a36cb::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita530fdab98b95fc220fbc2685e9a36cb::$classMap;

        }, null, ClassLoader::class);
    }
}
