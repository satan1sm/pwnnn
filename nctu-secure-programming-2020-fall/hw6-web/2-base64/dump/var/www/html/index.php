<?php
// You find the source code? Cool.
// It's time to find the other service on *this* server.
// Again, you still shouldn't scan me :/

set_time_limit(5);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Base64 Image Encoder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <style>
        html,
        body {
            font-family: 'Open Sans', sans-serif;
            background: #F0F2F4;
        }

        .hero-body {
            height: 500px;
        }

        .articles {
            margin: 5rem 0;
            margin-top: -250px;
        }

        .articles .content p {
            line-height: 1.9;
            margin: 15px 0;
        }

        .article {
            margin-bottom: 5rem;
        }

        .article-title {
            font-size: 2rem;
            font-weight: normal;
            line-height: 2;
        }

        .article-subtitle {
            color: #909AA0;
            margin-bottom: 3rem;
        }


        .article-body {
            line-height: 1.4;
            margin: 0 6rem;
        }

        @media screen and (max-width: 600px) {
            .article-body {
                margin: 0 1rem;
            }
        }

        .card-content {
            padding: 3rem 0;
        }
    </style>
</head>

<body>
    <section class="hero is-info is-medium is-bold">
        <div class="hero-body">
            <div class="container has-text-centered">
                <h1 class="title">Base64 Image Encoder</h1>
                <h2 class="subtitle">將您的圖片<s>加密</s>為 base64 編碼！</h2>
            </div>
        </div>
    </section>

    <div class="container">
        <section class="articles">
            <div class="column is-8 is-offset-2">
                <div class="card article">
                    <div class="card-content">
                        <div class="media">
                            <div class="media-content has-text-centered">
                                <p class="title article-title">快速轉換</p>
                            </div>
                        </div>
                        <div class="content article-body">
                            <?php
                            $page = str_replace("../", "", $_GET['page'] ?? 'home');
                            include("page/$page.inc.php");
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>

</html>
