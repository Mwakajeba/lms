<?php
namespace App\Http\Controllers;

use App\Models\Filetype;
use Illuminate\Http\Request;

class FiletypeController extends Controller
{
    public function index()
    {
        $filetypes = Filetype::all();
        return view('settings.filetypes.index', compact('filetypes'));
    }

    public function create()
    {
        return view('settings.filetypes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:filetypes,name',
        ]);

        Filetype::create($request->only('name'));

        return redirect()->route('settings.filetypes.index')->with('success', 'File type created successfully.');
    }

    public function edit(Filetype $filetype)
    {
        return view('settings.filetypes.edit', compact('filetype'));
    }

    public function update(Request $request, Filetype $filetype)
    {
        $request->validate([
            'name' => 'required|string|unique:filetypes,name,' . $filetype->id,
        ]);

        $filetype->update($request->only('name'));

        return redirect()->route('settings.filetypes.index')->with('success', 'File type updated successfully.');
    }

    public function destroy(Filetype $filetype)
    {
        $filetype->delete();
        return redirect()->route('settings.filetypes.index')->with('success', 'File type deleted.');
    }
}
