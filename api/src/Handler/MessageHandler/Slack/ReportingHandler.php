<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\PunchTimerStatusDto;
use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlashCommand;
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

    public function getUserReport(User $user, SlashCommand $command): SlackMessage
    {
        $period = str_replace(SlashCommandHandler::REPORT, '', $command->getText());
        try {
            extract(
                $this->time->getTimesSpentByTypeAndPeriod($user, $period),
                EXTR_OVERWRITE
            );
        } catch (MessageHandlerException $e) {
            $m = new SlackMessage();
            return $m->addTextSection($e->getMessage());
        }
        $desc = 'this '.$period;
        if ($period === 'day') {
            $desc = 'today';
        } else if ($period === 'all') {
            $desc = 'all time';
        }

        $m = new SlackMessage();
        $m->addTextSection(sprintf('Stats for %s:', $desc));
        $m->addTextSection(sprintf('Work: `%s` Break: `%s`', $this->time->formatSecondsAsHoursAndMinutes($work), $this->time->formatSecondsAsHoursAndMinutes($break)));
        return $m;
    }
}
