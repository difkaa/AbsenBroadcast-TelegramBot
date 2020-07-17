<?php
// use App\Http\Controllers\BotManController;

use App\Conversations\OnboardingConversation;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Attachments\Location;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

$botman = resolve('botman');

$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});

$botman->fallback(function($bot) {
    $bot->reply('Maaf Bot ini hanya satu arah',['parse_mode' => 'HTML']);
});

$botman->hears('/start', function(BotMan $bot){
    $bot->startConversation(new OnboardingConversation);
});

$botman->hears('keluar', function($bot){
    $bot->reply('oke terima kasih');
});

