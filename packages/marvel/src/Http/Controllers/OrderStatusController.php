<?php

namespace Marvel\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Marvel\Database\Repositories\OrderStatusRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\OrderStatusRequest;
use Prettus\Validator\Exceptions\ValidatorException;

class OrderStatusController extends CoreController
{
    public $repository;

    public function __construct(OrderStatusRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|Type[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?   $request->limit : 15;
        $language = $request->language ?? DEFAULT_LANGUAGE;
        return $this->repository->where('language', $language)->paginate($limit);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OrderStatusRequest $request
     * @return LengthAwarePaginator|Collection|mixed
     * @throws ValidatorException
     */
    public function store(OrderStatusRequest $request)
    {
        $validateData = $request->validated();
        return $this->repository->create($validateData);
    }

    /**
     * Display the specified resource.
     *
     * @param $name
     * @return JsonResponse
     */
    public function show(Request $request, $slug)
    {
        try {
            $language = $request->language ?? DEFAULT_LANGUAGE;
            return $this->repository->where('slug', $slug)->where('language', $language)->firstOrFail();
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OrderStatusRequest $orderStatusRequest
     * @param int $id
     * @return JsonResponse
     */

    public function update(OrderStatusRequest $orderStatusRequest, $id)
    {
        try {
            $validatedData = $orderStatusRequest->validated();
            return $this->repository->findOrFail($id)->update($validatedData);
        } catch (\Exception $exception) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            return $this->repository->findOrFail($id)->delete();
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }
}
