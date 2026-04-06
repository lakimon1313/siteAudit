<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Websites/Index', [
            'websites' => $request->user()
                ->websites()
                ->latest()
                ->get(['id', 'url', 'created_at']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Websites/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $request->user()->websites()->create($validated);

        return redirect()->route('websites.index');
    }
}
