<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Traits\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditLogController extends BaseController
{
    use AuditLogger;

    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        try {
            $limit = $request->input('limit', 10);

            $query = AuditLog::with(['user:id,name,email'])
                ->orderBy('performed_at', 'desc');

            $query->where(function ($q) use ($request) {
                if ($request->has('user_id')) {
                    $q->orWhere('user_id', $request->user_id);
                }

                if ($request->has('action')) {
                    $q->orWhere('action', $request->action);
                }

                if ($request->has('model_type')) {
                    $q->orWhere('model_type', $request->model_type);
                }

                if ($request->has('date_from')) {
                    $q->orWhereDate('performed_at', '>=', $request->date_from);
                }

                if ($request->has('date_to')) {
                    $q->orWhereDate('performed_at', '<=', $request->date_to);
                }
            });

            // Handle special case for returning all audit logs
            if ($limit === '*') {
                $auditLogs = $query->get();
            } else {
                $auditLogs = $query->limit($limit)->get();
            }

            return $this->returnResponse('Audit logs retrieved successfully', [
                'audit_logs' => $auditLogs
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to retrieve audit logs', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified audit log with detailed relationships.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $this->authorize('view', $auditLog);

        try {
            // Load the user relationship
            $auditLog->load(['user:id,name,email,created_at']);

            // Try to load the affected model if it still exists
            $affectedModel = null;
            if ($auditLog->model_type && $auditLog->model_id) {
                try {
                    $modelClass = $auditLog->model_type;
                    if (class_exists($modelClass)) {
                        $affectedModel = $modelClass::find($auditLog->model_id);

                        // Load additional relationships based on model type
                        if ($affectedModel) {
                            switch ($modelClass) {
                                case 'App\Models\User':
                                    $affectedModel->load(['roles:id,name']);
                                    break;
                                case 'App\Models\Role':
                                    $affectedModel->load(['permissions:id,name']);
                                    break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Model might have been deleted or class doesn't exist
                    $affectedModel = null;
                }
            }

            // Prepare detailed response
            $response = [
                'id' => $auditLog->id,
                'action' => $auditLog->action,
                'performed_at' => $auditLog->performed_at,
                'ip_address' => $auditLog->ip_address,
                'user_agent' => $auditLog->user_agent,
                'old_values' => $auditLog->old_values,
                'new_values' => $auditLog->new_values,
                'created_at' => $auditLog->created_at,
                'updated_at' => $auditLog->updated_at,
                'user' => $auditLog->user ? [
                    'id' => $auditLog->user->id,
                    'name' => $auditLog->user->name,
                    'email' => $auditLog->user->email,
                    'created_at' => $auditLog->user->created_at,
                ] : null,
                'affected_model' => [
                    'type' => $auditLog->model_type,
                    'id' => $auditLog->model_id,
                    'current_state' => $affectedModel ? $affectedModel->toArray() : null,
                    'exists' => $affectedModel !== null,
                ],
                'changes_summary' => $this->generateChangesSummary($auditLog),
            ];

            return $this->returnResponse('Audit log details retrieved successfully', [
                'audit_log' => $response
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to retrieve audit log details', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate a human-readable summary of changes.
     */
    private function generateChangesSummary(AuditLog $auditLog): array
    {
        $summary = [];

        if ($auditLog->old_values && $auditLog->new_values) {
            $oldValues = $auditLog->old_values;
            $newValues = $auditLog->new_values;

            foreach ($newValues as $field => $newValue) {
                $oldValue = $oldValues[$field] ?? null;

                if ($oldValue !== $newValue) {
                    $summary[] = [
                        'field' => $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'change_type' => $oldValue === null ? 'added' : ($newValue === null ? 'removed' : 'modified')
                    ];
                }
            }
        }

        return $summary;
    }
}
