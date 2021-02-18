<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit404375d7e7876bdd4ffa3cc248cbdef3
{
    public static $classMap = array (
        'JacksonJeans\\Collection' => __DIR__ . '/../..' . '/src/JacksonJeans/Collection.class.php',
        'JacksonJeans\\Database' => __DIR__ . '/../..' . '/src/JacksonJeans/Database.class.php',
        'JacksonJeans\\DatabaseConfiguration' => __DIR__ . '/../..' . '/src/JacksonJeans/DatabaseConfiguration.class.php',
        'JacksonJeans\\DatabaseException' => __DIR__ . '/../..' . '/src/JacksonJeans/DatabaseException.class.php',
        'JacksonJeans\\DatabaseInterface' => __DIR__ . '/../..' . '/src/JacksonJeans/DatabaseInterface.class.php',
        'JacksonJeans\\Item' => __DIR__ . '/../..' . '/src/JacksonJeans/Item.class.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit404375d7e7876bdd4ffa3cc248cbdef3::$classMap;

        }, null, ClassLoader::class);
    }
}
