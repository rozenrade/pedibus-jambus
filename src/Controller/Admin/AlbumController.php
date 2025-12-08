<?php
// src/Controller/Admin/AlbumController.php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\AlbumType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/albums')]
class AlbumController extends AbstractController
{
    #[Route('/', name: 'admin_album_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $albums = $em->getRepository(Album::class)->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('admin/album/index.html.twig', [
            'albums' => $albums,
        ]);
    }
    
    #[Route('/new', name: 'admin_album_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($album);
            $em->flush();

            $this->addFlash('success', 'Album créé avec succès !');
            
            // Option 1: Rediriger vers la liste
            return $this->redirectToRoute('admin_album_index');
            
            // Option 2: Rediriger vers l'ajout de photos directement
            // return $this->redirectToRoute('admin_photo_new_to_album', ['albumId' => $album->getId()]);
        }

        return $this->render('admin/album/new.html.twig', [
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'admin_album_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Album $album): Response
    {
        return $this->render('admin/album/show.html.twig', [
            'album' => $album,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'admin_album_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Album $album, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Album modifié avec succès !');
            return $this->redirectToRoute('admin_album_show', ['id' => $album->getId()]);
        }

        return $this->render('admin/album/edit.html.twig', [
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}/delete', name: 'admin_album_delete', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Album $album, EntityManagerInterface $em): Response
    {
        // Vérifier si l'album a des photos
        if ($album->getPhotos()->count() > 0) {
            $this->addFlash('warning', 'Impossible de supprimer un album contenant des photos. Supprimez d\'abord les photos.');
            return $this->redirectToRoute('admin_album_show', ['id' => $album->getId()]);
        }
        
        if ($request->isMethod('POST')) {
            // Vérifier le token CSRF
            $submittedToken = $request->request->get('_token');
            if ($this->isCsrfTokenValid('delete-album-' . $album->getId(), $submittedToken)) {
                // VichUploader s'occupe de supprimer le fichier automatiquement
                $em->remove($album);
                $em->flush();
                
                $this->addFlash('success', 'Album supprimé avec succès !');
                return $this->redirectToRoute('admin_album_index');
            }
        }
        
        return $this->render('admin/album/delete.html.twig', [
            'album' => $album,
        ]);
    }
    
    #[Route('/{id}/stats', name: 'admin_album_stats', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function stats(Album $album): Response
    {
        $photoCount = $album->getPhotos()->count();
        $lastPhoto = $photoCount > 0 ? $album->getPhotos()->last() : null;
        
        return $this->render('admin/album/stats.html.twig', [
            'album' => $album,
            'photo_count' => $photoCount,
            'last_photo_date' => $lastPhoto ? $lastPhoto->getUpdatedAt() : null,
        ]);
    }
    
    #[Route('/{id}/toggle-visibility', name: 'admin_album_toggle_visibility', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleVisibility(Request $request, Album $album, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle-visibility-' . $album->getId(), $request->request->get('_token'))) {
            $album->setIsPublic(!$album->isPublic());
            $em->flush();
            
            $status = $album->isPublic() ? 'public' : 'privé';
            $this->addFlash('success', "L'album est maintenant {$status}.");
        }
        
        return $this->redirectToRoute('admin_album_show', ['id' => $album->getId()]);
    }
    
    #[Route('/{id}/set-cover-from-photo/{photoId}', name: 'admin_album_set_cover_from_photo', methods: ['POST'], requirements: ['id' => '\d+', 'photoId' => '\d+'])]
    public function setCoverFromPhoto(Request $request, Album $album, EntityManagerInterface $em, int $photoId): Response
    {
        if ($this->isCsrfTokenValid('set-cover-' . $album->getId(), $request->request->get('_token'))) {
            $photo = $em->getRepository(\App\Entity\Photo::class)->find($photoId);
            
            if ($photo && $photo->getAlbum() === $album) {
                // Créer une copie du fichier pour la couverture
                $photoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/' . $photo->getImageName();
                $coverPath = $this->getParameter('kernel.project_dir') . '/public/uploads/albums/covers/album-' . $album->getId() . '-cover.jpg';
                
                if (file_exists($photoPath)) {
                    copy($photoPath, $coverPath);
                    $album->setCoverImage('album-' . $album->getId() . '-cover.jpg');
                    $em->flush();
                    
                    $this->addFlash('success', 'Photo définie comme couverture de l\'album !');
                }
            }
        }
        
        return $this->redirectToRoute('admin_album_show', ['id' => $album->getId()]);
    }
}