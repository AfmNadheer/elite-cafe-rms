<?php

/*
 * Elite Cafe rooms. Change the times in the .env file if needed.
 */
return [
    'check_in_time' => env('ROOM_CHECK_IN_TIME', '12:00'),    // 12:00 PM
    'check_out_time' => env('ROOM_CHECK_OUT_TIME', '11:00'),  // 11:00 AM
    'max_nights' => 30,
];
