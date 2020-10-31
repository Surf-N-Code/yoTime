<?php

namespace App\Fixtures;

use App\Entity\User;
use Faker\Generator;
use Faker\Provider\Base;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserProvider extends Base
{

    private $passwordEncoder;

    public function setPasswordEncoder(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function getHashedPassword($email, $password)
    {
        $user = new User();
        $user->setUsername($email);
        $user->setIsActive(1);
        $user->setTz('Europe/Amsterdam');
        $user->setTzOffset(120);
        return $this->passwordEncoder->encodePassword($user, $password);
    }
}
