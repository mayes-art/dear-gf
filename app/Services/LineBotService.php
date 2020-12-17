<?php
/**
 * LINE Bot 服務層
 * date: 2020-12-16
 */
namespace App\Services;


use App\Contants\LineUserMap;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;

class LineBotService
{
    protected $bot;

    protected $replyToken;

    protected $type;

    protected $userId;

    protected $say;

    protected $multiMessageBuilder;

    public function __construct(MultiMessageBuilder $multiMessageBuilder)
    {
        $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_ACCESS_TOKEN'));
        $this->bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_BOT_CHANNEL_SECRET')]);
        $this->multiMessageBuilder = $multiMessageBuilder;
    }

    public function setBot(array $data)
    {
        $p = $data['events'][0];

        $this->replyToken = $p['replyToken'];
        $this->type = $p['message']['type'];
        $this->userId = $p['source']['userId'];
        $this->say = $p['message']['text'];
    }

    public function randomChange(): int
    {
        $r = rand(1, 100);
        return $r;
    }

    public function getReqType(): string
    {
        return $this->type;
    }

    public function getSay(): string
    {
        return $this->say;
    }

    public function getReplyToken(): string
    {
        return $this->replyToken;
    }

    public function setText(string $data)
    {
        $this->multiMessageBuilder->add(new LINEBot\MessageBuilder\TextMessageBuilder($data));
    }

    public function setSticker()
    {

    }

    public function checkPrefix()
    {
            $prefix = mb_substr($this->say, 0, 2);
            if (in_array($prefix, ['阿公', '爸爸', 'ㄚ公', '老爸', '爺爺'])) {
                return $prefix;
            }

            return false;
    }

    public function reqNickname()
    {
        return LineUserMap::NICKNAME[$this->userId] ?? '';
    }

    public function bootHandler()
    {
        if (Str::contains($this->say, '幫我丟')) {
            $prefix = $this->checkPrefix();
            $message = $prefix . '(1~100)隨機骰出來的數字為: ' . $this->randomChange();
            $this->setText($message);
        }
    }

    public function reply()
    {
        $response = $this->bot->replyMessage($this->replyToken, $this->multiMessageBuilder);
        if (!$response->isSucceeded()) {
            Log::error(json_encode([
                'status' => $response->getHTTPStatus(),
                'detail' => $response->getRawBody(),
            ]));
        }
    }
}
