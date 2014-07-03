<?php

require_once "GitHubTree/GitHubTree.php";

$gt = new GitHubTree(array(
	"username" => "alexduloz",
	"access_token" => "119ff21f522db911a030d0b1121810b7277c039b",
));


/*
$gt->repo("name")->create(function($err, $code, $res){
	var_dump($err);
	var_dump($code);
	var_dump($res);
	die();
});
*/

/*
$gt->repo("name")->delete(function($err, $code, $res){
	var_dump($err);
	var_dump($code);
	var_dump($res);
	die();
});
*/


//
// Working tests!
//

// $gt->repo("name")->create();

// $gt->repo("name")->delete();

// $gt->repo("name")->create()->branch("gh-pages")->create();

// $gt->repo("name")->branch("gh-pages")->delete();

// $gt->repo("name")->create()->branch("gh-pages")->create()->makeDefault();

$gt->repo("name")->branch("gh-pages")->file("API.md")->commit("changed")->content("changed")->create();

// $gt->repo("name")->branch("gh-pages")->file("API.md")->commit("deleted")->delete();

die("reached");