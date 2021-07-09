<?php

namespace App\Controller;

use App\Entity\Ordonnance;
use App\Entity\PatientData;
use App\Repository\OrdonnanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */
class OrdonnanceController extends AbstractController
{
    public function generer_numero($long = 5)
    {
        $numero = '';
        for ($i = 0; $i < $long; ++$i) {
            $array = ['4', '0', '8', '9', '2', '3', '4', '3', '5', '6', '7', '8', '9', '0', '1'];
            $numero .= $array[rand(0, 14)];
        }

        return $numero;
    }

    /**
     * @Route("/ordonnance", name="ordonnance", methods={"GET"})
     */
    public function findOrdonnance(OrdonnanceRepository $ordonnanceRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER LES  APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_MEDECIN', null, 'Accès non autorisé ');

        return $this->json($ordonnanceRepository->findAll(), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/patientData/ordonnance", name="patientDataOrdonnance", methods={"GET"})
     */
    public function findpatientDataOrdonnance(OrdonnanceRepository $ordonnanceRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER LES  APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_MEDECIN', null, 'Accès non autorisé ');

        return $this->json($this->getUser()->getPatientData(), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/ordonnance/{id}", methods={"GET"})
     */
    public function findOrdonnanceByid($id, OrdonnanceRepository $ordonnanceRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR RECUPERER UN APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_MEDECIN', null, 'Accès non autorisé ');

        return $this->json($ordonnanceRepository->find($id), 200, [], ['groups' => 'patient']);
    }

    /**
     * @Route("/ordonnance", name="ordonnance_post",methods={"POST"})
     */
    public function addOrdonnance(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR AJOUTER UN APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_MEDECIN', null, 'Accès non autorisé ');

        $values = json_decode($request->getContent());
        if (isset($values->medicament,$values->quantite,$values->patientData,$values->dosage)) {
            try {
                $patient_id = intval(preg_replace('~[^0-9]~', '', $values->patientData));
                $patient = $entityManager->getRepository(PatientData::class)->find(intval($patient_id));
                // var_dump($patient);die;
                $ordonnance = new Ordonnance();

                $ordonnance->setMedicament($values->medicament);
                $ordonnance->setQuantite($values->quantite);
                $ordonnance->setPatient($patient);
                $ordonnance->setDosage($values->dosage);
                $ordonnance->setNumero('kdo' + $this->generer_numero);

                // $post=  $serializer->deserialize($values, Appointement::class, 'json');
                $erreur = $validator->validate($ordonnance);
                if (count($erreur) > 0) {
                    return $this->json(['status' => 400, 'message' => 'Donnees saisie incorrect']);
                }
                $entityManager->persist($ordonnance);
                $entityManager->flush();

                return $this->json($ordonnance, 201, [], ['groups' => 'patient']);
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
     * @Route("/ordonnance/{id}", name="ordonnance_delete", methods={"DELETE"})
     */
    public function deleteDelete(EntityManagerInterface $entityManager, $id, OrdonnanceRepository $ordonnanceRepository)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR SUPPRIMER UN APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_MEDECIN', null, 'Accès non autorisé ');

        $ordonnance = $ordonnanceRepository->find($id);

        try {
            if ($ordonnance == null) {
                return $this->json(['status' => 400, 'message' => "Cette Ordonnance n'existe pas"]);
            }

            $entityManager->remove($ordonnance);
            $entityManager->flush();

            return $this->json(['status' => 400, 'message' => 'Ordonnance supprime']);
        } catch (NotEncodableValueException $e) {
            return $this->json(['status' => 400, 'message' => $e->getMessage()]);
        }

        return $this->json(['status' => 400, 'message' => "Ce Ordonnance n'existe pas"]);
    }

    /**
     * @Route("/ordonnance/{id}", methods={"PUT"})
     */
    public function updateOrdonnance(EntityManagerInterface $entityManager, $id, OrdonnanceRepository $ordonnanceRepository, Request $request, ValidatorInterface $validator)
    {
        //AVOIR AU MOINS LE ROLE_SECRETAIRE POUR MODIFIER UN APPOINTEMENT
        $this->denyAccessUnlessGranted('ROLE_MEDECIN', null, 'Accès non autorisé');

        $ordonnance = $ordonnanceRepository->find($id);
        $values = json_decode($request->getContent());

        if ($ordonnance) {
            try {
                $ordonnance->setMedicament($values->medicament);
                $ordonnance->setQuantite($values->quantite);
                $ordonnance->setDosage($values->dosage);

                $erreur = $validator->validate($ordonnance);
                if (count($erreur) > 0) {
                    return $this->json(['status' => 400, 'message' => 'Donnees saisie incorrect']);
                }
                $entityManager->persist($ordonnance);
                $entityManager->flush();

                return $this->json($ordonnance, 201, [], ['groups' => 'patient']);
            } catch (NotEncodableValueException $e) {
                return $this->json([
                        'status' => 400,
                        'message' => $e->getMessage(),
                    ]);
            }
        } else {
            return $this->json(['status' => 400, 'message' => 'Remplissez tout les donnees']);
        }

        return $this->json(['status' => 400, 'message' => " Cette ordonnance n'existe pas"]);
    }
}
