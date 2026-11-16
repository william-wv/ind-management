<?php

namespace Tests\Unit\Services;

use App\Models\Pallet;
use App\Services\UploaderPhotos;
use PHPUnit\Framework\TestCase;

class UploaderPhotosTest extends TestCase
{
    private string $imagePath;

    protected function setUp(): void
    {
        $this->imagePath = __DIR__ . '/../../files/avatar_test.jpg';
    }

    public function test_valid_image_passes_mime_and_size_validation(): void
    {
        $pallet = new Pallet();
        $uploader = new UploaderPhotos($pallet);

        $this->assertTrue($uploader->isValidImage([
            'name' => 'photo.jpg',
            'tmp_name' => $this->imagePath,
            'size' => filesize($this->imagePath),
            'error' => UPLOAD_ERR_OK,
        ]));
    }

    public function test_image_larger_than_limit_is_rejected(): void
    {
        $pallet = new Pallet();
        $uploader = new UploaderPhotos($pallet);

        $this->assertFalse($uploader->isValidImage([
            'name' => 'photo.jpg',
            'tmp_name' => $this->imagePath,
            'size' => 2 * 1024 * 1024 + 1,
            'error' => UPLOAD_ERR_OK,
        ]));
        $this->assertSame(
            'Tamanho do arquivo maior que 2mb',
            $pallet->errors('photo')
        );
    }

    public function test_disallowed_content_is_rejected_even_with_image_extension(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($tmpFile, '<?php echo "not an image";');

        try {
            $pallet = new Pallet();
            $uploader = new UploaderPhotos($pallet);

            $this->assertFalse($uploader->isValidImage([
                'name' => 'disguised.png',
                'tmp_name' => $tmpFile,
                'size' => filesize($tmpFile),
                'error' => UPLOAD_ERR_OK,
            ]));
            $this->assertSame('Formato de imagem não permitido', $pallet->errors('photo'));
        } finally {
            unlink($tmpFile);
        }
    }

    public function test_upload_error_is_reported_without_reading_a_file(): void
    {
        $pallet = new Pallet();
        $uploader = new UploaderPhotos($pallet);

        $this->assertFalse($uploader->isValidImage([
            'name' => 'photo.jpg',
            'tmp_name' => '',
            'size' => 0,
            'error' => UPLOAD_ERR_INI_SIZE,
        ]));
        $this->assertSame(
            'Arquivo excede o limite permitido',
            $pallet->errors('photo')
        );
    }

    public function test_upload_rejects_an_invalid_batch(): void
    {
        $pallet = new Pallet();
        $uploader = new UploaderPhotos($pallet);

        $result = $uploader->upload([
            'name' => ['valid.jpg', 'too-large.jpg'],
            'tmp_name' => [$this->imagePath, ''],
            'size' => [filesize($this->imagePath), 0],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_INI_SIZE],
        ]);

        $this->assertFalse($result);
        $this->assertSame(
            'Arquivo excede o limite permitido',
            $pallet->errors('photo')
        );
    }
}
