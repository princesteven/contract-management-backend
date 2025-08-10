<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Traits\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseController
{
    use AuditLogger;

    /**
     * Display a listing of permissions.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);
        
        try {
            $limit = $request->input('limit', 10);
            
            $query = Permission::where('guard_name', 'api');

            $query->where(function ($q) use ($request) {
                if ($request->has('name')) {
                    $q->orWhere('name', 'like', '%' . $request->name . '%');
                }

                if ($request->has('guard_name')) {
                    $q->orWhere('guard_name', $request->guard_name);
                }
            });
            
            // Handle special case for returning all permissions
            if ($limit === '*') {
                $permissions = $query->get();
            } else {
                $permissions = $query->limit($limit)->get();
            }

            return $this->returnResponse('Permissions retrieved successfully', [
                'permissions' => $permissions
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to retrieve permissions', 500, ['error' => $e->getMessage()]);
        } finally {
            $this->logPermissionsViewed();
        }
    }
}
