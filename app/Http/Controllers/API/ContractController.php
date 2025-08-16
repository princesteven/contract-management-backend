<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contract::class);

        $limit = $request->input('limit', 10);
        $query = Contract::with(['contractCounterParty', 'businessUnit']);

        // Apply OR-logic filters
        $query->where(function ($q) use ($request) {
            if ($request->has('title')) {
                $q->orWhere('title', 'like', '%' . $request->title . '%');
            }
            if ($request->has('contract_counter_party_id')) {
                $q->orWhere('contract_counter_party_id', $request->contract_counter_party_id);
            }
            if ($request->has('business_unit_id')) {
                $q->orWhere('business_unit_id', $request->business_unit_id);
            }
            if ($request->has('is_active')) {
                $q->orWhere('is_active', $request->boolean('is_active'));
            }
            if ($request->has('date_signed_from')) {
                $q->orWhere('date_signed', '>=', $request->date_signed_from);
            }
            if ($request->has('date_signed_to')) {
                $q->orWhere('date_signed', '<=', $request->date_signed_to);
            }
            if ($request->has('expiry_date_from')) {
                $q->orWhere('expiry_date', '>=', $request->expiry_date_from);
            }
            if ($request->has('expiry_date_to')) {
                $q->orWhere('expiry_date', '<=', $request->expiry_date_to);
            }
            if ($request->has('status')) {
                // Filter by contract status (active, expired, expiring)
                switch ($request->status) {
                    case 'active':
                        $q->orWhere(function ($subQ) {
                            $subQ->where('is_active', true)
                                 ->where('expiry_date', '>', now());
                        });
                        break;
                    case 'expired':
                        $q->orWhere('expiry_date', '<=', now());
                        break;
                    case 'expiring':
                        $q->orWhere(function ($subQ) {
                            $subQ->where('is_active', true)
                                 ->whereBetween('expiry_date', [now(), now()->addWeek()]);
                        });
                        break;
                }
            }
        });

        if ($limit === '*') {
            $contracts = $query->get();
        } else {
            $contracts = $query->limit($limit)->get();
        }

        return $this->returnResponse('Contracts retrieved successfully', ['contracts' => $contracts]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateContractRequest $request): JsonResponse
    {
        $this->authorize('create', Contract::class);

        $contract = Contract::create($request->validated());
        $contract->load(['contractCounterParty', 'businessUnit']);

        return $this->returnResponse('Contract created successfully', ['contract' => $contract], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);

        $contract->load(['contractCounterParty', 'businessUnit']);

        return $this->returnResponse('Contract retrieved successfully', ['contract' => $contract]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractRequest $request, Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);

        $contract->update($request->validated());
        $contract->load(['contractCounterParty', 'businessUnit']);

        return $this->returnResponse('Contract updated successfully', ['contract' => $contract]);
    }
}
