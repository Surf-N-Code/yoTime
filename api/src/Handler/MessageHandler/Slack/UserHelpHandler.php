<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlackMessage;use App\Entity\Slack\SlashCommand;
use App\Handler\MessageController\Slack\SlashCommandController;

class UserHelpHandler
{
    public function showUserHelp(SlashCommand $command)
    {
        $m = new SlackMessage();

        $m->addTextSection(sprintf('Hey there, <@%s> I\'m here to help you :simple_smile:', $command->getUserId()));
        $m->addDivider();
        $m->addTextSection(sprintf('Here is a couple of ways you can interact with me.'));
        $m->addTextSection(sprintf(':rocket: to get going, register an account with YoTime use: `%s`.', SlashCommandHandler::REGISTER));
        $m->addDivider();
        $m->addTextSection(sprintf(':wave: You can sign in for the day by mentioning me with the following text: `hey @TimeMe` in the hello-bye channel.'));
        $m->addTextSection(sprintf(':runner: To sign out: `bye @TimeMe`'));
        $m->addTextSection(sprintf(':point_up_2: `%s` allows you to sign in at a later point in time if you have forgotten to sign in when you started working', SlashCommandHandler::LATE_HI));
        $m->addTextSection(sprintf(':clock1: `%s` will stop your currently running timer and notify of the timer duration', SlashCommandHandler::STOP_TIMER));
        $m->addTextSection(sprintf(':dash: `%s` this command allows you to add a break duration if you have forgotten to track your break time using `%s`', SlashCommandHandler::LATE_BREAK, SlashCommandHandler::START_BREAK));
        $m->addTextSection(sprintf(':white_check_mark: `%s` will start a work timer for you. You will only need this if you do *not* signin daily via the hello-bye channel.', SlashCommandHandler::START_WORK));
        $m->addTextSection(sprintf(':top: `%s` allows you to add a summary of what you did to make the world a better place today :heart:', SlashCommandHandler::DAILY_SUMMARY));
        $m->addTextSection(sprintf(':passport_control: `%s` provides you with a summary of your time entries from the chosen period. Possible periods are day, week, month, year, all', SlashCommandHandler::REPORT));
        $m->addDivider();
//        $m->addTextSection('Admins can execute the following reporting commands');
//        $m->addTextSection('`/admin_workreport` retrieves the total time worked and on break either per person or for all members of your slack team');
//        $m->addTextSection('`/admin_ds` retrieves the daily summaries either per person or for all members of your slack team');

        return $m;
    }
}
