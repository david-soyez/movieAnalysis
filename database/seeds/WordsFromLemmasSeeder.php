<?php

use Illuminate\Database\Seeder;
use App\Word;

class WordsFromLemmasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lang = 'en';

        // opens english lemmas
        $lemmasRaw = file_get_contents(__DIR__.'/lemmas/lemmatization-en.txt');



        $delimiter = "\n";
        $splitcontents = explode($delimiter, $lemmasRaw);
        $lemmas = array();
        foreach ( $splitcontents as $line )
        {
            $data = explode("\t", $line);
            if(count($data)!=2)
                continue;
            $lemmas[strtolower(trim($data[1]))] = strtolower(trim($data[0]));
        }

        $count = count($lemmas);
        $i=0;
        foreach($lemmas as $word => $lemma) {
            $i++;
            echo "$i/$count $word -> $lemma\n";

            // search if lemma exist
            $lemmaObj = Word::where(array('language'=>$lang,'value'=>$lemma))->first();
            if(empty($lemmaObj)) {
                $lemmaObj = new Word();
                $lemmaObj->language = $lang;
                $lemmaObj->value = ($lemma);
                $lemmaObj->save();
            }

            $wordObj = Word::where(array('language'=>$lang,'value'=>$word))->first();
            // word should not exist
            if(empty($wordObj)) {
                $wordObj = new Word();
                $wordObj->language = $lang;
                $wordObj->value = ($word);
                $wordObj->lemma_word_id = $lemmaObj->id;
                $wordObj->save();
            }

        }
    }
}
