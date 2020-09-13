<?php


namespace App\ObjectFactories;


use App\Entity\DailySummary;
use App\Entity\User;

class DailySummaryFactory {

    public function createDailySummaryObject(
        string $summary,
        User $user,
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
        }

        $dailySummary->setDailySummary($summary);
        $dailySummary->setUser($user);
        $dailySummary->setIsEmailSent(new \DateTime('now'));
        return $dailySummary;
    }
}
