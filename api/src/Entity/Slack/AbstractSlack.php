<?php

namespace App\Entity\Slack;

class AbstractSlack
{

    private $userId;

    /**
     * @var string Slack user name the message was received from
     */
    private $userName;

    /**
     * @var string Slack team ID the message was received from
     */
    private $teamId;

    /**
     * @var string Slack channel ID the message was received from
     */
    private $channelId;

    /**
     * @var string Slack channel name the message was received from
     */
    private $channelName;

    /**
     * @var string Slack Team ID the message was received from
     */
    private $command;

    /**
     * @var string Slack response url that can be used to respond to the command
     */
    private $responseUrl;

    /**
     * @var string Slack trigger id needed to open a dialog with the user
     */
    private $triggerId;

    /**
     * @var string Slack text sent with the command
     */
    private $text;

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }

    /**
     * @param string $teamId
     */
    public function setTeamId(string $teamId): void
    {
        $this->teamId = $teamId;
    }

    /**
     * @return string
     */
    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /**
     * @param string $channelId
     */
    public function setChannelId(string $channelId): void
    {
        $this->channelId = $channelId;
    }

    /**
     * @return string
     */
    public function getChannelName(): string
    {
        return $this->channelName;
    }

    /**
     * @param string $channelName
     */
    public function setChannelName(string $channelName): void
    {
        $this->channelName = $channelName;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getResponseUrl(): string
    {
        return $this->responseUrl;
    }

    /**
     * @param string $responseUrl
     */
    public function setResponseUrl(string $responseUrl): void
    {
        $this->responseUrl = $responseUrl;
    }

    /**
     * @return string
     */
    public function getTriggerId(): string
    {
        return $this->triggerId;
    }

    /**
     * @param string $triggerId
     */
    public function setTriggerId(string $triggerId): void
    {
        $this->triggerId = $triggerId;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }
}
