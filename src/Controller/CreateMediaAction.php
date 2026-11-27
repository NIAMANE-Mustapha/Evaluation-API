<?php

namespace App\Controller;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
class CreateMediaAction extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request): Media
    {
        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            throw new BadRequestHttpException('Le fichier "file" est requis.');
        }

        $projectDir = $this->getParameter('kernel.project_dir');
        $targetDirectory = $projectDir . '/public/uploads/media';

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        $extension = $uploadedFile->guessExtension() ?? $uploadedFile->getClientOriginalExtension() ?? 'bin';
        $filename = uniqid('media_', true) . '.' . $extension;

        $uploadedFile->move($targetDirectory, $filename);

        $media = new Media();
        $media->setChemin('/uploads/media/' . $filename);

        $this->entityManager->persist($media);
        $this->entityManager->flush();

        return $media;
    }
}
