<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Medication;
use App\Models\Appointment;
use App\Models\EmergencyContact;
use App\Models\HealthRecord;

class HealthDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar el primer usuario para asociar los datos
        $user = User::first();
        
        if (!$user) {
            $this->command->error('No hay usuarios en la base de datos. Ejecuta primero los seeders de usuarios.');
            return;
        }

        // Crear medicamentos de ejemplo
        $medications = [
            [
                'name' => 'Ibuprofen',
                'dosage' => '200mg',
                'frequency' => 'Cada 8 horas',
                'start_date' => '2025-07-15',
                'end_date' => '2025-07-25',
                'notes' => 'Tomar con comida',
                'is_active' => true
            ],
            [
                'name' => 'Metformina',
                'dosage' => '500mg',
                'frequency' => 'Dos veces al día',
                'start_date' => '2025-06-01',
                'end_date' => null,
                'notes' => 'Para control de diabetes',
                'is_active' => true
            ],
            [
                'name' => 'Vitamina D',
                'dosage' => '1000 UI',
                'frequency' => 'Una vez al día',
                'start_date' => '2025-06-01',
                'end_date' => '2025-08-31',
                'notes' => 'Suplemento vitamínico',
                'is_active' => true
            ]
        ];

        foreach ($medications as $medication) {
            $user->medications()->create($medication);
        }

        // Crear citas médicas de ejemplo
        $appointments = [
            [
                'title' => 'Consulta General',
                'doctor' => 'Dr. María García',
                'specialty' => 'Medicina General',
                'date' => '2025-08-15',
                'time' => '10:00',
                'location' => 'Hospital Central',
                'notes' => 'Control de rutina',
                'status' => 'scheduled'
            ],
            [
                'title' => 'Consulta Cardiológica',
                'doctor' => 'Dr. Carlos López',
                'specialty' => 'Cardiología',
                'date' => '2025-08-20',
                'time' => '14:30',
                'location' => 'Clínica del Corazón',
                'notes' => 'Seguimiento presión arterial',
                'status' => 'scheduled'
            ],
            [
                'title' => 'Examen de Laboratorio',
                'doctor' => 'Laboratorio Clínico',
                'specialty' => 'Laboratorio',
                'date' => '2025-07-10',
                'time' => '08:00',
                'location' => 'Lab Central',
                'notes' => 'Análisis de sangre completo',
                'status' => 'completed'
            ]
        ];

        foreach ($appointments as $appointment) {
            $user->appointments()->create($appointment);
        }

        // Crear contactos de emergencia de ejemplo
        $emergencyContacts = [
            [
                'name' => 'María Rodríguez',
                'relationship' => 'Madre',
                'phone' => '+593 999 123 456',
                'email' => 'maria@example.com',
                'address' => 'Quito, Ecuador',
                'is_primary' => true
            ],
            [
                'name' => 'Juan Pérez',
                'relationship' => 'Hermano',
                'phone' => '+593 998 765 432',
                'email' => 'juan@example.com',
                'address' => 'Guayaquil, Ecuador',
                'is_primary' => false
            ],
            [
                'name' => 'Dr. Ana Silva',
                'relationship' => 'Médico de familia',
                'phone' => '+593 997 111 222',
                'email' => 'dra.silva@hospital.com',
                'address' => 'Hospital Central, Quito',
                'is_primary' => false
            ]
        ];

        foreach ($emergencyContacts as $contact) {
            $user->emergencyContacts()->create($contact);
        }

        // Crear registros de historial médico de ejemplo
        $healthRecords = [
            [
                'title' => 'Consulta General - Julio 2025',
                'type' => 'consultation',
                'date' => '2025-07-01',
                'doctor' => 'Dr. María García',
                'institution' => 'Hospital Central',
                'description' => 'Consulta de rutina. Paciente en buen estado general.',
                'results' => 'Presión arterial: 120/80, Peso: 70kg, Altura: 1.75m'
            ],
            [
                'title' => 'Análisis de Sangre',
                'type' => 'test',
                'date' => '2025-06-15',
                'doctor' => 'Dr. Luis Martínez',
                'institution' => 'Laboratorio Clínico Central',
                'description' => 'Análisis completo de sangre para chequeo anual.',
                'results' => 'Glucosa: 95 mg/dl, Colesterol: 180 mg/dl, Triglicéridos: 150 mg/dl'
            ],
            [
                'title' => 'Vacuna COVID-19 (Refuerzo)',
                'type' => 'vaccination',
                'date' => '2025-05-20',
                'doctor' => 'Enf. Patricia López',
                'institution' => 'Centro de Salud Municipal',
                'description' => 'Aplicación de dosis de refuerzo de vacuna COVID-19.',
                'results' => 'Sin reacciones adversas'
            ],
            [
                'title' => 'Diagnóstico de Hipertensión Leve',
                'type' => 'diagnosis',
                'date' => '2025-04-10',
                'doctor' => 'Dr. Carlos López',
                'institution' => 'Clínica del Corazón',
                'description' => 'Diagnóstico de hipertensión arterial leve tras seguimiento.',
                'results' => 'Presión arterial promedio: 140/90 mmHg'
            ]
        ];

        foreach ($healthRecords as $record) {
            $user->healthRecords()->create($record);
        }

        $this->command->info('Datos de salud de ejemplo creados exitosamente.');
    }
}
