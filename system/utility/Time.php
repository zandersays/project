<?php
class Time {

    public static $minuteInSeconds = 60;
    public static $hourInSeconds = 3600;
    public static $dayInSeconds = 86400;
    public static $weekInSeconds = 604800;

    public static function nowInSeconds() {
        return time();
    }

    public static function dateTime($string = null) {
        if($string === null) {
            $response = date('Y-m-d H:i:s', Time::nowInSeconds());
        }
        else if(is_int($string)) {
            $response = date('Y-m-d H:i:s', $string);
        }
        else {
            $response = date('Y-m-d H:i:s', strtotime($string));
        }
        
        return $response;
    }

    public static function dateToAge($date) {
        $year_diff = '';
        $time = strtotime($date);
        if (false === $time) {
            return '';
        }

        $date = date('Y-m-d', $time);
        list($year, $month, $day) = explode("-", $date);
        $year_diff = date("Y") - $year;
        $month_diff = date("m") - $month;
        $day_diff = date("d") - $day;
        if ($day_diff < 0 || $month_diff < 0)
            $year_diff--;

        return $year_diff;
    }
    
    public static function differenceInSeconds($a, $b) {
        // Convert strings to time in seconds
        if(String::is($a)) {
            $a = String::time($a);
        }
        if(String::is($b)) {
            $b = String::time($b);
        }
        
        return $a - $b;
    }

    public static function differenceString($time, $shortTimeUnitNames = true, $timeIsInPast = true) {
        if(!Number::isInteger($time)) {
            $time = strtotime($time);
        }

        // array of time period chunks
        $chunks = array(
            array(60 * 60 * 24 * 365 , 'year'),
            array(60 * 60 * 24 * 30 , 'month'),
            array(60 * 60 * 24 * 7, 'week'),
            array(60 * 60 * 24 , 'day'),
            array(60 * 60 , 'hour'),
            array(60 , 'min'),
            array(1 , 'sec'),
        );

        $today = time(); /* Current unix time  */
        if($timeIsInPast) {
            $difference = $today - $time;
        }
        else {
            $difference = $time - $today;
        }

        // $j saves performing the count function each time around the loop
        for ($i = 0, $j = count($chunks); $i < $j; $i++) {

            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];

            // finding the biggest chunk (if the chunk fits, break)
            if (($count = floor($difference / $seconds)) != 0) {
            // DEBUG print "<!-- It's $name -->\n";
                break;
            }
        }

        $response = ($count == 1) ? '1 '.$name : "$count {$name}s";

        if ($i + 1 < $j) {
        // now getting the second item
            $seconds2 = $chunks[$i + 1][0];
            $name2 = $chunks[$i + 1][1];

            // add second item if it's greater than 0
            if (($count2 = floor(($difference - ($seconds * $count)) / $seconds2)) != 0) {
                //$print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}s";
            }
        }
        return $response;
    }

    public static function timeSinceString($time) {
        return self::differenceString($time);
    }

    public static function timeToString($time) {
        return self::differenceString($time, true, false);
    }

}
?>