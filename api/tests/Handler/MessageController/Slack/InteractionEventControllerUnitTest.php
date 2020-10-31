<?php

namespace App\Tests\Handler\MessageController\Slack;

use App\Entity\Slack\ModalSubmissionDto;
use App\Entity\Slack\SlackInteractionEvent;
use App\Entity\User;
use App\Exceptions\MessageHandlerException;
use App\Handler\MessageController\Slack\InteractionEventController;
use App\Handler\MessageHandler\Slack\DailySummaryHandler;
use App\Services\UserProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class InteractionEventControllerUnitTest extends TestCase
{

    use ProphecyTrait;

    private $userProvider;
    private $dailySummaryHandler;
    private $requestStack;
    private $payload;
    private $interactionEvent;
    private $user;
    private $modalStatus;

    public function setUp(): void
    {
        $this->userProvider = $this->prophesize(UserProvider::class);
        $this->dailySummaryHandler = $this->prophesize(DailySummaryHandler::class);
        $this->requestStack = $this->prophesize(RequestStack::class);
        $this->user = $this->prophesize(User::class);
        $this->modalStatus = $this->prophesize(ModalSubmissionDto::class);

        $this->payload = json_decode('{"type":"view_submission","team":{"id":"THW253RMX","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"daily_summary_block":{"summary_block_input":{"type":"plain_text_input","value":"sdf"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}', true);
        $this->interactionEvent = $this->prophesize(SlackInteractionEvent::class);
    }

    public function testDailySummaryModalViewSubmissionPayload()
    {
        $interactionEventController = new InteractionEventController(
            $this->dailySummaryHandler->reveal(),
            $this->userProvider->reveal()
        );

        $this->interactionEvent->getPayload()->shouldBeCalled()->willReturn($this->payload);

        $this->userProvider->getDbUserBySlackId('UHW253RU1')
            ->shouldBeCalled()
            ->willReturn($this->user->reveal());

        $modalStatus = new ModalSubmissionDto(ModalSubmissionDto::STATUS_SUCCESS, 'Signed you out', 'Daily Summary');
        $this->dailySummaryHandler->handleModalSubmission($this->payload, $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($modalStatus);

        $this->dailySummaryHandler->getDailySummaryConfirmView($modalStatus)
            ->shouldBeCalled()
            ->willReturn([]);

        $response = $interactionEventController($this->interactionEvent->reveal());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], json_decode($response->getContent(), true));
    }

    public function testDailySummaryModalBlockActionPayload()
    {
        $interactionEventController = new InteractionEventController(
            $this->dailySummaryHandler->reveal(),
            $this->userProvider->reveal()
        );

        $payload = json_decode('{"type":"block_actions","team":{"id":"THW253RMX","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"daily_summary_block":{"summary_block_input":{"type":"plain_text_input","value":"sdf"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}', true);
        $this->interactionEvent->getPayload()->shouldBeCalled()->willReturn($payload);

        $this->userProvider->getDbUserBySlackId('UHW253RU1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $response = $interactionEventController($this->interactionEvent->reveal());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('successful block action', $response->getContent(), true);
    }

    public function testDailySummaryModalUnsupportedPayload()
    {
        $interactionEventController = new InteractionEventController(
            $this->dailySummaryHandler->reveal(),
            $this->userProvider->reveal()
        );

        $payload = json_decode('{"type":"unsupported","team":{"id":"THW253RMX","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"daily_summary_block":{"summary_block_input":{"type":"plain_text_input","value":"sdf"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}', true);
        $this->interactionEvent->getPayload()->shouldBeCalled()->willReturn($payload);

        $this->userProvider->getDbUserBySlackId('UHW253RU1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $response = $interactionEventController($this->interactionEvent->reveal());
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUnsupportedViewSubmissionPayload()
    {
        $interactionEventController = new InteractionEventController(
            $this->dailySummaryHandler->reveal(),
            $this->userProvider->reveal()
        );

        $payload = json_decode('{"type":"view_submission","team":{"id":"THW253RMX","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"unsupported_id":{"summary_block_input":{"type":"plain_text_input","value":"sdf"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}', true);
        $this->interactionEvent->getPayload()->shouldBeCalled()->willReturn($payload);

        $this->userProvider->getDbUserBySlackId('UHW253RU1')
                           ->shouldBeCalled()
                           ->willReturn($this->user->reveal());

        $response = $interactionEventController($this->interactionEvent->reveal());
        $this->assertEquals(Response::HTTP_PRECONDITION_FAILED, $response->getStatusCode());
    }

    public function testUnregisteredUser()
    {
        $interactionEventController = new InteractionEventController(
            $this->dailySummaryHandler->reveal(),
            $this->userProvider->reveal()
        );

        $payload = json_decode('{"type":"view_submission","team":{"id":"THW253RMX","domain":"diltheymedia"},"user":{"id":"UHW253RU1","username":"ndilthey","name":"ndilthey","team_id":"THW253RMX"},"api_app_id":"ALTNUDXE0","token":"QbBtIJowqMvCl1NcYexCG7rN","trigger_id":"1359827201715.608073127745.2f087f09e939a0cc814be02608b7dc6c","view":{"id":"V01AKJ7HBSN","team_id":"THW253RMX","type":"modal","blocks":[{"type":"input","block_id":"daily_summary_block","label":{"type":"plain_text","text":"Tasks","emoji":true},"optional":false,"element":{"type":"plain_text_input","action_id":"summary_block_input","placeholder":{"type":"plain_text","text":"Add the tasks your completed here...","emoji":true},"multiline":true}},{"type":"input","block_id":"mail_block","label":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"optional":false,"element":{"type":"static_select","action_id":"mail_choice","placeholder":{"type":"plain_text","text":"Send E-Mail?","emoji":true},"initial_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},"options":[{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"},{"text":{"type":"plain_text","text":":x: no","emoji":true},"value":"false"}]}}],"private_metadata":"","callback_id":"ml_ds","state":{"values":{"unsupported_id":{"summary_block_input":{"type":"plain_text_input","value":"sdf"}},"mail_block":{"mail_choice":{"type":"static_select","selected_option":{"text":{"type":"plain_text","text":":heavy_check_mark: yes","emoji":true},"value":"true"}}}}},"hash":"1599938164.Fi7N1PWX","title":{"type":"plain_text","text":"Daily Summary","emoji":true},"clear_on_close":false,"notify_on_close":false,"close":null,"submit":{"type":"plain_text","text":"Send","emoji":true},"previous_view_id":null,"root_view_id":"V01AKJ7HBSN","app_id":"ALTNUDXE0","external_id":"","app_installed_team_id":"THW253RMX","bot_id":"BLU73PDGQ"},"response_urls":[]}', true);
        $this->interactionEvent->getPayload()->shouldBeCalled()->willReturn($payload);

        $this->userProvider->getDbUserBySlackId('UHW253RU1')
                           ->shouldBeCalled()
                           ->willThrow(MessageHandlerException::class);

        $response = $interactionEventController($this->interactionEvent->reveal());
        $this->assertEquals(Response::HTTP_PRECONDITION_FAILED, $response->getStatusCode());
    }
}
