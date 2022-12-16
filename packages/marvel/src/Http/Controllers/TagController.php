<?php


namespace Marvel\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Marvel\Database\Models\Tag;
use Marvel\Database\Repositories\TagRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\TagCreateRequest;
use Marvel\Http\Requests\TagUpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;


class TagController extends CoreController
{
    public $repository;

    public function __construct(TagRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Tag[]
     */
    public function index(Request $request)
    {
        $language = $request->language ?? DEFAULT_LANGUAGE;
        $limit = $request->limit ?   $request->limit : 15;
        return $this->repository->where('language', $language)->with(['type'])->paginate($limit);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TagCreateRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(TagCreateRequest $request)
    {
        $validatedData = $request->validated();
        return $this->repository->create($validatedData);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, $params)
    {

        try {
            $language = $request->language ?? DEFAULT_LANGUAGE;
            if (is_numeric($params)) {
                $params = (int) $params;
                return $this->repository->where('id', $params)->with(['type'])->firstOrFail();
            }
            return $this->repository->where('slug', $params)->where('language', $language)->with(['type'])->firstOrFail();
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TagUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(TagUpdateRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            return $this->repository->findOrFail($id)->update($validatedData);
        } catch (\Exception $e) {
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
