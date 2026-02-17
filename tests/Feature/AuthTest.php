<?php

namespace Tests\Feature;

use App\Models\Administrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de connexion avec identifiants valides
     */
    public function test_login_with_valid_credentials(): void
    {
        // Créer un administrateur de test
        $admin = Administrator::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        // Envoyer la requête de connexion
        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        // Vérifier la réponse
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'admin' => ['id', 'name', 'email'],
            ]);
    }

    /**
     * Test de connexion avec identifiants invalides
     */
    public function test_login_with_invalid_credentials(): void
    {
        // Créer un administrateur
        Administrator::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        // Essayer de se connecter avec un mauvais mot de passe
        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        // Vérifier que la connexion échoue
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test de connexion sans email
     */
    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test de déconnexion
     */
    public function test_logout(): void
    {
        // Créer un admin et obtenir un token
        $admin = Administrator::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        // Se déconnecter
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Déconnexion réussie']);
    }
}