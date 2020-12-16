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

    public function setBot(Array $data)
    {
        $p = $data['events'][0];

        $this->replyToken = $p['replyToken'];
        $this->type = $p['message']['type'];
    }

    public function randomChange() : int
    {
        $r = rand(1, 100);
        return $r;
    }

    public function getReqType() : string
    {
        return $this->type;
    }

    public function setText(String $data)
    {
        $this->multiMessageBuilder->add(new LINEBot\MessageBuilder\TextMessageBuilder($data));
    }

    public function setSticker()
    {

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
