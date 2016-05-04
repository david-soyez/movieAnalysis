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
        $this->importSpokenFrequences();
        return true;

        $lang = 'en';

        $row = 0;
        if (($handle = fopen(__DIR__.'/frequencylist/1_1_all_fullalpha.txt', "r")) !== FALSE) {
            $is_lemma = false;
            $skipLemma = false;
            $previousWord = null;
            $lemmaWordObj = null;
            while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
                $row++;

                echo "row $row\n";

                // columns
                $word = $data[1];
                $PoS = $data[2]; // Part of speech (grammatical word class
                $lemmaWord  = $data[3];
                $frequence = $data[4] * 1000000;// Rounded frequency per million word tokens (down to a minimum of 10 occurrences of a lemma per million)
                $range = $data[5]; // Range: number of sectors of the corpus (out of a maximum of 100) in which the word occurs 
                $dispersion = $data[6]; // Dispersion value (Juilland's D) from a minimum of 0.00 to a maximum of 1.00.

                // skip unused
                $skipArray = array('1','2','3','4','5','6','7','8','9','0','$','!','&','£','µ','+','€');
                if(in_array($word[0], $skipArray)) {
                    $skipLemma = true;
                    continue;
                }

                if($skipLemma && $word == '@') {
                    continue;
                }

                $skipLemma = false;

                // filter word
                $word = $this->filterWord($word);
                $lemmaWord = $this->filterWord($lemmaWord);

                // if not lemma anymore
                if($is_lemma && $word != '@') {
                    $is_lemma = false;
                    $lemmaWordObj = null;
                }

                // if lemma with previous word
                if($is_lemma == false && $word == '@') {

                    $lemmaWordObj = $previousWord;
                    $is_lemma = true;

                    // when first entry of lemma, it is just the same
                    if($lemmaWordObj->value == $lemmaWord) {
                        continue;
                    }
                }

                if($is_lemma == true) {
                    $word = $lemmaWord;
                }

                $wordObj = new Word();
                $wordObj->language = $lang;
                $wordObj->value = $word;
                $wordObj->pos = $PoS;
                $wordObj->frequence_written = $frequence;
                $wordObj->dispersion = $dispersion;
                $wordObj->range = $range;
                if($is_lemma) {
                    $lemmaWordObj->is_lemma = true;
                    $lemmaWordObj->save();
                    $wordObj->pos = $lemmaWordObj->pos;
                    $wordObj->lemma_word_id = $lemmaWordObj->id;
                }
                $wordObj->save();

                $previousWord = $wordObj;
            }
            fclose($handle);
        } 

        $this->importSpokenFrequences();
    }     

    public function importSpokenFrequences() {

        $lang = 'en';

        $row = 0;
        if (($handle = fopen(__DIR__.'/frequencylist/2_2_spokenvwritten.txt', "r")) !== FALSE) {
            $previousWord = null;
            while (($data = fgetcsv($handle, 1000, "\t", '"')) !== FALSE) {
                $row++;
                if($row <= 2)
                    continue;

                echo "row $row\n";

                $word = trim($data[1],' *');// word
                $PoS = $data[2];// pos
                $frequence_spoken = $data[3]* 1000000;// Frequency (per million words) in speech
                $comparison = $data[4];// + or -
                $likehood = $data[5];// Log likelihood (a measure of distinctiveness between speech and writing)
                $frequence_written= $data[6] * 1000000;// Frequency (per million words) in writing

                $skipArray = array('1','2','3','4','5','6','7','8','9','0','$','!','&','£','µ','+','€');
                if(in_array($word[0], $skipArray)) {
                    continue;
                }

                // search the word in the database
                $wordObj = Word::where(array('language'=>$lang,'value'=>$word,'pos'=>$PoS))->first();
                // word should exist
                if(!empty($wordObj)) {
                    $wordObj->frequence_spoken = $frequence_spoken;
                    $wordObj->frequence_written = $frequence_written;
                    echo "Found and saved $word\n";
                    $wordObj->save();
                }
            }
        }
    }

    protected function filterWord($word) {
        $word = html_entity_decode($word, ENT_QUOTES | ENT_HTML5);

        return $word;
    }
        
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function importLemmatization($lang='en')
    {
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
