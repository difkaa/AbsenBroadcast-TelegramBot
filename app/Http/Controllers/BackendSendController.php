<?php

namespace App\Http\Controllers;

use App\Subscriber;
use BotMan\BotMan\Messages\Attachments\Location;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\Drivers\Telegram\TelegramDriver;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackendSendController extends Controller
{
    public function send()
    {
        $botman = app('botman');
        $message = null;
        $date = new DateTime();
        $day = 'asuk.'.$date->format('d').'';
        $datas['sukses']= array();
        $datas['gagal'] = array();

        $queries = DB::table('tb_siswa as siswa')
        ->leftJoin('a_masuk as asuk', 'siswa.id_siswa', '=', 'asuk.id_siswa')
        ->leftJoin('a_masukket as ket', ''.$day.'', '=', 'ket.token_masuk')
        ->select('siswa.chat_id as chat_id','siswa.nis as nis', 'siswa.nama_depan', 'siswa.nama_belakang', ''.$day.' as token_kelas', 'ket.m_ket as keterangan', 'ket.m_alasan as alasan', 'ket.m_pada as jam_absen', 'ket.latitude as latitude', 'ket.longitude as longitude')
        ->get();

        foreach($queries as $query)
        {
            if($query->keterangan != null )
            {
                $dmyhis = $date->format('d-m-Y').' '.date('h:i:s',$query->jam_absen).'';
                $locationUrl = 'https://www.google.com/maps/@'.$query->latitude.','.$query->longitude.',15z';

                $message = "-------------------------------------- \n";
                $message .= "Name : ".$query->nama_depan." ".$query->nama_belakang." \n";
                $message .= "Absen Masuk : ".$dmyhis." \n";
                $message .= "Absen Pulang : 0821363xxxx \n";
                $message .= "Lokasi : ".$locationUrl." \n";
                $message .= "---------------------------------------";

                $attachment = new Location($query->latitude, $query->longitude,[
                    'custome_payload' => true,
                ]);
                $broadcast = OutgoingMessage::create($message);
                $castMap = OutgoingMessage::create('')->withAttachment($attachment);

                $fire = $botman->say($broadcast, [
                    $query->chat_id
                    ],TelegramDriver::class
                );
                $botman->say($castMap,[
                    $query->chat_id
                    ],TelegramDriver::class
                );

            }else{
                $message = "-------------------------------------- \n";
                $message .= "Name : ".$query->nama_depan." ".$query->nama_belakang." \n";
                $message .= "Absen Masuk : Alfa \n";
                $message .= "Absen Pulang : Alfa \n";
                $message .= "Lokasi : \n";
                $message .= "---------------------------------------";
                $broadcast = OutgoingMessage::create($message);
                $fire = $botman->say($broadcast, [
                    $query->chat_id
                    ],TelegramDriver::class
                );
            }

            $content = json_decode($fire->getContent());

            if($content->ok == false)
            {
                $datas['gagal'][] = $query->nis;
            }
            if($content->ok == true)
            {
                $datas['sukses'][] = $query->nis;
            }
        }

        return response()->json([
            'berhasil' => count($datas['sukses']),
            'gagal' => count($datas['gagal']),
            'nis_berhasil' => count($datas['sukses']) > 0 ? implode(',', $datas['sukses']):0,
            'nis_gagal' => count($datas['gagal']) > 0 ? implode(',', $datas['gagal']):0,
        ]);
    }

    public function sendPulang(Request $request)
    {
        $botman = app('botman');
        $message = null;
        $now = new DateTime();
        $date = $request->tanggal;
        $day = 'pulang.'.$date.'';

        $arrayTanggal = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31');
        if(!in_array($request->tanggal, $arrayTanggal))
        {
            return response()->json([
                'status' => false,
                'data' => 'Tanggal absen tidak masuk dalam jangkauan',
            ]);
        }

        $query = DB::table('tb_siswa as siswa')
        ->leftJoin('a_pulang as pulang', 'siswa.id_siswa', '=', 'pulang.id_siswa')
        ->leftJoin('a_pulangket as ket', ''.$day.'', '=', 'ket.token_pulang')
        ->select('siswa.chat_id as chat_id','siswa.nis as nis', 'siswa.nama_depan', 'siswa.nama_belakang', ''.$day.' as token_kelas', 'ket.id_pulangket as keterangan' , 'ket.p_pada as jam_absen', 'ket.latitude as latitude', 'ket.longitude as longitude')
        ->where('siswa.nis','=',$request->nis)
        ->first();

        if($query == null)
        {
            return response()->json([
                'status' => false,
                'data' => 'data siswa tidak ditemukan',
            ]);

        }
        elseif($query->keterangan != null )
        {
            $dmyhis = $now->format('d-m-Y').' '.date('h:i:s A',$query->jam_absen).'';
            $locationUrl = 'https://www.google.com/maps/@'.$query->latitude.','.$query->longitude.',15z';

            $message = "-------------------------------------- \n";
            $message .= "Name : ".$query->nama_depan." ".$query->nama_belakang." \n";
            $message .= "Absen Pulang : ".$dmyhis." \n";
            $message .= "Lokasi : ".$locationUrl." \n";
            $message .= "---------------------------------------";

            $attachment = new Location($query->latitude, $query->longitude,[
                'custome_payload' => true,
            ]);
            $broadcast = OutgoingMessage::create($message);
            $castMap = OutgoingMessage::create('')->withAttachment($attachment);

            $fire = $botman->say($broadcast, [
                $query->chat_id
                ],TelegramDriver::class
            );
            $botman->say($castMap,[
                $query->chat_id
                ],TelegramDriver::class
            );

            $content = json_decode($fire->getContent());

            if($content->ok == false)
            {
                return response()->json([
                    'status' => false,
                    'data' => $content->description
                ]);
            }
            if($content->ok == true)
            {
                return response()->json([
                    'status' => true,
                    'data' => $message
                ]);
            }

        }else{
            return response()->json([
                'status' => false,
                'data' => 'siswa belum melakukan absen pulang',
            ]);
        }
    }


    public function sendMasuk(Request $request)
    {
        $botman = app('botman');
        $message = null;
        $now = new DateTime();
        $date = $request->tanggal;
        $day = 'asuk.'.$date.'';
        $arrayTanggal = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31');
        if(!in_array($request->tanggal, $arrayTanggal))
        {
            return response()->json([
                'status' => false,
                'data' => 'Tanggal absen tidak masuk dalam jangkauan',
            ]);
        }

        $query = DB::table('tb_siswa as siswa')
        ->leftJoin('a_masuk as asuk', 'siswa.id_siswa', '=', 'asuk.id_siswa')
        ->leftJoin('a_masukket as ket', ''.$day.'', '=', 'ket.token_masuk')
        ->select('siswa.chat_id as chat_id','siswa.nis as nis', 'siswa.nama_depan', 'siswa.nama_belakang', ''.$day.' as token_kelas', 'ket.m_ket as keterangan', 'ket.m_alasan as alasan', 'ket.m_pada as jam_absen', 'ket.latitude as latitude', 'ket.longitude as longitude')
        ->where('siswa.nis','=',$request->nis)
        ->first();

        if($query == null)
        {
            return response()->json([
                'status' => false,
                'data' => 'data siswa tidak ditemukan',
            ]);

        }
        elseif($query->keterangan != null )
        {
            $dmyhis = $now->format('d-m-Y').' '.date('h:i:s A',$query->jam_absen).'';
            $locationUrl = 'https://www.google.com/maps/@'.$query->latitude.','.$query->longitude.',15z';

            $message = "-------------------------------------- \n";
            $message .= "Name : ".$query->nama_depan." ".$query->nama_belakang." \n";
            $message .= "Absen Masuk : ".$dmyhis." \n";
            $message .= "Lokasi : ".$locationUrl." \n";
            $message .= "---------------------------------------";

            $attachment = new Location($query->latitude, $query->longitude,[
                'custome_payload' => true,
            ]);
            $broadcast = OutgoingMessage::create($message);
            $castMap = OutgoingMessage::create('')->withAttachment($attachment);

            $fire = $botman->say($broadcast, [
                $query->chat_id
                ],TelegramDriver::class
            );
            $botman->say($castMap,[
                $query->chat_id
                ],TelegramDriver::class
            );


            $content = json_decode($fire->getContent());

            if($content->ok == false)
            {
                return response()->json([
                    'status' => false,
                    'data' => $content->description
                ]);
            }
            if($content->ok == true)
            {
                return response()->json([
                    'status' => true,
                    'data' => $message
                ]);
            }

        }else{
            return response()->json([
                'status' => false,
                'data' => 'siswa belum melakukan absen masuk',
            ]);
        }
    }
}
