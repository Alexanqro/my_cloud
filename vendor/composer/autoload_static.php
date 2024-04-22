<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdd5be033a5bef7f8f5a58ca75e43c283
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'A' => 
        array (
            'Alexa\\MyCloud\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'Alexa\\MyCloud\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInitdd5be033a5bef7f8f5a58ca75e43c283::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdd5be033a5bef7f8f5a58ca75e43c283::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdd5be033a5bef7f8f5a58ca75e43c283::$classMap;

        }, null, ClassLoader::class);
    }
}