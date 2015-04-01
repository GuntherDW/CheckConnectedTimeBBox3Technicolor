<?php
/**
 * Created by PhpStorm.
 * User: guntherdw
 * Date: 28/03/15
 * Time: 02:31
 */

class math {

    static $SECOND = 1;
    static $MINUTE = 1 * 60;
    static $HOUR   = 1 * 60 * 60;
    static $DAY    = 1 * 60 * 60 * 24;
    static $WEEK   = 1 * 60 * 60 * 24 * 7;

    public function sectoday($sec) {

        $numdays =  floor($sec / 86400);
        $numhours = floor(($sec % 86400) / 3600);
        $nummin =   floor((($sec % 86400) % 3600) / 60);
        $numsec =         (($sec % 86400) % 3600) % 60;

        if ($numdays > 0) {
            return $numdays . "Days " . " " . $numhours . "h" . " " . $nummin . "min" . " " . $numsec . "Sec";
        } else {
            return $numhours . "h" . " " . $nummin . "min" . " " . $numsec . "Sec";
        }
    }

}