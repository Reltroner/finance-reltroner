<?php
// app/Http/Controllers/AttachmentController.php
namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class AttachmentController extends Controller
{
    /**
     * Display a listing of attachments.
     */
    public function index(): JsonResponse
    {
        $attachments = Attachment::with('transaction')->paginate(20);
        return response()->json($attachments);
    }

    /**
     * Store a newly created attachment in storage (file upload).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'file'           => 'required|file|max:4096', // 4MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads');

        $attachment = Attachment::create([
            'transaction_id' => $validated['transaction_id'],
            'file_path'      => $path,
            'file_name'      => $file->getClientOriginalName(),
            'uploaded_at'    => now(),
        ]);

        return response()->json([
            'message' => 'Attachment uploaded successfully!',
            'data'    => $attachment,
        ], 201);
    }

    /**
     * Display the specified attachment.
     */
    public function show(Attachment $attachment): JsonResponse
    {
        $attachment->load('transaction');
        return response()->json($attachment);
    }

    /**
     * Download the specified attachment file.
     */
    public function download(Attachment $attachment)
    {
        if (!Storage::exists($attachment->file_path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }
        return Storage::download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Update the specified attachment (e.g. rename file).
     */
    public function update(Request $request, Attachment $attachment): JsonResponse
    {
        $validated = $request->validate([
            'file_name' => 'required|string',
        ]);

        $attachment->update(['file_name' => $validated['file_name']]);

        return response()->json([
            'message' => 'Attachment updated successfully!',
            'data'    => $attachment,
        ]);
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        // Optional: Also delete the file from storage
        if (Storage::exists($attachment->file_path)) {
            Storage::delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json([
            'message' => 'Attachment deleted successfully!'
        ]);
    }
}
