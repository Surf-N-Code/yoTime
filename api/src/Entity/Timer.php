<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TimerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ApiFilter(OrderFilter::class, properties={"dateEnd": { "nulls_comparison": OrderFilter::NULLS_LARGEST, "default_direction": "DESC" }})
 * @ORM\Entity(repositoryClass=TimerRepository::class)
 */
class Timer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEnd;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="timers")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity=Task::class, inversedBy="timers")
     */
    private $task;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="timers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $timerType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): self
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTimerType(): ?string
    {
        return $this->timerType;
    }

    public function setTimerType(string $timerType): self
    {
        $this->timerType = $timerType;

        return $this;
    }
}
