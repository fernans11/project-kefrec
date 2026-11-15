<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use Tests\TestCase;

class UserResourceRoleSeparationTest extends TestCase
{
    public function test_staff_roles_are_grouped_as_internal_accounts(): void
    {
        $this->assertSame(['admin', 'owner', 'kasir', 'dapur'], UserResource::getStaffUserTypes());
        $this->assertFalse(in_array('user', UserResource::getStaffUserTypes(), true));
    }

    public function test_staff_role_labels_use_clear_business_terms(): void
    {
        $this->assertSame('Admin', UserResource::getRoleLabel('admin'));
        $this->assertSame('Owner', UserResource::getRoleLabel('owner'));
        $this->assertSame('Karyawan', UserResource::getRoleLabel('kasir'));
        $this->assertSame('Karyawan', UserResource::getRoleLabel('dapur'));
        $this->assertSame('Pelanggan', UserResource::getRoleLabel('user'));
    }
}
