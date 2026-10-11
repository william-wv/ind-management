<?php

namespace Tests\Integration\Routes;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

/**
 * Hits the real app (nginx :8080 -> php_test) so the middlewares run end to end.
 */
class RouteAccessTest extends TestCase
{
    private Client $client;
    private CookieJar $cookies;

    public function setUp(): void
    {
        parent::setUp();

        $this->createUser('Admin', 'admin@example.com', 'admin');
        $this->createUser('Basic', 'basic@example.com', 'basic');

        $this->cookies = new CookieJar();
        $this->client = new Client([
            'allow_redirects' => false,
            'base_uri' => 'http://web:8080',
            'cookies' => $this->cookies,
        ]);
    }

    // Public routes
    public function test_login_page_is_accessible_without_authentication(): void
    {
        $response = $this->client->get('/login');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Entrar', (string) $response->getBody());
    }

    public function test_login_with_wrong_password_redirects_back_to_login(): void
    {
        $response = $this->client->post('/login', [
            'form_params' => ['user[email]' => 'admin@example.com', 'user[password]' => 'wrong'],
        ]);

        $this->assertRedirectTo('/login', $response);
    }

    // Authenticated routes, without a session
    /**
     * @return array<string, array{string}>
     */
    public static function restrictedRoutes(): array
    {
        return [
            'root' => ['/'],
            'profile' => ['/profile'],
            'users (admin)' => ['/users'],
            'logout' => ['/logout'],
        ];
    }

    #[DataProvider('restrictedRoutes')]
    public function test_restricted_route_redirects_to_login_when_not_authenticated(string $path): void
    {
        $response = $this->client->get($path);

        $this->assertRedirectTo('/login', $response);
    }

    // Authenticated routes, with a session
    public function test_authenticated_user_can_access_profile(): void
    {
        $this->login('basic@example.com');

        $response = $this->client->get('/profile');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('basic@example.com', (string) $response->getBody());
    }

    public function test_authenticated_user_is_redirected_to_login_after_logout(): void
    {
        $this->login('basic@example.com');

        $this->assertRedirectTo('/login', $this->client->get('/logout'));
        $this->assertRedirectTo('/login', $this->client->get('/profile'));
    }

    // Admin only routes
    public function test_basic_user_cannot_access_admin_route(): void
    {
        $this->login('basic@example.com');

        $response = $this->client->get('/users');

        $this->assertRedirectTo('/', $response);
    }

    public function test_admin_user_can_access_admin_route(): void
    {
        $this->login('admin@example.com');

        $response = $this->client->get('/users');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('basic@example.com', (string) $response->getBody());
    }

    private function createUser(string $name, string $email, string $role): void
    {
        $user = new User([
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $user->save();
    }

    private function login(string $email): void
    {
        $response = $this->client->post('/login', [
            'form_params' => ['user[email]' => $email, 'user[password]' => '123456'],
        ]);

        $this->assertRedirectTo('/', $response);
    }

    private function assertRedirectTo(string $location, ResponseInterface $response): void
    {
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($location, $response->getHeaderLine('Location'));
    }
}
