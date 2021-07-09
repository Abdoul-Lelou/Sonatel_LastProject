<?php

namespace App\Controller;

use App\Entity\Profil;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\ProfilRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */
class SecurityController extends AbstractController
{
    public function generer_matricule($long = 5)
    {
        $numero = '';
        for ($i = 0; $i < $long; ++$i) {
            $array = ['1', '0', '8', '7', '2', '3', 'n', '3', '5', '6', 'a', '8', '9', '0', '1', 'm'];
            $numero .= $array[rand(0, 14)];
        }

        return $numero;
    }

    /**
     * @Route("/users", methods={"GET"})
     */
    public function findUsers(UserRepository $userRep)
    {
        return $this->json($userRep->findAll(), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/profil/{id}", methods={"GET"})
     */
    public function findProfilById(ProfilRepository $profil, $id)
    {
        //AVOIR AU MOINS LE ROLE_ADMIN POUR AFFICHER LES USERS
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        $file = $profil->find($id);

        if ($file == null) {
            return $this->json(['status' => 400, 'message' => 'Profile introuvable']);
        }

        return new BinaryFileResponse(($file));
    }

    /**
     * @Route("/profil/{id}", methods={"DELETE"})
     */
    public function deleteProfile(EntityManagerInterface $entityManager, $id, ProfilRepository $profilRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR SUPPRIMER LE PATIENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé ');

        $profil = $profilRepository->find($id);

        if ($profil) {
            $entityManager->remove($profil);
            $entityManager->flush();

            return $this->json(['status' => 201, 'message' => 'Profile supprimé']);
        }

        return $this->json(['status' => 401, 'message' => "Profile n'existe pas"]);
    }

    /**
     * @Route("/profil", methods={"POST"})
     */
    public function addProfile(Request $request, EntityManagerInterface $entityManager)
    {
        //AVOIR AU MOINS LE ROLE_ADMIN POUR AFFICHER LES USERS
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        $profil = new Profil();

        $profil->setImageFile($request->files->get('image'));
        $profil->setUpdatedAt(new \DateTime());
        $entityManager->persist($profil);
        $entityManager->flush();

        $userProfile = $entityManager->getRepository(Profil::class)->find(intval($profil->getId()));

        $this->getUser()->setProfil($userProfile);
        $entityManager->flush();

        return $this->json(['status' => 201, 'message' => 'Profile ajouté']);
    }

    /**
     * @Route("/users/{id}", methods={"GET"})
     */
    public function findUsersById($id, UserRepository $userRep)
    {
        //AVOIR  LE ROLE_ADMIN POUR RECUPERER UN USER PAR SON ID
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès non autorisé');

        return $this->json($userRep->find($id), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/users/{id}", methods={"DELETE"})
     */
    public function deleteUser($id, UserRepository $userRep, EntityManagerInterface $entityManager)
    {
        //AVOIR  LE ROLE_ADMIN POUR SUPPRIMER UN USER
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès non autorisé');

        $users = $userRep->find($id);

        try {
            if ($users == null) {
                return $this->json(['status' => 400, 'message' => "Cet utilisateur n'existe pas"]);
            }

            $entityManager->remove($users);
            $entityManager->flush();

            return $this->json(['status' => 201, 'message' => 'utilisateur supprime']);
        } catch (NotEncodableValueException $e) {
            return $this->json(['status' => 400, 'message' => $e->getMessage()]);
        }

        return $this->json(['status' => 400, 'message' => "Cet utilisateur n'existe pas"]);
    }

    /**
     * @Route("/users", name="adduser", methods={"POST"})
     */
    public function addUser(Request $request, EntityManagerInterface $entityManager,
                                UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
    {
        //AVOIR  LE ROLE_ADMIN POUR AJOUTER UN USER
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès non autorisé');

        $values = json_decode($request->getContent());
        if (isset($values->nom,$values->role,$values->username,$values->email,$values->sexe,$values->prenom,$values->password)) {
            try {
                $role_id = intval(preg_replace('~[^0-9]~', '', $values->role));
                $role = $entityManager->getRepository(Role::class)->find(intval($role_id));

                $users = new User();

                $users->setPrenom($values->prenom);
                $users->setNom($values->nom);
                $users->setUsername($values->username);
                $users->setEmail($values->email);
                $users->setPassword($encoder->encodePassword($users, $values->password));
                $users->setRole($role);
                $users->setSexe($values->sexe);
                $users->setTel($values->tel);
                $users->setIsActive(true);
                $users->setMatricule('kds'.$this->generer_matricule());

                if (isset($values->specialite)) {
                    $users->setSpecialite($values->specialite);
                }

                $erreur = $validator->validate($users);
                if (count($erreur) > 1) {
                    return $this->json(['status' => 400, 'message' => 'Donées saisie incorrect']);
                }
                $entityManager->persist($users);
                $entityManager->flush();

                return $this->json($users, 201, [], ['groups' => 'patient']);
            } catch (NotEncodableValueException $e) {
                return $this->json([
                        'status' => 400,
                        'message' => $e->getMessage(),
                    ]);
            }
        } else {
            return $this->json(['status' => 400, 'message' => 'Données incorrecte']);
        }

        return $this->json(['status' => 400, 'message' => ' oups quelque chose ne va pas']);
    }

    /**
     * @Route("/users/update/{id}", name="update.user",  methods={"PUT"})
     */
    public function updateUser($id, UserRepository $userRep, EntityManagerInterface $entityManager, Request $request, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
    {
        //AVOIR  LE ROLE_ADMIN POUR MODIFIER UN USER
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès non autorisé');

        $users = $userRep->find($id);

        $values = json_decode($request->getContent());

        if ($users && isset($values->nom,$values->role,$values->username,$values->email,$values->sexe,$values->prenom)) {
            try {
                $role_id = intval(preg_replace('~[^0-9]~', '', $values->role));
                $role = $entityManager->getRepository(Role::class)->find(intval($role_id));

                $users->setPrenom($values->prenom);
                $users->setNom($values->nom);
                $users->setUsername($values->username);
                $users->setEmail($values->email);
                $users->setRole($role);
                $users->setSexe($values->sexe);
                $users->setSpecialite($values->specialite);
                $users->setTel($values->tel);
                $users->setMatricule($users->getMatricule());

                if (isset($values->password)) {
                    $users->setPassword($encoder->encodePassword($users, $values->password));
                }

                $erreur = $validator->validate($users);
                if (count($erreur) > 1) {
                    return $this->json(['status' => 400, 'message' => 'Données saisie manquante']);
                }
                $entityManager->persist($users);
                $entityManager->flush();

                return $this->json($users, 201, [], ['groups' => 'patient']);
            } catch (NotEncodableValueException $e) {
                return $this->json([
                        'status' => 400,
                        'message' => $e->getMessage(),
                    ]);
            }
        } else {
            return $this->json(['status' => 400, 'message' => 'Données saisie incorrect']);
        }

        return $this->json(['status' => 400, 'message' => " Ce utilisateur n'existe pas"]);
    }

    /**
     * @Route("/roles", name="roles", methods={"GET"})
     */
    public function findRoles(RoleRepository $roleRepository, SerializerInterface $serializer)
    {
        //AVOIR LE ROLE_ADMIN POUR AFFICHER LES ROLES
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès non autorisé');

        return $this->json($roleRepository->findAll(), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/roles/{id}", name="role.id", methods={"GET"})
     */
    public function findRolesById($id, RoleRepository $roleRepository, SerializerInterface $serializer)
    {
        //AVOIR  LE ROLE_ADMIN POUR AFFICHER LE ROLE PAR SON ID
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès non autorisé');

        return $this->json($roleRepository->find($id), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/roles/{id}", name="update.role", methods={"PUT"})
     */
    public function updateRoles($id, RoleRepository $roleRep, EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator)
    {
        //AVOIR  LE ROLE_ADMIN POUR MODIFIER LE ROLE
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès non autorisé');

        $roles = $roleRep->find($id);

        $values = json_decode($request->getContent());

        if ($roles != null && $values->libelle != null) {
            try {
                $roles->setLibelle($values->libelle);

                $entityManager->persist($roles);
                $entityManager->flush();

                return $this->json($roles, 201, [], ['groups' => 'patient']);
            } catch (NotEncodableValueException $e) {
                return $this->json([
                        'status' => 400,
                        'message' => $e->getMessage(),
                    ]);
            }
        } else {
            return $this->json(['status' => 400, 'message' => 'Données saisie incorrect']);
        }

        return $this->json(['status' => 400, 'message' => " Ce role n'existe pas"]);
    }

    /**
     * @Route("/users/disunable/{id}", methods={"PUT"})
     */
    public function disUnableUser($id, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        //AVOIR  LE ROLE_ADMIN POUR MODIFIER LE ROLE
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès non autorisé');
        $userConnect = $this->getUser()->getRoles();
        $user = $userRepository->find($id);

        if ($user == null) {
            return $this->json(['status' => 400, 'message' => " Ce utilisateur n'existe pas"]);
        }

        $role = $user->getRoles();

        if ($userConnect[0] == 'ROLE_ADMIN' && $role[0] == 'ROLE_ADMIN') {
            return $this->json(['status' => 400, 'message' => ' Vous ne pouvez pas bloquer ou debloquer un admin']);
        } elseif ($user->getIsActive() === true) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas bloquer un utilisateur');

            $user->setIsActive(false);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json(['status' => 400, 'message' => 'Utilisateur bloquer avec succes']);
        } elseif ($user->getIsActive() === false) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Vous ne pouvez pas debloquer un utilisateur');

            $user->setIsActive(true);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json(['status' => 400, 'message' => 'Utilisateur debloquer avec succes']);
        }

        return $this->json(['status' => 400, 'message' => 'Utilisateur introuvable']);
    }

    /**
     * @Route("/update/login/{id}", methods={"PUT"})
     */
    public function updateLogin($id, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = $userRepository->find($id);
        $values = json_decode($request->getContent());

        if (isset($values->newPassword,$values->currentPassword)) {
            $matchCurrentPassword = $encoder->isPasswordValid($user, $values->currentPassword);
            $matchNewPassword = $encoder->isPasswordValid($user, $values->newPassword);

            if ((bool) $matchCurrentPassword && (bool) !$matchNewPassword) {
                $user->setPassword($encoder->encodePassword($user, $values->newPassword));
                if (isset($values->username) && $values->username != $user->getUsername) {
                    $user->setUsername($values->username);
                }
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->json(['status' => 200, 'message' => 'Password modifié']);
            } elseif ((bool) $matchNewPassword) {
                return $this->json(['status' => 401, 'message' => 'Choisir un password diffrent']);
            }

            return $this->json(['status' => 401, 'message' => 'Password actuel incorrect']);
        }

        return $this->json(['status' => 400, 'message' => 'Données saisi incorrect']);
    }

    /**
     * @Route("/connect", methods={"GET"})
     */
    public function login()
    {
        return $this->json($this->getUser(), 200, [], ['groups' => 'patient']);
    }
}
