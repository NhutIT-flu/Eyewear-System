<?php

namespace App\Http\Middleware;

class RoleMiddleware
{
    public static function handle(array $allowedRoles): bool
    {
        if (empty($allowedRoles)) {
            return true;
        }

        $currentRolesStr = $_SERVER['AUTH_USER_ROLE'] ?? null;
        if ($currentRolesStr === null) {
            echo json_encode(\Core\ApiResponse::unauthorized('Authentication is required before role checks'));
            return false;
        }

        $allowedRoles = array_map('strtoupper', $allowedRoles);
        $currentRoles = explode(',', $currentRolesStr);
        $hasPermission = false;
        foreach ($currentRoles as $role) {
            if (in_array(strtoupper(trim($role)), $allowedRoles, true)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            echo json_encode(\Core\ApiResponse::forbidden('You do not have permission to access this resource'));
            return false;
        }

        return true;
    }
}