<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\PrinterStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrinterSettingController extends Controller
{
    public function index()
    {
        $logoPath = AppSetting::get('logo_path');

        return view('settings.printer.index', [
            'stations' => PrinterStation::orderBy('name')->get(),
            'logoUrl'  => $logoPath ? asset('storage/' . $logoPath) : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'location'   => ['nullable', 'string', 'max:100'],
        ]);

        PrinterStation::create([
            'name'       => $request->name,
            'ip_address' => $request->ip_address,
            'location'   => $request->location,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('settings.printer')->with('success', 'Printer station added.');
    }

    public function update(Request $request, PrinterStation $station)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'location'   => ['nullable', 'string', 'max:100'],
        ]);

        $station->update([
            'name'       => $request->name,
            'ip_address' => $request->ip_address,
            'location'   => $request->location,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('settings.printer')->with('success', 'Printer station updated.');
    }

    public function destroy(PrinterStation $station)
    {
        $station->delete();
        return redirect()->route('settings.printer')->with('success', 'Printer station removed.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg+xml', 'max:2048'],
        ]);

        $old = AppSetting::get('logo_path');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }

        $path = $request->file('logo')->store('logo', 'public');
        AppSetting::set('logo_path', $path);

        return redirect()->route('settings.printer')->with('success', 'Logo updated successfully.');
    }
}
