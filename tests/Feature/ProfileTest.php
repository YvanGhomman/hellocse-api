<?php

namespace Tests\Feature;

use App\Models\Administrator;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper : Créer un admin authentifié
     */
    protected function authenticatedAdmin()
    {
        $admin = Administrator::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        return $admin->createToken('test-token')->plainTextToken;
    }

    /**
     * Test : Lister uniquement les profils actifs (endpoint public)
     */
    public function test_list_only_active_profiles(): void
    {
        // Créer des profils avec différents statuts
        Profile::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'image' => 'test.jpg',
            'statut' => Profile::STATUT_ACTIF,
        ]);

        Profile::create([
            'nom' => 'Martin',
            'prenom' => 'Paul',
            'image' => 'test2.jpg',
            'statut' => Profile::STATUT_INACTIF,
        ]);

        Profile::create([
            'nom' => 'Durand',
            'prenom' => 'Marie',
            'image' => 'test3.jpg',
            'statut' => Profile::STATUT_EN_ATTENTE,
        ]);

        // Récupérer les profils
        $response = $this->getJson('/api/profiles');

        // Vérifier qu'on a uniquement le profil actif
        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['nom' => 'Dupont'])
            ->assertJsonMissing(['statut' => Profile::STATUT_ACTIF]);
    }

    /**
     * Test : Le champ statut n'est pas retourné sur l'endpoint public
     */
    public function test_public_endpoint_does_not_return_status(): void
    {
        Profile::create([
            'nom' => 'Test',
            'prenom' => 'User',
            'image' => 'test.jpg',
            'statut' => Profile::STATUT_ACTIF,
        ]);

        $response = $this->getJson('/api/profiles');

        $response->assertStatus(200)
            ->assertJsonMissing(['statut']);
    }

    /**
     * Test : Créer un profil nécessite une authentification
     */
    public function test_create_profile_requires_authentication(): void
    {
        Storage::fake('public');

        $response = $this->postJson('/api/profiles', [
            'nom' => 'Test',
            'prenom' => 'User',
            'image' => UploadedFile::fake()->image('test.jpg'),
            'statut' => Profile::STATUT_ACTIF,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test : Créer un profil avec authentification
     */
    public function test_create_profile_with_authentication(): void
    {
        Storage::fake('public');
        $token = $this->authenticatedAdmin();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post('/api/profiles', [
            'nom' => 'Nouveau',
            'prenom' => 'Profil',
            'image' => UploadedFile::fake()->image('test.jpg'),
            'statut' => Profile::STATUT_ACTIF,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Profil créé avec succès',
                'nom' => 'Nouveau',
                'prenom' => 'Profil',
            ]);

        // Vérifier que le profil est bien en base
        $this->assertDatabaseHas('profiles', [
            'nom' => 'Nouveau',
            'prenom' => 'Profil',
            'statut' => Profile::STATUT_ACTIF,
        ]);
    }

    /**
     * Test : Validation - nom requis
     */
    public function test_profile_creation_requires_nom(): void
    {
        Storage::fake('public');
        $token = $this->authenticatedAdmin();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post('/api/profiles', [
            'prenom' => 'Test',
            'image' => UploadedFile::fake()->image('test.jpg'),
            'statut' => Profile::STATUT_ACTIF,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nom']);
    }

    /**
     * Test : Validation - statut invalide
     */
    public function test_profile_creation_rejects_invalid_status(): void
    {
        Storage::fake('public');
        $token = $this->authenticatedAdmin();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post('/api/profiles', [
            'nom' => 'Test',
            'prenom' => 'User',
            'image' => UploadedFile::fake()->image('test.jpg'),
            'statut' => 'statut_invalide',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['statut']);
    }

    /**
     * Test : Voir un profil avec son statut (endpoint protégé)
     */
    public function test_show_profile_with_status(): void
    {
        $token = $this->authenticatedAdmin();

        $profile = Profile::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'image' => 'test.jpg',
            'statut' => Profile::STATUT_EN_ATTENTE,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/profiles/' . $profile->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'nom' => 'Dupont',
                'statut' => Profile::STATUT_EN_ATTENTE,
            ]);
    }

    /**
     * Test : Modifier un profil
     */
    public function test_update_profile(): void
    {
        $token = $this->authenticatedAdmin();

        $profile = Profile::create([
            'nom' => 'Ancien',
            'prenom' => 'Nom',
            'image' => 'test.jpg',
            'statut' => Profile::STATUT_EN_ATTENTE,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->putJson('/api/profiles/' . $profile->id, [
            'nom' => 'Nouveau',
            'statut' => Profile::STATUT_ACTIF,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Profil modifié avec succès',
                'nom' => 'Nouveau',
                'statut' => Profile::STATUT_ACTIF,
            ]);

        // Vérifier en base
        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'nom' => 'Nouveau',
            'statut' => Profile::STATUT_ACTIF,
        ]);
    }

    /**
     * Test : Supprimer un profil
     */
    public function test_delete_profile(): void
    {
        Storage::fake('public');
        $token = $this->authenticatedAdmin();

        $profile = Profile::create([
            'nom' => 'ToDelete',
            'prenom' => 'User',
            'image' => 'test.jpg',
            'statut' => Profile::STATUT_ACTIF,
        ]);

        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/profiles/' . $profile->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Profil supprimé avec succès',
            ]);

        // Vérifier que le profil n'existe plus
        $this->assertDatabaseMissing('profiles', [
            'id' => $profile->id,
        ]);
    }
}