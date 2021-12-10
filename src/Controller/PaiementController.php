<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Form\PaiementType;
use App\Repository\PaiementRepository;
use App\Repository\StudentRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/paiement")
 */
class PaiementController extends AbstractController
{

    private $thisYear;
    private $months;
    private $paiementRepo;

    function __construct(PaiementRepository $paiementRepo)
    {
        $this->paiementRepo = $paiementRepo;
        $this->thisYear = date('Y');
        $this->months = [
            'Janvier' ,  'Février' , 'Mars'      , 'Avril'   ,  'Mai'     , 'Juin'      , 
            'Juillet' ,  'Août'    , 'Septambre' , 'Octobre' ,  'Novembre', 'Décembre'  , 
        ];
    }

    /**
     * @Route("/", name="paiement_index", methods={"GET", "POST"})
     * @Route("/month/{month}", name="paiement_month", methods={"GET", "POST"})
     */
    public function index(Request $request, StudentRepository $StudentRepo, ?string $month = null): Response
    {   
        $year = (int) $this->thisYear;
        $m = (int) date('n') - 1;
        $mois = $this->months[$m] . ' - ' . $year;

        if ($request->isMethod('GET') && ($request->query->get('year') != null)) {
            $year = $request->query->get('year');
        }elseif (!is_null($month)) {
            $year = (int) explode(' - ', $month)[1];
            $mois = $month;
        }
        

        $years = [];
        for ($i=0; $i < 5 ; $i++) { 
            $years[] = (int) $this->thisYear - $i; 
        }

        $months = [];

        foreach ($this->months as $value) {
            $months[] = $value . ' - ' . $year;
        }

        $students = $StudentRepo->findAll();

        return $this->render('paiement/index.html.twig', [
            'years' => $years,
            'months' => $months,
            'year' => $year,
            'students' => $students,
            'mois' => $mois,
            //'paiements' => $this->paiementRepo->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="paiement_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $paiement = new Paiement();
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($paiement);
            $entityManager->flush();

            return $this->redirectToRoute('paiement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('paiement/new.html.twig', [
            'paiement' => $paiement,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="paiement_show", methods={"GET"})
     */
    public function show(Paiement $paiement): Response
    {
        return $this->render('paiement/show.html.twig', [
            'paiement' => $paiement,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="paiement_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Paiement $paiement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('paiement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('paiement/edit.html.twig', [
            'paiement' => $paiement,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="paiement_delete", methods={"POST"})
     */
    public function delete(Request $request, Paiement $paiement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$paiement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($paiement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('paiement_index', [], Response::HTTP_SEE_OTHER);
    }
}
