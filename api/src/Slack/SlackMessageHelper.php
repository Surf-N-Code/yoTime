<?php


namespace App\Slack;


use App\Entity\Slack\SlackMessage;
use App\Entity\Timer;
use App\Entity\TimerType;use App\Entity\User;use App\Services\Time;

class SlackMessageHelper
{
    private Time $time;

    public function __construct(Time $time){
        $this->time = $time;
    }
    public function createSlackMessage(): SlackMessage
    {
        return new SlackMessage();
    }

    public function addTextSection(string $text, SlackMessage $m): SlackMessage
    {
        $m->addTextSection($text);
        return $m;
    }

    public function addDivider(SlackMessage $m): SlackMessage
    {
        $m->addDivider();
        return $m;
    }
}
