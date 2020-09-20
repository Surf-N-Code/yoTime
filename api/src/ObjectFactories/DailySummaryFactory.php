<?php


namespace App\ObjectFactories;


use App\Entity\DailySummary;
use App\Entity\Timer;
use App\Entity\User;

class DailySummaryFactory {

    public function createDailySummaryObject(
        string $summary,
        User $user,
        Timer $punchOutTimer,
        DailySummary $dailySummary = null,
        $timeOnWork = 0,
        $timeOnBreak = 0
    ): DailySummary
    {
        if (!$dailySummary) {
            $dailySummary = new DailySummary();
            $dailySummary->setDate(new \DateTime());
            $dailySummary->setTimeWorkedInS($timeOnWork);
            $dailySummary->setTimeBreakInS($timeOnBreak ?? 0);
            $dailySummary->setStartTime($punchOutTimer->getDateStart());
            $dailySummary->setEndTime($punchOutTimer->getDateEnd());
        }

        $dailySummary->setDailySummary($summary);
        $dailySummary->setUser($user);
        return $dailySummary;
    }
}
