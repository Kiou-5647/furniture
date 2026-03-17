<?php

namespace App\Http\Controllers\Employee;

use App\Data\LookupFilterData;
use App\Http\Controllers\Controller;
use App\Services\Lookup\LookupService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LookupController extends Controller
{
    public function __construct(private LookupService $service) {}

    public function index(Request $request): Response
    {
        $filter = LookupFilterData::fromRequest($request);

        return Inertia::render('employee/lookups/Index', [
            'namespaces' => $this->service->getNamespaces(),
            'lookups' => Inertia::lazy(fn () => $this->service->getByNamespace($filter)),
        ]);
    }
}
