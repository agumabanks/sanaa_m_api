<?php


namespace Marvel\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Feedback;
use Marvel\Database\Repositories\FeedbackRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\FeedbackCreateRequest;
use Marvel\Http\Requests\FeedbackUpdateRequest;
use Prettus\Validator\Exceptions\ValidatorException;


class FeedbackController extends CoreController
{
    /**
     * @var array[]
     */
    protected $dataArray = [
        'model_id',
        'model_type',
        'positive',
        'negative',
        'user_id',
    ];

    public $repository;

    public function __construct(FeedbackRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Feedback[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ? $request->limit : 15;
        return $this->repository->with(['user'])->paginate($limit);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FeedbackCreateRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(FeedbackCreateRequest $request)
    {
        try {
            $model_id = $request['model_id'];
            $model_type = $request['model_type'];
            $model_name = "\\Marvel\\Database\\Models\\{$model_type}";
            $model = $model_name::findOrFail($model_id);
            $user_id = $request->user()->id;
            $feedback = $model->feedbacks()->where('user_id', $user_id)->first();

            if (empty($feedback)) {
                $request['user_id'] = $user_id;
                $model->feedbacks()->create($request->only($this->dataArray));
            } else {
                $positive = $feedback->positive;
                $negative = $feedback->negative;
                if ($request->input('positive') && $positive == NULL && $negative == true) {
                    $feedback->update([
                        'positive' => true,
                        'negative' => NULL
                    ]);
                }
                if ($request->input('negative') && $positive == true && $negative == NULL) {
                    $feedback->update([
                        'positive' => NULL,
                        'negative' => true
                    ]);
                }
            }
        } catch (Exception $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
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

    public function update(FeedbackUpdateRequest $request)
    {
        return 'update';
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
