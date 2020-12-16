<?php


namespace App\Services;


use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;

class LineBotService
{
    protected $bot;

    protected $replyToken;

    protected $type;

    protected $multiMessageBuilder;

    public function __construct(MultiMessageBuilder $multiMessageBuilder)
    {
        $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_ACCESS_TOKEN'));
        $this->bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_BOT_CHANNEL_SECRET')]);
        $this->multiMessageBuilder = $multiMessageBuilder;
    }

    public function reply()
    {
        $response = $this->bot->replyMessage($this->multiMessageBuilder);
        if (!$response->isSucceeded()) {
            Log::error(json_encode([
                'status' => $response->getHTTPStatus(),
                'detail' => $response->getRawBody(),
            ]));
        }
    }
}
