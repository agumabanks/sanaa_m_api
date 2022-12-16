<?php

namespace Marvel\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Marvel\Traits\Helper;

class OrderExport implements FromCollection, WithHeadings
{
    use Helper;

    private $shop_id;
    private $repository;

    /**
     * Init class
     *
     * @param object $repository
     * @param int $shop_id
     */
    public function __construct($repository, $shop_id)
    {
        $this->shop_id    = $shop_id;
        $this->repository = $repository;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $results = [];

        if (!empty($this->shop_id)) {
            $orders = $this->repository->where('shop_id', $this->shop_id)->get();
        } else {
            $orders = $this->repository->where('parent_id', NULL)->get();
            // $orders = $this->repository->where('parent_id', '<>', NULL)->get();
        }

        if (empty($orders)) {
            return collect($results);
        }

        foreach ($orders as $order) {
                       
            $results[] = [
                'id'                 => '#' . $order->id . ' ' . $order->customer->name,
                'customer_email'     => $order->customer->email,
                'created_at'         => (new Carbon($order->created_at))->format('Y-m-d'),
                'delivery_time'      => $order->delivery_time,
                'status'             => $order->orderStatus->name,
                'tracking_number'    => $order->tracking_number,
                'shop'               => isset($order->shop) && !empty($order->shop) ? $order->shop->name : NULL,
                'coupon_id'          => $order->coupon_id,
                'amount'             => $order->amount,
                'discount'           => $order->discount,
                'paid_amount'        => $order->paid_total,
                'total'              => $order->total,
                'sales_tax'          => $order->sales_tax,
                'delivery_fee'       => $order->delivery_fee,
                'payment_id'         => $order->payment_id,
                'payment_gateway'    => $order->payment_gateway,
                'billing_address'    => $this->formatAddress($order->billing_address),
                'shipping_address'   => $this->formatAddress($order->shipping_address),
                'customer_contact'   => $order->customer_contact,
                'logistics_provider' => $order->logistics_provider
            ];
        }

        return collect($results);
    }

    /**
     * File header
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Order Id',
            'Email',
            'Order Date',
            'Delivery Time',
            'Order Status',
            'Tracking No.',
            'Shop',
            'Coupon ID',
            'Amount',
            'Discount',
            'Paid',
            'Total',
            'Sales Tax',
            'Delivery Fee',
            'Payment Id',
            'Payment Gateway',
            'Billing Address',
            'Shipping Address',
            'Customer Contact',
            'Logistics Provider'
        ];
    }
}
