<?php

namespace App\Http\Controllers;

use App\Services\WebsiteAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebsiteAuditController extends Controller
{
    public function store(
        Request $request,
        int $website,
        WebsiteAuditService $websiteAuditService
    ): RedirectResponse {
        $websiteModel = $request->user()->websites()->findOrFail($website);

        $auditData = $websiteAuditService->run($websiteModel->url);

        $websiteModel->audits()->create($auditData);

        return redirect()->route('websites.show', $websiteModel);
    }
}
