<?php


namespace App\Handler\MessageController\Slack;

use App\Entity\Slack\SlackInteractionEvent;
use App\Entity\Slack\SlackBotMessage;
use App\Entity\Slack\SlackMessage;
use App\Exceptions\MessageHandlerException;use App\Handler\MessageHandler\Slack\BotMessageHandler;
use App\Handler\MessageHandler\Slack\DailySummaryHandler;
use App\Handler\MessageHandler\Slack\InteractionEventHandler;
use App\Services\JsonBodyTransform;
use App\Slack\SlackClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class InteractionEventController implements MessageHandlerInterface
{
    private JsonBodyTransform $jsonBodyTransform;
    private RequestStack $requestStack;
    private SlackClient $slackClient;
    private InteractionEventHandler $interactionEventHandler;

    private DailySummaryHandler $dailySummaryHandler;

    public function __construct(
        JsonBodyTransform $jsonBodyTransform,
        RequestStack $requestStack,
        SlackClient $slackClient,
        InteractionEventHandler $interactionEventHandler,
        DailySummaryHandler $dailySummaryHandler
    )
    {
        $this->jsonBodyTransform = $jsonBodyTransform;
        $this->requestStack = $requestStack;
        $this->slackClient = $slackClient;
        $this->interactionEventHandler = $interactionEventHandler;
        $this->dailySummaryHandler = $dailySummaryHandler;
    }

    public function __invoke(SlackInteractionEvent $interactionEvent)
    {
        dump( $this->requestStack->getCurrentRequest());
        $payload = $interactionEvent->getPayload();
        dump($payload);
        try {
            $m = $this->interactionEventHandler->parseEventType($payload);
            $json = $this->dailySummaryHandler->getDailySummaryConfirmView($m);
            dump($json);
            return new JsonResponse($json, 200); //@todo change to 200
        } catch (\Exception $e) {
            dump($e);
            return new Response(400);
        }
    }
}
