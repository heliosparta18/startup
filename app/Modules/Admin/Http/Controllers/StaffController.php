<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminClient;
use App\Models\Client;
use App\Models\Pengguna;
use App\Models\User;
use App\Modules\Admin\Services\StaffManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $users = Pengguna::orderBy('nama_lengkap', 'asc')->get();
        $clients = Client::orderBy('nama_client', 'asc')->get();

        return view('admin.staff-management', [
            'client' => $clientModel->slug ?? $clientModel->id_client,
            'clientModel' => $clientModel,
            'clients' => $clients,
            'staff' => $staff,
            'supervisions' => $supervisions,
            'users' => $users,
        ]);
    }

    public function createAccount(Request $request, string $client): RedirectResponse
    {
        $clientModel = Client::where('id_client', $client)
            ->orWhere('slug', $client)
            ->firstOrFail();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email', 'unique:pengguna,telepon_email'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'role' => ['required', 'string', 'in:apoteker,kasir'],
            'peran_lokal' => ['nullable', 'string', 'in:apoteker,asisten_apoteker,kasir,staf_klien'],
        ]);

        $email = strtolower(trim($validated['email']));
        $nama = trim(strip_tags($validated['nama_lengkap']));
        $peranDomain = $validated['role'] === 'kasir' ? 'admin' : 'apoteker';
        $peranLokal = $validated['peran_lokal'] ?? ($validated['role'] === 'kasir' ? 'kasir' : 'apoteker');

        DB::transaction(function () use ($clientModel, $validated, $email, $nama, $peranDomain, $peranLokal): void {
            $pengguna = Pengguna::create([
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => $nama,
                'peran' => $peranDomain,
                'telepon_email' => $email,
                'password' => Hash::make($validated['password']),
                'terdaftar_pada' => now()->toDateString(),
                'aktif' => true,
            ]);

            User::create([
                'name' => $nama,
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            AdminClient::firstOrCreate(
                [
                    'id_pengguna' => $pengguna->id_pengguna,
                    'id_client' => $clientModel->id_client,
                ],
                [
                    'id_admin' => (string) Str::uuid(),
                    'nama' => $nama,
                    'email' => $email,
                ]
            );

            $this->staffService->beriPeranCabang($clientModel->id_client, $pengguna->id_pengguna, $peranLokal);
        });

        return back()->with('success', "Akun {$nama} berhasil dibuat dan ditugaskan ke {$clientModel->nama_client}.");
    }

    public function toggleStatus(Request $request, string $client, string $penggunaId): RedirectResponse
    {
        $clientModel = Client::where('id_client', $client)
            ->orWhere('slug', $client)
            ->firstOrFail();

        $pengguna = Pengguna::where('id_pengguna', $penggunaId)->firstOrFail();
        $nextAktif = ! (bool) $pengguna->aktif;

        DB::transaction(function () use ($pengguna, $nextAktif): void {
            $pengguna->update(['aktif' => $nextAktif]);

            $user = User::where('email', $pengguna->telepon_email)->first();
            if ($user) {
                $user->update(['status' => $nextAktif ? 'active' : 'suspended']);
            }
        });

        $statusLabel = $nextAktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$pengguna->nama_lengkap} berhasil {$statusLabel} pada jaringan {$clientModel->nama_client}.");
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

        $pengguna = Pengguna::where('id_pengguna', $validated['id_pengguna'])->firstOrFail();

        DB::transaction(function () use ($clientModel, $pengguna, $validated): void {
            AdminClient::firstOrCreate(
                [
                    'id_pengguna' => $pengguna->id_pengguna,
                    'id_client' => $clientModel->id_client,
                ],
                [
                    'id_admin' => (string) Str::uuid(),
                    'nama' => $pengguna->nama_lengkap,
                    'email' => $pengguna->telepon_email,
                ]
            );

            $this->staffService->beriPeranCabang(
                $clientModel->id_client,
                $validated['id_pengguna'],
                $validated['peran']
            );
        });

        return back()->with('success', 'Peran staf cabang berhasil disimpan.');
    }

    public function removeRole(string $client, string $roleId): RedirectResponse
    {
        $clientModel = Client::where('id_client', $client)
            ->orWhere('slug', $client)
            ->firstOrFail();

        $this->staffService->hapusPeranCabang($clientModel->id_client, $roleId);

        return back()->with('success', 'Penugasan peran staf cabang berhasil dihapus.');
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

    public function removeSupervisor(string $client, string $supervisionId): RedirectResponse
    {
        $clientModel = Client::where('id_client', $client)
            ->orWhere('slug', $client)
            ->firstOrFail();

        $this->staffService->hapusPengawas($clientModel->id_client, $supervisionId);

        return back()->with('success', 'Relasi pengawasan APJ berhasil dihapus.');
    }
}
