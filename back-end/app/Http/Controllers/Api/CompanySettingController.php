<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanySettingRequest;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function show()
    {
        return response()->json(CompanySetting::firstOrFail());
    }

    public function update(UpdateCompanySettingRequest $request)
    {
        $settings = CompanySetting::firstOrFail();
        $settings->update($request->validated());

        return response()->json($settings);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $settings = CompanySetting::firstOrFail();

        if ($settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
        }

        $path = $request->file('logo')->store('logos', 'public');
        $settings->update(['logo_path' => $path]);

        return response()->json($settings);
    }
}