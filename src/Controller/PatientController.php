<?php

namespace App\Controller;

use App\Entity\Appointement;
use App\Entity\Patient;
use App\Entity\User;
use App\Repository\AppointementRepository;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */
class PatientController extends AbstractController
{
    public function generer_matricule($long = 5)
    {
        $numero = '';
        for ($i = 0; $i < $long; ++$i) {
            $array = ['4', '0', '8', '9', '2', '3', '4', '3', '5', '6', '7', '8', '9', '0', '1'];
            $numero .= $array[rand(0, 14)];
        }

        return $numero;
    }

    /**
     * @Route("/patient", methods={"GET"})
     */
    public function findPatient(PatientRepository $patientRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR AFFICHER LES PATIENTS
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès refusé');

        return $this->json($patientRepository->findAll(), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/patient/medecin", methods={"GET"})
     */
    public function findPatientMedecin(PatientRepository $patientRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR AFFICHER LES PATIENTS
        $this->denyAccessUnlessGranted('ROLE_MEDECIN', null, 'Accès non autorisé');

        return $this->json($this->getUser()->getPatient(), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/patient/{id}", methods={"GET"})
     */
    public function findPatientByid($id, PatientRepository $patientRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR AFFICHER LE PATIENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        return $this->json($patientRepository->find($id), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/patient/dataids/{id}", methods={"GET"})
     */
    public function findPatientByPatientDataId($id, PatientRepository $patientRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER LES DONNEES
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        return $this->json($patientRepository->findPatientByPatientDataId($id), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/patient/{id}", methods={"DELETE"})
     */
    public function deletePatient(EntityManagerInterface $entityManager, $id, PatientRepository $patientRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR SUPPRIMER LE PATIENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé ');

        $patient = $patientRepository->find($id);

        if ($patient) {
            $entityManager->remove($patient);

            $entityManager->flush();

            return $this->json(['status' => 201, 'message' => 'Patient supprimé']);
        }

        return $this->json(['status' => 401, 'message' => "Patient n'existe pas"]);
    }

    /**
     * @Route("/patient/{id}", name="patient_update", methods={"PUT"})
     */
    public function updatePatient(EntityManagerInterface $entityManager, $id, PatientRepository $patientRepository, Request $request, AppointementRepository $appointementRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR MODIFIER LE PATIENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        $patient = $patientRepository->find($id);

        if (!empty($patient)) {
            $values = json_decode($request->getContent());

            $medecin_id = intval(preg_replace('~[^0-9]~', '', $values->medecin));
            $medecin = $entityManager->getRepository(User::class)->find($medecin_id);

            $patient->setPrenom($values->prenom);
            $patient->setNom($values->nom);
            $patient->setAdresse($values->adresse);
            $patient->setAge($values->age);
            $patient->setSexe($values->sexe);
            $patient->setTel($values->tel);

            if (isset($values->medecin)) {
                $patient->setMedecin($medecin);
            }

            $entityManager->persist($patient);
            $entityManager->flush();

            return $this->json(['status' => 201, 'message' => 'Patient modifié']);
        }

        return $this->json(['status' => 401, 'message' => 'Veillez remplir tout les donnees']);
    }

    /**
     * @Route("/patient", name="addpatient", methods={"POST"})
     */
    public function addPatient(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR AJOUTER LE PATIENT
        $this->denyAccessUnlessGranted('ROLE_SECRETAIRE', null, 'Accès non autorisé');

        $values = json_decode($request->getContent());

        if (isset($values->nom,$values->sexe,$values->adresse,$values->age,$values->prenom,$values->tel,
                     $values->motif,$values->date,$values->heureFin,$values->heureDebut,$values->medecin)) {
            $medecin_id = intval(preg_replace('~[^0-9]~', '', $values->medecin));
            $medecin = $entityManager->getRepository(User::class)->find($medecin_id);

            $patient = new Patient();

            $patient->setPrenom($values->prenom);
            $patient->setNom($values->nom);
            $patient->setSexe($values->sexe);
            $patient->setAdresse($values->adresse);
            $patient->setAge($values->age);
            $patient->setTel($values->tel);
            $patient->setIsVisit(false);
            $patient->setMedecin($medecin);
            $patient->setMatricule('kds'.$this->generer_matricule());

            $entityManager->persist($patient);

            $appointement = new Appointement();

            $appointement->setMotif($values->motif);
            $appointement->setDate($values->date);
            $appointement->setPatient($patient);
            $appointement->setIsEnabled(true);
            $appointement->setMedecin($medecin);
            $appointement->setHeureDebut($values->heureDebut);
            $appointement->setHeureFin($values->heureFin);

            $erreurPatient = $validator->validate($patient);
            $erreurAppointement = $validator->validate($appointement);
            if (count($erreurPatient) > 1 || count($erreurAppointement) > 1) {
                return $this->json(['status' => 400, 'message' => 'Donnees saisie incorrect']);
            }
            $entityManager->persist($appointement);
            $entityManager->flush();

            return $this->json(['status' => 201, 'message' => 'Patient ajoute']);
        }

        return $this->json(['status' => 401, 'message' => 'Veillez remplir les donnees requise']);
    }
}
