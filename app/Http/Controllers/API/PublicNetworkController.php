<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PublicNetwork;
use Illuminate\Http\Request;

class PublicNetworkController extends Controller
{
    // public function index(Request $request)
    // {
    //     return $request->user()->publicNetworks;
    // }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'platform' => 'required|string',
    //         'url' => 'required|url',
    //         'icon' => 'nullable|string',
    //     ]);

    //     $network = PublicNetwork::firstOrCreate(
    //         ['platform' => $validated['platform'], 'url' => $validated['url']],
    //         ['icon' => $validated['icon'] ?? null]
    //     );

    //     $request->user()->publicNetworks()->syncWithoutDetaching([$network->id]);

    //     return response()->json(['message' => 'Réseau ajouté avec succès.', 'network' => $network]);
    // }

    // public function destroy(Request $request, PublicNetwork $network)
    // {
    //     $request->user()->publicNetworks()->detach($network->id);
    //     return response()->json(['message' => 'Réseau supprimé avec succès.']);
    // }

    public function index()
    {
        return auth()->user()->publicNetworks;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'platform' => 'required|string|max:255',
            'url' => 'required|url',
            'icon' => 'nullable|string',
        ]);

        $network = auth()->user()->publicNetworks()->create($data);
        return response()->json($network, 201);
    }

    // public function destroy(PublicNetwork $publicNetwork)
    // {
    //     if ($publicNetwork->user_id !== auth()->id()) {
    //         return response()->json(['error' => 'Unauthorized'], 403);
    //     }

    //     $publicNetwork->delete();
    //     return response()->json(['message' => 'Deleted successfully']);
    // }

    public function destroy($id)
    {
        $publicNetwork = PublicNetwork::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$publicNetwork) {
            return response()->json(['error' => 'Not found or unauthorized'], 403);
        }

        $publicNetwork->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
