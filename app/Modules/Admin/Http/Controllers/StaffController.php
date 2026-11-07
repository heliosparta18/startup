<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\StaffManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    protected StaffManagementService $staffService;

    public function __construct(StaffManagementService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function assignSupervisor(Request $request, string $client): RedirectResponse
    {
        $validated = $request->validate([
            'id_apj' => 'required|uuid|exists:pengguna,id_pengguna',
            'id_staf' => 'required|uuid|exists:pengguna,id_pengguna',
        ]);

        $this->staffService->tetapkanPengawas($client, $validated['id_apj'], $validated['id_staf']);

        return back()->with('success', 'Hierarki pengawasan APJ (5NF) berhasil disimpan.');
    }
}
