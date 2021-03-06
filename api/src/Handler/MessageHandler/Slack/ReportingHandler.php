<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\PunchTimerStatusDto;
use App\Entity\Slack\SlackMessage;
use App\Entity\Timer;use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Repository\TimerRepository;
use App\Services\DatabaseHelper;
use App\Services\Time;
use Symfony\Component\HttpFoundation\Response;

class ReportingHandler
{

    private Time $time;

    public function __construct(
        Time $time
    )
    {
        $this->time = $time;
    }

    public function getUserReport(User $user, string $commandStr): SlackMessage
    {
        $period = str_replace('/', '', $commandStr);
        [$timeOnWork, $timeOnBreak] = $this->time->getTimesSpentByTypeAndPeriod($user, $period);
        $desc = 'this '.$period;
        if ($period === 'day') {
            $desc = 'today';
        } else if ($period === 'all') {
            $desc = 'all time';
        }

        $m = new SlackMessage();
        $m->addTextSection(sprintf('Stats for %s\nWork: `%s`\nBreak: `%s`', $desc, $timeOnWork, $timeOnBreak));
        return $m;
    }
}
