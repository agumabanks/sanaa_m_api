<?php

namespace Marvel\Traits;

trait Helper
{
    /**
     * Format billing, shipping address
     *
     * @param array $address
     * @return string
     */
    public function formatAddress($address)
    {
        if (!$address) {
            return null;
        }

        return $address['street_address'] . ', ' . $address['zip'] . '-' . $address['city'] . ', ' . $address['state'] . ', ' . $address['country'];
    }
}
