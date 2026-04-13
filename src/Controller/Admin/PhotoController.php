<?php
// src/Controller/Admin/PhotoController.php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\Photo;
use App\Form\PhotoType;
use App\Form\PhotoMultipleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/photos')]
class PhotoController extends AbstractController
{
    #[Route('/', name: 'admin_photo_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $photos = $em->getRepository(Photo::class)->findBy([], ['updatedAt' => 'DESC']);

        return $this->render('admin/photo/index.html.twig', [
            'photos' => $photos,
        ]);
    }

    #[Route('/new', name: 'admin_photo_new', methods: ['GET', 'POST'])]
    // Dans PhotoController.php
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $photo = new Photo();
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // DEBUG : Afficher les informations
            dump($photo->getImageFile());  // Fichier uploadé
            dump($photo->getImageName());  // Nom du fichier (doit être rempli par Vich)

            $em->persist($photo);
            $em->flush();

            // DEBUG après flush
            dump($photo->getImageName());  // Doit être non-null

            $this->addFlash('success', 'Photo ajoutée avec succès !');

            // Rediriger vers l'album si défini, sinon vers la liste
            if ($photo->getAlbum()) {
                return $this->redirectToRoute('admin_album_show', ['id' => $photo->getAlbum()->getId()]);
            }

            return $this->redirectToRoute('admin_photo_index');
        }

        return $this->render('admin/photo/new.html.twig', [
            'photo' => $photo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new-to-album/{albumId}', name: 'admin_photo_new_to_album', methods: ['GET', 'POST'], requirements: ['albumId' => '\d+'])]
    public function newToAlbum(Request $request, EntityManagerInterface $em, int $albumId): Response
    {
        $album = $em->getRepository(Album::class)->find($albumId);

        if (!$album) {
            throw $this->createNotFoundException('Album non trouvé');
        }

        $photo = new Photo();
        $photo->setAlbum($album);

        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($photo);
            $em->flush();

            $this->addFlash('success', 'Photo ajoutée à l\'album avec succès !');

            // Options de redirection
            if ($request->request->get('add_another')) {
                return $this->redirectToRoute('admin_photo_new_to_album', ['albumId' => $albumId]);
            }

            return $this->redirectToRoute('admin_album_show', ['id' => $albumId]);
        }

        return $this->render('admin/photo/new_to_album.html.twig', [
            'photo' => $photo,
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/multiple-to-album/{albumId}', name: 'admin_photo_multiple_to_album', methods: ['GET', 'POST'], requirements: ['albumId' => '\d+'])]
    public function multipleToAlbum(Request $request, EntityManagerInterface $em, int $albumId): Response
    {
        $album = $em->getRepository(Album::class)->find($albumId);

        if (!$album) {
            throw $this->createNotFoundException('Album non trouvé');
        }

        $form = $this->createForm(PhotoMultipleType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();

            $count = 0;
            foreach ($images as $imageFile) {
                $photo = new Photo();
                $photo->setAlbum($album);
                $photo->setImageFile($imageFile); // VichUploader gère l'upload

                $em->persist($photo);
                $count++;
            }

            $em->flush();

            $this->addFlash('success', sprintf('%d photo(s) ajoutée(s) avec succès !', $count));
            return $this->redirectToRoute('admin_album_show', ['id' => $albumId]);
        }

        return $this->render('admin/photo/multiple_to_album.html.twig', [
            'form' => $form->createView(),
            'album' => $album,
        ]);
    }

    #[Route('/{id}', name: 'admin_photo_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Photo $photo): Response
    {
        return $this->render('admin/photo/show.html.twig', [
            'photo' => $photo,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_photo_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Photo $photo, EntityManagerInterface $em): Response
    {

        $form = $this->createForm(PhotoType::class, $photo, ['is_edit' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Photo modifiée avec succès !');
            return $this->redirectToRoute('admin_photo_show', ['id' => $photo->getId()]);
        }

        return $this->render('admin/photo/edit.html.twig', [
            'photo' => $photo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/assign-to-album', name: 'admin_photo_assign_to_album', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function assignToAlbum(Request $request, Photo $photo, EntityManagerInterface $em): Response
    {
        if ($photo->getAlbum()) {
            $this->addFlash('warning', 'Cette photo a déjà un album. Utilisez "Déplacer vers un autre album".');
            return $this->redirectToRoute('admin_photo_show', ['id' => $photo->getId()]);
        }

        $albums = $em->getRepository(Album::class)->findAll();

        if ($request->isMethod('POST')) {
            $albumId = $request->request->get('album_id');
            $album = $em->getRepository(Album::class)->find($albumId);

            if ($album && $this->isCsrfTokenValid('assign-photo-' . $photo->getId(), $request->request->get('_token'))) {
                $photo->setAlbum($album);
                $em->flush();

                $this->addFlash('success', 'Photo assignée à un album avec succès !');
                return $this->redirectToRoute('admin_photo_show', ['id' => $photo->getId()]);
            }
        }

        return $this->render('admin/photo/assign_to_album.html.twig', [
            'photo' => $photo,
            'albums' => $albums,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_photo_delete', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Photo $photo, EntityManagerInterface $em): Response
    {
        $albumId = $photo->getAlbum() ? $photo->getAlbum()->getId() : null;

        if ($request->isMethod('POST')) {
            $submittedToken = $request->request->get('_token');
            if ($this->isCsrfTokenValid('delete-photo-' . $photo->getId(), $submittedToken)) {
                // VichUploader s'occupe de supprimer le fichier automatiquement
                $em->remove($photo);
                $em->flush();

                $this->addFlash('success', 'Photo supprimée avec succès !');

                // Rediriger vers l'album si la photo en avait un, sinon vers la liste
                if ($albumId) {
                    return $this->redirectToRoute('admin_album_show', ['id' => $albumId]);
                }

                return $this->redirectToRoute('admin_photo_index');
            }
        }

        return $this->render('admin/photo/delete.html.twig', [
            'photo' => $photo,
        ]);
    }

    #[Route('/{id}/move-to-album', name: 'admin_photo_move_to_album', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function moveToAlbum(Request $request, Photo $photo, EntityManagerInterface $em): Response
    {
        $albums = $em->getRepository(Album::class)->findAll();

        if ($request->isMethod('POST')) {
            $albumId = $request->request->get('album_id');
            $album = $em->getRepository(Album::class)->find($albumId);

            if ($album && $this->isCsrfTokenValid('move-photo-' . $photo->getId(), $request->request->get('_token'))) {
                $photo->setAlbum($album);
                $em->flush();

                $this->addFlash('success', 'Photo déplacée vers un autre album avec succès !');
                return $this->redirectToRoute('admin_photo_show', ['id' => $photo->getId()]);
            }
        }

        return $this->render('admin/photo/move_to_album.html.twig', [
            'photo' => $photo,
            'albums' => $albums,
        ]);
    }

    #[Route('/{id}/set-as-album-cover', name: 'admin_photo_set_as_album_cover', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function setAsAlbumCover(Request $request, Photo $photo, EntityManagerInterface $em): Response
    {
        $album = $photo->getAlbum();

        if (!$album) {
            $this->addFlash('error', 'Cette photo n\'appartient à aucun album.');
            return $this->redirectToRoute('admin_photo_show', ['id' => $photo->getId()]);
        }

        if ($this->isCsrfTokenValid('set-album-cover-' . $photo->getId(), $request->request->get('_token'))) {
            // VichUploader pour la couverture de l'album
            $album->setCoverImageFile($photo->getImageFile());
            $em->flush();

            $this->addFlash('success', 'Photo définie comme couverture de l\'album !');
            return $this->redirectToRoute('admin_album_show', ['id' => $album->getId()]);
        }

        return $this->redirectToRoute('admin_photo_show', ['id' => $photo->getId()]);
    }
}
