<?php

namespace App\DataFixtures;

use App\Entity\Roles;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;
    public function __construct( UserPasswordEncoderInterface $encoder)
    {
       $this->encoder=$encoder;

    }

    public function load(ObjectManager $manager)
    {
        $role1= new Roles();
        $role1->setLibelle("ROLE_SUPER_ADMIN");
        $manager->persist($role1);
        
        $role2= new Roles();
        $role2->setLibelle("ROLE_ADMIN");
        $manager->persist($role2);

        $role3= new Roles();
        $role3->setLibelle("ROLE_USER");
        $manager->persist($role3);

        $role4= new Roles();
        $role4->setLibelle("ROLE_USER_PARTENAIRE");
        $manager->persist($role4);

        $role5= new Roles();
        $role5->setLibelle("ROLE_ADMIN_PARTENAIRE");
        $manager->persist($role5);

        $role6= new Roles();
        $role6->setLibelle("ROLE_PARTENAIRE");
        $manager->persist($role6);

        $user = new User();
    
        $user->setUsername("ADMIN_SYSTEM");
        $user->setPassword( $this->encoder->encodePassword($user, "passe"));
        $user->setNom("Nom");
        $user->setisActive(true);
        $user->setRole($role1);
        $manager->persist($user);
        $manager->flush();
        
        $user = new User();
    
        $user->setUsername("ADMIN");
        $user->setPassword( $this->encoder->encodePassword($user, "passe"));
        $user->setNom("Nom");
        $user->setisActive(true);
        $user->setRole($role2);
        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setUsername("USER");
        $user->setPassword( $this->encoder->encodePassword($user, "passe"));
        $user->setNom("Nom");
        $user->setisActive(true);
        $user->setRole($role3);
        $manager->persist($user);
        $manager->flush();

       
        $manager->persist($user);
        $manager->flush();

    
    }
}
