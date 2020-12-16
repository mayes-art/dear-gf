<?php

namespace App\Http\Controllers;

use App\Services\LineBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LobbyController extends Controller
{

    protected $bot;
    protected $messageBuilder;
    protected $lineBotService;

    public function __construct(LineBotService $lineBotService)
    {
        $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_ACCESS_TOKEN'));
        $this->bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_BOT_CHANNEL_SECRET')]);
        $this->messageBuilder = new MultiMessageBuilder();
        $this->lineBotService = $lineBotService;
    }

    public function lineGet()
    {
        try {
            $event = request()->all();
            Log::info(json_encode($event, JSON_UNESCAPED_UNICODE));
            return response('test');
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function linePost(Request $request)
    {
        try {
            $event = $request->all();
            Log::info(json_encode($event, JSON_UNESCAPED_UNICODE));

            $this->lineBotService->setBot($event);
            $say = $this->lineBotService->getSay();

//            dd($this->lineBotService->reqNickname());

            $prefix = $this->lineBotService->checkPrefix();
            if (!$prefix) {
                return;
            }

            if (Str::contains($say, '讀經')) {
                $stringFormat = explode(' ', $say);

                $response = Http::get('https://bible.fhl.net/json/listall.html');
                $blist = explode(',', $response->body());
//                dd(in_array($stringFormat[1], $blist));

                $blistC = collect($blist);
                $index = $blistC->search($stringFormat[1]);

                if ($index % 5 === 4) {
                    $index--;
                }

                $postParam = [
                    'chineses' => $blist[$index],
                    'chap'     => $stringFormat[2],
                    'sec'      => $stringFormat[3],
                    'strong'   => 0,
                    'gb'       => 0,
                    'version'  => 'unv',
                ];

//                dd($postParam);

                $response = Http::asForm()->post('https://bible.fhl.net/json/qb.php', $postParam);
//                dd($response);
                if ($response->successful()) {
                    $bible = $response->json();
//                    dd($bible);
                    $message = "";
                    foreach($bible['record'] as $k => $v) {
                        $message .= $v['sec'] . "  " . $v['bible_text'] . " %0D%0A ";
                    }

//                    dd($message);

                    $message .= $postParam['chineses'] . " " . $postParam['chap'] . " " . $postParam['sec'];

                    $this->lineBotService->setText($message);
                }
            }

            if (Str::contains($say, '幫我丟')) {
                $message = $prefix . '(1~100)隨機骰出來的數字為: ' . $this->lineBotService->randomChange();
                $this->lineBotService->setText($message);
            }

            if ('text' == $this->lineBotService->getReqType() && $this->lineBotService->randomChange() <= 34) {
                $this->lineBotService->setText('嘔咾上帝, 阿們');
            }

            $this->lineBotService->reply();
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function test(Request $request)
    {
        try {
            $event = $request->all();

            $this->lineBotService->setBot($event);
            $say = $this->lineBotService->getSay();

            if (Str::contains($say, '讀經')) {
                $stringFormat = explode(' ', $say);

                $response = Http::get('https://bible.fhl.net/json/listall.html');
                $blist = explode(',', $response->body());
//                dd(in_array($stringFormat[1], $blist));

                $blistC = collect($blist);
                $index = $blistC->search($stringFormat[1]);

                if ($index % 5 === 4) {
                    $index--;
                }

                $postParam = [
                    'chineses' => $blist[$index],
                    'chap'     => $stringFormat[2],
                    'sec'      => $stringFormat[3],
                    'strong'   => 0,
                    'gb'       => 0,
                    'version'  => 'unv',
                ];

//                dd($postParam);

                $response = Http::asForm()->post('https://bible.fhl.net/json/qb.php', $postParam);
//                dd($response);
                if ($response->successful()) {
                    $bible = $response->json();
//                    dd($bible);
                    $message = "";
                    foreach($bible['record'] as $k => $v) {
                        $message .= $v['sec'] . "  " . $v['bible_text'] . " %0D%0A ";
                    }

//                    dd($message);

                    $message .= $postParam['chineses'] . " " . $postParam['chap'] . " " . $postParam['sec'];

                    $this->lineBotService->setText($message);

                }
            }
        } catch (\Exception $e) {
            report($e);
        }
    }
}
