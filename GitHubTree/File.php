<?php

class File {
	
	protected $api = "https://api.github.com";
	protected $args;
	protected $username;
	protected $access_token;
	protected $request;
	protected $file_name;
	protected $branch_name;
	protected $repo_name;
	protected $repo_sha;
	
	protected $content;
	protected $commit;
	
	public function __construct($file_name, $branch_name, $repo_name, $repo_sha, $username, $access_token) {
		$this->file_name = $file_name;
		$this->branch_name = $branch_name;
		$this->repo_name = $repo_name;
		$this->repo_sha = $repo_sha;
		$this->username = $username;
		$this->access_token = $access_token;
		
		require_once dirname(__FILE__)."/Request.php";
		$this->request = new Request();
	}
	
	public function content ($content) {
		$this->content = $content;
		return $this;
	}
	
	public function commit ($commit) {
		$this->commit = $commit;
		return $this;
	}
	
	public function create ($callback = false) {
		
		//
		// Current commit sha
		//
		$this->request->get($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/refs/heads/" . $this->branch_name . "?access_token=" . $this->access_token);
		
		$code = $this->request->code();
		$res = json_decode($this->request->response());
		
		if ($code !== 200) {
			if (is_callable($callback)) {
				$callback(true, $code, $res);
			}
			return;
		}
		
		$commitSha = $res->object->sha; 

		//
		// Retrieve the tree the commit points to
		//		
		$this->request->get($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/trees/" . $commitSha . "?access_token=" . $this->access_token);
		
		$code = $this->request->code();
		$res = json_decode($this->request->response());
		
		if ($code !== 200) {
			if (is_callable($callback)) {
				$callback(true, $code, $res);
			}
			return;
		}
		
		$res = json_decode($this->request->response());
		$treeSha = $res->sha; 
		
		$tree = array();
		
		foreach ($res->tree as $current) {
			$tree[$current->path] = array(
				"mode" => $current->mode,
				"type" => $current->type,
				"sha" => $current->sha,
				"path" => $current->path,
			);
		}
		
		
		//
		// Create a blob
       	//
        $out = array(
        	"content" => $this->content,
        	"encoding" => "utf-8"
        );
        
        $out = json_encode($out);
        
        $this->request->post($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/blobs?access_token=" . $this->access_token, $out);
        
        $code = $this->request->code();
		$res = json_decode($this->request->response());
		
        if ($code !== 201) {
			if (is_callable($callback)) {
				$callback(true, $code, $res);
			}
			return;
		}
		
		$blobSha = $res->sha; 
		
		$tree[$this->file_name] = array(
			"mode" => "100644",
			"type" => "blob",
			"sha" => $blobSha,
			"path" => $this->file_name,
		);
		
		$tree = array_values($tree);

		//
		// Update the tree
       	//
        $out = array(
        	"base_tree" => $treeSha,
            "tree" => $tree
        );
        
        $out = json_encode($out);
        
        $this->request->post($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/trees?access_token=" . $this->access_token, $out);
   		
   		$code = $this->request->code();
		$res = json_decode($this->request->response());
		
   		if ($code !== 201) {
			if (is_callable($callback)) {
				$callback(true, $code, $res);
			}
			return;
		}
   		
   		$newTreeSha = $res->sha;
        
        $out = array(
        	"message" => $this->commit,
            "tree" => $newTreeSha,
            "parents" => array($commitSha)
        );
        
        $out = json_encode($out);
        $this->request->post($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/commits?access_token=" . $this->access_token, $out);                             
   		
   		$code = $this->request->code();
   		$res = json_decode($this->request->response());
   		
   		if ($code !== 201) {
			if (is_callable($callback)) {
				$callback(true, $code, $res);
			}
			return;
		}
		
   		$newCommitSha = $res->sha;
        
		$out = array(
			"sha" => $newCommitSha,
			"force" => true
		);
		$out = json_encode($out);
		$this->request->patch($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/refs/heads/".$this->branch_name."?access_token=" . $this->access_token, $out); 
		
		$code = $this->request->code();
		$res = $this->request->response();

		$err = ($code !== 200);
		
		if (is_callable($callback)) {
			$callback($err, $code, $res);
		}
		
		return $this;  
        
	} 
	
	
	public function delete ($callback = false) {
		
		//
		// Current commit sha
		//
		$this->request->get($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/refs/heads/" . $this->branch_name . "?access_token=" . $this->access_token);
		
		$code = $this->request->code();
		$res = json_decode($this->request->response());
		if ($this->request->code() !== 200) {
			if (is_callable($callback)) {
				$callback(true, $code, $res);
			}
			return;
		}
		
		$commitSha = $res->object->sha; 

		//
		// Retrieve the tree the commit points to
		//		
		$this->request->get($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/trees/" . $commitSha . "?access_token=" . $this->access_token);
		
		if ($this->request->code() !== 200) {
			if (is_callable($callback)) {
				$callback(true, $code, $res);
			}
			return;
		}
		
		$res = json_decode($this->request->response());
		$treeSha = $res->sha; 
		
		$tree = array();
		
		foreach ($res->tree as $current) {
			$tree[$current->path] = array(
				"mode" => $current->mode,
				"type" => $current->type,
				"sha" => $current->sha,
				"path" => $current->path,
			);
		}
		
		unset($tree[$this->file_name]);
		
		$tree = array_values($tree);

		//
		// Update the tree
       	//
        $out = array(
            "tree" => $tree
        );
   		
        $out = json_encode($out);
        
        $this->request->post($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/trees?access_token=" . $this->access_token, $out);
   		
   		$code = $this->request->code();
   		$res = json_decode($this->request->response());
   		
   		if ($code !== 201) {
			if (is_callable($callback)) {
				$callback(true, $code, $res);
			}
			return;
		}

   		$newTreeSha = $res->sha;
        
        $out = array(
        	"message" => $this->commit,
            "tree" => $newTreeSha,
            "parents" => array($commitSha)
        );
        
        $out = json_encode($out);
        $this->request->post($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/commits?access_token=" . $this->access_token, $out);                             
   		
   		$code = $this->request->code();
   		$res = json_decode($this->request->response());
   		
   		if ($code !== 201) {
			if (is_callable($callback)) {
				$callback(true, $code, $res);
			}
			return;
		}

   		$newCommitSha = $res->sha;
        
		$out = array(
			"sha" => $newCommitSha,
			"force" => true
		);
		$out = json_encode($out);
		$this->request->patch($this->api . "/repos/". $this->username . "/" . $this->repo_name . "/git/refs/heads/".$this->branch_name."?access_token=" . $this->access_token, $out); 
		
		$code = $this->request->code();
		$res = $this->request->response();

		$err = ($code !== 200);
		
		if (is_callable($callback)) {
			$callback($err, $code, $res);
		}
		
		return $this;  
	} 

}