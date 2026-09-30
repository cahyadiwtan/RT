<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\RtProfileRequest;
use App\Models\RtProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RtProfileController extends Controller
{
    public function index(): View
    {
        return view('pengurus.rt-profile.index', [
            'profile' => RtProfile::active(),
        ]);
    }

    public function update(RtProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $profile = RtProfile::active();

        if ($profile) {
            $profile->update($data);
        } else {
            $profile = RtProfile::create($data);
        }

        RtProfile::whereKeyNot($profile->getKey())->update(['is_active' => false]);

        return redirect()
            ->route('pengurus.rt-profile.index')
            ->with('success', 'Identitas RT berhasil disimpan. Laporan akan memakai data ini.');
    }
}
