<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Obtener el perfil del usuario autenticado
     */
    public function profile()
    {
        try {
            $user = Auth::user();
            
            // Debug info
            Log::info('Profile request - User ID: ' . ($user ? $user->id : 'NO USER'));
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $user->load('persona'); // Cargar la relación con persona
            
            // Debug info
            Log::info('Profile request - Has persona: ' . ($user->persona ? 'YES' : 'NO'));
            
            // Combinar datos de user y persona
            $profileData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
                'created_at' => $user->created_at,
            ];

            // Si existe registro en tabla persona, usar esos datos
            if ($user->persona) {
                $profileData = array_merge($profileData, [
                    'phone' => $user->persona->telefono,
                    'address' => $user->persona->direccion,
                    'birth_date' => $user->persona->fecha_nacimiento,
                    'cedula' => $user->persona->cedula,
                    'genero' => $user->persona->genero,
                    'telefono' => $user->persona->telefono,
                    'fecha_nacimiento' => $user->persona->fecha_nacimiento,
                    'direccion' => $user->persona->direccion,
                    'ciudad' => $user->persona->ciudad,
                    'pais' => $user->persona->pais,
                    // Mantener occupation de la tabla users si existe
                    'occupation' => $user->occupation ?? null,
                ]);
            } else {
                // Fallback a datos de la tabla users
                $profileData = array_merge($profileData, [
                    'phone' => $user->phone ?? null,
                    'address' => $user->address ?? null,
                    'occupation' => $user->occupation ?? null,
                    'birth_date' => $user->birth_date ?? null,
                    'cedula' => null,
                    'genero' => null,
                    'telefono' => $user->phone ?? null,
                    'fecha_nacimiento' => $user->birth_date ?? null,
                    'direccion' => $user->address ?? null,
                    'ciudad' => null,
                    'pais' => 'Ecuador',
                ]);
            }
            
            return response()->json([
                'success' => true,
                'data' => $profileData,
                'message' => 'Perfil obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar el perfil del usuario
     */
    public function updateProfile(Request $request)
    {
        try {
            Log::info('📋 UpdateProfile: Iniciando actualización de perfil');
            Log::info('📋 UpdateProfile: Datos recibidos', $request->all());
            
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'address' => 'sometimes|string|max:500',
                'occupation' => 'sometimes|string|max:255',
                'birth_date' => 'sometimes|date',
                'cedula' => 'sometimes|string|max:20',
                'genero' => 'sometimes|string|max:50',
                'telefono' => 'sometimes|string|max:20',
                'fecha_nacimiento' => 'sometimes|date',
                'direccion' => 'sometimes|string|max:500',
                'ciudad' => 'sometimes|string|max:255',
                'pais' => 'sometimes|string|max:255',
            ]);

            Log::info('✅ UpdateProfile: Validación exitosa');
            
            $user = Auth::user();
            Log::info('👤 UpdateProfile: Usuario autenticado ID: ' . $user->id);
            
            // Actualizar campos de la tabla users
            $userFields = $request->only([
                'name', 'phone', 'address', 'occupation', 'birth_date'
            ]);
            
            Log::info('📝 UpdateProfile: Campos de user a actualizar', $userFields);
            
            if (!empty($userFields)) {
                $user->update($userFields);
                Log::info('✅ UpdateProfile: Tabla users actualizada');
            }

            // Actualizar o crear registro en la tabla personas
            $personaFields = $request->only([
                'cedula', 'genero', 'telefono', 'fecha_nacimiento', 'direccion', 'ciudad', 'pais'
            ]);
            
            Log::info('📝 UpdateProfile: Campos de persona a actualizar', $personaFields);
            
            if (!empty($personaFields)) {
                $persona = $user->persona()->updateOrCreate(
                    ['user_id' => $user->id],
                    $personaFields
                );
                Log::info('✅ UpdateProfile: Tabla personas actualizada/creada', $persona->toArray());
            }

            // Cargar la relación persona para la respuesta
            $user->load('persona');
            Log::info('📦 UpdateProfile: Usuario recargado con relaciones');

            // Combinar datos de user y persona
            $profileData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
                'phone' => $user->phone,
                'address' => $user->address,
                'occupation' => $user->occupation,
                'birth_date' => $user->birth_date,
            ];

            // Agregar campos de persona si existen
            if ($user->persona) {
                $profileData = array_merge($profileData, [
                    'cedula' => $user->persona->cedula,
                    'genero' => $user->persona->genero,
                    'telefono' => $user->persona->telefono,
                    'fecha_nacimiento' => $user->persona->fecha_nacimiento,
                    'direccion' => $user->persona->direccion,
                    'ciudad' => $user->persona->ciudad,
                    'pais' => $user->persona->pais,
                ]);
                Log::info('✅ UpdateProfile: Datos de persona agregados al perfil');
            }

            Log::info('📤 UpdateProfile: Respuesta final', $profileData);

            return response()->json([
                'success' => true,
                'data' => $profileData,
                'message' => 'Perfil actualizado exitosamente'
            ]);

        } catch (ValidationException $e) {
            Log::error('❌ UpdateProfile: Error de validación', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('💥 UpdateProfile: Error crítico', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            $user = Auth::user();

            // Verificar contraseña actual
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ], 400);
            }

            // Actualizar contraseña
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña cambiada exitosamente'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar la contraseña: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir avatar del usuario
     */
    public function uploadAvatar(Request $request)
    {
        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = Auth::user();

            // Eliminar avatar anterior si existe
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }

            // Guardar nuevo avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            
            $user->update([
                'avatar' => $avatarPath
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'avatar_url' => Storage::url($avatarPath)
                ],
                'message' => 'Avatar actualizado exitosamente'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el avatar: ' . $e->getMessage()
            ], 500);
        }
    }
}
