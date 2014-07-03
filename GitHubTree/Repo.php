<?php
// 21353242
class Repo {
	
	protected $api = "https://api.github.com";
	protected $args;
	protected $username;
	protected $access_token;
	protected $request;
	protected $repo_name;
	protected $repo_sha;
	
	public function __construct($repo_name, $username, $access_token) {
		$this->repo_name = $repo_name;
		$this->username = $username;
		$this->access_token = $access_token;
		
		require_once dirname(__FILE__)."/Request.php";
		$this->request = new Request();
	}
		
	public function create ($callback = false) {
		
		$out = array();
		
		$out["name"] = $this->repo_name;

		$out["auto_init"] = true;
		
		$out = json_encode($out);
		
		$this->request->post($this->api . "/user/repos?access_token=" . $this->access_token, $out);
		
		$code = $this->request->code();
		$res = $this->request->response();
		$err = ($code !== 201);
		
		//
		// Repo sha: internal
		//
		$this->shaify();
		

		//
		// callback
		//        
		if (is_callable($callback)) {
			$callback($err, $code, $res);
		}
		
		return $this;
    }
    
    public function delete ($callback = false) {
    	
    	$this->unshaify();

		$this->request->delete($this->api . "/repos/".$this->username."/".$this->repo_name."?access_token=" . $this->access_token);
		
		$code = $this->request->code();
		$res = $this->request->response();
		$err = ($code !== 204);
		
		if (is_callable($callback)) {
			$callback($err, $code, $res);
		}
		
		return $this;
    }
    
    private function shaify() {
    	
    	//
    	// Do we already have a sha for that repo?
    	//
    	if (is_array($this->repo_sha)) {
    		if ($this->repo_sha["repo_name"] === $this->repo_name) {
    			return;
    		}
    	}
    	
    	$this->repo_sha = array();
    	
    	$this->repo_sha["repo_name"] = $this->repo_name;
    	
    	$this->request->get($this->api . "/repos/" . $this->username . "/" . $this->repo_name . "/git/refs/heads?access_token=" . $this->access_token);
        
        $code = $this->request->code();
        
        if ($code !== 200) {
        	throw new Exception("sha could not be retrieved for repo " . $this->repo_name . ". HTTP Code: " . $code);
        }
        
        $res = json_decode($this->request->response());
        $this->repo_sha["repo_sha"] = $res[0]->object->sha;  
    }
    
    private function unshaify() {
    	$this->repo_sha = null; 
    }
    
    public function branch($branch_name) {
    	
    	$this->shaify();
    	
   		require_once dirname(__FILE__)."/Branch.php";
		return new Branch($branch_name, $this->repo_name, $this->repo_sha["repo_sha"], $this->username, $this->access_token); 
    }
}