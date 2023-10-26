<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $names = ["ROLE_LIST_VIEW" => ["ROLE_LIST_VIEW"], "ROLE_ADD" => ["ROLE_ADD"],
         "ROLE_EDIT" => ["ROLE_LIST_VIEW","ROLE_EDIT"], "ROLE_DELETE"  => ["ROLE_LIST_VIEW","ROLE_DELETE"]];
        $faker = Factory::create("ru_RU");
        $plaintextPassword = "Qwerty123";
        
        foreach ($names as $name => $roles){
            $user = new User();
            $user->setUsername($name);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);
            $user->setRoles($roles);
            $user->setFio($faker->name());
            $manager->persist($user);
        }

        $manager->flush();
        
    }
}
