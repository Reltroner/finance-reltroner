<?php
// app/Http/Controllers/AttachmentController.php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Display a listing of attachments.
     */
    public function index(Request $request)
    {
        $attachments = Attachment::with('transaction')->orderByDesc('uploaded_at')->paginate(20);

        // Jika expects JSON, kembalikan JSON
        if ($request->expectsJson()) {
            return response()->json($attachments);
        }

        // Default: Tampilkan Blade
        return view('attachments.index', compact('attachments'));
    }

    /**
     * Show the form for creating a new attachment.
     */
    public function create()
    {
        // Tampilkan form upload attachment
        // Pastikan ada variable $transactions jika perlu pilih transaksi
        $transactions = \App\Models\Transaction::all();
        return view('attachments.create', compact('transactions'));
    }

    /**
     * Store a newly created attachment in storage (file upload).
     */
    public function store(Request $request)
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

        // Jika expects JSON, kembalikan JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Attachment uploaded successfully!',
                'data'    => $attachment,
            ], 201);
        }

        // Default: Redirect ke index + notif
        return redirect()
            ->route('attachments.index')
            ->with('success', 'Attachment uploaded successfully!');
    }

    /**
     * Display the specified attachment.
     */
    public function show(Request $request, Attachment $attachment)
    {
        $attachment->load('transaction');

        // Jika expects JSON, kembalikan JSON
        if ($request->expectsJson()) {
            return response()->json($attachment);
        }

        // Default: Tampilkan Blade
        return view('attachments.show', compact('attachment'));
    }

    /**
     * Show the form for editing the specified attachment (rename).
     */
    public function edit(Attachment $attachment)
    {
        return view('attachments.edit', compact('attachment'));
    }

    /**
     * Update the specified attachment (rename file).
     */
    public function update(Request $request, Attachment $attachment)
    {
        $validated = $request->validate([
            'file_name' => 'required|string',
        ]);

        $attachment->update(['file_name' => $validated['file_name']]);

        // Jika expects JSON, kembalikan JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Attachment updated successfully!',
                'data'    => $attachment,
            ]);
        }

        // Default: Redirect ke detail + notif
        return redirect()
            ->route('attachments.show', $attachment->id)
            ->with('success', 'Attachment updated successfully!');
    }

    /**
     * Download the specified attachment file.
     */
    public function download(Attachment $attachment)
    {
        if (!Storage::exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }
        return Storage::download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Request $request, Attachment $attachment)
    {
        // Optional: Also delete the file from storage
        if (Storage::exists($attachment->file_path)) {
            Storage::delete($attachment->file_path);
        }
        $attachment->delete();

        // Jika expects JSON, kembalikan JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Attachment deleted successfully!'
            ]);
        }

        // Default: Redirect ke index + notif
        return redirect()
            ->route('attachments.index')
            ->with('success', 'Attachment deleted successfully!');
    }
}
