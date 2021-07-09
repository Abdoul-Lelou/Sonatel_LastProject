<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager as PersistenceObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function generer_matricule($long = 5)
    {
        $numero = '';
        for ($i = 0; $i < $long; ++$i) {
            $array = ['1', 'o', '8', '7', 'k', '3', 'n', '3', '5', '4', 'a', '8', 's', '0', '1', 'm'];
            $numero .= $array[rand(0, 14)];
        }

        return $numero;
    }

    public function load(PersistenceObjectManager $manager)
    {
        $role1 = new Role();
        $role1->setLibelle('ROLE_MEDECIN');
        $manager->persist($role1);
        $manager->flush();

        $role2 = new Role();
        $role2->setLibelle('ROLE_ADMIN');
        $manager->persist($role2);
        $manager->flush();

        $role3 = new Role();
        $role3->setLibelle('ROLE_SECRETAIRE');
        $manager->persist($role3);
        $manager->flush();

        $user = new User();
        $user->setUsername('INFIRMIERE');
        $user->setPassword($this->encoder->encodePassword($user, 'passe'));
        $user->setNom('Sene');
        $user->setPrenom('Fatou');
        $user->setTel(338224432);
        $user->setSpecialite('Generaliste');
        $user->setEmail('Fatou@gmail.com');
        $user->setisActive(true);
        $user->setSexe('F');
        $user->setMatricule($this->generer_matricule());
        $user->setRole($role1);
        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setUsername('MEDECIN');
        $user->setPassword($this->encoder->encodePassword($user, 'passe'));
        $user->setNom('Fall');
        $user->setPrenom('Abdoulaye');
        $user->setTel(338224432);
        $user->setSpecialite('Dentiste');
        $user->setEmail('Abdoulaye@gmail.com');
        $user->setisActive(true);
        $user->setSexe('M');
        $user->setMatricule($this->generer_matricule());
        $user->setRole($role1);
        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setUsername('SECRETAIRE');
        $user->setPassword($this->encoder->encodePassword($user, 'passe'));
        $user->setNom('Diop');
        $user->setPrenom('Fatou');
        $user->setTel(338104432);
        $user->setSpecialite('Dentiste');
        $user->setEmail('fatou@gmail.com');
        $user->setisActive(true);
        $user->setSexe('F');
        $user->setMatricule($this->generer_matricule());
        $user->setRole($role3);
        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setUsername('ADMIN');
        $user->setPassword($this->encoder->encodePassword($user, 'passe'));
        $user->setNom('admin');
        $user->setPrenom('admin');
        $user->setEmail('admin@gmail.com');
        $user->setisActive(true);
        $user->setSexe('M');
        $user->setMatricule($this->generer_matricule());
        $user->setRole($role2);
        $manager->persist($user);
        $manager->flush();
    }
}
