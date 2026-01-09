<?php
namespace App\Utils;

use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class FileUploader
{
    private string $basePath = 'C:/Users/Equipo/Desktop/proga/kubik/src/assets/salas/';

    public function uploadSingle(
        UploadedFileInterface $file
    ): string {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al subir archivo');
        }

        $this->ensureDirectory();

        $filename = $this->generateFilename($file->getClientFilename());
        $absolutePath = $this->basePath . $filename;

        $file->moveTo($absolutePath);

        return 'assets/salas/' . $filename;
    }

    public function uploadMultiple(array $files): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFileInterface && $file->getError() === UPLOAD_ERR_OK) {
                $paths[] = $this->uploadSingle($file);
            }
        }

        return $paths;
    }

    private function ensureDirectory(): void
    {
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }
    }

    private function generateFilename(string $original): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $original);
        return uniqid() . '_' . $safe;
    }
}
