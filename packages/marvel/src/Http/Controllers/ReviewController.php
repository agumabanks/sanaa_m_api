<?php


namespace Marvel\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Order;
use Marvel\Database\Models\Review;
use Marvel\Database\Repositories\ReviewRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\FeedbackCreateRequest;
use Marvel\Http\Requests\ReviewCreateRequest;
use Marvel\Http\Requests\ReviewUpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;


class ReviewController extends CoreController
{
    public $repository;

    public function __construct(ReviewRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Review[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ? $request->limit : 15;
        if (isset($request['product_id']) && !empty($request['product_id'])) {
            if (null !== $request->user()) {
                $request->user()->id; // need another way to force login
            }
            return $this->repository->where('product_id', $request['product_id'])->paginate($limit);
        }
        return $this->repository->paginate($limit);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ReviewCreateRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(ReviewCreateRequest $request)
    {
        $product_id = $request['product_id'];
        $order_id = $request['order_id'];
        $hasProductInOrder = Order::where('id', $order_id)->whereHas('products', function ($q) use ($product_id) {
            $q->where('product_id', $product_id);
        })->exists();

        if( false === $hasProductInOrder) {
            throw new MarvelException(NOT_FOUND);
        }

        try {
            $user_id = $request->user()->id;
            $request['user_id'] = $user_id;
            if(isset($request['variation_option_id']) && !empty($request['variation_option_id'])) {
                $review = $this->repository->where('user_id', $user_id)->where('order_id', $order_id)->where('product_id', $product_id)->where('shop_id', $request['shop_id'])->where('variation_option_id', $request['variation_option_id'])->get();
            } else {
                $review = $this->repository->where('user_id', $user_id)->where('order_id', $order_id)->where('product_id', $product_id)->where('shop_id', $request['shop_id'])->get();
            }

            if (count($review)) {
                throw new MarvelException(ALREADY_GIVEN_REVIEW_FOR_THIS_PRODUCT);
            }
            return $this->repository->storeReview($request);
        } catch (\Exception $e) {
            throw new MarvelException(ALREADY_GIVEN_REVIEW_FOR_THIS_PRODUCT);
        }
    }

    public function show($id)
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    public function update(ReviewUpdateRequest $request, $id)
    {
        $request->id = $id;
        return $this->updateReview($request);
    }

    public function updateReview(ReviewUpdateRequest $request)
    {
        $id =  $request->id;
        return $this->repository->updateReview($request, $id);
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
