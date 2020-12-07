<?php
session_start();
require("lib.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rero Meme</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/bulma@0.9.0/css/bulma.min.css" />
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500&display=swap');

        * {
            font-family: 'Quicksand', sans-serif;
        }
    </style>
</head>

<body>
    <?php
    $user = NULL;
    if (isset($_POST['username']) || isset($_SESSION['username'])) {
        $user = new User($_POST['username'] ?? $_SESSION['username'], "images/");
    } else {
        include "login.html";
        exit;
    }

    if (isset($_FILES['meme'])) {
        if ($_FILES['meme']['error'] !== UPLOAD_ERR_OK)
            die("Upload error: error code=" . $_FILES['meme']['error']);

        $tmp_name = $_FILES['meme']['tmp_name'];
        if (exif_imagetype($tmp_name) === IMAGETYPE_GIF && preg_match("/[a-z0-9_\- ]+/i", $_POST['title'])) {
            $content = file_get_contents($tmp_name);
            $meme = new Meme($_POST['title'], $user, $content);
        } else {
            die("Invalid Meme.");
        }
        header("Location: /");
    }
    ?>
    <div class="navbar is-inline-flex is-transparent">
        <div class="navbar-brand">
            <a class="navbar-item">
                <p class="title is-4">Rero Meme</p>
            </a>
        </div>
        <div class="navbar-menu">
            <div class="navbar-item">
                <div class="control has-icons-left">
                    <input class="input is-small has-text-centered" type="text" placeholder="search">
                    <span class="icon is-left is-small">
                        <i class="material-icons">search</i>
                    </span>
                </div>
            </div>
        </div>
        <div class="navbar-item is-flex-touch">
            <a class="navbar-item">
                <i class="material-icons">explore</i>
            </a>
            <a class="navbar-item">
                <i class="material-icons">favorite_border</i>
            </a>
            <a class="navbar-item">
                <i class="material-icons">person_outline</i>
            </a>
        </div>
    </div>
    <div class="columns body-columns">
        <div class="column is-half is-offset-one-quarter">
            <p>Welcome, <?= $user ?>. Post your meme here:</p>
            <div id="post-meme">
                <form method="POST" enctype="multipart/form-data">
                    <div class="field">
                        <input class="input" type="text" name="title" placeholder="Meme Title">
                    </div>
                    <div class="field">
                        <input type="file" name="meme">
                    </div>
                    <div class="field">
                        <button class="button is-small is-fullwidth" type="submit">Submit meme</button>
                    </div>
                </form>
            </div>
            <?php foreach (array_reverse(glob("images/$user/*.gif")) as $filename) : ?>
                <div class="card">
                    <div class="header">
                        <div class="media">
                            <div class="media-left">
                                <figure class="image is-48x48">
                                    <img src="https://source.unsplash.com/random/96x96" alt="Placeholder image">
                                </figure>
                            </div>
                            <div class="media-content">
                                <p class="title is-4"><?= $user ?></p>
                                <p class="subtitle is-6">@<?= $user ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-image">
                        <figure class="image ">
                            <img src="<?= htmlentities($filename) ?>" alt="Placeholder image">
                        </figure>
                    </div>
                    <div class="card-content">
                        <div class="level is-mobile">
                            <div class="level-left">
                                <div class="level-item has-text-centered">
                                    <a href="">
                                        <i class="material-icons">favorite_border</i>
                                    </a>
                                </div>
                                <div class="level-item has-text-centered">
                                    <div>
                                        <a href="">
                                            <i class="material-icons">chat_bubble_outline</i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="content">
                            <p>
                                <strong>87 Likes</strong>
                            </p>
                            <?= htmlentities(basename($filename, "gif")) ?>
                            <br>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="columns is-mobile">
                            <div class="column is-11">
                                <div class="field">
                                    <div class="control">
                                        <input class="input is-medium" type="text" placeholder="Add a comment . . .">
                                    </div>
                                </div>
                            </div>
                            <div class="column has-text-centered">
                                <button class="button">
                                    <i class="material-icons">more_horiz</i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="card">
                <div class="header">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <img src="https://source.unsplash.com/random/96x96" alt="Placeholder image">
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-4">Admin</p>
                            <p class="subtitle is-6">@admin</p>
                        </div>
                    </div>
                </div>
                <div class="card-image">
                    <figure class="image ">
                        <img src="default_meme.gif" alt="Placeholder image">
                    </figure>
                </div>
                <div class="card-content">
                    <div class="level is-mobile">
                        <div class="level-left">
                            <div class="level-item has-text-centered">
                                <a href="">
                                    <i class="material-icons">favorite_border</i>
                                </a>
                            </div>
                            <div class="level-item has-text-centered">
                                <div>
                                    <a href="">
                                        <i class="material-icons">chat_bubble_outline</i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="content">
                        <p>
                            <strong>87 Likes</strong>
                        </p>
                        Rero rero.
                        <a href="#">#rero</a>
                        <a href="#">#kakyoin</a>
                        <br>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="columns is-mobile">
                        <div class="column is-11">
                            <div class="field">
                                <div class="control">
                                    <input class="input is-medium" type="text" placeholder="Add a comment . . .">
                                </div>
                            </div>
                        </div>
                        <div class="column has-text-centered">
                            <button class="button">
                                <i class="material-icons">more_horiz</i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <div class="container is-fluid">
                    <div class="content has-text-centered">
                        <p>
                            <strong>Bulma</strong> by
                            <a href="https://bulmatemplates.github.io/bulma-templates/">Bulma Templates</a>. The source code is licensed
                            <a href="http://opensource.org/licenses/mit-license.php">MIT</a>. The website content is licensed
                            <a href="http://creativecommons.org/licenses/by-nc-sa/4.0/">CC BY NC SA 4.0</a>.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>
