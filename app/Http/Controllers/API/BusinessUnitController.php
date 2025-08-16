<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateBusinessUnitRequest;
use App\Http\Requests\UpdateBusinessUnitRequest;
use App\Models\BusinessUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessUnitController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BusinessUnit::class);

        $limit = $request->input('limit', 10);
        $query = BusinessUnit::withCount('contracts');

        // Apply OR-logic filters
        $query->where(function ($q) use ($request) {
            if ($request->has('name')) {
                $q->orWhere('name', 'like', '%' . $request->name . '%');
            }
            if ($request->has('description')) {
                $q->orWhere('description', 'like', '%' . $request->description . '%');
            }
            if ($request->has('is_active')) {
                $q->orWhere('is_active', $request->boolean('is_active'));
            }
        });

        if ($limit === '*') {
            $businessUnits = $query->get();
        } else {
            $businessUnits = $query->limit($limit)->get();
        }

        return $this->returnResponse('Business units retrieved successfully', ['business_units' => $businessUnits]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBusinessUnitRequest $request): JsonResponse
    {
        $this->authorize('create', BusinessUnit::class);

        $businessUnit = BusinessUnit::create($request->validated());

        return $this->returnResponse('Business unit created successfully', ['business_unit' => $businessUnit], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessUnit $businessUnit): JsonResponse
    {
        $this->authorize('view', $businessUnit);

        $businessUnit->load(['contracts' => function ($query) {
            $query->select('id', 'title', 'business_unit_id', 'date_signed', 'expiry_date', 'is_active');
        }]);

        return $this->returnResponse('Business unit retrieved successfully', ['business_unit' => $businessUnit]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBusinessUnitRequest $request, BusinessUnit $businessUnit): JsonResponse
    {
        $this->authorize('update', $businessUnit);

        $businessUnit->update($request->validated());

        return $this->returnResponse('Business unit updated successfully', ['business_unit' => $businessUnit]);
    }
}
