<?php
/*
  auto-tweet
  Core Twitter Library
  ----------------------------------------------------------------------------------------------------------------

  A nifty script send notification you when people unfollow you on Twitter.
  More information at http://gleuch.com/projects/auto-tweet -or- http://github.com/gleuch/auto-tweet

  ----------------------------------------------------------------------------------------------------------------
  Released under Creative Common License Attribution-Noncommercial-Share Alike 3.0
  
*/

class Twitter {
	public $Host;
  private $Username;
  private $Password;
  private $Email;
  private $RealName;


	public $Message;
	public $Result;
	public $ReturnInfo;
	public $Send;

	private $Connection = '';
	public $DevDebug = false;
	public $Post = 0;

  public function Init($user) {
    $this->Username = $user['user'];
    $this->Password = $user['pass'];
    $this->Email = $user['email'];
    $this->RealName = $user['name'];
  }

  public function SendEmail($subj, $msg) {
    if (!empty($this->Email)) {
      $email = $this->Email;
    } elseif (defined('DEFAULT_EMAIL') && DEFAULT_EMAIL != '') {
      $email = DEFAULT_EMAIL;
    } else {
      echo 'No email address defined for '. $this->Username .'.';
      return false;
    }
    if (!empty($this->RealName)) {
      $to = $this->RealName .'<'. $email .'>';
    } elseif (defined('DEFAULT_REAL_NAME') && DEFAULT_REAL_NAME != '') {
      $to = DEFAULT_REAL_NAME .'<'. $email .'>';
    } else {
      $to = $email;
    }
    $from = 'Tweet-note Notifier <'. (defined('DEFAULT_FROM_EMAIL') && DEFAULT_FROM_EMAIL != '' ? DEFAULT_FROM_EMAIL : $email) .'>';

    return mail($to, '[auto-tweet] '. $subj, $msg, "From:". $from ."\nReply-to:". $from ."\nContent-Type: text/html; charset=utf-8");
  }

  public function GetPath($do, $page=false, $format='json') {
    switch ($do) {
      case "tweet": $path = 'statuses/update'; break;
      case "friends": $path = "statuses/friends"; break;
      case "followers": default: $path = "statuses/followers"; break;
    }
    return 'http://twitter.com/'. $path .'.'. $format . ($page && $page > 0 ? '?page='. $page : '');
  }


  public function Followers() {
    $size = 100; $page = 1; $followers = array();
    while ($size == 100) {
      $this->Host = $this->GetPath('followers', $page);
      $this->Start();
      $page_followers = json_decode(strip_tags($this->Result));
      if (!$page_followers->error) {
        $size = count($page_followers);
        if ($size > 0) foreach ($page_followers as $k=>$follower) array_push($followers, $follower->screen_name);
        if ($size == 100) $page++;
      } else {
        echo $this->Username .': '. $page_followers->error;
        return false;
      }
    }
    return $followers;
  }



	public function Start() {
	  $this->Post = 1;
		$this->Connect();
		$this->Tweet();
		$this->ReturnInfo();
		$this->Disconnect();
	}

	private function Test() {
		echo 'test: '. $this->Username .' - '. $this->Host;
	}

	private function Connect() {
		if ($this->Debug()) echo 'Starting connection...';
		$this->Connection = curl_init();

		if (!$this->Connected()) return;
		if ($this->Debug()) echo 'connected!<br />';
		curl_setopt($this->Connection, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->Connection, CURLOPT_USERPWD, $this->Username .':'. $this->Password);
		curl_setopt($this->Connection, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($this->Connection, CURLOPT_POST, $this->Post);
	}

	private function Tweet() {
		if (!$this->Connected()) return;
		if ($this->Debug()) echo 'Posting...';
    $this->Host = $this->GetPath('tweet', false, 'xml');
		$host = $this->Host;
		$host .= (eregi('\?', $host) ? '&' : '?') . 'status='. urlencode($this->Send);
		$host .= (eregi('\?', $host) ? '&' : '?') .'source=twitterart';
		curl_setopt($this->Connection, CURLOPT_URL, $host);
		$this->Result = curl_exec($this->Connection);
		if ($this->Debug()) echo 'complete!<br />';
	}

	private function ReturnInfo() {
		if (!$this->Connected()) return;
		$this->ReturnInfo = curl_getinfo($this->Connection);
	}

	private function Disconnect() {
		if (!$this->Connected()) return;
		if ($this->Debug()) echo 'Closing connection...';
		curl_close($this->Connection);
		if ($this->Debug()) echo 'closed.';
	}

	private function Connected() {
		if ($this->Connection) return true;
		if ($this->Debug()) echo 'Twitter connection not available.';
		return false;
	}

	protected function Debug() {
		return ($this->DevDebug === true);
	}

	public function ErrorCode($code='0') {
		$codes = array(
			'0' => 'Unknown Code',
			'200' => 'OK: everything went awesome.',
			'304' => 'Not Modified: there was no new data to return.',
			'400' => 'Bad Request: your request is invalid, and we\'ll return an error message that tells you why. This is the status code returned if you\'ve exceeded the rate limit (see below).',
			'401' => 'Not Authorized: either you need to provide authentication credentials, or the credentials provided aren\'t valid.',
			'403' => 'Forbidden: we understand your request, but are refusing to fulfill it.  An accompanying error message should explain why.',
			'404' => 'Not Found: either you\'re requesting an invalid URI or the resource in question doesn\'t exist (ex: no such user).',
			'500' => 'Internal Server Error: we did something wrong.  Please post to the group about it and the Twitter team will investigate.',
			'502' => 'Bad Gateway: returned if Twitter is down or being upgraded.',
			'503' => 'Service Unavailable: the Twitter servers are up, but are overloaded with requests. Try again later.',
		);

		return (array_key_exists($code, $codes)) ? $codes[$code] : $codes['0'];
	}


  public function FancyTime($time, $wording=false) {
    if ($time > 2592000) {
      $t = floor($time/2592000);
      $m = $t .' month'. ($t != 1 ? 's' : '');
    } elseif ($time > 604800) {
      $t = floor($time/604800);
      $m = $t .' week'. ($t != 1 ? 's' : '');
    } elseif ($time > 86400) {
      $t = floor($time/86400);
      $m = $t .' day'. ($t != 1 ? 's' : '');
    } elseif ($time > 3600) {
      $t = floor($time/3660);
      $m = $t .' hour'. ($t != 1 ? 's' : '');
    } elseif ($time > 60) {
      $t = floor($time/60);
      $m = $t .' minute'. ($t != 1 ? 's' : '');
    } elseif ($time > 15) {
      $m = $time .' seconds';
    } else {
      $m = ($wording ? '' : 'a ') .'few seconds';
    }

    return !empty($wording) ? sprintf($wording, $m) : ($time > 0 ? 'in '. $m : $m .' ago');
  }
}

?>