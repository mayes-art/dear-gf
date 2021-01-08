<?php

namespace App\Http\Controllers;

use App\Constants\Constellation;
use App\Services\LineBotService;
use Hanson\Chinese\Chinese;
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

            $nickName = $this->lineBotService->reqNickname();
            $prefix = $this->lineBotService->checkPrefix();
//            if (!$prefix) {
//                return;
//            }

            if (Str::contains($say, '讀經')) {
                $stringFormat = explode(' ', $say);

                $response = Http::get('http://bible.fhl.net/json/listall.html');
                $blist = explode(',', $response->body());
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

                $response = Http::asForm()->post('http://bible.fhl.net/json/qb.php', $postParam);
                if ($response->successful()) {
                    $bible = $response->json();
                    foreach($bible['record'] as $k => $v) {
                        $this->lineBotService->setText($v['sec'] . "  " . $v['bible_text']);
                    }

                    $this->lineBotService->setText($postParam['chineses'] . " " . $postParam['chap'] . " " . $postParam['sec']);
                }
            }

            if (Str::contains($say, 'roll')) {
                $message = $prefix . '(1~100)隨機骰出來的數字為: ' . $this->lineBotService->randomChange();
                $this->lineBotService->setText($message);
            }

            if ('text' == $this->lineBotService->getReqType() && $this->lineBotService->randomChange() <= 34) {
                $this->lineBotService->setText('嘔咾上帝, 阿們');
                if (!empty($nickName)) {
                    $this->lineBotService->setText($nickName);
                }
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

            if (Str::contains("{$say}座", Constellation::ALL_TW)) {
                $say2s = Chinese::simplified($say . "座");
                dd($say2s);
                $apiUri = "https://api.5tk.xyz/api/conste.php?msg={$say2s}";
                $response = Http::get($apiUri);
                echo Chinese::traditional($response->body());
            }
        } catch (\Exception $e) {
            report($e);
        }
    }
}
