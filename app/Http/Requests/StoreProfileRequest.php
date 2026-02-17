<?php

namespace App\Http\Requests;

use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;

class StoreProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seuls les utilisateurs authentifiés peuvent créer
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // Max 10MB
            'statut' => 'required|in:' . implode(',', Profile::getStatuts()),
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'image.required' => 'L\'image est obligatoire.',
            'image.image' => 'Le fichier doit être une image.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être : inactif, en_attente ou actif.',
        ];
    }
}