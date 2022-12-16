<?php

namespace Marvel\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Marvel\Database\Models\DeliveryTime;
use Marvel\Database\Repositories\DeliveryTimeRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\DeliveryTimeRequest;
use Prettus\Validator\Exceptions\ValidatorException;

class DeliveryTimeController extends CoreController
{
    public $repository;

    public function __construct(DeliveryTimeRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|DeliveryTime[]
     */
    public function index(Request $request)
    {
        $language = $request->language ?? DEFAULT_LANGUAGE;
        return $this->repository->where('language', $language)->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DeliveryTimeRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(DeliveryTimeRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    /**
     * Display the specified resource.
     *
     * @param $slug
     * @return JsonResponse
     */
    public function show(Request $request, $params)
    {
        try {
            $language = $request->language ?? DEFAULT_LANGUAGE;
            return $this->repository->where('id', $params)->where('language', $language)->firstOrFail();
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DeliveryTimeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(DeliveryTimeRequest $request, $id)
    {
        return $this->repository->findOrFail($id)->update($request->validated());
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
