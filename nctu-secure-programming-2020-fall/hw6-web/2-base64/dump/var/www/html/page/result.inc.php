<?php
if ($url = @$_POST['url']) {
    if ($hostname = parse_url($url)['host']) {
        $ip = gethostbyname($hostname);
        $ip_part = explode(".", $ip);
        if (count($ip_part) !== 4 || in_array($ip_part[0], ['192', '172', '10', '127']))
            die("Invalid hostname.");
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $b64_img = base64_encode(curl_exec($ch));
    echo curl_error($ch) ? curl_error($ch) : "<img src=\"data:image/jpeg;base64,$b64_img\">";
    curl_close($ch);
}

?>
<hr>
<a href="/" class="button is-danger is-large is-fullwidth">
    <span class="icon">
        <i class="fas fa-arrow-left"></i>
    </span>
    <span>返回</span>
</a>
