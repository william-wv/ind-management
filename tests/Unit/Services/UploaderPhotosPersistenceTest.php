<?php

namespace Tests\Unit\Services;

use App\Models\Pallet;
use App\Models\Photo;
use App\Services\UploaderPhotos;
use Core\Constants\Constants;
use Tests\TestCase;

class UploaderPhotosPersistenceTest extends TestCase
{
    /** @var list<string> */
    private array $filesToClean = [];
    private int $palletNumber = 0;

    public function tearDown(): void
    {
        foreach ($this->filesToClean as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_destroy_removes_photo_file_and_database_record(): void
    {
        $pallet = $this->createPallet();
        [$photo, $filePath] = $this->createStoredPhoto($pallet);

        $uploader = new UploaderPhotos($pallet);

        $this->assertTrue($uploader->destroy($photo->id));
        $this->assertFileDoesNotExist($filePath);
        $this->assertNull(Photo::findById($photo->id));
    }

    public function test_destroy_all_removes_every_related_photo_and_file(): void
    {
        $pallet = $this->createPallet();
        [$firstPhoto, $firstPath] = $this->createStoredPhoto($pallet);
        [$secondPhoto, $secondPath] = $this->createStoredPhoto($pallet);

        $uploader = new UploaderPhotos($pallet);

        $this->assertTrue($uploader->destroyAll());
        $this->assertFileDoesNotExist($firstPath);
        $this->assertFileDoesNotExist($secondPath);
        $this->assertNull(Photo::findById($firstPhoto->id));
        $this->assertNull(Photo::findById($secondPhoto->id));
        $this->assertSame([], $pallet->photos()->get());
    }

    public function test_destroy_does_not_remove_a_photo_belonging_to_another_pallet(): void
    {
        $pallet = $this->createPallet();
        $otherPallet = $this->createPallet();
        [$otherPhoto, $filePath] = $this->createStoredPhoto($otherPallet);

        $uploader = new UploaderPhotos($pallet);

        $this->assertFalse($uploader->destroy($otherPhoto->id));
        $this->assertFileExists($filePath);
        $this->assertNotNull(Photo::findById($otherPhoto->id));
        $this->assertSame(
            'Não foi possível excluir, arquivo não encontrado',
            $pallet->errors('photo')
        );
    }

    private function createPallet(): Pallet
    {
        $this->palletNumber++;
        $pallet = new Pallet([
            'item' => 'Photo test ' . bin2hex(random_bytes(4)),
            'width' => (string) (80 + $this->palletNumber),
            'length' => (string) (120 + $this->palletNumber),
            'location' => 'Unit test',
            'amount' => 5,
            'minimum_amount' => 1,
        ]);

        $this->assertTrue($pallet->save());

        return $pallet;
    }

    /**
     * @return array{Photo, string}
     */
    private function createStoredPhoto(Pallet $pallet): array
    {
        $relativeDirectory = "/assets/uploads/{$pallet::table()}/{$pallet->id}/";
        $directory = Constants::rootPath()->join('public' . $relativeDirectory);

        if (!is_dir($directory) && !mkdir(directory: $directory, recursive: true) && !is_dir($directory)) {
            $this->fail('Could not create the photo test directory.');
        }

        $fileName = 'unit-' . bin2hex(random_bytes(8)) . '.png';
        $filePath = $directory . $fileName;
        file_put_contents($filePath, 'test image bytes');
        $this->filesToClean[] = $filePath;

        /** @var Photo $photo */
        $photo = $pallet->photos()->new([
            'url' => $relativeDirectory . $fileName,
            'original_name' => $fileName,
            'mime_type' => 'image/png',
            'size_bytes' => filesize($filePath),
        ]);

        $this->assertTrue($photo->save());

        return [$photo, $filePath];
    }
}
