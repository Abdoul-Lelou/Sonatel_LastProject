<?php

namespace App\Controller;

use App\Entity\AffecterCompte;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Partenaire;
use App\Entity\Sender;
use App\Entity\Transaction;
use App\Repository\SenderRepository;
use App\Repository\TarifRepository;
use App\Entity\Receiver;
use App\Entity\User;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Isset_;
use Proxies\__CG__\App\Entity\AffecterCompte as EntityAffecterCompte;
use Proxies\__CG__\App\Entity\Compte as EntityCompte;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

    /**
     * @Route("/api")
     */
class TransactionController extends AbstractController
{

  
     //GENERER UN NOMBRE 
  public function generer_number($long=8)
  {
        $numero = '';
          for($i=0;$i<$long;$i++)
          {
            $array = array('I','R','K','C','V','Z','A','X','O','S','T','8','9','D','E');
            $array1 = array('0','9','2','8','3','7','4','6','5','H','P','W','Q','1','L');
             $numero .= $array[rand(0,14)].$array1[rand(0,14)];
          }
        return $numero;
  }

     /**
     * @Route("/transaction", name="sender", methods={"POST"})
     */
    public function sender(Request $request,EntityManagerInterface $entityManager,TarifRepository $tarifRepository,
                                    TransactionRepository $transactionRepository)
    {
      //APPARTENIR AU MOINS A UN PARTENAIRE POUR POUVOIR FAIRE DES DEPOTS
      $this->denyAccessUnlessGranted("ROLE_USER_PARTENAIRE",null,"Vous ne pouvez pas faire des depots");
     
      //RECUPERER UTILISATEUR CONNECTE
      $user=$this->getUser();
      $id_user=$user->getId(); // SON ID
      $userParteId=$user->getPartenaire(); // SON PARTENAIRE
      $userParte = $entityManager->getRepository(Partenaire::class)->find($userParteId);
      
      
      if (!$userParte) 
      {
        # code...
        $data = [
          'status' => 200,
          'message' => 'L\'utilisateur n\'appartient à aucun partenaire'
        ];
        return new JsonResponse($data);
      }
      $affectId=$user->getAffecterCompte();
      $affecterCompte = $entityManager->getRepository(AffecterCompte::class)->find($affectId);
      $idCompte=$affecterCompte->getCompte();
      $compteId = $entityManager->getRepository(EntityCompte::class)->find($idCompte);

     
      $values=json_decode($request->getContent());
     
      if (isset($values->montant,$values->nomClient,$values->prenomClient,$values->telClient,$values->telRecepteur,
                  $values->nomRecepteur,$values->prenomRecepteur))
      {
        if ($values->montant>$compteId->getSolde()) 
        {
          # code...
          $data = [
            'status' => 200,
            'message' => 'Vous n\'avez pas ce montant dans votre compte'
          ];
        return new JsonResponse($data);

        }
        //RECUPERATION DU FRAIS D'ENVOIE
        $tarif=$tarifRepository->findTarif($values->montant);
        $frais=$tarif[0]->getFrais();

        $client=new Client();
        $client->setNomClient($values->nomClient);
        $client->setPrenomClient($values->prenomClient);
        $client->setTelClient($values->telClient);
        $client->setTelRecepteur($values->telRecepteur);
        $client->setNomRecepteur($values->nomRecepteur);
        $client->setPrenomRecepteur($values->prenomRecepteur);
        $entityManager->persist($client);
        $entityManager->flush();

        $idClient=$client->getId();
        $clientId=$entityManager->getRepository(Client::class)->find($idClient);

        $transaction=new Transaction();
        $transaction->setDate_depot(new \DateTime("now"));
        $transaction->setIsActive(true);
        $transaction->setMontant($values->montant);
        $transaction->setClient($clientId);
        $transaction->setFrais($frais);
        $transaction->setDeposer($idCompte);
        $transaction->setCode($this->generer_number());
        $transaction->setCommissionEnvoie($frais*10/100);
        $transaction->setCommissionEtat($frais*40/100);
        $transaction->setCommissionSysteme($frais*30/100);
        $transaction->setCommissionRetait($frais*20/100);

        $entityManager->persist($transaction);
        $entityManager->flush();

        $data = [
          'status' => 200,
          'message' => 'Envoie effectuer avec success'
        ];
        return new JsonResponse($data);
      }
      elseif (isset($values->nomRecepteur,$values->prenomRecepteur,$values->code)){
        # code...
          $trans_exist=$transactionRepository->findBy(array("code"=>$values->code));
          
            if ($trans_exist) {
                # code...
                //RECUPERER ID DE LA TRANSACTION
                $tId=$trans_exist[0]->getId();
                $trans_id= $entityManager->getRepository(Transaction::class)->find($tId);
                $idClient=$trans_id->getClient();
                $client_id= $entityManager->getRepository(Client::class)->find($idClient);
               
                //CALCULER LA DATE
                $date1=$trans_exist[0]->getDate_depot();
                $date2= new \DateTime("now");
                $diff=date_diff($date1,$date2);
      
                $date3 = $diff->format("Total number of days: %a.");
                $dateExpire=intval( preg_replace('~[^0-9]~', '', $date3));
                
                //VERIFIER SI LA DELAI N'EST PAS DEPASSER
                if ($dateExpire>7) {
                  # code...
                  $trans_exist[0]->setIsActive(false);
                  $entityManager->flush();
                }

            
                //RECUPERER ID DE L'EVOIE COTE TRANSACTION

               if ($trans_exist[0]->getIsActive()==false) {
                # code...
                $data = [
                  'status' => 200,
                  'message' => 'Code expire retrait imposible '
                ];
                return new JsonResponse($data);
              }elseif($trans_exist[0]->getRetirer()) {
                # code...
                $data = [
                  'status' => 200,
                  'message' => 'Somme déjà retirée '
                ];
                return new JsonResponse($data);
              }  
                
                $somme= $trans_exist[0]->getMontant();
               //VERIFIER SI LES INFORMATIONS DU CLIENT SONT CORRECTE
                if ($values->prenomRecepteur==$client_id->getPrenomRecepteur() &&  $values->code==$trans_exist[0]->getCode()
                        && $values->nomRecepteur==$client_id->getNomRecepteur()){

                  $trans_exist[0]->setDateRetrait(new \DateTime("now"));
                  $trans_exist[0]->setRetirer($idCompte);
                  
                  $entityManager->flush();
                  $data = [
                    'status' => 200,
                    'message' => 'Retrait des '.$somme.' effectué avec succes '
                  ];
                  return new JsonResponse($data);
                }   

                $data = [
                  'status' => 200,
                  'message' => 'Information du client incorrect'
                ];
                return new JsonResponse($data);
              }
                  
                $data = [
                  'status' => 200,
                  'message' => 'code incorrect'
                ];
                return new JsonResponse($data);  
      }
      $data = [
        'status' => 200,
        'message' => 'Donnees saisi incorrecte incorrect'
      ];
      return new JsonResponse($data);
    }
       
}
