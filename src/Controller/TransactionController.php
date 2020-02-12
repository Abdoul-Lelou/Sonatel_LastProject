<?php

namespace App\Controller;

use App\Entity\Sender;
use App\Entity\Transaction;
use App\Repository\SenderRepository;
use App\Repository\TarifRepository;
use App\Entity\Receiver;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
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

  public function index(TranslatorInterface $translator)
    {
      $translated = $translator->trans('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 
                                          1 for key UNIQ_3DB88C962FC0CB0F ');
    
        // ...
        return $translated;
    }   

     //GENERER UN NOMBRE 
     function generer_number($long=8){
        $numero = '';
          for($i=0;$i<$long;$i++){
            $array = array('I','0','1','C','2','Z','A','X','5','J','7','8','9','D','E');
             $numero .= $array[rand(0,14)];
           }
        return $numero;
     }

    /**
     * @Route("/sender", name="sender", methods={"POST"})
     */
    public function sender(Request $request,EntityManagerInterface $entityManager,TarifRepository $tarifRepository,
                                    TransactionRepository $transactionRepository)
    {
     
        $values=json_decode($request->getContent());
        
        if (isset($values->montant,$values->client,$values->typePiece,$values->numeroPiece,$values->tel)) 
        {
            //RECUPERATION DU FRAIS D'ENVOIE
            $tarif=$tarifRepository->findTarif($values->montant);
            $frais=$tarif[0]->getFrais();

            //EFFECTUER L'ENVOIE 
            $sender=new Sender();
            $sender->setMontant($values->montant);
            $sender->setNumeroPiece($values->numeroPiece);
            $sender->setTel($values->tel);
            $sender->setTypePiece($values->typePiece);
            $sender->setClient($values->client);
            $sender->setDate(new  \DateTime("now"));
            $sender->setCommission($frais*10/100);

            $entityManager->persist($sender);
            $entityManager->flush();

            //RECUPERER ID DE L'ENVOIE
            $idsender=$sender->getId();
            $sender_id= $entityManager->getRepository(Sender::class)->find($idsender);
            
            //EFFECTUER LA TRANSACTION COTE ENVOIE
            $transaction= new Transaction();
            $transaction->setCode($this->generer_number());
            $transaction->setFrais($frais);
            $transaction->setSenderId($sender_id);
            $transaction->setCommissionSysteme($frais*30/100);
            $transaction->setCommissionEtat($frais*40/100);

            $entityManager->persist($transaction);
            $entityManager->flush();

            
            $entityManager->flush();
            $data = [
                'status' => 200,
                'message' => 'Evoie effectuer avec succes'
              ];
            return new JsonResponse($data);
        }
        $data = [
            'status' => 200,
            'message' => 'Donnee invalides'
          ];
        return new JsonResponse($data);

    }



    /**
     * @Route("/receiver", name="receiver", methods={"POST"})
     */
    public function receiver(Request $request,EntityManagerInterface $entityManager,SenderRepository $senderRepository,
                                    TransactionRepository $transactionRepository)
    {
        $values=json_decode($request->getContent());

        if (isset($values->code,$values->numeroPiece,$values->client))
        {
            # code...
            //VERIFIER SI LE CODE D'ENVOIE EXISTE
            $trans_exist=$transactionRepository->findBy(array("code"=>$values->code));
            if ($trans_exist) {
                # code...
                //RECUPERER ID DE LA TRANSACTION
                $tId=$trans_exist[0]->getId();
                $trans_id= $entityManager->getRepository(Transaction::class)->find($tId);

                //RECUPERER ID DE L'EVOIE COTE TRANSACTION
                $sId=$trans_exist[0]->getSenderId();
                $send_exist=$senderRepository->findBy(array("id"=>$sId));

                //VERIFIER SI LE NOM DU CLIENT EST CORRECTE
                if ($values->client==$send_exist[0]->getClient() &&  $values->code==$trans_exist[0]->getCode())
                {
                    # code...
                    //EFFECTUER LE RETRAIT
                    $receiver= new Receiver();
                    $receiver->setDate(new \DateTime("now"));
                    $receiver->setTransaction($trans_id);
                    $receiver->setClient($values->client);
                    $receiver->setTel($send_exist[0]->getTel());
                    $receiver->setMontant($send_exist[0]->getMontant());
                    $receiver->setNumeroPiece($values->numeroPiece);
                    $receiver->setCommission($trans_exist[0]->getFrais()*20/100);
                    $receiver->setTypePiece($send_exist[0]->getTypePiece());

                    $entityManager->persist($receiver);
                    $entityManager->flush();

                    //RECUPERER L'ID DU RETRAIT EFFECTUER
                    $rId=$receiver->getId();
                    $receiver_id= $entityManager->getRepository(Receiver::class)->find($rId);

                    //AJOUTER ID DU RETRAIT A LA TRANSACTION
                    $trans_exist[0]->setReceiver($receiver_id);

                    $entityManager->persist($trans_exist[0]);
                    $entityManager->flush();

                    $data = [
                        'status' => 200,
                        'message' => 'Retrait effectuer avec succes'
                      ];
                    return new JsonResponse($data);

                }

                $data = [
                    'status' => 200,
                    'message' => 'Nom du client incorrecte '
                  ];
                  
                return new JsonResponse($data);
                
            }
        }
        $data = [
            'status' => 200,
            'message' => 'Erreur de saisi '
          ];

        return new JsonResponse($data);
    }
    
}