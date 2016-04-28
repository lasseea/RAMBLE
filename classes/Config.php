<?php
//Denne klasse bruges til at hente variabler fra $GLOBALS i core/init.php klassen
//F.eks. echo Config::get('mysql/host');
//Det vil fremvise "localhost" som tekst på skærmen, da det er værdien af host, i mysql arrayet, som er del af $GLOBALS arrayet.
class Config {
    public static function get($path = null) {
        if($path) {
            $config = $GLOBALS['config'];
            $path = explode('/', $path);

            foreach($path as $bit) {
                if(isset($config[$bit])) {
                    $config = $config[$bit];
                }
            }

            return $config;
        }

        return false;
    }
}
