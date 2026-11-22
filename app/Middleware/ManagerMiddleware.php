<?php

namespace App\Middleware;

class ManagerMiddleware extends RoleMiddleware
{
    protected function allowedRoles(): array
    {
        return ['IT_MANAGER', 'ADMIN'];
    }
}

