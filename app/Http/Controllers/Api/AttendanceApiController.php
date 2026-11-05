<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceApiController extends Controller
{
    /**
     * Sinkronisasi pengajuan izin yang disetujui (Approved) ke tabel presensi (attendances).
     */
    public static function syncApprovedLeaves(?int $studentId = null): void
    {
        $query = LeaveRequest::where('status_approval', 'Approved');
        if ($studentId) {
            $query->where('student_id', $studentId);
        }
        $approvedLeaves = $query->get();

        foreach ($approvedLeaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date ?? $leave->start_date);
            if ($end->lt($start)) {
                $end = $start->copy();
            }

            $days = min($start->diffInDays($end) + 1, 30);
            for ($i = 0; $i < $days; $i++) {
                $curDate = $start->copy()->addDays($i)->toDateString();
                $isSakit = stripos($leave->reason, 'sakit') !== false;
                $status = $isSakit ? 'Sakit' : 'Izin';

                $existing = Attendance::where('student_id', $leave->student_id)
                    ->where('date', $curDate)
                    ->first();

                if (! $existing) {
                    Attendance::create([
                        'journal_id' => null,
                        'student_id' => $leave->student_id,
                        'status' => $status,
                        'photo_path' => $leave->document_path,
                        'date' => $curDate,
                    ]);
                } elseif ($existing->status !== 'Hadir') {
                    $existing->update([
                        'status' => $status,
                        'photo_path' => $existing->photo_path ?? $leave->document_path,
                    ]);
                }
            }
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $isChildScoped = $user && in_array((int) $user->role_id, [3, 6], true) && $user->student_id;
        $targetStudentId = $isChildScoped ? (int) $user->student_id : ($request->has('student_id') ? (int) $request->student_id : null);

        // Otomatis sinkronkan izin disetujui agar rekap kehadiran akurat
        self::syncApprovedLeaves($targetStudentId);

        $query = Attendance::with(['student', 'journal']);

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        if ($targetStudentId) {
            $query->where('student_id', $targetStudentId);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->latest('date')->get();

        return response()->json([
            'status' => 'success',
            'total' => $attendances->count(),
            'data' => $attendances,
        ], 200);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['student', 'journal'])->find($id);

        if (! $attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data presensi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $attendance,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'journal_id' => 'nullable|integer|exists:journals,id',
            'student_id' => 'required|integer|exists:siswas,id',
            'status' => 'required|in:Hadir,Sakit,Izin,Alpha',
            'notes' => 'nullable|string|max:1000',
            'date' => 'nullable|date',
            'photo_path' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $photoPath = $request->photo_path;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendances', 'public');
        }

        $attendance = Attendance::updateOrCreate(
            [
                'journal_id' => $request->journal_id,
                'student_id' => $request->student_id,
            ],
            [
                'status' => $request->status,
                'notes' => $request->notes,
                'photo_path' => $photoPath,
                'date' => $request->date ?? now()->toDateString(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Presensi berhasil dicatat',
            'data' => $attendance->load(['student', 'journal']),
        ], 201);
    }
}
