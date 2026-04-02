<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show($filename)
{
    $path = "profiles/{$filename}";

    if (!Storage::disk('private')->exists($path)) {
        abort(404);
    }

    return Response::file(Storage::disk('private')->path($path));
}
}
