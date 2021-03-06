<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf37243fa987f488f50f4eb6de499e39f
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PhpAmqpLib\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PhpAmqpLib\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-amqplib/php-amqplib/PhpAmqpLib',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf37243fa987f488f50f4eb6de499e39f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf37243fa987f488f50f4eb6de499e39f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
