<?php

/*
 * Online delivery orders. Change the values in the .env file.
 */
return [
    'fee' => env('DELIVERY_FEE', 250),            // Rs. per order
    'min_order' => env('DELIVERY_MIN_ORDER', 500),  // Rs., food value before the fee
];
