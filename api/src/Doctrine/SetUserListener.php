<?php


namespace App\Doctrine;


use App\Entity\DailySummary;
use App\Entity\Task;
use App\Entity\Timer;
use Symfony\Component\Security\Core\Security;

class SetUserListener
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist($obj)
    {
        if (!is_a($obj, Timer::class) &&
            !is_a($obj, DailySummary::class) &&
            !is_a($obj, Task::class)
        ) {
            return;
        }
        if ($obj->getUser()) {
            return;
        }
        if ($this->security->getUser()) {
            $obj->setUser($this->security->getUser());
        }
    }
}
