<?php


namespace App\Handler\MessageController\Slack;

use App\Entity\Slack\SlackMessage;
use App\Entity\Slack\SlashCommand;
use App\Exceptions\DatabaseException;
use App\Exceptions\SlashCommandException;
use App\Exceptions\MessageHandlerException;
use App\Handler\MessageHandler\Slack\SlashCommandHandler;
use App\Slack\SlackClient;
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

    public function __invoke(SlashCommand $command): void
    {
        try {
            $m = $this->slashCommandHandler->getSlashCommandToExecute($command);
        } catch (SlashCommandException | MessageHandlerException | DatabaseException $e) {
            $m = new SlackMessage();
            $m->addTextSection($e->getMessage());
        }

        $this->slackClient->sendWebhook([
            'response_url' => $command->getResponseUrl(),
            'blocks' => $m->getBlocks()
        ]);
    }
}
