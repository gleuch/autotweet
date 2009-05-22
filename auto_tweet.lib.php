<?php

class AutoTweet extends Twitter {
  protected $structures;
  protected $nouns;
  protected $proper_nouns;
  protected $prepositions;
  protected $phrases;
  protected $verbs;
  protected $pronouns;
  protected $predicate_pronouns;
  protected $adjectives;
  protected $adverbs;
  protected $articles;
  protected $punctuation;
  protected $tags;
  protected $emoticons;
  protected $prefixs;
  protected $cleanup;

  public $phrase;

  public function MakePhrase() {
    $this->structures = $this->ReadLanguageFile('structures');
    $this->nouns = $this->ReadLanguageFile('nouns');
    $this->proper_nouns = $this->ReadLanguageFile('proper_nouns');
    $this->prepositions = $this->ReadLanguageFile('prepositions');
    $this->phrases = $this->ReadLanguageFile('phrases');
    $this->verbs = $this->ReadLanguageFile('verbs');
    $this->pronouns = $this->ReadLanguageFile('pronouns');
    $this->predicate_pronouns = $this->ReadLanguageFile('predicate_pronouns');
    $this->adjectives = $this->ReadLanguageFile('adjectives');
    $this->adverbs = $this->ReadLanguageFile('adverbs');
    $this->articles = $this->ReadLanguageFile('articles');
    $this->punctuation = $this->ReadLanguageFile('punctuation');
    $this->tags = $this->ReadLanguageFile('tags');
    $this->emoticons = $this->ReadLanguageFile('emoticons');
    $this->prefixes = $this->ReadLanguageFile('prefixes');
    $this->cleanup = $this->ReadLanguageFile('cleanup', true);

    $this->ParseStructure();

    $this->Send = $this->phrase;
    $this->Start();
  }

  private function ParseStructure() {
    /*
      %n = noun
      %N = proper noun
      %v = verb
      %p = pronoun
      %c = preposition
      %P = phrase
      %a = adjective
      %A = adverb
      %W = predicate pronouns (him, her, me)
      %w = twitter tags
      %t = articles (a, an, the)
      %s = prefix
      %m = punctuation
    */
    $find = "/(%[W|n|N|v|p|c|P|a|A|t|w|m|e|s])/m";

    $this->phrase = $this->getRandom($this->structures);
    while (preg_match($find, $this->phrase)) $this->phrase = preg_replace_callback($find, array($this, 'DetermineWord'), $this->phrase);  
    foreach ($this->cleanup as $k=>$v) $this->phrase = preg_replace("/". $k ."/im", $v, $this->phrase);
    $this->phrase = $this->Capitalize($this->phrase);
    $this->phrase = trim(preg_replace("/(\s){2,}/m", " ", $this->phrase));

    if ($this->Debug()) echo '<p>'. $this->phrase .'</p>';

    // If it is over 140 characters
    if (strlen($this->phrase) > 140) $this->ParseStructure();
  }

  private function Capitalize($phrase) {
    if (substr($phrase,0,1) == '"') {
      $e = '"';
      $phrase = substr($phrase,1,strlen($phrase));
    }

    $phrase = ucfirst($phrase);
    return $e.$phrase;
  }

  private function DetermineWord($matches) {
    $found = $matches[0];
    switch ($found) {
      case "%s":
        return $this->GetRandom($this->prefixes); break;
      case "%W":
        return $this->GetRandom($this->predicate_pronouns); break;
      case "%n":
        return $this->GetRandom($this->nouns); break;
      case "%N":
        return $this->GetRandom($this->proper_nouns); break;
      case "%c":
        return $this->GetRandom($this->prepositions) .' '. $this->getRandom(array('%t %n', '%t %n', '%t %n', '%N', '%N', '%t %a %n', '%a %N')); break;
      case "%P":
        return $this->GetRandom($this->phrases); break;
      case "%v":
        return $this->GetRandom($this->verbs); break;
      case "%p":
        return $this->GetRandom($this->pronouns); break;
      case "%a":
        return $this->GetRandom($this->adjectives); break;
      case "%A":
        return $this->GetRandom($this->adverbs); break;
      case "%t":
        return $this->GetRandom($this->articles); break;
      case "%m":
        return $this->GetRandom($this->punctuation); break;
      case "%w":
        return $this->GetRandom($this->tags); break;
      case "%e":
        return $this->GetRandom($this->emoticons); break;
      default:
        return '???'; break;
    }
  }

  private function GetRandom($group) {
    if (count($group) > 0) {
      $i = mt_rand(0, (count($group)-1));
      return $group[$i];
    } else {
      return '!!!';
    }
  }


  private function ReadLanguageFile($file, $keyed=false) {
    if (is_file('./languages/'. DEFAULT_LANGUAGE .'/'. $file)) {
      $s = str_replace("\n\n", "\n", trim(file_get_contents('./languages/'. DEFAULT_LANGUAGE .'/'. $file)));
      $x = explode("\n", $s);
      if (!empty($s) && count($x) > 0) {
        if ($keyed) {
          $y = array();
          foreach($x as $v) {
            list($k, $v) = explode("\t", preg_replace("/([\s|\t]{2,})/im", "\t", $v));
            $y[$k] = $v;
          }
          return $y;
        } else {
          return $x;
        }
      } else {
        if ($this->Debug()) echo '<p>No '. $file .' in the '. DEFAULT_LANGUAGE .'/'. $file .' file.</p>';
        return array();
      }      
    } else {
      if ($this->Debug()) echo '<p>Missing the '. DEFAULT_LANGUAGE .'/'. $file .' file.</p>';
      return array();
    }
  }
}

?>