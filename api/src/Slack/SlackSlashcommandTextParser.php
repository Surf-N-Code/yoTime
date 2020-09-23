<?php


namespace App\Slack;

use App\Exceptions\SlackException;

class SlackSlashcommandTextParser
{
    public function parseCommandTextForTimeConstraint($commandText)
    {
        preg_match('/day|week|month|year|all/', strtolower($commandText), $matches);
        if (!isset($matches[0])) {
            throw new SlackException('Please provide a time period for your report. Available periods are: `day`, `week`, `month`, `year`, `all`', 400);
        }
        return $matches[0];
    }
}
