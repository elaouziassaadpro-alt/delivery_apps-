<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Driver;
use App\Models\Vehicle;

try {
    echo "Driver class: " . Driver::class . PHP_EOL;
    echo "Vehicle class: " . Vehicle::class . PHP_EOL;
    
    $driver = new Driver();
    echo "Driver instance created" . PHP_EOL;
    
    $relation = $driver->vehicle();
    echo "Relation object: " . get_class($relation->getRelated()) . PHP_EOL;
    
    if ($relation->getRelated() instanceof Vehicle) {
        echo "Success: Vehicle class is recognized inside Driver" . PHP_EOL;
    } else {
        echo "Failure: Relation related is not instance of Vehicle" . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo "Caught exception: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;
}
