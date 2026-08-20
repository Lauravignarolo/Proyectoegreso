<?php

echo "PHP: " . PHP_VERSION . "<br>";
echo "INI: " . php_ini_loaded_file() . "<br>";
echo "PDO MYSQL: ";
var_dump(extension_loaded("pdo_mysql"));
echo "DRIVERS: ";
var_dump(PDO::getAvailableDrivers());