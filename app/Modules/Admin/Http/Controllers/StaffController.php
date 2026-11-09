<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Pengguna;
use App\Modules\Admin\Services\StaffManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    protected StaffManagementService $staffService;

    public function __construct(StaffManagementService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function index(Request $request, string $client): View
    {
        $clientModel = Client::where('id_client', $client)
            ->orWhere('slug', $client)
            ->firstOrFail();

        $staff = $this->staffService->getStaffByClient($clientModel->id_client);
        $supervisions = $this->staffService->getPengawasByClient($clientModel->id_client);
        $users = Pengguna::where('aktif', true)->orderBy('nama_lengkap', 'asc')->get();

        return view('admin.staff-management', [
            'client' => $clientModel->slug ?? $clientModel->id_client,
            'clientModel' => $clientModel,
            'staff' => $staff,
            'supervisions' => $supervisions,
            'users' => $users,
        ]);
    }

    public function assignRole(Request $request, string $client): RedirectResponse
    {
        $clientModel = Client::where('id_client', $client)
            ->orWhere('slug', $client)
            ->firstOrFail();

        $validated = $request->validate([
            'id_pengguna' => 'required|uuid|exists:pengguna,id_pengguna',
            'peran' => 'required|string|in:apoteker,asisten_apoteker,kasir,staf_klien',
        ]);

        $this->staffService->beriPeranCabang(
            $clientModel->id_client,
            $validated['id_pengguna'],
            $validated['peran']
        );

        return back()->with('success', 'Peran staf cabang berhasil disimpan.');
    }

    public function assignSupervisor(Request $request, string $client): RedirectResponse
    {
        $clientModel = Client::where('id_client', $client)
            ->orWhere('slug', $client)
            ->firstOrFail();

        $validated = $request->validate([
            'id_apj' => 'required|uuid|exists:pengguna,id_pengguna',
            'id_staf' => 'required|uuid|exists:pengguna,id_pengguna|different:id_apj',
        ]);

        $this->staffService->tetapkanPengawas(
            $clientModel->id_client,
            $validated['id_apj'],
            $validated['id_staf']
        );

        return back()->with('success', 'Hierarki pengawasan APJ (5NF) berhasil disimpan.');
    }
}
