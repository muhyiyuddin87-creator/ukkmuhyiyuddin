<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiswaApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::query();

        if ($request->has('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $siswas = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'total' => $siswas->count(),
            'data' => $siswas,
        ], 200);
    }

    public function show($id)
    {
        $siswa = Siswa::find($id);

        if (! $siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $siswa,
        ], 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($user && (int) $user->role_id !== 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak memiliki hak akses untuk menambah data siswa. Penambahan siswa hanya dapat dilakukan oleh Administrator.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'kelas' => 'required|string|max:100',
            'jurusan' => 'required|string|max:100',
            'alamat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $siswa = Siswa::create([
            'name' => $request->name,
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan,
            'alamat' => $request->alamat ?? '-',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data siswa berhasil ditambahkan',
            'data' => $siswa,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if ($user && (int) $user->role_id !== 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak memiliki hak akses untuk mengubah data siswa.',
            ], 403);
        }

        $siswa = Siswa::find($id);

        if (! $siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'kelas' => 'sometimes|required|string|max:100',
            'jurusan' => 'sometimes|required|string|max:100',
            'alamat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $siswa->update($request->only(['name', 'kelas', 'jurusan', 'alamat']));

        return response()->json([
            'status' => 'success',
            'message' => 'Data siswa berhasil diperbarui',
            'data' => $siswa,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if ($user && (int) $user->role_id !== 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak memiliki hak akses untuk menghapus data siswa.',
            ], 403);
        }

        $siswa = Siswa::find($id);

        if (! $siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan',
            ], 404);
        }

        $siswa->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data siswa berhasil dihapus',
        ], 200);
    }
}
