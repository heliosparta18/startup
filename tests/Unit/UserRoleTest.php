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
}
