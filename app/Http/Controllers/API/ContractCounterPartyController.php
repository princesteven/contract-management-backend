<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateContractCounterPartyRequest;
use App\Http\Requests\UpdateContractCounterPartyRequest;
use App\Models\ContractCounterParty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractCounterPartyController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ContractCounterParty::class);

        $limit = $request->input('limit', 10);
        $query = ContractCounterParty::withCount('contracts');

        // Apply OR-logic filters
        $query->where(function ($q) use ($request) {
            if ($request->has('name')) {
                $q->orWhere('name', 'like', '%' . $request->name . '%');
            }
            if ($request->has('contact_person')) {
                $q->orWhere('contact_person', 'like', '%' . $request->contact_person . '%');
            }
            if ($request->has('email')) {
                $q->orWhere('email', 'like', '%' . $request->email . '%');
            }
            if ($request->has('phone')) {
                $q->orWhere('phone', 'like', '%' . $request->phone . '%');
            }
            if ($request->has('is_active')) {
                $q->orWhere('is_active', $request->boolean('is_active'));
            }
            if ($request->has('address')) {
                $q->orWhere('address', 'like', '%' . $request->address . '%');
            }
        });

        if ($limit === '*') {
            $counterParties = $query->get();
        } else {
            $counterParties = $query->limit($limit)->get();
        }

        return $this->returnResponse('Contract counter parties retrieved successfully', ['contract_counter_parties' => $counterParties]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateContractCounterPartyRequest $request): JsonResponse
    {
        $this->authorize('create', ContractCounterParty::class);

        $counterParty = ContractCounterParty::create($request->validated());

        return $this->returnResponse('Contract counter party created successfully', ['contract_counter_party' => $counterParty], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ContractCounterParty $contractCounterParty): JsonResponse
    {
        $this->authorize('view', $contractCounterParty);

        $contractCounterParty->load(['contracts' => function ($query) {
            $query->select('id', 'title', 'contract_counter_party_id', 'date_signed', 'expiry_date', 'is_active');
        }]);

        return $this->returnResponse('Contract counter party retrieved successfully', ['contract_counter_party' => $contractCounterParty]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractCounterPartyRequest $request, ContractCounterParty $contractCounterParty): JsonResponse
    {
        $this->authorize('update', $contractCounterParty);

        $contractCounterParty->update($request->validated());

        return $this->returnResponse('Contract counter party updated successfully', ['contract_counter_party' => $contractCounterParty]);
    }
}
