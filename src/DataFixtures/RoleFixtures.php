<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $role_names = ["ROLE_LIST_VIEW", "ROLE_ADD", "ROLE_EDIT", "ROLE_DELETE"];
        // $role_name = "ROLE_LIST_VIEW";
        foreach ($role_names as $role_name){
            $role = new Role();
            $role->setRoleName($role_name);
            $manager->persist($role);
            
        }
        $manager->flush();
    }
}
