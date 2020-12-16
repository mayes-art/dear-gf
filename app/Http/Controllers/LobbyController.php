<?php

namespace App\Http\Controllers;

use App\Services\LineBotService;
use Illuminate\Http\Request;
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

            $this->lineBotService->setBot($event);
            $say = $this->lineBotService->getSay();

            if (Str::contains($say, '幫我丟')) {
                $prefix = mb_substr($say, 0, 2);
                if (!in_array($prefix, ['阿公', '爸爸', 'ㄚ公'])) {
                    return;
                }

                $message = $prefix . '(1~100)隨機骰出來的數字為: ' . $this->lineBotService->randomChange();
                $this->bot->replyText($this->lineBotService->getReplyToken(), $message);
            }

            if ('text' == $this->lineBotService->getReqType() && $this->lineBotService->randomChange() <= 34) {
                $this->bot->replyText($event['events'][0]['replyToken'], '嘔咾上帝, 阿們');
            }
        } catch (\Exception $e) {
            report($e);
        }
    }
}
