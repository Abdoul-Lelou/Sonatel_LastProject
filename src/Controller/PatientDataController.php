<?php

namespace App\Controller;

use App\Entity\Ordonnance;
use App\Entity\Patient;
use App\Entity\PatientData;
use App\Entity\User;
use App\Repository\PatientDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */
class PatientDataController extends AbstractController
{
    public function generer_numero($taille = 5)
    {
        $numero = '';
        for ($i = 0; $i < $taille; ++$i) {
            $array = ['4', '0', '8', '9', '2', '3', '4', '3', '5', '6', '7', '8', '9', '0', '1'];
            $numero .= $array[rand(0, 14)];
        }

        return $numero;
    }

    /**
     * @Route("/patientdata/data", methods={"GET"})
     */
    public function findPatientsData(PatientDataRepository $patientDataRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER LES DONNEES
        // $this->denyAccessUnlessGranted('ROLE_SECRETAIRE',null,"Accès non autorisé");

        return $this->json($patientDataRepository->findAll(), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/patient/data/{id}", methods={"GET"})
     */
    public function findPatientDataByPatientId($id, PatientDataRepository $patientDataRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER LES DONNEES
        // $this->denyAccessUnlessGranted('ROLE_SECRETAIRE',null,"Accès non autorisé");

        return $this->json($patientDataRepository->findPatientDataByPatientId($id), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/patientdata/medecin", methods={"GET"})
     */
    public function findPatientsDataByMedecin(PatientDataRepository $patientDataRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER LES DONNEES
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autoisé');

        return $this->json($this->getUser()->getPatientData(), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/patient/dataid/{id}", methods={"GET"})
     */
    public function findPatientByPatientDataId($id, PatientDataRepository $patientDataRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER LES DONNEES
        // $this->denyAccessUnlessGranted('ROLE_SECRETAIRE',null,"Accès non autorisé");

        return $this->json($patientDataRepository->findPatientByPatientDataId($id), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/patientdata/data/{id}", methods={"GET"})
     */
    public function findPatientDataByid($id, PatientDataRepository $patientDataRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER UNE DONNEES
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès  autorisé');

        return $this->json($patientDataRepository->find($id), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/patientdata/data", methods={"POST"})
     */
    public function addPatientData(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR AJOUTER DES DONNEES
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès  autorisé');

        $values = json_decode($request->getContent());

        if (isset($values->heure,$values->symptome,$values->patient,$values->medecin,$values->date,$values->taille,$values->poids,$values->constat,$values->groupe)) {
            try {
                $medecin_id = intval(preg_replace('~[^0-9]~', '', $values->medecin));
                $medecin = $entityManager->getRepository(User::class)->find(intval($medecin_id));
                $patient_id = intval(preg_replace('~[^0-9]~', '', $values->patient));
                $patient = $entityManager->getRepository(Patient::class)->find(intval($patient_id));
                //var_dump($user);die;
                $patientData = new PatientData();

                $patientData->setSymptome($values->symptome);
                $patientData->setTaille($values->taille);
                $patientData->setPoids($values->poids);
                $patientData->setGroupe($values->groupe);
                $patientData->setHeure($values->heure);
                $patientData->setDate($values->date);
                $patientData->setConstat($values->constat);
                $patientData->setPatient($patient);
                $patientData->setMedecin($medecin);

                // $post=  $serializer->deserialize($values, Appointement::class, 'json');
                $erreur = $validator->validate($patientData);
                if (count($erreur) > 0) {
                    return $this->json(['status' => 400, 'message' => 'Donnees saisie incorrect']);
                }
                $entityManager->persist($patientData);
                $entityManager->flush();

                return $this->json($patientData, 201, [], ['groups' => 'patient']);
            } catch (NotEncodableValueException $e) {
                return $this->json([
                        'status' => 400,
                        'message' => $e->getMessage(),
                    ]);
            }
        } else {
            return $this->json(['status' => 400, 'message' => 'Remplissez tout les donnees']);
        }

        return $this->json(['status' => 400, 'message' => ' oups quelque chose ne va pas']);
    }

    /**
     * @Route("/patientdata/data/{id}", methods={"DELETE"})
     */
    public function deletePatientData(EntityManagerInterface $entityManager, $id, PatientDataRepository $patientDataRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR SUPPRIMER UNE DONNEE
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        $patientData = $patientDataRepository->find($id);
       
        try {
            if ($patientData == null) {
                return $this->json(['status' => 400, 'message' => "Ce donnée n'existe pas"]);
            }
            
            $patient = $entityManager->getRepository(Patient::class)->find($patientData->getPatient());
            $entityManager->remove($patientData);
            $entityManager->flush();
            
            if(count($patient->getPatientData()) == 0){
               $patient->setIsVisit(false);
               $entityManager->persist($patient);
               $entityManager->flush();
            }    
      
            return $this->json(['status' => 400, 'message' => 'Données supprimé']);
        } catch (NotEncodableValueException $e) {
            return $this->json(['status' => 400, 'message' => $e->getMessage()]);
        }

        return $this->json(['status' => 400, 'message' => "Ce donnée n'existe pas"]);
    }

    /**
     * @Route("/patientdata/data/{id}", methods={"PUT"})
     */
    public function updatePatientData(EntityManagerInterface $entityManager, $id, PatientDataRepository $patientDataRepository, Request $request, ValidatorInterface $validator)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR MODIFIER UNE DONNEE
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        $patientData = $patientDataRepository->find($id);
        $values = json_decode($request->getContent());

        if ($patientData) {
            try {
                //$medecin_id=intval( preg_replace('~[^0-9]~', '', $values->medecin));
                // $medecin = $entityManager->getRepository(User::class)->find(intVal($medecin_id));
                // $patient_id=intval( preg_replace('~[^0-9]~', '', $values->patient));
                // $patient = $entityManager->getRepository(Patient::class)->find(intVal($patient_id));
                //var_dump($user);die;

                $patientData->setSymptome($values->symptome);
                $patientData->setConstat($values->constat);
                $patientData->setTaille($values->taille);
                $patientData->setPoids($values->poids);
                $patientData->setGroupe($values->groupe);

                // $post=  $serializer->deserialize($values, Appointement::class, 'json');
                $erreur = $validator->validate($patientData);
                if (count($erreur) > 0) {
                    return $this->json(['status' => 400, 'message' => 'Donnees saisie incorrect']);
                }
                $entityManager->persist($patientData);
                $entityManager->flush();

                return $this->json($patientData, 201, [], ['groups' => 'patient']);
            } catch (NotEncodableValueException $e) {
                return $this->json([
                        'status' => 400,
                        'message' => $e->getMessage(),
                    ]);
            }
        } else {
            return $this->json(['status' => 400, 'message' => 'Remplissez tout les donnees']);
        }

        return $this->json(['status' => 400, 'message' => " Ce Rendez-vous n'existe pas"]);
    }

    /**
     * @Route("/patient/examen", methods={"POST"})
     */
    public function examenPatient(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->denyAccessUnlessGranted('ROLE_MEDECIN', null, 'Accès non autorisé');

        $values = json_decode($request->getContent());

        if (isset($values->heure,$values->symptome,$values->patient,$values->date,$values->taille,$values->poids,$values->constat,$values->groupe,$values->quantite,$values->dosage,$values->medicament)) {
            $medecin = $entityManager->getRepository(User::class)->find($this->getUser()->getId());
            $patient_id = intval(preg_replace('~[^0-9]~', '', $values->patient));
            $patient = $entityManager->getRepository(Patient::class)->find($patient_id);

            $patientData = new PatientData();
            $ordonnance = new Ordonnance();

            $ordonnance->setMedicament($values->medicament);
            $ordonnance->setQuantite($values->quantite);
            // $ordonnance->setPatientData($patientDataId);
            $ordonnance->setDosage($values->dosage);
            $ordonnance->setNumero('kdo'.$this->generer_numero());

            $entityManager->persist($ordonnance);
            $entityManager->flush();

            $ordonnanceId = $entityManager->getRepository(Ordonnance::class)->find($ordonnance->id);
            $patientData->setSymptome($values->symptome);
            $patientData->setTaille($values->taille);
            $patientData->setPoids($values->poids);
            $patientData->setGroupe($values->groupe);
            $patientData->setHeure($values->heure);
            $patientData->setDate($values->date);
            $patientData->setConstat($values->constat);
            $patientData->setPatient($patient);
            $patientData->setMedecin($medecin);
            $patientData->setOrdonnance($ordonnanceId);

            $entityManager->persist($patientData);
            $entityManager->flush();

            $erreurPatientData = $validator->validate($patientData);
            $erreurOrdonnance = $validator->validate($ordonnance);
            if (count($erreurPatientData) > 0 || count($erreurOrdonnance) > 0) {
                $entityManager->remove($patientData);
                $entityManager->flush();

                return $this->json(['status' => 400, 'message' => 'Donnees saisie incorrect']);
            }
            $patient->setIsVisit(true);
            $entityManager->persist($patient);
            $entityManager->persist($ordonnance);
            $entityManager->flush();

            return $this->json(['status' => 201, 'message' => 'Patient examiné']);
        }

        return $this->json(['status' => 401, 'message' => 'Veillez remplir les donnees requise']);
    }
}
