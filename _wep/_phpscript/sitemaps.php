<?php

function data2sitemaps($data) {
	$xml = '<?xml version="1.0" encoding="UTF-8"<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
	foreach($data) {
		$xml .= '
	<url>
      <loc>http://www.example.com/</loc>
      <lastmod>2005-01-01</lastmod>
      <changefreq>monthly</changefreq>
      <priority>0.8</priority>
   </url>';
	}
	$xml .= '</urlset>';
	return $xml;
}


