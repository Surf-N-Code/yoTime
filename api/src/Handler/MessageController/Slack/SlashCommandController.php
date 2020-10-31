<?php


namespace App\Handler\MessageController\Slack;

use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlashCommand;
use App\Exceptions\DatabaseException;
use App\Exceptions\SlashCommandException;
use App\Exceptions\MessageHandlerException;
use App\Handler\MessageHandler\Slack\SlashCommandHandler;
use App\Slack\SlackClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SlashCommandController implements MessageHandlerInterface
{

    private SlashCommandHandler $slashCommandHandler;

    private SlackClient $slackClient;

    public function __construct(SlashCommandHandler $slashCommandHandler, SlackClient $slackClient)
    {
        $this->slashCommandHandler = $slashCommandHandler;
        $this->slackClient = $slackClient;
    }

    public function __invoke(SlashCommand $command): Response
    {
        try {
            $statusCode = $this->slashCommandHandler->getSlashCommandToExecute($command);
            return new Response('', $statusCode);
        } catch (SlashCommandException | MessageHandlerException | DatabaseException $e) {
            $m = new SlackMessage();
            $m->addTextSection($e->getMessage());

            $this->slackClient->slackApiCall('POST', 'chat.postEphemeral', [
                'channel' => $command->getChannelId(),
                'user' => $command->getUserId(),
                'text' => $m->getBlockText(0),
                'blocks' => $m->getBlocks()
            ]);
            return new Response($e->getMessage(), $e->getCode());
        }
    }
}
