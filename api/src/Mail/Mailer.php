<?php


namespace App\Mail;


use App\Entity\User;use App\Exceptions\MessageHandlerException;use App\Services\Time;use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class Mailer {

    private $mailer;
    private LoggerInterface $logger;
    private Time $time;

    public function __construct(
        MailerInterface $mailer,
        LoggerInterface $logger,
        Time $time
    )
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->time = $time;
    }

    public function send($from, $to, $subject, $content): void
    {
        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->html($content);

        try{
            $this->mailer->send($email);
        } catch(TransportExceptionInterface $e) {
            $this->logger->error('Error sending daily summary email with message: '.$e->getMessage());
            throw new MessageHandlerException('Ups an error occured sending your daily saummary via email to your boss. Please try again through the web frontend.');
        }
    }

    public function sendDAilySummaryMail(int $timeOnBreak, int $timeOnWork, User $user, string $summary)
    {
        $formattedTimeOnBreak = $this->time->formatSecondsAsHoursAndMinutes($timeOnBreak);
        $ormattedTimeOnWork = $this->time->formatSecondsAsHoursAndMinutes($timeOnWork - $timeOnBreak);

        $subject = sprintf('Daily Summary of %s', $user->getFullName());
        $content = sprintf('<strong>%s</strong><br>Work: %s<br>Break: %s<br><pre>%s</pre>', $user->getFullName(), $ormattedTimeOnWork, $formattedTimeOnBreak, $summary);
        $sender = new Address('yoTime@yotime.io', 'yoTime');
        $recipient = new Address('ndilthey@gmail.com', 'Norman Dilthey
        ');
        $envelope = new Envelope($sender, [$recipient]);
        $rawMessage = new RawMessage($content);
        $this->mailer->send($rawMessage, $envelope);
    }
}
