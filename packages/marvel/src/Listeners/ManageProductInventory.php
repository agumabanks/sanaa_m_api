<?php

namespace Marvel\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Marvel\Database\Models\Product;
use Marvel\Database\Models\Variation;
use Marvel\Events\OrderCreated;

class ManageProductInventory implements ShouldQueue
{
    protected function updateProductInventory($product)
    {
        try {
            $updatedQuantity = $product->quantity - $product->pivot->order_quantity;
            if ($updatedQuantity > -1) {
                if (TRANSLATION_ENABLED) {
                    $this->updateTranslationsInventory($product, $updatedQuantity);
                } else {
                    Product::find($product->id)->update(['quantity' => $updatedQuantity]);
                }
                if (!empty($product->pivot->order_quantity->variation_option_id)) {
                    $variationOption = Variation::findOrFail($product->pivot->order_quantity->variation_option_id);
                    $updatedQuantity = $variationOption->quantity - $product->pivot->order_quantity;
                    if (TRANSLATION_ENABLED) {
                        $this->updateVariationTranslationsInventory($variationOption, $updatedQuantity);
                    } else {
                        $variationOption->update([['quantity' => $updatedQuantity]]);
                    }
                }
            }
        } catch (Exception $th) {
            //
        }
    }

    public function updateTranslationsInventory($product, $updatedQuantity)
    {
        Product::where('sku', $product->sku)->update(['quantity' => $updatedQuantity]);
    }

    public function updateVariationTranslationsInventory($variationOption, $updatedQuantity)
    {
        Variation::where('sku', $variationOption->sku)->update(['quantity' => $updatedQuantity]);
    }



    /**
     * Handle the event.
     *
     * @param OrderCreated $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        $products = $event->order->products;
        foreach ($products as $product) {
            $this->updateProductInventory($product);
        }
    }
}
