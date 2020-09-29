<?php

namespace App\Fixtures;

use Faker\Provider\Base;

class TimerProvider extends Base
{
    public static function getDateEnd(\DateTime $dateStart)
    {
        $h = rand(1,12);
        $m = rand(1,60);
        $s = rand(1,60);
        $dateEnd = clone($dateStart);
        return $dateEnd->add(new \DateInterval('PT'.$h.'H'.$m.'M'.$s.'S'));
    }

    public static function getDateStart()
    {
        $d = rand(1,28);
        $h = rand(5,9);
        $m = rand(10,59);
        $s = rand(10,59);
        $month = rand(1,12);
        if ($month < 10) {
            str_pad($month,2,'0',STR_PAD_LEFT);
        }
        if ($d < 10) {
            str_pad($d,2,'0',STR_PAD_LEFT);
        }
        return new \DateTime('2020-'.$month.'-'.$d.' 0'.$h.':'.$m.':'.$s);
    }
}
