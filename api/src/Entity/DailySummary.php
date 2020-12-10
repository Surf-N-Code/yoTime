<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ApiResource()
 * @ApiFilter(OrderFilter::class, properties={"date": { "nulls_comparison": OrderFilter::NULLS_LARGEST, "default_direction": "DESC" }})
 * @ORM\Entity(repositoryClass="App\Repository\DailySummaryRepository")
 * @ORM\EntityListeners({"App\Doctrine\SetUserListener"})
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="IDX_USER_DATE", columns={"user_id", "date"})})
 */
class DailySummary
{
    //* @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="IDX_USER_DATE", columns={"user_id", "date"})})

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $dailySummary;

    /**
     * @ORM\Column(type="date", nullable=false)
     */
    private $date;

    /**
     * @ORM\Column(type="float")
     */
    private $timeWorkedInS;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $timeBreakInS;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $startTime;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $endTime;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="dailySummary")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isEmailSent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isSyncedToPersonio;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDailySummary(): ?string
    {
        return $this->dailySummary;
    }

    public function setDailySummary(?string $dailySummary)
    {
        $this->dailySummary = $dailySummary;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date)
    {
        $this->date = $date;
    }

    public function getTimeWorkedInS(): ?int
    {
        return $this->timeWorkedInS;
    }

    public function setTimeWorkedInS(int $timeWorkedInS)
    {
        $this->timeWorkedInS = $timeWorkedInS;
    }

    public function getTimeBreakInS(): ?int
    {
        return $this->timeBreakInS;
    }

    public function setTimeBreakInS(?int $timeBreakInS)
    {
        $this->timeBreakInS = $timeBreakInS;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public  function getIsEmailSent(): bool
    {
        return $this->isEmailSent;
    }

    public function setIsEmailSent(bool $isEmailSent)
    {
        $this->isEmailSent = $isEmailSent;
        return $this;
    }

    public function getStartTime(): \DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): \DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getIsSyncedToPersonio(): ?bool
    {
        return $this->isSyncedToPersonio;
    }

    public function setIsSyncedToPersonio(bool $isSyncedToPersonio): void
    {
        $this->isSyncedToPersonio = $isSyncedToPersonio;
    }

}
