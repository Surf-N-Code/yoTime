<?php

namespace App\Entity\Slack;

class SlackBotEvent
{
    //@TODO phpdoc anpassen für alle private properties
    private string $type;

    private string $text;

    private string $user;

    private $ts;

    private $channel;

    private $eventTs;

    private $clientMsgId;

    /**
     * @return string
     */
    public function getClientMsgId(): string
    {
        return $this->clientMsgId;
    }

    /**
     * @param string $clientMsgId
     */
    public function setClientMsgId(string $clientMsgId): void
    {
        $this->clientMsgId = $clientMsgId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getTs(): string
    {
        return $this->ts;
    }

    /**
     * @param string $ts
     */
    public function setTs(string $ts): void
    {
        $this->ts = $ts;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getEventTs(): string
    {
        return $this->eventTs;
    }

    /**
     * @param string $eventTs
     */
    public function setEventTs(string $eventTs): void
    {
        $this->eventTs = $eventTs;

    }
}
