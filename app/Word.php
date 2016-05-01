<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Word extends Model
{
    public $timestamps = false;

    static public $words = array();

    /*
     * Returns the number of words equals to 20% of the total
     * @return integer
     */
    public static function get20Count() {
        $count20 = ceil((DB::table('words')->count()*20)/100);

        return $count20;
    }

    public static function get20Words() {
        if(!empty(self::$words)) {
            return self::$words;
        }
        return self::$words = Word::where(array())->orderBy('frequence','desc')->take(Word::get20Count())->get();         

    }
}

