<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanCollateral;
use App\Models\Loan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class LoanCollateralController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'estimated_value' => 'required|numeric|min:0',
            'appraised_value' => 'nullable|numeric|min:0',
            'condition' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'valuation_date' => 'nullable|date',
            'valuator_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png,jpg|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('collaterals/images', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Handle document uploads
            $documentPaths = [];
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $path = $document->store('collaterals/documents', 'public');
                    $documentPaths[] = $path;
                }
            }

            $collateral = LoanCollateral::create([
                'loan_id' => $request->loan_id,
                'type' => $request->type,
                'title' => $request->title,
                'description' => $request->description,
                'estimated_value' => $request->estimated_value,
                'appraised_value' => $request->appraised_value,
                'condition' => $request->condition,
                'location' => $request->location,
                'valuation_date' => $request->valuation_date,
                'valuator_name' => $request->valuator_name,
                'notes' => $request->notes,
                'serial_number' => $request->serial_number,
                'registration_number' => $request->registration_number,
                'images' => $imagePaths,
                'documents' => $documentPaths,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Collateral added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error adding collateral: ' . $e->getMessage());
        }
    }

    public function show(LoanCollateral $collateral)
    {
        $collateral->load(['loan', 'creator', 'updater']);
        return response()->json([
            'success' => true,
            'collateral' => $collateral,
            'images' => $collateral->getImageUrls(),
            'documents' => $collateral->getDocumentUrls(),
        ]);
    }

    public function update(Request $request, LoanCollateral $collateral)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'estimated_value' => 'required|numeric|min:0',
            'appraised_value' => 'nullable|numeric|min:0',
            'condition' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'valuation_date' => 'nullable|date',
            'valuator_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'new_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'new_documents.*' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png,jpg|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $imagePaths = $collateral->images ?? [];
            $documentPaths = $collateral->documents ?? [];

            // Handle new image uploads
            if ($request->hasFile('new_images')) {
                foreach ($request->file('new_images') as $image) {
                    $path = $image->store('collaterals/images', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Handle new document uploads
            if ($request->hasFile('new_documents')) {
                foreach ($request->file('new_documents') as $document) {
                    $path = $document->store('collaterals/documents', 'public');
                    $documentPaths[] = $path;
                }
            }

            $collateral->update([
                'type' => $request->type,
                'title' => $request->title,
                'description' => $request->description,
                'estimated_value' => $request->estimated_value,
                'appraised_value' => $request->appraised_value,
                'condition' => $request->condition,
                'location' => $request->location,
                'valuation_date' => $request->valuation_date,
                'valuator_name' => $request->valuator_name,
                'notes' => $request->notes,
                'serial_number' => $request->serial_number,
                'registration_number' => $request->registration_number,
                'images' => $imagePaths,
                'documents' => $documentPaths,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Collateral updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating collateral: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, LoanCollateral $collateral)
    {
        $request->validate([
            'status' => 'required|in:active,sold,released,foreclosed,damaged,lost',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $oldStatus = $collateral->status;
            
            $collateral->update([
                'status' => $request->status,
                'status_changed_at' => now(),
                'status_changed_by' => Auth::user()->name,
                'status_change_reason' => $request->reason,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Collateral status changed from '{$oldStatus}' to '{$request->status}' successfully!",
                'collateral' => $collateral->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(LoanCollateral $collateral)
    {
        try {
            DB::beginTransaction();

            // Delete associated files
            if ($collateral->images) {
                foreach ($collateral->images as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            if ($collateral->documents) {
                foreach ($collateral->documents as $documentPath) {
                    Storage::disk('public')->delete($documentPath);
                }
            }

            $collateral->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Collateral deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting collateral: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeFile(Request $request, LoanCollateral $collateral)
    {
        $request->validate([
            'file_path' => 'required|string',
            'file_type' => 'required|in:image,document'
        ]);

        try {
            $filePath = $request->file_path;
            $fileType = $request->file_type;

            if ($fileType === 'image') {
                $images = $collateral->images ?? [];
                $images = array_values(array_filter($images, function($img) use ($filePath) {
                    return $img !== $filePath;
                }));
                $collateral->update(['images' => $images]);
            } else {
                $documents = $collateral->documents ?? [];
                $documents = array_values(array_filter($documents, function($doc) use ($filePath) {
                    return $doc !== $filePath;
                }));
                $collateral->update(['documents' => $documents]);
            }

            // Delete the file from storage
            Storage::disk('public')->delete($filePath);

            return response()->json([
                'success' => true,
                'message' => ucfirst($fileType) . ' removed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing file: ' . $e->getMessage()
            ], 500);
        }
    }
}
