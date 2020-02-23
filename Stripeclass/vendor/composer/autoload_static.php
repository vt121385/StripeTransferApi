<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1f58487c4611ab59aee24129fd4b0266
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1f58487c4611ab59aee24129fd4b0266::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1f58487c4611ab59aee24129fd4b0266::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
