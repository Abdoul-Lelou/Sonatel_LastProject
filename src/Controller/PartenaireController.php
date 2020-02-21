<?php

namespace App\Controller;

use App\Entity\AffecterCompte;
use App\Entity\Compte;
use App\Entity\Depot;
use App\Entity\Partenaire;
use App\Entity\User as EntityUser;
use App\Repository\CompteRepository;
use App\Repository\ContratRepository;
use App\Repository\PartenaireRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/api")
*/
class PartenaireController extends AbstractController
{

    
    public function __construct( UserPasswordEncoderInterface $encoder)
    {   
       $this->encoder=$encoder;
    }

    
    //GENERER UN NOMBRE 
    function generer_caracteres($long=8){
        $numero = '';
          for($i=0;$i<$long;$i++){
            $array = array('I','0','1','C','2','3','4','P','5','6','7','8','9','D','E');
             $numero .= $array[rand(0,14)];
           }
        return $numero;
     }
   

     /**
     * @Route("/addPartenaire", name="partenaire", methods={"POST"})
     * 
     */ 
    public function createPartenaire(Request $request,EntityManagerInterface $entityManager,CompteRepository $cp,
                                      PartenaireRepository $prtRepo,ContratRepository $contratRepository,TranslatorInterface $translator)
    {
      //AVOIR AU MOINS LE ROLE ADMIN POUR CREER UN PARTENAIRE
      $this->denyAccessUnlessGranted("ROLE_ADMIN",null,"Vous n'avez pas les droits requis");
    
      $values = json_decode($request->getContent());
      
      $user=$this->getUser();
      $id_user=$user->getId();
      $userCreator = $entityManager->getRepository(EntityUser::class)->find($id_user);
      $numero=$this->generer_caracteres(9);

      
        if(isset($values->ninea,$values->rc,$values->email,$values->tel,$values->logo,$values->solde))
        {
            if ($values->solde<50000) {
              # code...
              $data = [
                'status' => 403,
                'message' => 'Vous ne pouvez pas faire un depot inferieure à 50000'
            ];
    
            return new JsonResponse($data);
            }
            $partenaire  = new Partenaire();
            $user_exist=$prtRepo->findBy(array("ninea"=>$values->ninea));
            
            if ($user_exist) {
             # code...
             //RECUPERER PARTENAIRE
             $pId=$user_exist[0]->getId();
             $part_id= $entityManager->getRepository(Partenaire::class)->find($pId);

             //RECUPERER COMPTE ET INCREMENTAION DU SOLDE 
             $cpt_exist=$cp->findBy(array("partenaire"=>$part_id));
             $solde_current=$cpt_exist[0]->getSolde(); 
             $cpt_exist[0]->setSolde($solde_current+$values->solde);
  
             $cpId=$cpt_exist[0]->getId();
             $compte_id= $entityManager->getRepository(Compte::class)->find($cpId);
             
             //AJOUT DU NOUVEAU DEPOT
             $depot= new Depot();
             $depot->setDateDepot(new \DateTime("now"));
             $depot->setMontant($values->solde);
             $depot->setCompte($compte_id);
             $depot->setUser($userCreator);

             $entityManager->persist($cpt_exist[0]);
             $entityManager->persist($depot);

             $entityManager->flush();

              $data = [
                'status' => 200,
                'message' => 'Compte mise à jour avec succès'
              ];
            return new JsonResponse($data);

        } 
        //CREATION DU NOUVEAU PARTENAIRE

            $contrat= $contratRepository->findAll();
            
            $partenaire->setNinea($values->ninea);
            $partenaire->setRc($values->rc);
            $partenaire->setTel($values->tel);
            $partenaire->setEmail($values->email);
            $partenaire->setContrat($contrat[0]->getTerme());

            $entityManager->persist($partenaire);
            $entityManager->flush();

            //RECUPERATION DE L'ID DU PARTENAIRE
            $idpart=$partenaire->getId();
            $part_id= $entityManager->getRepository(Partenaire::class)->find($idpart);

            //AJOUT DU NOUVEAU COMPTE PARTENAIRE 
            $compte= new Compte();
            $compte->setNumero("n°".$numero);
            $compte->setDateCreation(new \DateTime('now'));
            $compte->setUser($userCreator);
            $compte->setPartenaire($part_id);
            $compte->setSolde(500000);

            $entityManager->persist($compte);
            $entityManager->flush();
            
            //RECUPERATION DE L'ID DU COMPTE
            $cp=$compte->getId();
            $compte_id= $entityManager->getRepository(Compte::class)->find($cp);

            //AJOUT DU NOUVEAU DEPOT
            $depot= new Depot();
            $depot->setDateDepot(new \DateTime("now"));
            $depot->setMontant(500000);
            $depot->setCompte($compte_id);
            $depot->setUser($userCreator);

            $entityManager->persist($depot);
            $entityManager->flush();

            $data = [
              'status' => 200,
              'message' => 'Compte creer avec succes'
          ];

          return new JsonResponse($data);
        }            
          $data = [
            'status' => 403,
            'message' => 'Données non valide'
        ];

        return new JsonResponse($data);
    }
 
      /**
     * @Route("/affect/{id}", methods={"PUT"})
     */
    public function affecterCompte(UserRepository $userRepository,Request $request, CompteRepository $compteRepository,EntityManagerInterface $entityManager,$id)
    {
      //APPARTENIR AU MOINS A UN PARTNAIRE
      $this->denyAccessUnlessGranted("ROLE_ADMIN_PARTENAIRE",null,"Vous ne pouvez pas affecter un compte à un utilisateur");

      $values = json_decode($request->getContent());
      $user=$userRepository->find($id);
      if (!$user->getPartenaire()) {
        # code...
        $data = [
          'status' => 200,
          'message' => 'Cet utilisateur n\'existe pas'
      ];

      return new JsonResponse($data);
      }

      $userConnect=$this->getUser();
      $parteUser=$userConnect->getPartenaire();
      
      $partenaireId=$entityManager->getRepository(Partenaire::class)->find($parteUser);

      if (!$partenaireId) {
        # code...
        $data = [
          'status' => 200,
          'message' => 'Cet utilisateur n\'appartient pas à un partenaire'
      ];

      return new JsonResponse($data);
      }

      $compte=$compteRepository->find($partenaireId);

      if (!$compte) {
        # code...
        $data = [
          'status' => 200,
          'message' => 'Cet utilisateur n\'a pas de compte partenaire'
      ];

      return new JsonResponse($data);
      }
      $idCompte=$entityManager->getRepository(Compte::class)->find($compte->getId());
      if (isset($values->dateFin) ) {
        # code...
      $affecter=new AffecterCompte();
      $affecter->setCompte($idCompte);
      $affecter->setDateDebut(new \DateTime("now"));
      $affecter->setDateFin($values->dateFin);
      $affecter->setUserAffect($userConnect);

      $entityManager->persist($affecter);
      $entityManager->flush();

      $affectId=$entityManager->getRepository(AffecterCompte::class)->find($affecter->getId());

      $user->setAffecterCompte($affectId);
      $entityManager->flush();
      
      $data = [
        'status' => 200,
        'message' => 'Compte affecté avec succes'
      ];

    return new JsonResponse($data);
    }

    $data = [
      'status' => 200,
      'message' => 'Donnée invalides'
    ];

    return new JsonResponse($data);
    }
}