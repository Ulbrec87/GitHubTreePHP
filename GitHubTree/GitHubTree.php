<?php
/**
 * GitHubTreePHP
 * @author Alex Duloz ~ @alexduloz
 * MIT license
 */
class GitHubTree
{
    protected $api = "https://api.github.com";
    protected $username;
    protected $access_token;
    
    public function __construct($args)
    {
        $this->username     = $args["username"];
        $this->access_token = $args["access_token"];
    }
    
    public function repo($repo_name)
    {
        require_once dirname(__FILE__) . "/Repo.php";
        return new Repo($repo_name, $this->username, $this->access_token);
    }
}