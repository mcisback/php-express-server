<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4b6e0abd5e4e270a20bce7683e936f20
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Mcisback\\PhpExpress\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Mcisback\\PhpExpress\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit4b6e0abd5e4e270a20bce7683e936f20::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4b6e0abd5e4e270a20bce7683e936f20::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4b6e0abd5e4e270a20bce7683e936f20::$classMap;

        }, null, ClassLoader::class);
    }
}