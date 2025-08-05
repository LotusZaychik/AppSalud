<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Persona;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario de prueba
        $user = User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'test@example.com',
            'phone' => '0987654321',
            'address' => 'Av. Principal 123',
            'occupation' => 'Ingeniero',
            'birth_date' => '1990-05-15',
        ]);

        // Crear registro en tabla persona para el usuario
        $user->persona()->create([
            'cedula' => '1234567890',
            'genero' => 'masculino',
            'telefono' => '0987654321',
            'fecha_nacimiento' => '1990-05-15',
            'direccion' => 'Av. Principal 123',
            'ciudad' => 'Quito',
            'pais' => 'Ecuador',
        ]);
    }
}
