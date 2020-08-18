<?php


namespace App\Entity\Slack;


class SlackMessage
{
    private $channel;
    private $user;
    private $blocks = [];

    public function __construct($chan = null, $user = null)
    {
        $this->channel = $chan;
        $this->user    = $user;
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function getBlock()
    {
        return $this->getBlock()[0];
    }

    public function addTextSection($text = null): self
    {
        $this->blocks[] = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => $text
            ]
        ];
        return $this;
    }

    public function addContext($text): void
    {
        $this->blocks[] = [
            'type' => 'context',
            'elements' => [
                [
                    'type' => 'mrkdwn',
                    'text' => $text
                ]
            ]
        ];
    }

    public function addDivider()
    {
        $this->blocks[] = [
            'type' => 'divider',
        ];
    }

    public function getBlockTexts()
    {
        $blockText = [];
        foreach ($this->blocks as $index => $block) {
            $blockText[] = $block['text']['text'];
        }
        return $blockText;
    }

    /**
     * @param int $blockId
     *
     * @return string
     */
    public function getBlockText(int $blockId): string
    {
        return $this->blocks[$blockId]['text']['text'];
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param mixed $channel
     */
    public function setChannel($channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }
}
