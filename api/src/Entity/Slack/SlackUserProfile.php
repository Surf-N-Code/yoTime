<?php

namespace App\Entity\Slack;

class SlackUserProfile
{
    /**
     * @var bool
     */
    protected $alwaysActive;
    /**
     * @var string
     */
    protected $avatarHash;
    /**
     * @var string
     */
    protected $displayName;
    /**
     * @var string
     */
    protected $displayNameNormalized;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var mixed
     */
    protected $fields;
    /**
     * @var string
     */
    protected $firstName;
    /**
     * @var string
     */
    protected $guestChannels;
    /**
     * @var string
     */
    protected $image192;
    /**
     * @var string
     */
    protected $image24;
    /**
     * @var string
     */
    protected $image32;
    /**
     * @var string
     */
    protected $image48;
    /**
     * @var string
     */
    protected $image512;
    /**
     * @var string
     */
    protected $image72;
    /**
     * @var string
     */
    protected $imageOriginal;
    /**
     * @var string
     */
    protected $lastName;
    /**
     * @var string
     */
    protected $phone;
    /**
     * @var string
     */
    protected $realName;
    /**
     * @var string
     */
    protected $realNameNormalized;
    /**
     * @var string
     */
    protected $skype;
    /**
     * @var string
     */
    protected $statusEmoji;
    /**
     * @var int
     */
    protected $statusExpiration;
    /**
     * @var string
     */
    protected $statusText;
    /**
     * @var string
     */
    protected $statusTextCanonical;
    /**
     * @var string
     */
    protected $team;
    /**
     * @var string
     */
    protected $title;

    /**
     * @return bool
     */
    public function getAlwaysActive(): ?bool
    {
        return $this->alwaysActive;
    }

    /**
     * @param bool $alwaysActive
     *
     * @return self
     */
    public function setAlwaysActive(?bool $alwaysActive): self
    {
        $this->alwaysActive = $alwaysActive;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatarHash(): ?string
    {
        return $this->avatarHash;
    }

    /**
     * @param string $avatarHash
     *
     * @return self
     */
    public function setAvatarHash(?string $avatarHash): self
    {
        $this->avatarHash = $avatarHash;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     *
     * @return self
     */
    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayNameNormalized(): ?string
    {
        return $this->displayNameNormalized;
    }

    /**
     * @param string $displayNameNormalized
     *
     * @return self
     */
    public function setDisplayNameNormalized(?string $displayNameNormalized): self
    {
        $this->displayNameNormalized = $displayNameNormalized;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param mixed $fields
     *
     * @return self
     */
    public function setFields($fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return self
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getGuestChannels(): ?string
    {
        return $this->guestChannels;
    }

    /**
     * @param string $guestChannels
     *
     * @return self
     */
    public function setGuestChannels(?string $guestChannels): self
    {
        $this->guestChannels = $guestChannels;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage192(): ?string
    {
        return $this->image192;
    }

    /**
     * @param string $image192
     *
     * @return self
     */
    public function setImage192(?string $image192): self
    {
        $this->image192 = $image192;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage24(): ?string
    {
        return $this->image24;
    }

    /**
     * @param string $image24
     *
     * @return self
     */
    public function setImage24(?string $image24): self
    {
        $this->image24 = $image24;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage32(): ?string
    {
        return $this->image32;
    }

    /**
     * @param string $image32
     *
     * @return self
     */
    public function setImage32(?string $image32): self
    {
        $this->image32 = $image32;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage48(): ?string
    {
        return $this->image48;
    }

    /**
     * @param string $image48
     *
     * @return self
     */
    public function setImage48(?string $image48): self
    {
        $this->image48 = $image48;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage512(): ?string
    {
        return $this->image512;
    }

    /**
     * @param string $image512
     *
     * @return self
     */
    public function setImage512(?string $image512): self
    {
        $this->image512 = $image512;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage72(): ?string
    {
        return $this->image72;
    }

    /**
     * @param string $image72
     *
     * @return self
     */
    public function setImage72(?string $image72): self
    {
        $this->image72 = $image72;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageOriginal(): ?string
    {
        return $this->imageOriginal;
    }

    /**
     * @param string $imageOriginal
     *
     * @return self
     */
    public function setImageOriginal(?string $imageOriginal): self
    {
        $this->imageOriginal = $imageOriginal;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return self
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return self
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

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
    public function getRealNameNormalized(): ?string
    {
        return $this->realNameNormalized;
    }

    /**
     * @param string $realNameNormalized
     *
     * @return self
     */
    public function setRealNameNormalized(?string $realNameNormalized): self
    {
        $this->realNameNormalized = $realNameNormalized;

        return $this;
    }

    /**
     * @return string
     */
    public function getSkype(): ?string
    {
        return $this->skype;
    }

    /**
     * @param string $skype
     *
     * @return self
     */
    public function setSkype(?string $skype): self
    {
        $this->skype = $skype;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusEmoji(): ?string
    {
        return $this->statusEmoji;
    }

    /**
     * @param string $statusEmoji
     *
     * @return self
     */
    public function setStatusEmoji(?string $statusEmoji): self
    {
        $this->statusEmoji = $statusEmoji;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusExpiration(): ?int
    {
        return $this->statusExpiration;
    }

    /**
     * @param int $statusExpiration
     *
     * @return self
     */
    public function setStatusExpiration(?int $statusExpiration): self
    {
        $this->statusExpiration = $statusExpiration;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusText(): ?string
    {
        return $this->statusText;
    }

    /**
     * @param string $statusText
     *
     * @return self
     */
    public function setStatusText(?string $statusText): self
    {
        $this->statusText = $statusText;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusTextCanonical(): ?string
    {
        return $this->statusTextCanonical;
    }

    /**
     * @param string $statusTextCanonical
     *
     * @return self
     */
    public function setStatusTextCanonical(?string $statusTextCanonical): self
    {
        $this->statusTextCanonical = $statusTextCanonical;

        return $this;
    }

    /**
     * @return string
     */
    public function getTeam(): ?string
    {
        return $this->team;
    }

    /**
     * @param string $team
     *
     * @return self
     */
    public function setTeam(?string $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return self
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
