<?php


namespace App\Handler\MessageController\Slack;

use App\Entity\Slack\SlackBotMessage;
use App\Entity\Slack\SlackMessage;
use App\Exceptions\MessageHandlerException;use App\Handler\MessageHandler\Slack\BotMessageHandler;
use App\Services\JsonBodyTransform;
use App\Slack\SlackClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class BotMessageController implements MessageHandlerInterface
{
    private JsonBodyTransform $jsonBodyTransform;
    private RequestStack $requestStack;
    private BotMessageHandler $botMessageHandler;
    private SlackClient $slackClient;

    private LoggerInterface $logger;

    public function __construct(
        JsonBodyTransform $jsonBodyTransform,
        RequestStack $requestStack,
        BotMessageHandler $botMessageHandler,
        SlackClient $slackClient,
        LoggerInterface $logger
    )
    {
        $this->jsonBodyTransform = $jsonBodyTransform;
        $this->requestStack = $requestStack;
        $this->botMessageHandler = $botMessageHandler;
        $this->slackClient = $slackClient;
        $this->logger = $logger;
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
            $this->slackClient->slackApiCall('POST', 'chat.postEphemeral', [
                'channel' => $slackBotMessage->getChannelId(),
                'user' => $slackBotMessage->getUserId(),
                'text' => 'Sorry, something went wrong :('
            ]);
            $this->logger->error(sprintf('Could not parse JSON coming from slack bot event with message: %s', $e->getMessage()));
            return new Response($e->getMessage(), $e->getCode());
        }

        $slackUserId = $slackBotMessage->getEvent()->getUser();
        $event = $request->request->get('event');
        try {
            $m = $this->botMessageHandler->parseEventType($event);

            $this->sendEphemeral($slackBotMessage, $slackUserId, $m);
            return new Response('', 201);
        } catch (MessageHandlerException $e) {
            $m = new SlackMessage();
            $m->addTextSection($e->getMessage());

            $this->sendEphemeral($slackBotMessage, $slackUserId, $m);
            return new Response($e->getMessage(), Response::HTTP_OK);
        }
    }

    private function sendEphemeral(SlackBotMessage $slackBotMessage, string $slackUserId, SlackMessage $m)
    {
        $this->slackClient->slackApiCall('POST', 'chat.postEphemeral', [
            'channel' => $slackBotMessage->getEvent()->getChannel(),
            'user' => $slackUserId,
            'text' => $m->getBlockText(0),
            'blocks' => $m->getBlocks()
        ]);
    }
}
