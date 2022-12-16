<?php

namespace Marvel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\CouponRequest;
use Marvel\Http\Requests\UpdateCouponRequest;
use Marvel\Database\Repositories\CouponRepository;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponController extends CoreController
{
    public $repository;

    public function __construct(CouponRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
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
     * @param CouponRequest $request
     * @return LengthAwarePaginator|Collection|mixed
     * @throws ValidatorException
     */
    public function store(CouponRequest $request)
    {
        $validateData = $request->validated();
        return $this->repository->create($validateData);
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
                return $this->repository->where('id', $params)->firstOrFail();
            }
            return $this->repository->where('code', $params)->where('language', $language)->firstOrFail();
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }
    /**
     * Verify Coupon by code.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code'     => 'required|string',
        ]);
        $code = $request->code;
        try {
            return $this->repository->verifyCoupon($code);
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CouponRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCouponRequest $request, $id)
    {
        $request->id = $id;
        return $this->updateCoupon($request);
    }

    /**
     * Undocumented function
     *
     * @param  $request
     * @return void
     */
    public function updateCoupon(Request $request)
    {
        $id = $request->id;
        $dataArray = ['id', 'code', 'language', 'description', 'image', 'type', 'amount', 'active_from', 'expire_at'];

        try {
            $code = $this->repository->findOrFail($id);
           
            if ($request->has('language') && $request['language'] === DEFAULT_LANGUAGE) {
                $updatedCoupon = $request->only($dataArray);

                $nonTranslatableKeys = ['language', 'image', 'description', 'id'];
                foreach ($nonTranslatableKeys as $key) {
                    if (isset($updatedCoupon[$key])) {
                        unset($updatedCoupon[$key]);
                    }
                }

                $this->repository->where('code', $code->code)->update($updatedCoupon);
            }

            return $this->repository->update($request->only($dataArray), $id);
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
