<?php

// We have a custom stylesheet
$stylesheets[] = '.mylikes_like {
	background-image: url(images/valid.png) !important;
	filter: grayscale(100%);
	-webkit-filter: grayscale(100%);
	-moz-filter: grayscale(100%);
	-ms-filter: grayscale(100%);
	-o-filter: grayscale(100%);
}

.mylikes_like.liked {
	filter: grayscale(0%);
	-webkit-filter: grayscale(0%);
	-moz-filter: grayscale(0%);
	-ms-filter: grayscale(0%);
	-o-filter: grayscale(0%);
}

.mylikes_likes {
	padding-left: 2px !important;
	background-image: url() !important;
}';