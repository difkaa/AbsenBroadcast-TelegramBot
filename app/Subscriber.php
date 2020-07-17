<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $guarded = [];

    protected $table = 'tb_siswa';

    public $timestamps = false;

    public function ScopeNisMurid($query, string $answer)
    {
        $ret = $query->where('nis','=',$answer)->first();
        return $ret != null ? $ret:false;

    }
    public function scopeSubscribeUser($query, $answering, $botmanUser)
    {
        $query->where('nis','=', $answering);
        $query->update([
            'chat_id' => $botmanUser->getId()
        ]);
    }

    public function scopeUnsubscribeUser($query, string $chatId)
    {
        $subscriber = $query->where('chat_id', $chatId);
        if($subscriber){
            $subscriber->update([
                'chat_id' => null
            ]);
        }
    }
}
