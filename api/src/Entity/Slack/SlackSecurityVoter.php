<?php


namespace App\Entity\Slack;


class SlackSecurityVoter
{
    const SECURITY_ENABLED_ENDPOINTS = [
        SlashCommand::ENDPOINT_PATH,
        SlackBotMessage::ENDPOINT_PATH,
        SlackInteractionEvent::ENDPOINT_PATH
    ];
}
