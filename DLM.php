<?php
/**
 * Created by PhpStorm.
 * User: guntherdw
 * Date: 01/04/15
 * Time: 16:59
 */

class DLM {

    private $checks = array();
    private $seconds = array();

    function __construct()
    {
        $this->checks = array(
            "0.5  dag"  => math::$HOUR*12,
            "2  dagen"  => math::$DAY*2,
            "5  dagen"  => math::$DAY*5,
            "10 dagen"  => math::$DAY*10,
            "20 dagen"  => math::$DAY*20,
            "40 dagen"  => math::$DAY*40,
            "80 dagen"  => math::$DAY*80,
            "90 dagen"  => math::$DAY*90
        );
        /* foreach($this->checks as $k => $v) {
            $this->seconds[] = $k == 0 ? $v : $this->seconds[$k-1]+$this->checks[$k];
        } */
    }

    function getEstimateToNextCheck($seconds) {
        $stillChecking = true;
        $secondsToDeduct = 0;
        $stopIncrementing = false;

        $nextCheck = 0;

        reset($this->checks);

        while($stillChecking) {
            if(current($this->checks) !== FALSE) {
                $secondsToDeduct = current($this->checks);
                // $checkMark = key($this->checks);
            } else {
                $stopIncrementing = true;
            }
            $nextCheck += $secondsToDeduct;

            if($seconds - $secondsToDeduct <= 0) {
                $stillChecking = false;
            } else {
                $seconds -= $secondsToDeduct;
                // echo "Passed the " . $checkMark. " mark\n";
            }

            if(!$stopIncrementing)
                next($this->checks);
            /* else
                echo "Reached the maximum DLM mark!\n"; */
        }
        return $nextCheck;
    }


}