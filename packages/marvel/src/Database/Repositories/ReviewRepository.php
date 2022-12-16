<?php


namespace Marvel\Database\Repositories;


use App\Events\QuestionAnswered;
use App\Events\ReviewCreated;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Review;
use Marvel\Exceptions\MarvelException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;


class ReviewRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'rating',
        'shop_id',
        'product_id',
    ];

    /**
     * @var array[]
     */
    protected $dataArray = [
        'order_id',
        'product_id',
        'variation_option_id',
        'user_id',
        'shop_id',
        'comment',
        'rating',
        'photos'
    ];

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
        }
    }


    /**
     * Configure the Model
     **/
    public function model()
    {
        return Review::class;
    }


    /**
     * @param $request
     * @return LengthAwarePaginator|JsonResponse|Collection|mixed
     */
    public function storeReview($request)
    {
        // add logic to verified purchase and only one rating on each product
        try {
            $reviewInput = $request->only($this->dataArray);
            $review = $this->create($reviewInput);

            event(new ReviewCreated($review));
            return $review;
        } catch (\Exception $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }

    public function updateReview($request, $id)
    {
        try {
            $review = $this->findOrFail($id);
            $review->update($request->only($this->dataArray));
            return $review;
        } catch (ValidatorException $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }
}
