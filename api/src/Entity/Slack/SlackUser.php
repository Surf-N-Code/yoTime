<?php

namespace App\Entity\Slack;

use App\Entity\Slack\SlackUserProfile;

class SlackUser
{
    /**
     * @var string
     */
    protected $color;

    /**
     * @var bool
     */
    protected $deleted;

    /**
     * @var bool
     */
    protected $has2fa;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var bool
     */
    protected $isAdmin;

    /**
     * @var bool
     */
    protected $isAppUser;

    /**
     * @var bool
     */
    protected $isBot;

    /**
     * @var bool
     */
    protected $isOwner;

    /**
     * @var bool
     */
    protected $isPrimaryOwner;

    /**
     * @var bool
     */
    protected $isRestricted;

    /**
     * @var bool
     */
    protected $isUltraRestricted;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $presence;

    /**
     * @var SlackUserProfile
     */
    protected $profile;

    /**
     * @var string
     */
    protected $realName;

    /**
     * @var string
     */
    protected $teamId;

    /**
     * @var string
     */
    protected $tz;

    /**
     * @var string
     */
    protected $tzLabel;

    /**
     * @var integer
     */
    protected $tzOffset;

    /**
     * @var integer
     */
    protected $updated;

    /**
     * @return string
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return self
     */
    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     *
     * @return self
     */
    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return bool
     */
    public function getHas2fa(): ?bool
    {
        return $this->has2fa;
    }

    /**
     * @param bool $has2fa
     *
     * @return self
     */
    public function setHas2fa(?bool $has2fa): self
    {
        $this->has2fa = $has2fa;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return self
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    /**
     * @param bool $isAdmin
     *
     * @return self
     */
    public function setIsAdmin(?bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAppUser(): ?bool
    {
        return $this->isAppUser;
    }

    /**
     * @param bool $isAppUser
     *
     * @return self
     */
    public function setIsAppUser(?bool $isAppUser): self
    {
        $this->isAppUser = $isAppUser;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsBot(): ?bool
    {
        return $this->isBot;
    }

    /**
     * @param bool $isBot
     *
     * @return self
     */
    public function setIsBot(?bool $isBot): self
    {
        $this->isBot = $isBot;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsOwner(): ?bool
    {
        return $this->isOwner;
    }

    /**
     * @param bool $isOwner
     *
     * @return self
     */
    public function setIsOwner(?bool $isOwner): self
    {
        $this->isOwner = $isOwner;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPrimaryOwner(): ?bool
    {
        return $this->isPrimaryOwner;
    }

    /**
     * @param bool $isPrimaryOwner
     *
     * @return self
     */
    public function setIsPrimaryOwner(?bool $isPrimaryOwner): self
    {
        $this->isPrimaryOwner = $isPrimaryOwner;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsRestricted(): ?bool
    {
        return $this->isRestricted;
    }

    /**
     * @param bool $isRestricted
     *
     * @return self
     */
    public function setIsRestricted(?bool $isRestricted): self
    {
        $this->isRestricted = $isRestricted;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsUltraRestricted(): ?bool
    {
        return $this->isUltraRestricted;
    }

    /**
     * @param bool $isUltraRestricted
     *
     * @return self
     */
    public function setIsUltraRestricted(?bool $isUltraRestricted): self
    {
        $this->isUltraRestricted = $isUltraRestricted;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return self
     */
    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPresence(): ?string
    {
        return $this->presence;
    }

    /**
     * @param string $presence
     *
     * @return self
     */
    public function setPresence(?string $presence): self
    {
        $this->presence = $presence;

        return $this;
    }

    /**
     * @return SlackUserProfile
     */
    public function getProfile(): ?SlackUserProfile
    {
        return $this->profile;
    }

    /**
     * @param SlackUserProfile $profile
     *
     * @return self
     */
    public function setProfile(?SlackUserProfile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return string
     */
    public function getRealName(): ?string
    {
        return $this->realName;
    }

    /**
     * @param string $realName
     *
     * @return self
     */
    public function setRealName(?string $realName): self
    {
        $this->realName = $realName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTeamId(): ?string
    {
        return $this->teamId;
    }

    /**
     * @param string $teamId
     *
     * @return self
     */
    public function setTeamId(?string $teamId): self
    {
        $this->teamId = $teamId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTz(): ?string
    {
        return $this->tz;
    }

    /**
     * @param string $tz
     *
     * @return self
     */
    public function setTz(?string $tz): self
    {
        $this->tz = $tz;

        return $this;
    }

    /**
     * @return string
     */
    public function getTzLabel(): ?string
    {
        return $this->tzLabel;
    }

    /**
     * @param string $tzLabel
     *
     * @return self
     */
    public function setTzLabel(?string $tzLabel): self
    {
        $this->tzLabel = $tzLabel;

        return $this;
    }

    /**
     * @return float
     */
    public function getTzOffset(): ?float
    {
        return $this->tzOffset;
    }

    /**
     * @param float $tzOffset
     *
     * @return self
     */
    public function setTzOffset(?float $tzOffset): self
    {
        $this->tzOffset = $tzOffset;

        return $this;
    }

    /**
     * @return float
     */
    public function getUpdated(): ?float
    {
        return $this->updated;
    }

    /**
     * @param float $updated
     *
     * @return self
     */
    public function setUpdated(?float $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
