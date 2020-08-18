<?php


namespace App\Handler\MessageController\Slack;

use App\Entity\Slack\SlackBotMessage;
use App\Entity\Slack\SlackMessage;
use App\Exceptions\MessageHandlerException;use App\Handler\MessageHandler\Slack\BotMessageHandler;
use App\Services\JsonBodyTransform;
use App\Slack\SlackClient;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class BotMessageController implements MessageHandlerInterface
{
    private JsonBodyTransform $jsonBodyTransform;
    private RequestStack $requestStack;
    private BotMessageHandler $botMessageHandler;
    private SlackClient $client;

    public function __construct(
        JsonBodyTransform $jsonBodyTransform,
        RequestStack $requestStack,
        BotMessageHandler $botMessageHandler,
        SlackClient $client
    )
    {
        $this->jsonBodyTransform = $jsonBodyTransform;
        $this->requestStack = $requestStack;
        $this->botMessageHandler = $botMessageHandler;
        $this->client = $client;
    }

    public function __invoke(SlackBotMessage $slackBotMessage)
    {
        try {
            $request = $this->jsonBodyTransform->transformJsonBody(
                $this->requestStack->getCurrentRequest()
            );

            //Only needed for setting up a new slack bot event request url for slack
            $challenge = $request->request->get('challenge');
            if ($challenge) {
                return new Response($challenge, 200);
            }
        } catch (\Exception $e) {
            $this->client->sendEphemeral([
                'channel' => $slackBotMessage->getChannelId(),
                'user' => $slackBotMessage->getUserId(),
                'text' => $slackBotMessage->getText()
            ]);
            return new Response($e->getMessage(), $e->getCode());
        }

        $slackUserId = $slackBotMessage->getEvent()->getUser();
        $event = $request->request->get('event');
        try {
            $m = $this->botMessageHandler->parseEventType($event);
        } catch (MessageHandlerException $e) {
            $m = new SlackMessage();
            $m->addTextSection($e->getMessage());
        }

        $this->client->sendWebhook([
            'channel' => $slackBotMessage->getEvent()->getChannel(),
            'text' => $m->getBlockText(0),
            'user' => $slackUserId
        ]);

        return null;
    }
}
