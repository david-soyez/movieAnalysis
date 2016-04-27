<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Word extends Model
{
    public $timestamps = false;

    /*
     * Returns the number of words equals to 20% of the total
     * @return integer
     */
    public static function get20Count() {
        $count20 = ceil((DB::table('words')->count()*20)/100);

        return $count20;
    }
}

