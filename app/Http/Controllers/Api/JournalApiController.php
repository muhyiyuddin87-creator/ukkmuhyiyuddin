<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JournalApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Journal::with(['schedule', 'attendances']);

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        if ($request->has('schedule_id')) {
            $query->where('schedule_id', $request->schedule_id);
        }

        $journals = $query->latest('date')->get();

        return response()->json([
            'status' => 'success',
            'total' => $journals->count(),
            'data' => $journals,
        ], 200);
    }

    public function show($id)
    {
        $journal = Journal::with(['schedule', 'attendances.student'])->find($id);

        if (! $journal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jurnal tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $journal,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|integer|exists:schedules,id',
            'date' => 'required|date|after_or_equal:today',
            'topic_description' => 'nullable|string',
            'photo_path' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $targetDate = $request->date ?? now()->toDateString();

        // Pastikan tidak ada jurnal ganda untuk jadwal yang sama di hari yang sama
        $alreadyExists = Journal::where('schedule_id', $request->schedule_id)
            ->where('date', $targetDate)
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jurnal untuk mata pelajaran pada jadwal ini sudah pernah diisi untuk hari ini.',
            ], 422);
        }

        $photoPath = $request->photo_path;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('journals', 'public');
        }

        $journal = Journal::create([
            'schedule_id' => $request->schedule_id,
            'date' => $request->date,
            'topic_description' => $request->topic_description,
            'photo_path' => $photoPath,
            'created_by' => $request->user() ? $request->user()->id : null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jurnal mengajar berhasil dibuat',
            'data' => $journal->load('schedule'),
        ], 201);
    }
}
