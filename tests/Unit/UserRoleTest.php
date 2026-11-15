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
}
