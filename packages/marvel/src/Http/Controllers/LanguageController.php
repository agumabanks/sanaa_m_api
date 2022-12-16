<?php

namespace Marvel\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Marvel\Database\Models\Language;
use Marvel\Database\Repositories\LanguageRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\LanguageRequest;
use Prettus\Validator\Exceptions\ValidatorException;

class LanguageController extends CoreController
{
    public $repository;

    public function __construct(LanguageRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Language[]
     */
    public function index(Request $request)
    {
        return $this->repository->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param LanguageRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(LanguageRequest $request)
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
            return $this->repository->where('id', $params)->firstOrFail();
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param LanguageRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(LanguageRequest $request, $id)
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
