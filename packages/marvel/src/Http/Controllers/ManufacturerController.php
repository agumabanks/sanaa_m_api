<?php

namespace Marvel\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Marvel\Database\Models\Manufacturer;
use Marvel\Database\Repositories\ManufacturerRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\ManufacturerRequest;

class ManufacturerController extends CoreController
{
    public $repository;

    public function __construct(ManufacturerRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Manufacturer[]
     */
    public function index(Request $request)
    {
        $language = $request->language ?? DEFAULT_LANGUAGE;
        $limit = $request->limit ?   $request->limit : 15;
        return $this->repository->where('language', $language)->with('type')->paginate($limit);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ManufacturerRequest $request
     * @return mixed
     */
    public function store(ManufacturerRequest $request)
    {
        if ($this->repository->hasPermission($request->user(), $request->shop_id)) {
            return $this->repository->storeManufacturer($request);
        } else {
            throw new MarvelException(NOT_AUTHORIZED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $slug
     * @return JsonResponse
     */
    public function show(Request $request, $slug)
    {
        $request->slug = $slug;
        return $this->fetchManufacturer($request);
    }

    /**
     * Display the specified resource.
     *
     * @param $slug
     * @return JsonResponse
     */
    public function fetchManufacturer(Request $request)
    {

        try {
            $slug = $request->slug;
            $language = $request->language ?? DEFAULT_LANGUAGE;
            if (is_numeric($slug)) {
                $slug = (int) $slug;
                return $this->repository->with('type')->where('id', $slug)->firstOrFail();
            }
            return $this->repository->with('type')->where('slug', $slug)->where('language', $language)->firstOrFail();
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ManufacturerRequest $request
     * @param int $id
     * @return array
     */
    public function update(ManufacturerRequest $request, $id)
    {
        $request->id = $id;
        return $this->updateManufacturer($request);
    }

    public function updateManufacturer(Request $request)
    {
        if ($this->repository->hasPermission($request->user(), $request->shop_id)) {
            try {
                $Manufacturer = $this->repository->findOrFail($request->id);
            } catch (\Exception $e) {
                throw new MarvelException(NOT_FOUND);
            }
            return $this->repository->updateManufacturer($request, $Manufacturer);
        } else {
            throw new MarvelException(NOT_AUTHORIZED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
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

    public function topManufacturer(Request $request)
    {
        $limit = $request->limit ? $request->limit : 10;
        $language = $request->language ?? DEFAULT_LANGUAGE;
        return $this->repository->where('language', $language)->withCount('products')->orderBy('products_count', 'desc')->take($limit)->get();
    }
}
