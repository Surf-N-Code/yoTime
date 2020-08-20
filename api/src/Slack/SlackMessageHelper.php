<?php


namespace App\Slack;


use App\Entity\Slack\SlackMessage;
use App\Entity\Timer;
use App\Entity\TimerType;use App\Entity\User;use App\Services\Time;

class SlackMessageHelper
{
    private Time $time;

    public function __construct(Time $time){
        $this->time = $time;
    }
    public function createSlackMessage(): SlackMessage
    {
        return new SlackMessage();
    }

    public function addTextSection(string $text, SlackMessage $m): SlackMessage
    {
        $m->addTextSection($text);
        return $m;
    }

    public function addDivider(SlackMessage $m): SlackMessage
    {
        $m->addDivider();
        return $m;
    }

    public function getTimeSpentOnTaskMessage(Timer $timeEntry): string
    {
        $timeSpent = $this->time->formatSecondsAsHoursAndMinutes(
            abs($timeEntry->getDateEnd()->getTimestamp() - $timeEntry->getDateStart()->getTimestamp())
        );
        return sprintf('You spent `%s` on `%s`', $timeSpent, $timeEntry->getTimerType());
    }

    public function getFormattedTimeSpentOnWorkAndBreak(User $user)
    {
        $timeOnWork = $this->time->getTimeSpentOnTypeByPeriod(
            $user,
            'day',
            TimerType::PUNCH
        );

        $timeOnBreak = $this->time->getTimeSpentOnTypeByPeriod(
            $user,
            'day',
            TimerType::BREAK
        );
        $formattedTimeOnWork = $this->time->formatSecondsAsHoursAndMinutes($timeOnWork - $timeOnBreak);
        $formattedTimeOnBreak = $this->time->formatSecondsAsHoursAndMinutes($timeOnBreak);
        return sprintf('spent `%s` on work and `%s` on break.', $formattedTimeOnWork, $formattedTimeOnBreak);
    }

    public function getFormattedTimeSpentOnWork(User $user)
    {
        $timeOnWork = $this->time->getTimeSpentOnTypeByPeriod(
            $user,
            'day',
            TimerType::PUNCH
        );

        $timeOnBreak = $this->time->getTimeSpentOnTypeByPeriod(
            $user,
            'day',
            TimerType::BREAK
        );
        return $this->time->formatSecondsAsHoursAndMinutes($timeOnWork - $timeOnBreak);
    }

    public function getFormattedTimeSpentOnBreak(User $user)
    {
        $timeOnBreak = $this->time->getTimeSpentOnTypeByPeriod(
            $user,
            'day',
            TimerType::BREAK
        );
        return $this->time->formatSecondsAsHoursAndMinutes($timeOnBreak);
    }
}
