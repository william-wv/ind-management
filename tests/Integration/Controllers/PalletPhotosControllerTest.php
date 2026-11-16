<?php

namespace Tests\Integration\Controllers;

use App\Models\Pallet;
use App\Models\Photo;
use App\Models\User;
use Core\Constants\Constants;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

class PalletPhotosControllerTest extends TestCase
{
    private Client $client;
    private CookieJar $cookies;
    private Pallet $pallet;
    private string $fixturePath;

    /** @var list<string> */
    private array $filesToClean = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->fixturePath = Constants::rootPath()->join('tests/files/avatar_test.jpg');
        $this->cookies = new CookieJar();
        $this->client = new Client([
            'allow_redirects' => false,
            'base_uri' => 'http://web:8080',
            'cookies' => $this->cookies,
        ]);

        $admin = new User([
            'name' => 'Photo test admin',
            'email' => 'photo-test-admin@example.com',
            'role' => 'admin',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $this->assertTrue($admin->save());

        $this->pallet = new Pallet([
            'item' => 'Photo test pallet',
            'width' => '80',
            'length' => '120',
            'location' => 'Integration test',
            'amount' => 5,
            'minimum_amount' => 1,
        ]);
        $this->assertTrue($this->pallet->save());

        $loginResponse = $this->client->post('/login', [
            'form_params' => [
                'user[email]' => $admin->email,
                'user[password]' => '123456',
            ],
        ]);
        $this->assertSame(302, $loginResponse->getStatusCode());
    }

    public function tearDown(): void
    {
        foreach ($this->filesToClean as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_uploaded_images_are_displayed_and_can_be_deleted(): void
    {
        $uploadResponse = $this->uploadImages(2);
        $this->assertSame(302, $uploadResponse->getStatusCode());
        $this->assertSame('/pallets', $uploadResponse->getHeaderLine('Location'));

        $photos = $this->pallet->photos()->get();
        $this->assertCount(2, $photos);

        foreach ($photos as $photo) {
            $filePath = $this->absolutePhotoPath($photo->url);
            $this->filesToClean[] = $filePath;
            $this->assertFileExists($filePath);
        }

        $galleryResponse = $this->client->get("/pallets/{$this->pallet->id}/gallery");
        $this->assertSame(200, $galleryResponse->getStatusCode());
        $galleryHtml = (string) $galleryResponse->getBody();
        $this->assertSame(2, substr_count($galleryHtml, '<img'));
        foreach ($photos as $photo) {
            $this->assertStringContainsString('src="' . $photo->url . '"', $galleryHtml);
        }

        $photoToDelete = $photos[0];
        $deleteResponse = $this->client->delete(
            "/pallets/{$this->pallet->id}/photos/{$photoToDelete->id}"
        );

        $this->assertSame(302, $deleteResponse->getStatusCode());
        $this->assertSame(
            "/pallets/{$this->pallet->id}/gallery",
            $deleteResponse->getHeaderLine('Location')
        );
        $this->assertNull(Photo::findById($photoToDelete->id));
        $this->assertFileDoesNotExist($this->absolutePhotoPath($photoToDelete->url));

        $updatedGallery = $this->client->get("/pallets/{$this->pallet->id}/gallery");
        $this->assertSame(1, substr_count((string) $updatedGallery->getBody(), '<img'));
    }

    private function uploadImages(int $count): ResponseInterface
    {
        $contents = file_get_contents($this->fixturePath);
        $this->assertNotFalse($contents);

        $multipart = [[
            'name' => 'id',
            'contents' => (string) $this->pallet->id,
        ]];

        for ($index = 0; $index < $count; $index++) {
            $multipart[] = [
                'name' => 'images[]',
                'contents' => $contents,
                'filename' => "gallery-photo-{$index}.jpg",
            ];
        }

        return $this->client->post('/pallets/upload', ['multipart' => $multipart]);
    }

    private function absolutePhotoPath(string $url): string
    {
        return (string) Constants::rootPath()->join('public' . $url);
    }
}
