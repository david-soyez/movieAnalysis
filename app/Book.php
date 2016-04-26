<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BookWord;
use DB;

class Book extends Model
{
    /**
     * Get the lines.
     */
    public function lines()
    {
        return $this->hasMany('App\BookLine');


    }

    /*
     * Returns the percentage of the book covered for the first top 100 words known.
     * @return float
     */
    public function getPercent100Words() {
        // retrieve the 100th line
       $bookWord = BookWord::where(array('book_id'=>$this->id))->orderBy('frequence','desc')->offset(99)->limit(1)->first();  

       return empty($bookWord) ? null : $bookWord->percentage_book_cumul;
    }

    /*
     * Returns the percentage of the book covered for 20% of the top words known.
     * @return float
     */
    public function getPercent20() {
       // retrieve the closest line to 20%
       $bookWord =  DB::select('SELECT * FROM paretobook.book_words WHERE book_id = '.$this->id.' ORDER BY ABS( percentage_cumul - 20 ) LIMIT 1');

       return empty($bookWord) ? null : $bookWord[0]->percentage_book_cumul;       
        
    }

    /*
     * Returns the mean value of the lines 
     * @return float
     */
    public function getMean() {
        $result = DB::select('SELECT (sum(sum_pareto_20/sum_top100)/count(*)) as mean FROM paretobook.book_lines where book_id = '.$this->id);

        return empty($result) ? null : $result[0]->mean;
    }

    /*
     * Returns the mean value of the lines for the words above 20%
     * @return float
     */
    public function getMeanAbove20() {
        $result = DB::select('SELECT (sum(sum_pareto_above_20)/count(*)) as mean FROM paretobook.book_lines where book_id = '.$this->id);

        return empty($result) ? null : $result[0]->mean;
    }

    /*
     * Returns the mean value of the lines for the words 20%
     * @return float
     */
    public function getMean20() {
        $result = DB::select('SELECT (sum(sum_pareto_20)/count(*)) as mean FROM paretobook.book_lines where book_id = '.$this->id);

        return empty($result) ? null : $result[0]->mean;
    }

    /*
     * Returns the mean value of the lines for the top 100 words 
     * @return float
     */
    public function getMeanTop100() {
        $result = DB::select('SELECT (sum(sum_top100)/count(*)) as mean FROM paretobook.book_lines where book_id = '.$this->id);

        return empty($result) ? null : $result[0]->mean;
    }

     /*
     * Returns the total value of the lines for the top 100 words 
     * @return float
     */
    public function getTotal100() {
        $result = DB::select('SELECT (sum(sum_top100)) as total FROM paretobook.book_lines where book_id = '.$this->id);

        return empty($result) ? null : $result[0]->total;
    }


     /*
     * Returns the total value of the lines for the 20% 
     * @return float
     */
    public function getTotal20() {
        $result = DB::select('SELECT (sum(sum_pareto_20)) as total FROM paretobook.book_lines where book_id = '.$this->id);

        return empty($result) ? null : $result[0]->total;
    }


     /*
     * Returns the total value of the lines for the +20%
     * @return float
     */
    public function getTotalAbove20() {
        $result = DB::select('SELECT (sum(sum_pareto_above_20)) as total FROM paretobook.book_lines where book_id = '.$this->id);

        return empty($result) ? null : $result[0]->total;
    }
}
