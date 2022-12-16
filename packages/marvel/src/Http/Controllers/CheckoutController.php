<?php

namespace Marvel\Http\Controllers;

use Marvel\Database\Repositories\CheckoutRepository;
use Marvel\Http\Requests\CheckoutVerifyRequest;

class CheckoutController extends CoreController
{
    public $repository;

    public function __construct(CheckoutRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Verify the checkout data and calculate tax and shipping.
     *
     * @param CheckoutVerifyRequest $request
     * @return array
     */
    public function verify(CheckoutVerifyRequest $request)
    {
        return $this->repository->verify($request);
    }
}
