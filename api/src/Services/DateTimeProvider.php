<?php


namespace App\Services;


use App\Entity\User;

class DateTimeProvider
{
    public function getNow(): \DateTime
    {
        return new \DateTime('now');
    }

    public function convertToLocalUserTime(\DateTime $dateTimeUser, User $user): \DateTime
    {
        if (0 === $timezoneOffset = $user->getTzOffset()) {
            return (new \DateTime())->setTimestamp($dateTimeUser->getTimestamp());
        }

        return $this->applyOffset($dateTimeUser, $timezoneOffset);
    }

    public function applyOffset(\DateTime $dateTime, int $timezoneOffset): \DateTime
    {
        if ($timezoneOffset < 0) {
            $modify = sprintf('- %d seconds', abs($timezoneOffset));
        } else {
            $modify = sprintf('+ %d seconds', $timezoneOffset);
        }

        return $dateTime->modify($modify);
    }

    public function getLocalUserTime(User $user): \DateTime
    {
        return $this->convertToLocalUserTime(
            $this->getNow(),
            $user
        );
    }
    public function getUserLocalDateTime(User $user)
    {
        $myDateTime = new \DateTime('now', new \DateTimeZone('GMT'));

        $myInterval = \DateInterval::createFromDateString('0 seconds');
        if ($user->getTzOffset()) {
            $myInterval = \DateInterval::createFromDateString($user->getTzOffset() . 'seconds');
        }

        $myDateTime->add($myInterval);

        return $myDateTime;
    }
}
