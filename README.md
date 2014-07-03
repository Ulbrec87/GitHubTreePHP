# GitHubTree.php 

v0.1.0

A powerful PHP library to manage files on GitHub. That's what we use on [The Pastry Box Project](https://the-pastry-box-project.net) to synchronize our content with GitHub.

## Intro

GitHubTree is not a wrapper for the GitHub API. It aims at letting you create/delete repos|branches|files in a very simple way, by automating the interactions between your application and the GitHub API.

## Install

Download this repo. All the code you need is located in the `GitHubTree` folder.

```
require_once "path/to/GitHubTree/GitHubTree.php";

$gt = new GitHubTree(array(
    "username" => "",
    "access_token" => "",
));
```

Provide a `username` and an `access_token` and you're all set. You can acquire an access token programatically, by querying the GitHub API, or you can create a personal access token through the GitHub website: "Account Settings > Applications > Personal access tokens". You would typically do the latter if you're interacting with a repo you own.

## Examples

### Repos

#### Create a repo

```
$gt->repo("repo_name")->create();
```

With a callback:

```
$gt->repo("repo_name")->create(function($err, $code, $res){
    var_dump($err);
    var_dump($code);
    var_dump($res);
});
```

#### Delete a repo

```
$gt->repo("repo_name")->delete();
```

With a callback:

```
$gt->repo("repo_name")->delete(function($err, $code, $res){
    var_dump($err);
    var_dump($code);
    var_dump($res);
});
```

### Branches

To work with branches, you first need to access a repo:

```
$repo = $gt->repo("repo_name");
```

You can then alter its branches:

```
$repo->branch("gh-pages")->create();
$repo->branch("master")->delete();
```

With callbacks:

```
$repo->branch("gh-pages")->create(function($err, $code, $res){
    var_dump($err);
    var_dump($code);
    var_dump($res);
});

$repo->branch("master")->delete(function($err, $code, $res){
    var_dump($err);
    var_dump($code);
    var_dump($res);
});
```

We could also create a repo on the fly, and then alter its branches:

```
$gt->repo("some_name")->create()->branch("gh-pages")->create();
```

To make a branch the default branch, just use the `makeDefault` method:

```
$gt->repo("name")->create()->branch("gh-pages")->create()->makeDefault();
```

### Files

To work with files, you first need to access a repo and one of its branches:

```
// Instance of a HELLO.md file
$file = $gt->repo("repo_name")->branch("gh-pages")->file("HELLO.md");

// Commit, add content, and then push the instance on GitHub
$file->commit("some commit message")->content("I am the HELLO file")->create();

// Later, we could also delete the file
$file->commit("delete the file")->delete();
```

## API

Technically, a GitHubTree instance has only one method: `repo()`. All other methods are accessed through this "original method", which makes writing API specs quite annoying. For the sake of simplicity, we're going to consider that there are more than *one* mwthod to a GitHubTree instance.

### The repo method

#### `repo( string $name )`

```
$repo = $gt->repo("some_name");
```

Once you have instantiated a `$repo` object, you can either create the actual repo on GitHub or delete it:

##### create( [function $callback] )

```
$repo->create(function($err, $code, $res){
    
});
```

##### delete( [function $callback] )

```
$repo->delete(function($err, $code, $res){
    
});
```

### The branch method

#### `branch( string $name )`

A `$branch` object can be accessed through a `$repo` object.

```
$branch = $gt->repo("some_name")->branch("gh-pages");
```

Once you have instantiated a `$branch` object, you can either create the actual branch on GitHub, delete it or make it the default branch of `$repo`:

##### `create( [function $callback] )`

```
$branch->create(function($err, $code, $res){
    
});
```

##### `delete( [function $callback] )`

```
$branch->delete(function($err, $code, $res){
    
});
```

##### `makeDefault( [function $callback] )`

```
$branch->makeDefault(function($err, $code, $res){
    
});
```

### The file method

#### `file( string $name )`

A file object can be accessed through a `$branch` object (itself accessible through a `$repo` object).

```
$file = $gt->repo("some_name")->branch("gh-pages")->file("README.md");
```

Once you have instantiated a `$file` object, you can either create the actual file on GitHub (and add content to it) or delete it. For each action, you have the possibility to add a commit message.

##### `content( string $content )`

```
$file->content("Hello!");
```

##### `commit( string $content )`

```
$file->commit("init");
```

##### `create( function $callback )`

```
$file->create(function($err, $code, $res){
    
});
```

##### `delete( function $callback )`

```
$file->delete(function($err, $code, $res){
    
});
```