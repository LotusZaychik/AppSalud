<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Persona;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Log para debug - TEMPORAL
        \Log::info('Datos recibidos en el registro:', ['data' => $request->all()]);
        \Log::info('Headers recibidos:', ['headers' => $request->headers->all()]);
        
        // Debug específico para los campos problemáticos
        \Log::info('Campo cedula:', ['cedula' => $request->cedula]);
        \Log::info('Campo genero:', ['genero' => $request->genero]);
        \Log::info('Campo telefono:', ['telefono' => $request->telefono]);
        
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|string|min:6',
            'cedula'            => 'required|string|unique:personas,cedula',
            'genero'            => 'required|in:masculino,femenino,otro',
            'telefono'          => 'required|string',
            'fecha_nacimiento'  => 'nullable|date',
            'direccion'         => 'nullable|string',
            'ciudad'            => 'nullable|string',
            'pais'              => 'nullable|string',
        ]);

        // Crear el usuario
        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'email_verified_at' => now(),
            'password'          => Hash::make($request->password),
            'remember_token'    => Str::random(10),
        ]);

        // Crear la persona asociada
        $persona = Persona::create([
            'user_id'           => $user->id,
            'cedula'            => $request->cedula,
            'genero'            => $request->genero,
            'telefono'          => $request->telefono,
            'fecha_nacimiento'  => $request->fecha_nacimiento,
            'direccion'         => $request->direccion,
            'ciudad'            => $request->ciudad,
            'pais'              => $request->pais ?? 'Ecuador',
        ]);

        // Cargar la relación persona en el usuario
        $user->load('persona');

        // Crear token para el usuario recién registrado
        $token = $user->createToken('AppToken')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('AppToken')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    public function perfil(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }
}


