<?php

namespace App\Controller\Public;

use App\Entity\Photo;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class PhotoController extends AbstractController
{
    #[Route('/photo/{uuid}/image', name: 'public_photo_image')]
    public function image(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Photo $photo,
    ): Response {
        $path = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/' . $photo->getImageName();

        if (!file_exists($path)) {
            throw new NotFoundHttpException('Photo introuvable.');
        }

        return new BinaryFileResponse($path);
    }
}
