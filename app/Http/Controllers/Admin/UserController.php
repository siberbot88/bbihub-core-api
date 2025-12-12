<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function edit($id)
    {
        return view('admin.users.edit', [
            'user' => User::findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        return redirect()
            ->route('admin.users.edit', $id)
            ->with('success', 'Data pengguna diperbarui.');
    }
}
