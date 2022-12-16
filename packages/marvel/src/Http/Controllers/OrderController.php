<?php

namespace Marvel\Http\Controllers;

use Exception;
use Cknow\Money\Money;
use Http\Discovery\Exception\NotFoundException;
use Marvel\Traits\Wallets;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Marvel\Enums\Permission;
use Marvel\Events\OrderCreated;
use Marvel\Exports\OrderExport;
use Marvel\Database\Models\Shop;
use Marvel\Database\Models\User;
use Illuminate\Http\JsonResponse;
// use Barryvdh\DomPDF\Facade as PDF;
use Marvel\Database\Models\Order;
use Marvel\Database\Models\Refund;
use Marvel\Database\Models\Wallet;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Balance;
use Maatwebsite\Excel\Facades\Excel;
use Marvel\Database\Models\Settings;
use Marvel\Exceptions\MarvelException;
use Illuminate\Support\Facades\Session;
use Marvel\Database\Models\DownloadToken;
use Illuminate\Database\Eloquent\Collection;
use Marvel\Http\Requests\OrderCreateRequest;
use Marvel\Http\Requests\OrderUpdateRequest;
use Marvel\Database\Repositories\OrderRepository;
use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class OrderController extends CoreController
{
    use Wallets;
    public $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Order[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?   $request->limit : 10;
        return $this->fetchOrders($request)->paginate($limit)->withQueryString();
    }

    public function fetchOrders(Request $request)
    {
        $user = $request->user();

        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN) && (!isset($request->shop_id) || $request->shop_id === 'undefined')) {
            return $this->repository->with('children')->where('id', '!=', null)->where('parent_id', '=', null); //->paginate($limit);
        } else if ($this->repository->hasPermission($user, $request->shop_id)) {
            // if ($user && $user->hasPermissionTo(Permission::STORE_OWNER)) {
            return $this->repository->with('children')->where('shop_id', '=', $request->shop_id)->where('parent_id', '!=', null); //->paginate($limit);
            // } elseif ($user && $user->hasPermissionTo(Permission::STAFF)) {
            //     return $this->repository->with('children')->where('shop_id', '=', $request->shop_id)->where('parent_id', '!=', null); //->paginate($limit);
            // }
        } else {
            return $this->repository->with('children')->where('customer_id', '=', $user->id)->where('parent_id', '=', null); //->paginate($limit);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OrderCreateRequest $request
     * @return LengthAwarePaginator|\Illuminate\Support\Collection|mixed
     */
    public function store(OrderCreateRequest $request)
    {
        return $this->repository->storeOrder($request);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, $params)
    {
        $user = $request->user() ?? null;
        $language = $request->language ?? DEFAULT_LANGUAGE;
        try {
            $order = $this->repository->where('language', $language)->with(['products', 'status', 'children.shop', 'wallet_point'])->where('id', $params)->orWhere('tracking_number', $params)->firstOrFail();
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
        if (!$order->customer_id) {
            return $order;
        }
        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN)) {
            return $order;
        } elseif (isset($order->shop_id)) {
            if ($user && ($this->repository->hasPermission($user, $order->shop_id) || $user->id == $order->customer_id)) {
                return $order;
            }
        } elseif ($user && $user->id == $order->customer_id) {
            return $order;
        } else {
            throw new MarvelException(NOT_AUTHORIZED);
        }
    }
    public function findByTrackingNumber(Request $request, $tracking_number)
    {
        $user = $request->user() ?? null;
        try {
            $order = $this->repository->with(['products', 'status', 'children.shop', 'wallet_point'])
                ->findOneByFieldOrFail('tracking_number', $tracking_number);

            if ($order->customer_id === null) {
                return $order;
            }
            if ($user && ($user->id === $order->customer_id || $user->can('super_admin'))) {
                return $order;
            } else {
                throw new MarvelException(NOT_AUTHORIZED);
            }
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OrderUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(OrderUpdateRequest $request, $id)
    {
        $request->id = $id;
        return $this->updateOrder($request);
    }


    public function updateOrder(Request $request)
    {
        try {
            $order = $this->repository->findOrFail($request->id);
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }
        $user = $request->user();
        if (isset($order->shop_id)) {
            if ($this->repository->hasPermission($user, $order->shop_id)) {
                return $this->changeOrderStatus($order, $request->status);
            }
        } else if ($user->hasPermissionTo(Permission::SUPER_ADMIN)) {
            return $this->changeOrderStatus($order, $request->status);
        } else {
            throw new MarvelException(NOT_AUTHORIZED);
        }
    }

    public function changeOrderStatus($order, $status)
    {
        $order->status = $status;
        $order->save();
        try {
            $children = json_decode($order->children);
        } catch (\Throwable $th) {
            $children = $order->children;
        }
        if (is_array($children) && count($children)) {
            foreach ($order->children as $child_order) {
                $child_order->status = $status;
                $child_order->save();
            }
        }
        return $order;
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

    /**
     * Export order dynamic url
     *
     * @param Request $request
     * @param int $shop_id
     * @return string
     */
    public function exportOrderUrl(Request $request, $shop_id = null)
    {
        $user = $request->user();

        if ($user && !$this->repository->hasPermission($user, $request->shop_id)) {
            throw new MarvelException(NOT_AUTHORIZED);
        }

        $dataArray = [
            'user_id' => $user->id,
            'token'   => Str::random(16),
            'payload' => $request->shop_id
        ];
        $newToken = DownloadToken::create($dataArray);

        return route('export_order.token', ['token' => $newToken->token]);
    }

    /**
     * Export order to excel sheet
     *
     * @param string $token
     * @return void
     */
    public function exportOrder($token)
    {
        $shop_id = 0;
        try {
            $downloadToken = DownloadToken::where('token', $token)->first();

            $shop_id = $downloadToken->payload;
            if ($downloadToken) {
                $downloadToken->delete();
            } else {
                return ['message' => TOKEN_NOT_FOUND];
            }
        } catch (Exception $e) {
            throw new MarvelException(TOKEN_NOT_FOUND);
        }

        try {
            return Excel::download(new OrderExport($this->repository, $shop_id), 'orders.xlsx');
        } catch (Exception $e) {
            return ['message' => NOT_FOUND];
        }
    }

    /**
     * Export order dynamic url
     *
     * @param Request $request
     * @param int $shop_id
     * @return string
     */
    public function downloadInvoiceUrl(Request $request)
    {
        $user = $request->user();

        if ($user && !$this->repository->hasPermission($user, $request->shop_id)) {
            throw new MarvelException(NOT_AUTHORIZED);
        }

        if(empty($request->order_id)){
            throw new NotFoundException(NOT_FOUND);
        }

        $language = $request->language ?? DEFAULT_LANGUAGE;
        $isRTL = $request->is_rtl ?? false;

        $translatedText = $this->formatInvoiceTranslateText($request->translated_text);

        $payload = [
            'user_id'           => $user->id,
            'order_id'          => intval($request->order_id),
            'language'          => $language,
            'translated_text'   => $translatedText,
            'is_rtl'            => $isRTL
        ];

        $data = [
            'user_id' => $user->id,
            'token'   => Str::random(16),
            'payload' => serialize($payload)
        ];

        $newToken = DownloadToken::create($data);

        return route('download_invoice.token', ['token' => $newToken->token]);
    }

    /**
     * Helper method for generate default translated text for invoice
     *
     * @param array $translatedText
     * @return array
     */
    public function formatInvoiceTranslateText($translatedText = [])
    {
        return [
            'subtotal'      => Arr::has($translatedText, 'subtotal') ? $translatedText['subtotal'] : 'SubTotal',
            'discount'      => Arr::has($translatedText, 'discount') ? $translatedText['discount'] : 'Discount',
            'tax'           => Arr::has($translatedText, 'tax') ? $translatedText['tax'] : 'Tax',
            'delivery_fee'  => Arr::has($translatedText, 'delivery_fee') ? $translatedText['delivery_fee'] : 'Delivery Fee',
            'total'         => Arr::has($translatedText, 'total') ? $translatedText['total'] : 'Total',
            'products'      => Arr::has($translatedText, 'products') ? $translatedText['products'] : 'Products',
            'quantity'      => Arr::has($translatedText, 'quantity') ? $translatedText['quantity'] : 'Qty',
            'invoice_no'    => Arr::has($translatedText, 'invoice_no') ? $translatedText['invoice_no'] : 'Invoice No',
            'date'          => Arr::has($translatedText, 'date') ? $translatedText['date'] : 'Date',
        ];
    }

    /**
     * Export order to excel sheet
     *
     * @param string $token
     * @return void
     */
    public function downloadInvoice($token)
    {
        $payloads = [];
        try {
            $downloadToken = DownloadToken::where('token', $token)->first();
            $payloads      = unserialize($downloadToken->payload);

            if ($downloadToken) {
                $downloadToken->delete();
            } else {
                return ['message' => TOKEN_NOT_FOUND];
            }
        } catch (Exception $e) {
            throw new MarvelException(TOKEN_NOT_FOUND);
        }

        try {
            $settings = Settings::getData($payloads['language']);
            $order = $this->repository->with(['products', 'status', 'children.shop', 'wallet_point'])->where('id', $payloads['order_id'])->firstOrFail();
        } catch (\Exception $e) {
            throw new MarvelException(NOT_FOUND);
        }

        $invoiceData = [
            'order'           => $order,
            'settings'        => $settings,
            'translated_text' => $payloads['translated_text'],
            'is_rtl'          => $payloads['is_rtl'],
            'language'        => $payloads['language'],
        ];

        $pdf = PDF::loadView('pdf.order-invoice', $invoiceData);
        $filename = 'invoice-order-' . $payloads['order_id'] . '.pdf';

        return $pdf->download($filename);
    }
}
