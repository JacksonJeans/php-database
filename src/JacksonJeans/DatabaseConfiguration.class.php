<?php

namespace JacksonJeans;

/**
 * DatabaseConfiguration - Klasse
 * 
 * @category    Class
 * @package     GIDUTEX
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @link        https://gidutex.de
 * @version     1.0
 */
class DatabaseConfiguration
{

    /**
     * @param string $host
     */
    public $host;

    /**
     * @param string $database 
     */
    public $database;

    /**
     * @param string $username 
     */
    public $username;

    /**
     * @param string $password
     */
    public $password;

    /**
     * @param int $port 
     */
    public $port;

    /**
     * @param string $file 
     */
    public $file;

    /**
     * @param string $instance
     */
    public $instance;
}
