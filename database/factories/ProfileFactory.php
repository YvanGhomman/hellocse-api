<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'image' => 'profiles/default.jpg', // Ou générer une vraie image
            'statut' => fake()->randomElement(Profile::getStatuts()),
        ];
    }
}