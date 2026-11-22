<?php

namespace App\Middleware;

class AdminMiddleware extends RoleMiddleware
{
    protected function allowedRoles(): array
    {
        return ['ADMIN'];
    }
}

