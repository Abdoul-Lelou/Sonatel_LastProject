<?php

namespace App\Controller;

use App\Entity\Partenaire as EntityPartenaire;
use App\Entity\User;
use App\Repository\PartenaireRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\Roles as EntityRoles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;


    /**
     * @Route("/api")
     */
class SecurityController extends AbstractController
{
   

    /**
     * @Route("/users", methods={"GET"})
     */
    public function findUsers(UserRepository $userRep,SerializerInterface $serializer)
    {
        //AVOIR AU MOINS LE ROLE_ADMIN POUR AFFICHER LES USERS
        $this->denyAccessUnlessGranted('ROLE_ADMIN',null,"role incorrect");
        
        $user= $userRep->findAll();
        $data=$serializer->serialize($user,"json", [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return new JsonResponse($data,Response::HTTP_OK,[],true);
    }
   

    /**
     * @Route("/partenaire", methods={"GET"})
     */
    public function partenaireUsers(PartenaireRepository $partRep,SerializerInterface $serializer)
    {
        //AVOIR AU MOINS LE ROLE_ADMIN POUR AFFICHER LES PARTENAIRES
        $this->denyAccessUnlessGranted('ROLE_ADMIN',null,"role incorrect");
        
        $user= $partRep->findAll();
        $data=$serializer->serialize($user,"json", [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return new JsonResponse($data,Response::HTTP_OK,[],true);
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     * @category
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder,EntityManagerInterface $entityManager)
    {
        //AJOUTER LES UTILISATEURS
       
        $values = json_decode($request->getContent());
       
        if(isset($values->username,$values->password,$values->nom,$values->role_id,$values->isActive,$values->partenaire_id)) 
        {
            //EXTRAIRE LES DONNEES NUMERIQUES DE LA CHAINE /api/roles/numero
           
            $role_id=intval( preg_replace('~[^0-9]~', '', $values->role_id)); 
            $userRole = $entityManager->getRepository(EntityRoles::class)->find(intVal($role_id));
            $userConnect=$this->getUser();
            
            $user = new User();
            $user->setNom($values->nom);
            $user->setUsername($values->username);
            $user->setIsActive($values->isActive);
            $user->setPassword($passwordEncoder->encodePassword($user, $values->password));
            $user->setRole($userRole);
     
            
             if ($user->getRoles()==["ROLE_ADMIN"]) {
                # code...
                $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN',null,"Vous n'avez les droits requis  pour ajouter un admin");
            
                $entityManager->persist($user);
                $entityManager->flush();
                
                $data = [
                    'status' => 200,
                    'message' => 'L\'utilisateur a été créé'
                ];
                return new JsonResponse($data);

             }else if ($user->getRoles()==["ROLE_USER"]) {
                 # code...
                $this->denyAccessUnlessGranted('ROLE_ADMIN',null,"Vous n'avez les droits requis");
            
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                    'status' => 200,
                    'message' => 'L\'utilisateur a été créé'
                ];
                return new JsonResponse($data);

             }elseif ($user->getRoles()==["ROLE_PARTENAIRE"]) {
                 # code...
                 $this->denyAccessUnlessGranted('ROLE_ADMIN',null,"Vous n'avez pas les droits requis");
            
                 $part_id=intval( preg_replace('~[^0-9]~', '', $values->partenaire_id)); 
                 $partenaire_id = $entityManager->getRepository(EntityPartenaire::class)->find(intVal($part_id));
                 if (!$partenaire_id) 
                 {
                     # code...
                     $data = [
                        'status' => 200,
                        'message' => 'Partenaire n\'existe pas'
                    ];
                    return new JsonResponse($data);
                 }
                 $user->setPartenaire($partenaire_id);

                 $entityManager->persist($user);
                 $entityManager->flush();
                 $data = [
                    'status' => 200,
                    'message' => 'Le partenaire a été créé'
                ];
                return new JsonResponse($data);

             }elseif ($user->getRoles()==["ROLE_SUPER_ADMIN"]){
                # code...

                $data = [
                   'status' => 200,
                   'message' => 'Vous n\'avez pas le droit de créer un SUPEUR_ADMIN'
               ];
               return new JsonResponse($data);
               
            }elseif ($user->getRoles()==["ROLE_ADMIN_PARTENAIRE"]) {
                # code...
               
                $this->denyAccessUnlessGranted("ROLE_PARTENAIRE",null,"Vous ne pouvais pas ajouter un admin partenaire");
                
                $part_id=intval( preg_replace('~[^0-9]~', '', $values->partenaire_id)); 
                $partenaire_id = $entityManager->getRepository(EntityPartenaire::class)->find(intVal($part_id));
                if (!$partenaire_id) {
                    # code...
                    $data = [
                       'status' => 200,
                       'message' => 'Partenaire n\'existe pas'
                   ];
                   return new JsonResponse($data);
                }
                $user->setPartenaire($partenaire_id);
                $entityManager->persist($user);
                $entityManager->flush();
               
                $data = [
                   'status' => 200,
                   'message' => 'L\'utilisateur a été créé'
               ];
               return new JsonResponse($data);
               
              }elseif ( $user->getRoles()==["ROLE_USER_PARTENAIRE"] ) {
                # code...
                $this->denyAccessUnlessGranted("ROLE_ADMIN_PARTENAIRE",null,"Vous ne pouvais pas ajouter un user partenaire");

                $part_id=intval( preg_replace('~[^0-9]~', '', $values->partenaire_id)); 
                $partenaire_id = $entityManager->getRepository(EntityPartenaire::class)->find(intVal($part_id));
                if (!$partenaire_id) {
                    # code...
                    $data = [
                       'status' => 200,
                       'message' => 'Partenaire n\'existe pas'
                   ];
                   return new JsonResponse($data);
                }
                $user->setPartenaire($partenaire_id);
                $entityManager->persist($user);
                $entityManager->flush();
               
                $data = [
                   'status' => 200,
                   'message' => 'L\'utilisateur a été créé'
               ];
               return new JsonResponse($data);
              }
            
        }
            $data = [
                'status' => 200,
                'message' => 'Donnees invalides'
            ];

            return new JsonResponse($data);
    }
    
    /**
     * @Route("/disUnable/{id}", methods={"PUT"})
     */
     public function disableUnableUser($id,UserRepository $userRepository,EntityManagerInterface $entityManager)
     {
        //ACTIVER OU DESACTIVER USERS OU PARTENAIRE
        
        $user=$userRepository->find($id);
        if(!empty($user))
        {
            $user=$userRepository->find($id);
            $role=$user->getRoles();   
            if ($user->getIsActive()===true && $role==["ROLE_ADMIN"]) {
                # code...
                $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN',null,"Vous ne pouvez pas bloquer un admin system");

                $user->setIsActive(false);
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                        'status' => 200,
                        'message' => 'Utilisateur bloquer avec succes'
                    ];

                    return new JsonResponse($data, 200);

            }else  if ($user->getIsActive()===false && $role==["ROLE_ADMIN"]){
                # code...
                $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN',null,"Vous ne pouvez pas debloquer un admin  system");

                $user->setIsActive(true);
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                    'status' => 200,
                    'message' => 'Utilisateur debloquer avec succes'
                ];

                return new JsonResponse($data, 200);

            }else  if ($user->getIsActive()===false && $role==["ROLE_USER"]){
                # code...
                $this->denyAccessUnlessGranted('ROLE_ADMIN',null,"Vous ne pouvez pas debloquer un utilisateur");

                $user->setIsActive(true);
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                    'status' => 200,
                    'message' => 'Utilisateur debloquer avec succes'
                ];

                return new JsonResponse($data, 200);

            }else  if ($user->getIsActive()===true && $role==["ROLE_USER"]){
                # code...
                $this->denyAccessUnlessGranted('ROLE_ADMIN',null,"Vous ne pouvez pas bloquer un utilisateur");

                $user->setIsActive(false);
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                    'status' => 200,
                    'message' => 'Utilisateur bloquer avec succes'
                ];

                return new JsonResponse($data, 200);
            }else  if ($user->getIsActive()===false && $role==["ROLE_PARTENAIRE"]){
                # code...
                $this->denyAccessUnlessGranted('ROLE_ADMIN',null,"Vous ne pouvez pas debloquer un partenaire");

                $user->setIsActive(true);
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                    'status' => 200,
                    'message' => 'Partenaire debloquer avec succes'
                ];

                return new JsonResponse($data, 200);

            }else  if ($user->getIsActive()===true && $role==["ROLE_PARTENAIRE"]){
                # code...
                $this->denyAccessUnlessGranted('ROLE_ADMIN',null,"Vous ne pouvez pas bloquer un partenaire");

                $user->setIsActive(false);
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                    'status' => 200,
                    'message' => 'Partenaire bloquer avec succes'
                ];

                return new JsonResponse($data, 200);
            }else  if ($user->getIsActive()===true && $role==["ROLE_ADMIN_PARTENAIRE"]){
                # code...
                $this->denyAccessUnlessGranted('ROLE_PARTENAIRE',null,"Vous ne pouvez pas bloquer un admin partenaire");

                $user->setIsActive(false);
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                    'status' => 200,
                    'message' => 'Utilisateur bloquer avec succes'
                ];

                return new JsonResponse($data, 200);
            }else  if ($user->getIsActive()===false && $role==["ROLE_ADMIN_PARTENAIRE"]){
                # code...
                $this->denyAccessUnlessGranted('ROLE_PARTENAIRE',null,"Vous ne pouvez pas debloquer un user partenaire");

                $user->setIsActive(true);
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                    'status' => 200,
                    'message' => 'Utilisateur debloquer avec succes'
                ];

                return new JsonResponse($data, 200);

            }else  if ($user->getIsActive()===true && $role==["ROLE_USER_PARTENAIRE"]){
                # code...
                $this->denyAccessUnlessGranted('ROLE_ADMIN_PARTENAIRE',null,"Vous ne pouvez pas bloquer un user partenaire");

                $user->setIsActive(false);
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                    'status' => 200,
                    'message' => 'Utilisateur bloquer avec succes'
                ];

                return new JsonResponse($data, 200);
            }else  if ($user->getIsActive()===false && $role==["ROLE_USER_PARTENAIRE"]){
                # code...
                $this->denyAccessUnlessGranted('ROLE_ADMIN_PARTENAIRE',null,"Vous ne pouvez pas debloquer un user partenaire");

                $user->setIsActive(true);
                $entityManager->persist($user);
                $entityManager->flush();
                $data = [
                    'status' => 200,
                    'message' => 'Utilisateur debloquer avec succes'
                ];

                return new JsonResponse($data, 200);

            }
        }
        else{
            $data = [
                'status' => 200,
                'message' => 'Cette identifiant n\'existe'
            ];
            return new JsonResponse($data, 500,);
        }        
            $data = [
                'status' => 200,
                'message' => 'Vous ne pouvez pas bloquer ou debloquer un SUPER_ADMIN'
            ];
            return new JsonResponse($data);
    }
     
    
}