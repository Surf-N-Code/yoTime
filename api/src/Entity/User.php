<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Table(name="`user`")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email")
 */
class User
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"TimeEntry"})
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @Groups({"TimeEntry"})
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $fullName;

    /**
     * @Groups({"TimeEntry"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slackUserId;

    /**
     * @ApiSubresource()
     * @ORM\OneToMany(targetEntity="App\Entity\TimeEntry", mappedBy="user")
     */
    private $timeEntries;

    /**
     * @Groups({"TimeEntry"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tz;

    /**
     * @Groups({"TimeEntry"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tzOffset;

    /**
     * @Groups({"TimeEntry"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $displayName;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Project", inversedBy="users")
     */
    private $projects;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="user", orphanRemoval=true)
     */
    private $tasks;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Client", inversedBy="users")
     */
    private $clients;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\SlackTeam", mappedBy="user", cascade={"persist"})
     */
    private $slackTeams;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $contractWorkHours;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DailySummary", mappedBy="user")
     */
    private $dailySummary;

    public function __construct()
    {
        $this->timeEntries = new ArrayCollection();
        $this->projects    = new ArrayCollection();
        $this->tasks       = new ArrayCollection();
        $this->clients     = new ArrayCollection();
        $this->slackTeams = new ArrayCollection();
        $this->dailySummary = new ArrayCollection();
    }

    //only needed for fixtures
    public function setId($id)
    {
        $this->id = $id;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSlackUserId(): ?string
    {
        return $this->slackUserId;
    }

    public function setSlackUserId($slackUserId): self
    {
        $this->slackUserId = $slackUserId;

        return $this;
    }

    /**
     * @return Collection|TimeEntry[]
     */
    public function getTimeEntries(): Collection
    {
        return $this->timeEntries;
    }

    public function startTimer(TimeEntry $timeEntry): self
    {
        if (!$this->timeEntries->contains($timeEntry)) {
            $this->timeEntries[] = $timeEntry;
            $timeEntry->setUser($this);
        }

        return $this;
    }

    public function removeTimeEntry(TimeEntry $timeEntry): self
    {
        if ($this->timeEntries->contains($timeEntry)) {
            $this->timeEntries->removeElement($timeEntry);
            // set the owning side to null (unless already changed)
            if ($timeEntry->getUser() === $this) {
                $timeEntry->setUser(null);
            }
        }

        return $this;
    }

    public function getTz(): ?string
    {
        return $this->tz;
    }

    public function setTz(?string $tz): self
    {
        $this->tz = $tz;

        return $this;
    }

    public function getTzOffset(): ?string
    {
        return $this->tzOffset;
    }

    public function setTzOffset(?string $tzOffset): self
    {
        $this->tzOffset = $tzOffset;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
        }

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            // set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Client[]
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->contains($client)) {
            $this->clients->removeElement($client);
        }

        return $this;
    }

    public function getSlackTeams(): Collection
    {
        return $this->slackTeams;
    }

    public function addSlackTeam(SlackTeam $slackTeam): self
    {
        if (!$this->slackTeams->contains($slackTeam)) {
            $this->slackTeams[] = $slackTeam;
            $slackTeam->addUser($this);
        }

        return $this;
    }

    public function removeSlackTeam(SlackTeam $slackTeam): self
    {
        if ($this->slackTeams->contains($slackTeam)) {
            $this->slackTeams->removeElement($slackTeam);
            $slackTeam->removeUser($this);
        }

        return $this;
    }

    public function getContractWorkHours()
    {
        return $this->contractWorkHours;
    }

    public function setContractWorkHours($contractWorkHours): void
    {
        $this->contractWorkHours = $contractWorkHours;
    }

    /**
     * @return Collection|DailySummary[]
     */
    public function getDailySummary(): Collection
    {
        return $this->dailySummary;
    }

    public function addDailySummary(DailySummary $dailySummary): self
    {
        if (!$this->dailySummary->contains($dailySummary)) {
            $this->dailySummary[] = $dailySummary;
            $dailySummary->setUser($this);
        }

        return $this;
    }

    public function removeDailySummary(DailySummary $dailySummary): self
    {
        if ($this->dailySummary->contains($dailySummary)) {
            $this->dailySummary->removeElement($dailySummary);
            // set the owning side to null (unless already changed)
            if ($dailySummary->getUser() === $this) {
                $dailySummary->setUser(null);
            }
        }

        return $this;
    }

    public function getFullName()
    {
        return $this->fullName;
    }

    public function setFullName($fullName): void
    {
        $this->fullName = $fullName;
    }
}
