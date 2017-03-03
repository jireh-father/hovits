<?php
define('CONTENT_TYPE_MOVIE', 'movie');
define('CONTENT_TYPE_PEOPLE', 'people');
define('CONTENT_TYPE_MOVIE_ACTOR', 'movie_actor');
define('CONTENT_TYPE_MOVIE_STAFF', 'movie_staff');

define('CONTENTS_PROVIDER_KOFIC', 'kofic');
define('CONTENTS_PROVIDER_CGV', 'cgv');
define('CONTENTS_PROVIDER_NAVER', 'naver');
define('CONTENTS_PROVIDER_DAUM', 'daum');
define('CONTENTS_PROVIDER_IMDB', 'imdb');
define('CONTENTS_PROVIDER_LOTTE', 'lotte');
define('CONTENTS_PROVIDER_WATCHA', 'watcha');
define('CONTENTS_PROVIDER_HOVITS', 'hovits');
define('CONTENTS_PROVIDER_MEGA', 'mega');

define('OPTION_LAST_UPDATE_DATE_MOVIE_KOFIC', 'ludm_kofic');
define('OPTION_LAST_UPDATE_ID_MOVIE_KOFIC', 'luim_kofic');

define('OPTION_LAST_UPDATE_DATE_PEOPLE_KOFIC', 'ludp_kofic');
define('OPTION_LAST_UPDATE_ID_PEOPLE_KOFIC', 'luip_kofic');

define('THUMB_KEY_FULL_SIZE', 'full_size_path');
define('THUMB_KEY_SMALL_SIZE', 'small_size_path');
define('THUMB_KEY_MID_SIZE', 'mid_size_path');
define('THUMB_KEY_BIG_SIZE', 'big_size_path');

define('THUMB_KEY_HIGH_QUALITY', 'high_quality_path');
define('THUMB_KEY_MID_QUALITY', 'mid_quality_path');
define('THUMB_KEY_LOW_QUALITY', 'low_quality_path');

define('USER_SESSION_KEY', 'user_session_key');

define('FLAG_CONTENT_ALL', 30719);
define('FLAG_CONTENT_MOVIE', 1);
define('FLAG_CONTENT_THUMB', 2);
define('FLAG_CONTENT_STILL_CUT', 4);
define('FLAG_CONTENT_BOX_OFFICE', 8);
define('FLAG_CONTENT_REAL_TIME_BOX_OFFICE', 16);
define('FLAG_CONTENT_GRADE', 32);
define('FLAG_CONTENT_GENRE', 64);
define('FLAG_CONTENT_ALL_IMAGE', 128);
define('FLAG_CONTENT_MOVIE_MATCH', 256);
define('FLAG_CONTENT_MOVIE_MATCH_CHOICE', 512);
define('FLAG_CONTENT_MOVIE_MATCH_GRADE', 1024);
define('FLAG_CONTENT_MOVIE_PEOPLE', 2048);
define('FLAG_CONTENT_MOVIE_SIMILARITY', 4096);
define('FLAG_CONTENT_PEOPLE', 8192);
define('FLAG_CONTENT_DEFAULT_MOVIE', FLAG_CONTENT_MOVIE | FLAG_CONTENT_THUMB | FLAG_CONTENT_STILL_CUT | FLAG_CONTENT_BOX_OFFICE | FLAG_CONTENT_REAL_TIME_BOX_OFFICE | FLAG_CONTENT_GRADE);