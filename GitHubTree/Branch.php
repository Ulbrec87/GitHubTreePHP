<?php

class Branch {
	
	protected $api = "https://api.github.com";
	protected $args;
	protected $username;
	protected $access_token;
	protected $request;
	protected $branch_name;
	protected $repo_name;
	protected $repo_sha;
	
	public function __construct($branch_name, $repo_name, $repo_sha, $username, $access_token) {
		$this->branch_name = $branch_name;
		$this->repo_name = $repo_name;
		$this->repo_sha = $repo_sha;
		$this->username = $username;
		$this->access_token = $access_token;
		
		require_once dirname(__FILE__)."/Request.php";
		$this->request = new Request();
	}
	
	public function create ($callback = false) {
		$out = json_encode(array(
			"ref" => "refs/heads/" . $this->branch_name,
			"sha" => $this->repo_sha
		));
		        
		$this->request->post($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/refs?access_token=" . $this->access_token, $out);
		
		$code = $this->request->code();
		$res = $this->request->response();
		$err = ($code !== 201);
		
		if (is_callable($callback)) {
			$callback($err, $code, $res);
		}
		
		return $this;
	}
		
    public function delete ($callback = false) {
			
		$this->request->delete($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/refs/heads/" . $this->branch_name . "?access_token=" . $this->access_token);
		
		$code = $this->request->code();
		$res = $this->request->response();
		$err = ($code !== 201);
		
		if (is_callable($callback)) {
			$callback($err, $code, $res);
		}
		
		return $this;

    }
    
    public function makeDefault ($callback = false) {
		$out = array(
			"name" => $this->repo_name,
			"default_branch" => $this->branch_name,
		);
		
		$out = json_encode($out);
		
		$this->request->patch($this->api . "/repos/" . $this->username . "/" . $this->repo_name . "?access_token=" . $this->access_token, $out);
		
		$code = $this->request->code();
		$res = $this->request->response();
		$err = ($code !== 200);
		
		if (is_callable($callback)) {
			$callback($err, $code, $res);
		}
		
		return $this;        
    }   
    
    public function file($file_name) {

   		require_once dirname(__FILE__)."/File.php";
		return new File($file_name, $this->branch_name, $this->repo_name, $this->repo_sha["repo_sha"], $this->username, $this->access_token); 
    }     
}