<?php

function autoloader($className) {
    if (file_exists('./src/' . $className . '.php')) {
        require_once './src/' . $className . '.php';
    }
}

spl_autoload_register('autoloader');
