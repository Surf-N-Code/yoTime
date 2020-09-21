<?php


namespace App\Handler\MessageHandler\Slack;


use App\Entity\Slack\SlackMessage;use App\Entity\Slack\SlashCommand;

class UserHelpHandler
{
    public function showUserHelp(SlashCommand $command)
    {
        $m = new SlackMessage();

        $m->addTextSection(sprintf('Hey there, <@%s> I\'m here to help you :simple_smile:', $command->getUserId()));
        $m->addDivider();
        $m->addTextSection('Here is a couple of ways you can interact with me.');
        $m->addTextSection(':wave: You can sign in for the day by mentioning me `hi @TimeMe` in the hello-bye channel.');
        $m->addTextSection(':runner: To sign out `bye @TimeMe`');
        $m->addTextSection(':point_up_2: `/late_hi` allows you to sign in at a later point in time if you have forgotten to sign in when you started working');
        $m->addTextSection(':sleeping: `/break` will start tracking your break time');
        $m->addTextSection(':clock1: `/end_break` will stop your break timer');
        $m->addTextSection(':dash: `/late_break` this command allows you to add a break duration if you have forgotten to track your break time using /break and /end_break');
        $m->addTextSection(':white_check_mark: `/work` will start a work timer for you. You will only need this if you do *not* use the /hi and /bye commands.');
        $m->addTextSection(':clock9: `/end_work` stops your work timer.');
        $m->addTextSection(':top: `/ds` allows you to add a summary of what you have achieved today');
//        $m->addTextSection(':passport_control: `/timereport` provides you with a summary of your time entries from the chosen period. Possible periods are day, week, month, year, all');
//        $m->addDivider();
//        $m->addTextSection('Admins can execute the following reporting commands');
//        $m->addTextSection('`/admin_workreport` retrieves the total time worked and on break either per person or for all members of your slack team');
//        $m->addTextSection('`/admin_ds` retrieves the daily summaries either per person or for all members of your slack team');

        return $m;
    }
}
