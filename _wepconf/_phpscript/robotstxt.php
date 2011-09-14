<?
header("Content-type:text;charset=utf-8");
echo "User-Agent: *
Allow: /*.html$
Disallow: /*.php$
Disallow: /mail_*.html
Disallow: /add_*.html
Disallow: /subscribe.html
Host: ".$_SERVER['HTTP_HOST']."
Sitemap: http://".$_SERVER['HTTP_HOST']."/sitemap.xml
";