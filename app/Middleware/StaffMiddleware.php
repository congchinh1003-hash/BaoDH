<?php

namespace App\Middleware;

class StaffMiddleware extends RoleMiddleware
{
    protected function allowedRoles(): array
    {
        return ['IT_STAFF', 'IT_MANAGER', 'ADMIN'];
    }
}

