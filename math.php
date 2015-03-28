<?php
/**
 * Created by PhpStorm.
 * User: guntherdw
 * Date: 28/03/15
 * Time: 02:31
 */

class math {

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