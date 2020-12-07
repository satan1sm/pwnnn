<?php
class User
{
    public $username;
    function __construct($username, $directory=".") {
        chdir($directory);
        if(!is_dir($username)) mkdir($username);
        $this->username = $_SESSION['username'] = $username;
        chdir($_SERVER["DOCUMENT_ROOT"]);
    }
    function __toString()
    {
        return htmlentities($this->username);
    }
}

class Meme
{
    public $title;
    public $author;
    public $filename;
    private $content = NULL;
    function __construct($title, $author, $content = NULL)
    {
        $this->title = $title;
        $this->author = $author;
        $this->content = $content;
        $this->filename = "images/$author/$title.gif";
    }
    function __destruct()
    {
        if ($this->content != NULL)
            file_put_contents($this->filename, $this->content);
    }
}
