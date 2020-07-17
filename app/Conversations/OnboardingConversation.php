<?php

namespace App\Conversations;

use App\Subscriber;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Illuminate\Support\Facades\DB;

class OnboardingConversation extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->welcomeUser();
    }

    private function welcomeUser()
    {
        $this->say('Selamat datang di Bot Absen, Saya memiliki informasi bot tentang absen harian');
        $this->getDataMurid();
    }

    private function getDataMurid()
    {
        $this->ask('Silahkan masukkan NIS siswa', function(Answer $answer){
            $answering = $answer->getText();
            $query = Subscriber::nisMurid($answering);

            if($query)
            {
                $this->say("Oke Data ditemukan Nis <b>".$query->nis."</b> dengan nama <b>".$query->nama_depan." ".$query->nama_belakang."</b>",['parse_mode' => 'HTML']);
                $this->askUserToSubscribe($answering);

            }
            else{
                $this->say('Maaf data tidak ditemukan dengan Nis '.$answer->getText().'');
                $this->repeat();
            }
        });

    }
    private function askUserToSubscribe($nis)
    {
        $question = Question::create('Saya bisa mengirimkan notifikasi informasi absen setiap hari, untuk melakukan hal itu, saya perlu memerlukan data kamu. Apakah kamu bersedia?')
                ->addButtons([
                    Button::create('Ya, saya bersedia.')->value('yes'),
                    Button::create('Tidak, jangan sekarang')->value('no'),
                ]);
        $this->ask($question, function(Answer $answer) use ($nis){
            switch ($answer->getText()) {
                case 'no':
                    Subscriber::unsubscribeUser($this->bot->getUser()->getId());
                    $this->bot->typesAndWaits(3);
                    return $this->say('Oke tidak masalah, Terima Kasih.');
                case 'yes':
                    Subscriber::subscribeUser(
                        $nis,
                        $this->bot->getUser(),
                    );
                    $this->bot->typesAndWaits(3);
                    return $this->say('Terima Kasih, saya akan berikan informasi absen setiap hari.');
                default:
                    $this->bot->typesAndWaits(3);
                    $this->say('Maaf saya tidak paham maksudmu, bisa diulangi?');
                    $this->repeat();
            }
        });
    }
}
