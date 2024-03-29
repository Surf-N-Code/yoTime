<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Table(name="`user`")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ApiFilter(OrderFilter::class, properties={"dailySumary.date": "DESC"})
 * @UniqueEntity("email")
 * @ORM\EntityListeners({"App\Doctrine\SetUserListener"})
 */
class User implements UserInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"Timer"})
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     * @Assert\Unique(groups={"duplicate_check"})
     * @Assert\Email()
     */
    private ?string $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Groups({"Timer", "dailies"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @Groups({"Timer", "dailies"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @Groups({"Timer"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slackUserId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $personioEmployeeId;

    /**
     * @Groups({"Timer"})
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $timezone;

    /**
     * @Groups({"Timer"})
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private $tzOffset;

    /**
     * @Groups({"Timer"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $displayName;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

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
     * @ApiSubresource
     */
    private $dailySummary;

    /**
     * @ORM\OneToMany(targetEntity=Timer::class, mappedBy="user", orphanRemoval=true)
     */
    private $timers;

    public function __construct()
    {
        $this->projects    = new ArrayCollection();
        $this->tasks       = new ArrayCollection();
        $this->clients     = new ArrayCollection();
        $this->slackTeams = new ArrayCollection();
        $this->dailySummary = new ArrayCollection();
        $this->timers = new ArrayCollection();
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

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function setUsername($email): self
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

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

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

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return Collection|Timer[]
     */
    public function getTimers(): Collection
    {
        return $this->timers;
    }

    public function addTimer(Timer $timer): self
    {
        if (!$this->timers->contains($timer)) {
            $this->timers[] = $timer;
            $timer->setUser($this);
        }

        return $this;
    }

    public function removeTimer(Timer $timer): self
    {
        if ($this->timers->contains($timer)) {
            $this->timers->removeElement($timer);
            // set the owning side to null (unless already changed)
            if ($timer->getUser() === $this) {
                $timer->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        return 'salt';
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive($isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getPersonioEmployeeId()
    {
        return $this->personioEmployeeId;
    }

    public function setPersonioEmployeeId($personioEmployeeId): void
    {
        $this->personioEmployeeId = $personioEmployeeId;
    }
}
