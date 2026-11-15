<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_has_role_true_only_when_active_and_matching(): void
    {
        $u = new User(['name' => 'A', 'email' => 'a@b.c']);
        $u->role = 'admin';
        $u->status = 'active';
        $this->assertTrue($u->hasRole('admin'));
        $this->assertTrue($u->isAdmin());
        $this->assertFalse($u->hasRole('tenant'));
        $this->assertFalse($u->isTenantOperator());
    }

    public function test_suspended_user_holds_no_role(): void
    {
        $u = new User;
        $u->role = 'admin';
        $u->status = 'suspended';
        $this->assertFalse($u->hasRole('admin'));
        $this->assertFalse($u->isActive());
    }

    public function test_null_role_holds_no_role(): void
    {
        $u = new User;
        $u->status = 'active';
        $this->assertFalse($u->hasRole('admin'));
        $this->assertFalse($u->hasRole('tenant'));
    }

    public function test_role_label_returns_customer_for_null_or_unknown_roles(): void
    {
        $u = new User;
        $this->assertSame('Customer', $u->roleLabel());

        $u->role = null;
        $this->assertSame('Customer', $u->roleLabel());

        $u->role = '';
        $this->assertSame('Customer', $u->roleLabel());

        $u->role = 'random_role';
        $this->assertSame('Customer', $u->roleLabel());
    }

    public function test_role_label_maps_roles_correctly(): void
    {
        $u = new User;

        $u->role = 'super_admin';
        $this->assertSame('Super Admin', $u->roleLabel());

        $u->role = 'admin';
        $this->assertSame('Administrator', $u->roleLabel());

        $u->role = 'apoteker';
        $this->assertSame('Apoteker', $u->roleLabel());

        $u->role = 'tenant';
        $this->assertSame('Operator Apotek', $u->roleLabel());

        $u->role = 'kasir';
        $this->assertSame('Kasir', $u->roleLabel());

        $u->role = 'pelanggan';
        $this->assertSame('Customer', $u->roleLabel());

        $u->role = 'customer';
        $this->assertSame('Customer', $u->roleLabel());
    }

    public function test_kasir_and_admin_are_isolated_from_super_admin(): void
    {
        $kasir = new User(['name' => 'Kasir', 'email' => 'k@test.com']);
        $kasir->role = 'kasir';
        $kasir->status = 'active';

        $admin = new User(['name' => 'Admin Cabang', 'email' => 'a@test.com']);
        $admin->role = 'admin';
        $admin->status = 'active';

        $superAdmin = new User(['name' => 'Super Admin', 'email' => 'sa@test.com']);
        $superAdmin->role = 'super_admin';
        $superAdmin->status = 'active';

        $this->assertTrue($kasir->hasRole('kasir'));
        $this->assertTrue($kasir->hasRole('admin'));
        $this->assertFalse($kasir->hasRole('super_admin'));

        $this->assertTrue($admin->hasRole('kasir'));
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertFalse($admin->hasRole('super_admin'));

        $this->assertTrue($superAdmin->hasRole('super_admin'));
        $this->assertTrue($superAdmin->hasRole('admin'));
        $this->assertFalse($superAdmin->hasRole('kasir'));
    }

    public function test_profile_actions_adapt_strictly_per_role(): void
    {
        $superAdmin = new User(['name' => 'SA', 'email' => 'sa@test.com']);
        $superAdmin->role = 'super_admin';
        $superAdmin->status = 'active';
        $saKeys = array_column($superAdmin->profileActions('rogojampi'), 'key');
        $this->assertContains('admin-dashboard', $saKeys);
        $this->assertContains('admin-cabang', $saKeys);
        $this->assertContains('admin-obat', $saKeys);
        $this->assertContains('admin-staf', $saKeys);
        $this->assertContains('admin-cek-apoteker', $saKeys);
        $this->assertNotContains('kasir-dashboard', $saKeys);

        $apoteker = new User(['name' => 'Apt', 'email' => 'dewi@omnipharma.test']);
        $apoteker->role = 'apoteker';
        $apoteker->status = 'active';
        $aptActions = $apoteker->profileActions('banyuwangi-kota');
        $aptKeys = array_column($aptActions, 'key');
        $this->assertContains('apoteker-dashboard', $aptKeys);
        $this->assertContains('apoteker-resep', $aptKeys);
        $this->assertContains('apoteker-stok', $aptKeys);
        $this->assertNotContains('admin-obat', $aptKeys);
        $this->assertStringContainsString('/cabang/rogojampi/dashboard', $aptActions[0]['href']);

        $stafKasir = new User(['name' => 'Kasir', 'email' => 'kasir@test.com']);
        $stafKasir->role = 'staf_klien';
        $stafKasir->status = 'active';
        $this->assertTrue($stafKasir->hasRole('kasir'));
        $kasirKeys = array_column($stafKasir->profileActions(), 'key');
        $this->assertContains('kasir-dashboard', $kasirKeys);
        $this->assertContains('kasir-ledger', $kasirKeys);
        $this->assertNotContains('apoteker-dashboard', $kasirKeys);

        $pelanggan = new User(['name' => 'User', 'email' => 'user@test.com']);
        $pelanggan->role = 'pelanggan';
        $pelanggan->status = 'active';
        $custKeys = array_column($pelanggan->profileActions(), 'key');
        $this->assertContains('pelanggan-katalog', $custKeys);
        $this->assertContains('pelanggan-riwayat', $custKeys);
        $this->assertNotContains('admin-dashboard', $custKeys);
        $this->assertNotContains('apoteker-dashboard', $custKeys);
        $this->assertNotContains('kasir-dashboard', $custKeys);
    }
}
