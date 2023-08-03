<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Entity\Publication;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PublicationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/', name: 'app_home')]
class HomeController extends AbstractController
{
    public function __construct(
        // private EntityManagerInterface $entityManager,
        private PublicationRepository $publicationRepository,
        private PaginatorInterface $paginator
    ){

    }
    
    #[Route('/', name: 'app_home')]
    public function index(PublicationRepository $publicationRepository, Request $request): Response
    {
        $qb = $this->publicationRepository->getQbAll();

        $pagination = $this->paginator->paginate(
            $qb,
            $request->query->getInt('page', 1), // réxupérer le get
            10                                  // nbr element par page
        );

        return $this->render('home/index.html.twig', [
            // 'publications' => $publicationRepository->findAll(),
            'publications' => $pagination
        ]);
    }

    #[Route('/{id}', name: 'app_publication_show', methods: ['GET', 'POST'])]
    public function show(Publication $publication, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $commentContent = $form->get('content')->getData();
            $comment->setContent($commentContent);
            $comment->setPublication($publication);

            $timezoneParis = new DateTimeZone('Europe/Paris');
            $dateTimeParis = new DateTime('now', $timezoneParis);

            $comment->setCreatedAt($dateTimeParis);
            $form = $this->createForm(CommentType::class, $comment);

            $entityManager->persist($comment);
            $entityManager->flush();
        }
    
        return $this->render('home/show.html.twig', [
            'publication' => $publication,
            'form' => $form->createView()
        ]);
    }
}
