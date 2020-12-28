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
        $h = rand(5,9);
        $m = rand(10,59);
        $s = rand(10,59);
        $curMonth = date('m');
        $month = rand(1,$curMonth);
        $curDayMaxed = min(date('d'), 27);
        $d = rand(1,$curDayMaxed);
        if ($month < 10) {
            str_pad($month,2,'0',STR_PAD_LEFT);
        }
        if ($d < 10) {
            str_pad($d,2,'0',STR_PAD_LEFT);
        }
        return new \DateTime('2020-'.$month.'-'.$d.' 0'.$h.':'.$m.':'.$s);
    }

    public static function generateDsDate(string $current, $userId)
    {
        if ($userId >= 1) {
            $current -= 50*$userId;
        }
        return (new \DateTime('2020-01-01'))->modify('+'.$current.' days');
    }
}
