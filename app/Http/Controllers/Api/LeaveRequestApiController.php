<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveRequestApiController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['student', 'applicant', 'verifier']);

        $user = $request->user();
        if ($user && in_array((int) $user->role_id, [2, 5], true)) {
            $isWali = $user->isWaliKelas();
            $waliClasses = array_map(fn($c) => trim(strtolower($c)), $user->getWaliClasses());

            if ($isWali && !empty($waliClasses)) {
                $query->whereHas('student', function ($q) use ($waliClasses) {
                    $q->where(function ($sub) use ($waliClasses) {
                        foreach ($waliClasses as $wc) {
                            $sub->orWhereRaw('LOWER(TRIM(kelas)) = ?', [$wc]);
                        }
                    });
                });
            } else {
                // Guru biasa (bukan wali kelas) tidak memiliki daftar izin siswa
                $query->whereRaw('1 = 0');
            }
        }

        if ($user && in_array((int) $user->role_id, [3, 6], true) && $user->student_id) {
            $query->where('student_id', $user->student_id);
        }

        if ($request->has('status_approval')) {
            $query->where('status_approval', $request->status_approval);
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $leaveRequests = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'total' => $leaveRequests->count(),
            'data' => $leaveRequests,
        ], 200);
    }

    public function show($id)
    {
        $leaveRequest = LeaveRequest::with(['student', 'applicant', 'verifier'])->find($id);

        if (! $leaveRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan izin tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $leaveRequest,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:siswas,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'document_path' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
            'document' => 'nullable|file|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $documentPath = $request->document_path;
        if ($request->hasFile('photo')) {
            $documentPath = $request->file('photo')->store('leave_requests', 'public');
        } elseif ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('leave_requests', 'public');
        }

        $applicantId = $request->user() ? $request->user()->id : ($request->applicant_id ?? 1);

        $leaveRequest = LeaveRequest::create([
            'student_id' => $request->student_id,
            'applicant_id' => $applicantId,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'document_path' => $documentPath,
            'status_approval' => 'Pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan izin berhasil dibuat',
            'data' => $leaveRequest->load(['student', 'applicant']),
        ], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::with('student')->find($id);

        if (! $leaveRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan izin tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status_approval' => 'required|in:Approved,Rejected,Pending',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if ($user) {
            $roleId = (int) $user->role_id;
            $isWali = $user->isWaliKelas();
            $waliClasses = $user->getWaliClasses();
            $isKesiswaan = ($roleId === 4);
            $isWaka = ($roleId === 1);

            // Hak verifikasi izin siswa:
            // - Kesiswaan (role 4): Berhak memproses verifikasi izin siswa dari SEMUA kelas
            // - Waka Kurikulum (role 1): Berhak memproses izin siswa dari semua kelas
            // - Wali Kelas (role 5 atau Guru role 2 yang bertugas wali kelas): Hanya kelas binaannya
            if (! $isKesiswaan && ! $isWaka && ! $isWali) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Persetujuan atau penolakan izin hanya dapat dilakukan oleh Wali Kelas atau Kesiswaan.',
                ], 403);
            }

            // Jika Wali Kelas (bukan Kesiswaan/Waka), batasi hanya untuk siswa di kelas binaan
            if ($isWali && ! $isKesiswaan && ! $isWaka) {
                if (empty($waliClasses)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda tidak memiliki kelas binaan sebagai Wali Kelas.',
                    ], 403);
                }

                $studentClass = trim(strtolower($leaveRequest->student?->kelas ?? ''));
                $waliNormalized = array_map(fn($c) => trim(strtolower($c)), $waliClasses);
                if ($studentClass === '' || ! in_array($studentClass, $waliNormalized, true)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Wali Kelas hanya berhak memproses izin siswa di kelas binaan Anda (' . implode(', ', $waliClasses) . ').',
                    ], 403);
                }
            }
        }

        $verifierId = $user ? $user->id : ($request->verified_by ?? 1);

        $leaveRequest->update([
            'status_approval' => $request->status_approval,
            'verified_by' => $verifierId,
        ]);

        if ($request->status_approval === 'Approved') {
            AttendanceApiController::syncApprovedLeaves($leaveRequest->student_id);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status pengajuan izin berhasil diperbarui',
            'data' => $leaveRequest->load(['student', 'verifier']),
        ], 200);
    }
}
