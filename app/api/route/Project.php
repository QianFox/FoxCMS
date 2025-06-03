<?php

use think\facade\Route;

Route::group('project', function () {
    Route::rule('versionDownload', 'Project/versionDownload');
})->allowCrossDomain(['Access-Control-Allow-Credentials'   => 'true'])->middleware(\app\api\middleware\FrequentAccessCheck::class);