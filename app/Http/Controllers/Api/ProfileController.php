<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Lister les profils actifs (PUBLIC)
     */
    public function index()
    {
        $profiles = Profile::where('statut', Profile::STATUT_ACTIF)
            ->select('id', 'nom', 'prenom', 'image', 'created_at', 'updated_at')
            ->get();

        return response()->json($profiles);
    }

    /**
     * Créer un profil (PROTÉGÉ)
     */
    /* public function store(StoreProfileRequest $request)
    {
        // Upload de l'image
        $imagePath = $request->file('image')->store('profiles', 'public');

        // Création du profil
        $profile = Profile::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'image' => $imagePath,
            'statut' => $request->statut,
        ]);

        return response()->json([
            'message' => 'Profil créé avec succès',
            'profile' => $profile,
        ], 201);
    } */
   public function store(StoreProfileRequest $request)
{
    try {
        // Vérifie que le fichier est bien reçu
        if (!$request->hasFile('image')) {
            return response()->json(['error' => 'Aucun fichier image reçu'], 400);
        }

        // Vérifie que le fichier est valide
        if (!$request->file('image')->isValid()) {
            return response()->json(['error' => 'Fichier image invalide'], 400);
        }

        // Upload de l'image
        $imagePath = $request->file('image')->store('profiles', 'public');

        if (!$imagePath) {
            return response()->json(['error' => 'Échec du stockage de l\'image'], 500);
        }

        // Création du profil
        $profile = Profile::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'image' => $imagePath,
            'statut' => $request->statut,
        ]);

        return response()->json([
            'message' => 'Profil créé avec succès',
            'profile' => $profile,
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la création',
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Afficher un profil avec son statut (PROTÉGÉ)
     */
    public function show(Profile $profile)
    {
        return response()->json($profile);
    }

    /**
     * Modifier un profil (PROTÉGÉ)
     */
    public function update(UpdateProfileRequest $request, Profile $profile)
    {
        $data = $request->validated();

        // Si une nouvelle image est uploadée
        if ($request->hasFile('image')) {
            // Supprime l'ancienne image
            Storage::disk('public')->delete($profile->image);
            
            // Enregistre la nouvelle
            $data['image'] = $request->file('image')->store('profiles', 'public');
        }

        $profile->update($data);

        return response()->json([
            'message' => 'Profil modifié avec succès',
            'profile' => $profile,
        ]);
    }

    /**
     * Supprimer un profil (PROTÉGÉ)
     */
    public function destroy(Profile $profile)
    {
        // Supprime l'image du storage
        Storage::disk('public')->delete($profile->image);
        
        // Supprime le profil
        $profile->delete();

        return response()->json([
            'message' => 'Profil supprimé avec succès',
        ]);
    }
}