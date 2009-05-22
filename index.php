<?php
/*
  auto-tweet
  ----------------------------------------------------------------------------------------------------------------

  A nifty script send notification you when people unfollow you on Twitter.
  More information at http://gleuch.com/projects/auto-tweet -or- http://github.com/gleuch/auto-tweet

  ----------------------------------------------------------------------------------------------------------------
  Released under Creative Common License Attribution-Noncommercial-Share Alike 3.0
  
*/


if (!is_file('config.inc.php')) {
  echo '<H1>Go create your config.inc.php file. For more instructions, check out the README file or visit <a href="http://github.com/gleuch/tweetnotes">github.com/gleuch/tweetnotes</a>.';
  exit;
}

include_once('config.inc.php');
include_once('twitter.lib.php');
include_once('auto_tweet.lib.php');


if (!is_dir('languages/'. DEFAULT_LANGUAGE)) {
  echo '<H1>Please correct your default language or begin to create words and structures for a new language.</h1>';
  exit;
}

foreach ($twitter as $k=>$user) {
  echo '<h3>'. $user['user'] .'</h3>';
  $autotweet = new AutoTweet();
  $autotweet->DevDebug = true;
  $autotweet->Init($user);
  $autotweet->MakePhrase();

}

?>