<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/** Pemilih peran demo (hanya mode mandiri). */
class DemoActorController extends Controller
{
    public function switch(Request $request)
    {
        abort_unless(config('symbionusa.standalone'), 404);

        $data = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);

        $request->session()->put('demo_user_id', User::findOrFail($data['user_id'])->id);

        return redirect()->route('shipments.index');
    }
}
