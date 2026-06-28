<?php
// src/EventSubscriber/ImageResizeSubscriber.php

namespace App\EventSubscriber;

use App\Entity\Album;
use App\Entity\Photo;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;
use Vich\UploaderBundle\Storage\StorageInterface;

class ImageResizeSubscriber implements EventSubscriberInterface
{
    /**
     * [classe => [champ Vich => [largeur max, hauteur max, qualité JPEG]]]
     */
    private const RULES = [
        Photo::class => ['imageFile' => [1920, 1920, 82]],
        Album::class => ['coverImageFile' => [1200, 1200, 82]],
    ];

    public function __construct(private StorageInterface $storage)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_UPLOAD => 'onPostUpload',
        ];
    }

    public function onPostUpload(Event $event): void
    {
        $object = $event->getObject();
        $class = get_class($object);

        if (!isset(self::RULES[$class])) {
            return;
        }

        foreach (self::RULES[$class] as $field => [$maxWidth, $maxHeight, $quality]) {
            $path = $this->storage->resolvePath($object, $field);

            if (!$path || !is_file($path) || str_ends_with(strtolower($path), '.gif')) {
                continue; // on laisse les GIF tranquilles (animation)
            }

            $imagine = new Imagine();
            $image = $imagine->open($path);
            $size = $image->getSize();

            if ($size->getWidth() > $maxWidth || $size->getHeight() > $maxHeight) {
                $image = $image->thumbnail(
                    new Box($maxWidth, $maxHeight),
                    ImageInterface::THUMBNAIL_INSET
                );
            }

            $image->save($path, [
                'jpeg_quality' => $quality,
                'png_compression_level' => 6,
            ]);
        }
    }
}