<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $users = User::all(); // Récupérer tous les utilisateurs
        return response()->json($users); // Retourner les utilisateurs en JSON
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        return response()->json(['message' => 'Utilisateur créé avec succès!', 'user' => $user], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $user = User::findOrFail($id); // Trouver l'utilisateur ou échouer
        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        // Valider les champs du formulaire
        $validator = Validator::make($request->all(), [
            'last_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:15|unique:users,phone,' . $id,
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Rechercher l'utilisateur
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        // Préparer les données à mettre à jour
        $dataToUpdate = [];

        if ($request->filled('first_name') && $request->first_name !== $user->first_name) {
            $dataToUpdate['first_name'] = $request->first_name;
        }

        if ($request->filled('last_name') && $request->last_name !== $user->last_name) {
            $dataToUpdate['last_name'] = $request->last_name;
        }

        if ($request->filled('email') && $request->email !== $user->email) {
            $dataToUpdate['email'] = $request->email;
        }

        if ($request->filled('phone') && $request->phone !== $user->phone) {
            $dataToUpdate['phone'] = $request->phone;
        }

        if ($request->filled('address') && $request->address !== $user->address) {
            $dataToUpdate['address'] = $request->address;
        }

        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        if (!empty($dataToUpdate)) {
            // Mettre à jour uniquement si des modifications sont nécessaires
            $user->update($dataToUpdate);
            return response()->json(['message' => 'Utilisateur mis à jour avec succès!', 'user' => $user]);
        }

        return response()->json(['message' => 'Aucune donnée mise à jour, car aucune modification détectée.']);
    }


    public function userUpdate(Request $request)
    {
        // Valider les champs du formulaire d'inscription
        $validator = Validator::make($request->all(), [
            // 'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'last_name' => 'sometimes|string|max:255',
            'first_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user_id,
            'password' => 'sometimes|string|min:8',
            'phone' => 'sometimes|string|max:15|unique:users', // validation simple pour le téléphone
            'adresse' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::findOrFail($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $user->update([
            'name' => $request->first_name . ' ' . $request->last_name ?? $user->name,
            'last_name' => $request->last_name ?? $user->last_name,
            'first_name' => $request->first_name ?? $user->first_name,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
            'address' => $request->adresse ?? $user->address,
            'password' => Hash::make($request->password) ?? $user->password,
        ]);

        return response()->json(['message' => 'Utilisateur mis à jour avec succès!', 'user' => $user]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès!']);
    }
}
