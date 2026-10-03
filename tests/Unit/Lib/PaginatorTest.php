<?php

namespace Tests\Unit\Lib;

use App\Models\User;
use Lib\Paginator;
use Tests\TestCase;

class PaginatorTest extends TestCase
{
    private Paginator $paginator;
    /** @var User[] $users */
    private array $users;

    public function setUp(): void
    {
        parent::setUp();

        for ($i = 0; $i < 10; $i++) {
            $this->users[] = $this->createUser($i);
        }
        $this->paginator = new Paginator(User::class, 1, 5, 'users', ['name']);
    }

    public function test_total_of_registers(): void
    {
        $this->assertEquals(10, $this->paginator->totalOfRegisters());
    }

    public function test_total_of_pages(): void
    {
        $this->assertEquals(2, $this->paginator->totalOfPages());
    }

    public function test_total_of_pages_when_the_division_is_not_exact(): void
    {
        $this->createUser(10);
        $this->paginator = new Paginator(User::class, 1, 5, 'users', ['name']);

        $this->assertEquals(3, $this->paginator->totalOfPages());
    }

    public function test_previous_page(): void
    {
        $this->assertEquals(0, $this->paginator->previousPage());
    }

    public function test_next_page(): void
    {
        $this->assertEquals(2, $this->paginator->nextPage());
    }

    public function test_has_previous_page(): void
    {
        $this->assertFalse($this->paginator->hasPreviousPage());

        $paginator = new Paginator(User::class, 2, 5, 'users', ['name']);
        $this->assertTrue($paginator->hasPreviousPage());
    }

    public function test_has_next_page(): void
    {
        $this->assertTrue($this->paginator->hasNextPage());

        $paginator = new Paginator(User::class, 2, 5, 'users', ['name']);
        $this->assertFalse($paginator->hasNextPage());
    }

    public function test_is_page(): void
    {
        $this->assertTrue($this->paginator->isPage(1));
        $this->assertFalse($this->paginator->isPage(2));
    }

    public function test_entries_info(): void
    {
        $entriesInfo = 'Mostrando 1 - 5 de 10';
        $this->assertEquals($entriesInfo, $this->paginator->entriesInfo());
    }

    public function test_register_return_all(): void
    {
        $this->assertCount(5, $this->paginator->registers());

        $paginator = new Paginator(User::class, 1, 10, 'users', ['name', 'email']);
        $this->assertEquals(
            array_map(fn($user) => $user->email, $this->users),
            array_map(fn($user) => $user->email, $paginator->registers())
        );
    }

    private function createUser(int $index): User
    {
        $user = new User([
            'name' => "User $index",
            'email' => "fulano{$index}@example.com",
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        return $user;
    }
}
