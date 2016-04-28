<?php
class Cookie {
    //Bruges til at tjekke om en cookie af navnet i $name variablen eksisterer
    public static function exists($name) {
        return (isset($_COOKIE[$name])) ? true : false;
    }

    //Bruges til at få navnet på cookie
    public static function get($name) {
        return $_COOKIE[$name];
    }

    public static function put($name, $value, $expiry) {
        if(setcookie($name, $value, time() + $expiry, '/')) {
            return true;
        }
        return false;
    }

    public static function delete($name) {
        self::put($name, '', time() - 1);
    }
}