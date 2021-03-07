<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\PunchTimerStatusDto;
use App\Entity\Slack\SlackUser;
use App\Entity\Timer;use App\Entity\TimerType;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Repository\TimerRepository;
use App\Services\DatabaseHelper;
use App\Services\Time;
use App\Slack\SlackClient;
use App\Slack\SlackUserClient;
use Symfony\Component\HttpFoundation\Response;

class TimerHandler
{

    private Time $time;

    private TimerRepository $timerRepository;

    private SlackUserClient $slackUserClient;

    public function __construct(
        Time $time,
        TimerRepository $timerRepository,
        SlackUserClient $slackUserClient
    )
    {
        $this->time = $time;
        $this->timerRepository = $timerRepository;
        $this->slackUserClient = $slackUserClient;
    }

    public function startTimer(User $user, string $commandStr): Timer
    {
        $timer = $this->timerRepository->findRunningTimer($user);
        if ($timer) {
            $this->time->stopTimer($timer);
        }
        $timerType = str_replace('/', '', $commandStr);
        $statusText = '';
        $emoji = '';

        if ($commandStr === SlashCommandHandler::START_BREAK) {
            $statusText = 'Shortly away';
            $emoji = ':away:';
        }

        $this->slackUserClient->slackApiCall('POST', 'users.profile.set', [
            'profile' => [
                'status_text' => $statusText,
                'status_emoji' => $emoji,
                'status_expiration' => 0
            ]
        ]);
        return $this->time->startTimer($user, $timerType);
    }

    public function stopTimer(User $user, string $taskDescription = null): Timer
    {
        $timer = $this->timerRepository->findRunningTimer($user);
        $this->throwWhenMissingTimer($timer);

        if ($taskDescription && $timer) {
            $timer = $this->time->addTaskToTimer($timer, $taskDescription);
        }

        $this->slackUserClient->slackApiCall('POST', 'users.profile.set', [
            'profile' => [
                'status_text' => '',
                'status_emoji' => '',
                'status_expiration' => 0
            ]
        ]);

        return $this->time->stopTimer($timer);
    }

    public function lateSignIn(User $user, string $timeStr): Timer
    {
        $this->throwOnExistingTimerFromToday($user);
        return $this->time->startTimerFromTimeString($user, $timeStr, TimerType::WORK);
    }

    public function punchOut(User $user): PunchTimerStatusDto
    {
        $timers = $this->timerRepository->findTimersFromToday($user);

        if (empty($timers)) {
            throw new MessageHandlerException(sprintf('Seems like you didn\'t sign in this morning. You can travel back in time to check yourself in for today by using the `%s` command.', SlashCommandHandler::LATE_HI), Response::HTTP_PRECONDITION_FAILED);
        }

        $latestTimer = $timers[count($timers)-1];

        $isPunchedOut = true;
        foreach ($timers as $timer) {
            if (!$timer->getDateEnd()) {
                $isPunchedOut = false;
            }
        }

        if ($isPunchedOut) {
            return new PunchTimerStatusDto(false, $latestTimer);
        }

        $this->slackUserClient->slackApiCall('POST', 'users.profile.set', [
            'profile' => [
                'status_text' => '',
                'status_emoji' => '',
                'status_expiration' => 0
            ]
        ]);

        $this->time->stopTimer($latestTimer);
        return new PunchTimerStatusDto(true, $latestTimer);
    }

    private function throwWhenMissingTimer(?Timer $timeEntry)
    {
        if (!$timeEntry) {
            throw new MessageHandlerException(
                sprintf(
                    'No timer is running at the moment. Please start one using `%s` or `%s`',
                    TimerType::WORK,
                    TimerType::BREAK
                ),
                Response::HTTP_PRECONDITION_FAILED
            );
        }
    }

    private function throwOnExistingTimerFromToday(User $user): void
    {
        $timers = $this->timerRepository->findTimersFromToday($user);

        if (!empty($timers)) {
            throw new MessageHandlerException(sprintf('Seems like you have already signed in for today. The timer was started on `%s`.',
                $timers[0]->getDateStart()->format('d.m.Y H:i:s')
            ), Response::HTTP_PRECONDITION_FAILED);
        }
    }
}
