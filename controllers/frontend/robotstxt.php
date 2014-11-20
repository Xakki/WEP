<?php
header("Content-type:text;charset=utf-8");
echo "User-Agent: *
Disallow: /*.php
Host: " . $_SERVER['HTTP_HOST'] . "
Sitemap: http://" . $_SERVER['HTTP_HOST'] . "/sitemap.xml
";