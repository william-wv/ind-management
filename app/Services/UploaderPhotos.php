<?php

namespace App\Services;

use finfo;
use App\Models\Pallet;
use Core\Constants\Constants;
use RuntimeException;

class UploaderPhotos
{
    private int $sizeLimit =  2 * 1024 * 1024;
    /** @var list<string> */
    private array $mimeTypeValid = ['image/png', 'image/jpeg','image/webp'];
    private Pallet $pallet;


    public function __construct(Pallet $pallet)
    {
        $this->pallet = $pallet;
    }



    /**
     * @param array{
     *     name: array<int, string>,
     *     tmp_name: array<int, string>,
     *     size: array<int, int>,
     *     error: array<int, int>
     * } $content
     */
    public function upload(array $content): bool
    {
        $images = [];

        foreach ($content['name'] as $key => $name) {
            $images[] = [
                'name'     => $content['name'][$key],
                'tmp_name' => $content['tmp_name'][$key],
                'size'     => $content['size'][$key],
                'error'    => $content['error'][$key]
            ];
        }

        foreach ($images as $image) {
            if (!$this->isValidImage($image)) {
                return false;
            }
        }

        foreach ($images as $image) {
            if (!$this->store($image)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array{name: string, tmp_name: string, size: int, error: int} $image
     */
    public function isValidImage(array $image): bool
    {
        if ($image['error'] !== UPLOAD_ERR_OK) {
            $this->pallet->addError('photo', $this->uploadErrorMessage($image['error']));
            return false;
        }

        if (!$image['tmp_name'] || !$this->validMimeType($image['tmp_name'])) {
            $this->pallet->addError('photo', 'Formato de imagem não permitido');
            return false;
        }

        if (!$this->validSize($image['size'])) {
            $this->pallet->addError('photo', 'Tamanho do arquivo maior que 2mb');
            return false;
        }

        return true;
    }

    private function validMimeType(string $tmpPath): false|string
    {
        $mimeType  = $this->detectMimeType($tmpPath);

        if (!$mimeType) {
            return false;
        }

        foreach ($this->mimeTypeValid as $typeValid) {
            if ($typeValid === $mimeType) {
                return $mimeType;
            }
        }

        return false;
    }

    private function validSize(int $size): bool
    {
        if ($size > 0 && $size <= $this->sizeLimit) {
            return true;
        }

        return false;
    }

    /**
     * @param array{name: string, tmp_name: string, size: int, error: int} $image
     */
    private function store(array $image): bool
    {
        if ($this->pallet->id === null) {
            $this->pallet->addError('photo', 'Salve o pallet antes de enviar as fotos.');
            return false;
        }

        $mimeType = $this->detectMimeType($image['tmp_name']);

        $extension = match ($mimeType) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => throw new RuntimeException('Tipo de imagem não suportado.'),
        };

        $relativeDirectory = "/assets/uploads/{$this->pallet::table()}/{$this->pallet->id}/";
        $storageDirectory = Constants::rootPath()->join('public' . $relativeDirectory);

        if (
            !is_dir($storageDirectory)
            && !mkdir(directory: $storageDirectory, recursive: true)
            && !is_dir($storageDirectory)
        ) {
            throw new RuntimeException('Não foi possível criar a pasta de fotos.');
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $destinationPath = $storageDirectory . $fileName;

        if (!move_uploaded_file($image['tmp_name'], $destinationPath)) {
            $error = error_get_last();
            throw new RuntimeException(
                'Não foi possível armazenar a foto: ' . ($error['message'] ?? 'erro desconhecido')
            );
        }

        $photo = $this->pallet->photos()->new([
            'url' => $relativeDirectory . $fileName,
            'original_name' => $image['name'],
            'mime_type' => $mimeType,
            'size_bytes' => $image['size'],
        ]);

        $photoSaved = false;
        try {
            $photoSaved = $photo->save();
        } finally {
            if (!$photoSaved && is_file($destinationPath) && !unlink($destinationPath)) {
                throw new RuntimeException('Não foi possível remover o arquivo após falha ao salvar a foto.');
            }
        }

        if (!$photoSaved) {
            $this->pallet->addError('photo', 'Não foi possível registrar a foto.');
            return false;
        }

        return true;
    }

    private function uploadErrorMessage(int $error): string
    {

        return match ($error) {
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede o limite permitido',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o limite do formulário',
            UPLOAD_ERR_PARTIAL => 'Upload parcial',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário ausente',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar arquivo',
            UPLOAD_ERR_EXTENSION => 'Upload interrompido por extensão PHP',
            default => 'Erro desconhecido'
        };
    }

    private function detectMimeType(string $tmpPath): string|false
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($tmpPath);
    }


    public function destroy(int $id): bool
    {
        $photo = $this->pallet->photos()->findById($id);

        if (!$photo) {
            $this->pallet->addError('photo', 'Não foi possível excluir, arquivo não encontrado');
            return false;
        }

        $relativeDirectory = "/assets/uploads/{$this->pallet::table()}/{$this->pallet->id}/";
        $fileName = basename($photo->url);
        $absolutePath = (string) Constants::rootPath()
            ->join('public' . $relativeDirectory . $fileName);

        if (is_file($absolutePath) && !unlink($absolutePath)) {
            $this->pallet->addError('photo', 'Não foi possível remover o arquivo.');
            return false;
        }

        if (!$photo->destroy()) {
            $this->pallet->addError('photo', 'Não foi possível remover o registro da foto.');
            return false;
        }

        return true;
    }


    public function destroyAll(): bool
    {
        foreach ($this->pallet->photos()->get() as $photo) {
            if (!$this->destroy($photo->id)) {
                return false;
            }
        }

        return true;
    }
}
