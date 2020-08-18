<?php


namespace App\ObjectFactories;


use App\Entity\DailySummary;
use App\Entity\User;

class DailySummaryFactory {

    public function createDailySummaryObject(
        string $summary,
        ?DailySummary $dailySummary,
        User $user,
        $timeOnWork = null,
        $timeOnBreak = null
    ): DailySummary
    {
        if (!$dailySummary) {
            $dailySummary = new DailySummary();
            $dailySummary->setDate(new \DateTime());
            $dailySummary->setDailySummary($summary);
            $dailySummary->setTimeWorkedInS($timeOnWork ?? 0);
            $dailySummary->setTimeWorkedInS($timeOnBreak ?? 0);
        }

        $dailySummary->setDailySummary($summary);
        $dailySummary->setUser($user);
        $dailySummary->setIsEmailSent(new \DateTime('now'));
        return $dailySummary;
    }
}
