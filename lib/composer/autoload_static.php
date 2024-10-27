<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfe9aeef9b1ec0b48c43f537ee9e259cc
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'Orhanerday\\OpenAi\\' => 18,
        ),
        'C' => 
        array (
            'Cognitive Dynamics\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Orhanerday\\OpenAi\\' => 
        array (
            0 => __DIR__ . '/..' . '/orhanerday/open-ai/src',
        ),
        'Cognitive Dynamics\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInitfe9aeef9b1ec0b48c43f537ee9e259cc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfe9aeef9b1ec0b48c43f537ee9e259cc::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitfe9aeef9b1ec0b48c43f537ee9e259cc::$classMap;

        }, null, ClassLoader::class);
    }
}