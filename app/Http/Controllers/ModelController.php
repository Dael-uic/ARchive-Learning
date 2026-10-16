<?php

namespace App\Http\Controllers;

use App\Models\Model3D;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ModelController extends Controller
{
    // NFR3: Browser Security constraint — only these extensions are ever accepted
    private const ALLOWED_EXT = ['glb', 'gltf'];
    private const MAX_KB = 50 * 1024; // 50MB, matches the Python prototype's limit

    public function index(Request $request)
    {
        $query = Model3D::query();
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function show($id)
    {
        $model = Model3D::findOrFail($id);
        return response()->json($model);
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:50',
            'uploader_id' => 'required|exists:users,id',
            'file'        => 'required|file|max:' . self::MAX_KB,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $teacher = User::find($request->uploader_id);
        if (! $teacher || $teacher->role !== 'teacher') {
            return response()->json(['error' => 'Only teacher accounts can upload models'], 403);
        }

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, self::ALLOWED_EXT)) {
            return response()->json(['error' => "File type '.$ext' not allowed. Only .glb / .gltf accepted."], 400);
        }

        // Stored under storage/app/public/models — run `php artisan storage:link` once
        // so these are reachable at /storage/models/<file> (see routes/web.php).
        $safeName = (string) \Illuminate\Support\Str::uuid()
            . '.' . $ext;

        $file->storeAs('models', $safeName, 'public');

        $model = Model3D::create([
            'title'       => $request->title,
            'description' => $request->description,
            'category'    => $request->category,
            'filename'    => $safeName,
            'uploader_id' => $teacher->id,
        ]);

        return response()->json($model, 201);
    }

    public function destroy($id)
    {
        $model = Model3D::findOrFail($id);
        Storage::disk('public')->delete('models/' . $model->filename);
        $model->delete();
        return response()->json(['ok' => true]);
    }
}
