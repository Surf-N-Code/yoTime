<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\DailySummaryRepository")
 */
class DailySummary
{
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
     * @ORM\Column(type="date")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="dailySummary")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $isEmailSent;

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

    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    public  function getIsEmailSent(){
        return $this->isEmailSent;
    }

    public function setIsEmailSent($isEmailSent){
        $this->isEmailSent = $isEmailSent;
        return $this;
    }
}