<?php

require_once('../vendor/autoload.php');

$config = array(
    # current development environment
    "env" => "development",
    # Localhost
    "development" => [
        "host" => "localhost",  
        "database" => "test",
        "username" => "root",
        "password" => ""
    ],
    # Server
    "production"  => [
        "host" => "",
        "database" => "",
        "username" => "",
        "password" => ""
    ]
);

$Database = new JacksonJeans\Database(
    $config, 
    JacksonJeans\Database::MYSQL
);

$Result = $Database->table('test')
    ->select('id, name')
    ->join(JacksonJeans\Database::LEFT,'test2')
    ->on('test','id','test2','id')
    ->selectFromJoin('last_name','test2')
    ->where('test.name','Julian')
    ->orWhere('test.name','Nico')
    ->between('test.id', 1, 7)
    ->orderBy('test.id',JacksonJeans\Database::ASCENDING)
    ->get();


var_dump(
    $Result->toArray(),
    $Result->toJSON(),
    $Database->getSQL(),
    $Database->getTime()
);


$Update = $Database ->update('test',array('name' => 'NewJulian'))
                    ->simulate();

var_dump($Update,
$Database->getTotalTime(),
$Database->getTime());
