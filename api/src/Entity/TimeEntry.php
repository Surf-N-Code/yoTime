<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     messenger=true,
 * )
 * @ORM\Entity(repositoryClass="App\Repository\TimeEntryRepository")
 */
class TimeEntry
{
    /**
     * @var int The id of this timer entry.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \DateTime The start time of this entry in datetime format.
     *
     * @Assert\DateTime())
     * @ORM\Column(type="datetime")
     */
    private $dateStart;

    /**
     * @var \DateTime|null The end time of this entry in datetime format.
     *
     * @Assert\DateTime())
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEnd;

    /**
     * @var Project|null The project this time entry belongs to.
     *
     * @Groups({"TimeEntry"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="timeEntries")
     */
    private $project;

    /**
     * @var Task|null The task of this time entry belongs to.
     *
     * @Groups({"TimeEntry"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Task", inversedBy="timeEntries", cascade={"persist"})
     */
    private $task;

    /**
     * @var User The user this time entry belongs to.
     *
     * @Assert\NotBlank()
     * @Groups({"TimeEntry"})
     * @ORM\ManyToOne(targetEntity="User", inversedBy="timeEntries")
     */
    private $user;

    /**
     * @var string The type of the time entry.
     *
     * @Groups({"TimeEntry"})
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $timerType;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateStart(): \DateTime
    {
        return $this->dateStart;
    }

    public function getDateStartString(): string
    {
        return $this->dateStart->format('Y-m-d');
    }

    public function getDateTimeStartString(): string
    {
        return $this->dateStart->format('Y-m-d H:i:s');
    }

    public function setDateStart(?\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTimerType(): string
    {
        return $this->timerType;
    }

    public function setTimerType($timerType): void
    {
        $this->timerType = $timerType;
    }

    public function getTimeSpentOnFinishedTimer()
    {
        if (!$this->getDateEnd()) {
            throw new \InvalidArgumentException('Time Entry does not have a date end set yet');
        }
        return abs($this->getDateEnd()->getTimestamp() - $this->getDateStart()->getTimestamp());
    }
}
