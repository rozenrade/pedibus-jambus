<?php 

namespace App\Controller\Public;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PublicGalleryController extends AbstractController
{
    #[Route('/galerie', name: 'public_album')]
    public function gallery(AlbumRepository $albumRepository): Response
    {
        // Récupérer uniquement les albums publics, triés par date d'événement (plus récent d'abord)
        $albums = $albumRepository->findBy(
            ['isPublic' => true],
            ['eventDate' => 'DESC', 'createdAt' => 'DESC']
        );
        
        return $this->render('public/gallery/index.html.twig', [
            'albums' => $albums,
        ]);
    }
    
    #[Route('/galerie/{id}', name: 'public_album_show', requirements: ['id' => '\d+'])]
    public function show(int $id, AlbumRepository $albumRepository): Response
    {
        // Récupérer l'album par ID
        $album = $albumRepository->find($id);
        
        // Vérifier si l'album existe et s'il est public
        if (!$album) {
            throw $this->createNotFoundException('Album non trouvé');
        }
        
        if (!$album->isPublic()) {
            throw $this->createAccessDeniedException('Cet album n\'est pas public');
        }
        
        // Les photos sont déjà triées grâce au OrderBy dans l'entité Album
        return $this->render('public/gallery/show.html.twig', [
            'album' => $album,
        ]);
    }
}