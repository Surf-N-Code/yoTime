<?php


namespace App\Tests\Handler\MessageHandler\Slack;


use App\Entity\DailySummary;
use App\Entity\Slack\SlackMessage;
use App\Entity\Timer;use App\Entity\TimerType;
use App\Entity\User;
use App\Handler\MessageHandler\Slack\DailySummaryHandler;
use App\Handler\MessageHandler\Slack\PunchTimerHandler;
use App\HrTools\Personio\Gateway;
use App\ObjectFactories\DailySummaryFactory;
use App\Repository\DailySummaryRepository;
use App\Services\DatabaseHelper;
use App\Mail\Mailer;
use App\Services\Time;
use App\Slack\SlackMessageHelper;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class DailySummaryHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $time;
    private $user;
    private $dailySummaryRepo;
    private DailySummaryHandler $dailySummaryHandler;
    private $punchTimerHandler;
    private $slackMessageHelper;
    private $timeEntryProphecy;
    private $dailySummaryProphecy;
    private $dailySummaryFactory;
    private $slackMessage;
    private $databaseHelper;
    private $mailer;
    private $personio;
    private $logger;

    public function setup(): void
    {
        $this->timeEntryProphecy = $this->prophesize(Timer::class);
        $this->user = $this->prophesize(User::class);
        $this->dailySummaryRepo = $this->prophesize(DailySummaryRepository::class);
        $this->time = $this->prophesize(Time::class);
        $this->punchTimerHandler = $this->prophesize(PunchTimerHandler::class);
        $this->slackMessageHelper = $this->prophesize(SlackMessageHelper::class);
        $this->dailySummaryProphecy = $this->prophesize(DailySummary::class);
        $this->dailySummaryFactory = $this->prophesize(DailySummaryFactory::class);
        $this->slackMessage = $this->prophesize(SlackMessage::class);
        $this->databaseHelper = $this->prophesize(DatabaseHelper::class);
        $this->mailer = $this->prophesize(Mailer::class);
        $this->personio = $this->prophesize(Gateway::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->dailySummaryHandler = new DailySummaryHandler(
            $this->punchTimerHandler->reveal(),
            $this->dailySummaryRepo->reveal(),
            $this->time->reveal(),
            $this->dailySummaryFactory->reveal(),
            $this->databaseHelper->reveal(),
            $this->mailer->reveal(),
            $this->slackMessageHelper->reveal(),
            $this->personio->reveal(),
            $this->logger->reveal()
        );
    }

    public function testHandleModalSubmissionWithPunchoutAndEmail()
    {
        $this->punchTimerHandler->punchOut($this->user->reveal())
                                ->shouldBeCalled()
                                ->willReturn([true, $this->timeEntryProphecy->reveal()]);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::PUNCH)
                   ->shouldBeCalled()->willReturn(3600);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::BREAK)
                   ->shouldBeCalled()->willReturn(600);

        $this->dailySummaryRepo->findOneBy(Argument::type('array'))
                               ->shouldBeCalled()
                               ->willReturn(null);

        $this->dailySummaryFactory->createDailySummaryObject('Daily summary notes', $this->user->reveal(), $this->timeEntryProphecy->reveal(), null,3600, 600)
                                  ->shouldBeCalled()
                                  ->willReturn($this->dailySummaryProphecy->reveal());

        $this->time->formatSecondsAsHoursAndMinutes(3000)
                   ->shouldBeCalled()->willReturn('0h 20min');

        $this->time->formatSecondsAsHoursAndMinutes(600)
                   ->shouldBeCalled()->willReturn('0h 10min');

        $this->databaseHelper->flushAndPersist($this->dailySummaryProphecy->reveal())
                             ->shouldBeCalled();

        $this->dailySummaryProphecy->getDailySummary()->shouldBeCalled()->willReturn('Daily summary notes');

        $this->mailer->sendDailySummaryMail(600, 3000, $this->user->reveal(), 'Daily summary notes')
                     ->shouldBeCalled();

        $this->personio->postAttendanceForEmployee(2269559, $this->dailySummaryProphecy->reveal())
            ->shouldBeCalled()
            ->willReturn(['success' => true, 'data' => ['message' => 'success']]);

        $evt['view']['state']['values']['daily_summary_block']['summary_block_input']['value'] = 'Daily summary notes';
        $evt['view']['state']['values']['mail_block']['mail_choice']['selected_option']['value'] = 'true';

        $this->slackMessage->getBlockText(0)->shouldBeCalled()->willReturn(':heavy_check_mark: Signed you out for the day and sent your summary via mail :call_me_hand:. You spent *0h 20min* on work and *0h 10min* on break.');
        $this->slackMessageHelper->createSlackMessage()->shouldBeCalled()->willReturn($this->slackMessage->reveal());
        $this->slackMessageHelper->addTextSection(':heavy_check_mark: Signed you out for the day and sent your summary via mail :call_me_hand:. You spent *0h 20min* on work and *0h 10min* on break.' . PHP_EOL . ':heavy_check_mark: Synced your attendance to Personio.', $this->slackMessage->reveal())
                           ->shouldBeCalled()
                           ->willReturn($this->slackMessage->reveal());

        $this->dailySummaryHandler->handleModalSubmission($evt, $this->user->reveal());
    }

    public function testHandleModalSubmissionWithPunchoutNoAndEmail()
    {
        $this->punchTimerHandler->punchOut($this->user->reveal())
                                ->shouldBeCalled()
                                ->willReturn([true, $this->timeEntryProphecy->reveal()]);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::PUNCH)
                   ->shouldBeCalled()->willReturn(3600);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::BREAK)
                   ->shouldBeCalled()->willReturn(600);

        $this->dailySummaryRepo->findOneBy(Argument::type('array'))
                               ->shouldBeCalled()
                               ->willReturn(null);

        $this->dailySummaryFactory->createDailySummaryObject('Daily summary notes', $this->user->reveal(), $this->timeEntryProphecy->reveal(), null,3600, 600)
                                  ->shouldBeCalled()
                                  ->willReturn($this->dailySummaryProphecy->reveal());

        $this->time->formatSecondsAsHoursAndMinutes(3000)
                   ->shouldBeCalled()->willReturn('0h 20min');

        $this->time->formatSecondsAsHoursAndMinutes(600)
                   ->shouldBeCalled()->willReturn('0h 10min');

        $this->databaseHelper->flushAndPersist($this->dailySummaryProphecy->reveal())
                             ->shouldBeCalled();


        $this->mailer->sendDailySummaryMail(600, 3000, $this->user->reveal(), 'Daily summary notes')
                     ->shouldNotBeCalled();

        $this->personio->postAttendanceForEmployee(2269559, $this->dailySummaryProphecy->reveal())
                       ->shouldBeCalled()
                       ->willReturn(['success' => true, 'data' => ['message' => 'success']]);

        $evt['view']['state']['values']['daily_summary_block']['summary_block_input']['value'] = 'Daily summary notes';
        $evt['view']['state']['values']['mail_block']['mail_choice']['selected_option']['value'] = 'false';

        $this->slackMessage->getBlockText(0)->shouldBeCalled()->willReturn(':heavy_check_mark: Signed you out for the day :call_me_hand:. You spent *0h 20min* on work and *0h 10min* on break.');
        $this->slackMessageHelper->createSlackMessage()->shouldBeCalled()->willReturn($this->slackMessage->reveal());
        $this->slackMessageHelper->addTextSection(':heavy_check_mark: Signed you out for the day :call_me_hand:. You spent *0h 20min* on work and *0h 10min* on break.' . PHP_EOL . ':heavy_check_mark: Synced your attendance to Personio.', $this->slackMessage->reveal())
                                 ->shouldBeCalled()
                                 ->willReturn($this->slackMessage->reveal());

        $this->dailySummaryHandler->handleModalSubmission($evt, $this->user->reveal());
    }

    public function testHandleModalSubmissionWithoutPunchoutNoAndEmail()
    {
        $this->punchTimerHandler->punchOut($this->user->reveal())
                                ->shouldBeCalled()
                                ->willReturn([false, $this->timeEntryProphecy->reveal()]);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::PUNCH)
                   ->shouldBeCalled()->willReturn(3600);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::BREAK)
                   ->shouldBeCalled()->willReturn(600);

        $this->dailySummaryRepo->findOneBy(Argument::type('array'))
                               ->shouldBeCalled()
                               ->willReturn(null);

        $this->dailySummaryFactory->createDailySummaryObject('Daily summary notes', $this->user->reveal(), $this->timeEntryProphecy->reveal(), null,3600, 600)
                                  ->shouldBeCalled()
                                  ->willReturn($this->dailySummaryProphecy->reveal());

        $this->time->formatSecondsAsHoursAndMinutes(3000)
                   ->shouldBeCalled()->willReturn('0h 20min');

        $this->time->formatSecondsAsHoursAndMinutes(600)
                   ->shouldBeCalled()->willReturn('0h 10min');

        $this->databaseHelper->flushAndPersist($this->dailySummaryProphecy->reveal())
                             ->shouldBeCalled();

        $this->mailer->sendDailySummaryMail(600, 3000, $this->user->reveal(), 'Daily summary notes')
                     ->shouldNotBeCalled();

        $this->personio->postAttendanceForEmployee(2269559, $this->dailySummaryProphecy->reveal())
                       ->shouldBeCalled()
                       ->willReturn(['success' => true, 'data' => ['message' => 'success']]);

        $evt['view']['state']['values']['daily_summary_block']['summary_block_input']['value'] = 'Daily summary notes';
        $evt['view']['state']['values']['mail_block']['mail_choice']['selected_option']['value'] = 'false';

        $this->slackMessage->getBlockText(0)->shouldBeCalled()->willReturn('Summary saved :slightly_smiling_face:');
        $this->slackMessageHelper->createSlackMessage()->shouldBeCalled()->willReturn($this->slackMessage->reveal());
        $this->slackMessageHelper->addTextSection('Summary saved :slightly_smiling_face:' . PHP_EOL . ':heavy_check_mark: Synced your attendance to Personio.', $this->slackMessage->reveal())
                                 ->shouldBeCalled()
                                 ->willReturn($this->slackMessage->reveal());

        $this->dailySummaryHandler->handleModalSubmission($evt, $this->user->reveal());
    }

    public function testHandleModalSubmissionWithoutPunchoutAndEmail()
    {
        $this->punchTimerHandler->punchOut($this->user->reveal())
                                ->shouldBeCalled()
                                ->willReturn([false, $this->timeEntryProphecy->reveal()]);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::PUNCH)
                   ->shouldBeCalled()->willReturn(3600);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::BREAK)
                   ->shouldBeCalled()->willReturn(600);

        $this->dailySummaryRepo->findOneBy(Argument::type('array'))
                               ->shouldBeCalled()
                               ->willReturn(null);

        $this->dailySummaryFactory->createDailySummaryObject('Daily summary notes', $this->user->reveal(), $this->timeEntryProphecy->reveal(), null,3600, 600)
                                  ->shouldBeCalled()
                                  ->willReturn($this->dailySummaryProphecy->reveal());

        $this->time->formatSecondsAsHoursAndMinutes(3000)
                   ->shouldBeCalled()->willReturn('0h 20min');

        $this->time->formatSecondsAsHoursAndMinutes(600)
                   ->shouldBeCalled()->willReturn('0h 10min');

        $this->databaseHelper->flushAndPersist($this->dailySummaryProphecy->reveal())
                             ->shouldBeCalled();

        $this->dailySummaryProphecy->getDailySummary()->shouldBeCalled()->willReturn('Daily summary notes');

        $this->mailer->sendDailySummaryMail(600, 3000, $this->user->reveal(), 'Daily summary notes')
                     ->shouldBeCalled();

        $this->personio->postAttendanceForEmployee(2269559, $this->dailySummaryProphecy->reveal())
                       ->shouldBeCalled()
                       ->willReturn(['success' => true, 'data' => ['message' => 'success']]);

        $evt['view']['state']['values']['daily_summary_block']['summary_block_input']['value'] = 'Daily summary notes';
        $evt['view']['state']['values']['mail_block']['mail_choice']['selected_option']['value'] = 'true';

        $this->slackMessage->getBlockText(0)->shouldBeCalled()->willReturn('Summary sent :slightly_smiling_face:');
        $this->slackMessageHelper->createSlackMessage()->shouldBeCalled()->willReturn($this->slackMessage->reveal());
        $this->slackMessageHelper->addTextSection('Summary sent :slightly_smiling_face:' . PHP_EOL . ':heavy_check_mark: Synced your attendance to Personio.', $this->slackMessage->reveal())
                                 ->shouldBeCalled()
                                 ->willReturn($this->slackMessage->reveal());

        $this->dailySummaryHandler->handleModalSubmission($evt, $this->user->reveal());
    }

    public function testHandleModalSubmissionWithPunchoutWorkTimeOnly()
    {
        $this->punchTimerHandler->punchOut($this->user->reveal())
                                ->shouldBeCalled()
                                ->willReturn([true, $this->timeEntryProphecy->reveal()]);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::PUNCH)
                   ->shouldBeCalled()->willReturn(3600);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::BREAK)
                   ->shouldBeCalled()->willReturn(0);

        $this->dailySummaryRepo->findOneBy(Argument::type('array'))
                               ->shouldBeCalled()
                               ->willReturn(null);

        $this->dailySummaryFactory->createDailySummaryObject('Daily summary notes', $this->user->reveal(), $this->timeEntryProphecy->reveal(), null,3600, 0)
                                  ->shouldBeCalled()
                                  ->willReturn($this->dailySummaryProphecy->reveal());

        $this->time->formatSecondsAsHoursAndMinutes(3600)
                   ->shouldBeCalled()->willReturn('0h 30min');

        $this->time->formatSecondsAsHoursAndMinutes(0)
                   ->shouldBeCalled()->willReturn('0h 0min');

        $this->databaseHelper->flushAndPersist($this->dailySummaryProphecy->reveal())
                             ->shouldBeCalled();

        $this->mailer->sendDailySummaryMail(0, 3600, $this->user->reveal(), 'Daily summary notes')
                     ->shouldNotBeCalled();

        $this->personio->postAttendanceForEmployee(2269559, $this->dailySummaryProphecy->reveal())
                       ->shouldBeCalled()
                       ->willReturn(['success' => true, 'data' => ['message' => 'success']]);

        $evt['view']['state']['values']['daily_summary_block']['summary_block_input']['value'] = 'Daily summary notes';
        $evt['view']['state']['values']['mail_block']['mail_choice']['selected_option']['value'] = 'false';

        $this->slackMessage->getBlockText(0)->shouldBeCalled()->willReturn(':heavy_check_mark: Signed you out for the day :call_me_hand:. You spent *0h 30min* on work.');
        $this->slackMessageHelper->createSlackMessage()->shouldBeCalled()->willReturn($this->slackMessage->reveal());
        $this->slackMessageHelper->addTextSection(':heavy_check_mark: Signed you out for the day :call_me_hand:. You spent *0h 30min* on work.' . PHP_EOL . ':heavy_check_mark: Synced your attendance to Personio.', $this->slackMessage->reveal())
                                 ->shouldBeCalled()
                                 ->willReturn($this->slackMessage->reveal());

        $this->dailySummaryHandler->handleModalSubmission($evt, $this->user->reveal());
    }

    public function testHandleModalSubmissionFailedPersonioSync()
    {
        $this->punchTimerHandler->punchOut($this->user->reveal())
                                ->shouldBeCalled()
                                ->willReturn([true, $this->timeEntryProphecy->reveal()]);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::PUNCH)
                   ->shouldBeCalled()->willReturn(3600);

        $this->time->getTimeSpentOnTypeByPeriod($this->user->reveal(), 'day', TimerType::BREAK)
                   ->shouldBeCalled()->willReturn(0);

        $this->dailySummaryRepo->findOneBy(Argument::type('array'))
                               ->shouldBeCalled()
                               ->willReturn(null);

        $this->dailySummaryFactory->createDailySummaryObject('Daily summary notes', $this->user->reveal(), $this->timeEntryProphecy->reveal(), null,3600, 0)
                                  ->shouldBeCalled()
                                  ->willReturn($this->dailySummaryProphecy->reveal());

        $this->time->formatSecondsAsHoursAndMinutes(3600)
                   ->shouldBeCalled()->willReturn('0h 30min');

        $this->time->formatSecondsAsHoursAndMinutes(0)
                   ->shouldBeCalled()->willReturn('0h 0min');

        $this->databaseHelper->flushAndPersist($this->dailySummaryProphecy->reveal())
                             ->shouldBeCalled();

        $this->mailer->sendDailySummaryMail(0, 3600, $this->user->reveal(), 'Daily summary notes')
                     ->shouldNotBeCalled();

        $this->personio->postAttendanceForEmployee(2269559, $this->dailySummaryProphecy->reveal())
                       ->shouldBeCalled()
                        ->willReturn(['success' => false, 'error' => ['message' => 'Existing overlapping attendances periods']]);

        $this->logger->error(Argument::type('string'))
            ->shouldBeCalled();

        $evt['view']['state']['values']['daily_summary_block']['summary_block_input']['value'] = 'Daily summary notes';
        $evt['view']['state']['values']['mail_block']['mail_choice']['selected_option']['value'] = 'false';

        $this->slackMessage->getBlockText(0)->shouldBeCalled()->willReturn(':heavy_check_mark: Signed you out for the day :call_me_hand:. You spent *0h 30min* on work.' . PHP_EOL . ':x: Error synching your attendance to Personio.');
        $this->slackMessageHelper->createSlackMessage()->shouldBeCalled()->willReturn($this->slackMessage->reveal());
        $this->slackMessageHelper->addTextSection(':heavy_check_mark: Signed you out for the day :call_me_hand:. You spent *0h 30min* on work.' . PHP_EOL . ':x: Error syncing your attendance to Personio.', $this->slackMessage->reveal())
                                 ->shouldBeCalled()
                                 ->willReturn($this->slackMessage->reveal());

        $this->dailySummaryHandler->handleModalSubmission($evt, $this->user->reveal());
    }
}
