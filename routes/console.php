<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('mail:test', function () {
    Mail::raw('Email percobaan via console.php.', function ($msg) {
        $msg->to('admin@example.com')->subject('Test Mailtrap Console');
    });

    $this->info('Email test terkirim ke Mailtrap.');
});
