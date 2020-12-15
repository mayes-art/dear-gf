<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LobbyController extends Controller
{

    protected $bot;
    protected $messageBuilder;

    public function __construct()
    {
        $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_ACCESS_TOKEN'));
        $this->bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_BOT_CHANNEL_SECRET')]);
        $this->messageBuilder = new MultiMessageBuilder();
    }

    public function lineGet()
    {
        try {
            $event = request()->all();
            Log::info(json_encode($event));
            logger(json_encode($event, JSON_UNESCAPED_UNICODE));
            return response('test');
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function linePost(Request $request)
    {
        try {
            $event = $request->all();
            Log::info(json_encode($event));
            logger(json_encode($event, JSON_UNESCAPED_UNICODE));

            $text = new TextMessageBuilder('歐嘍上帝, 阿們');
            $message = '歐嘍上帝, 阿們';
            $this->bot->replyText($event['events'][0]['replyToken'], $message);
//            return response('test');
        } catch (\Exception $e) {
            report($e);
        }
    }
}
