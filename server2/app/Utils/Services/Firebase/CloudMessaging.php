<?php

namespace App\Utils\Services\Firebase;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class CloudMessaging
{
    protected Messaging $messaging;

    protected CloudMessage $cloudMessage;

    protected array $deviceTokens = [];

    public function __construct()
    {
        $this->messaging = Firebase::messaging();

        $this->cloudMessage = CloudMessage::new();
    }

    public function setTokens(array $deviceTokens): static
    {
        $this->deviceTokens = $deviceTokens;

        return $this;
    }

    public function setNotification(string $title, string $body = '', string $imageUrl = ''): static
    {
        $this->cloudMessage = $this->cloudMessage->withNotification(Notification::create($title, $body, $imageUrl));

        return $this;
    }

    public function setData(array $data): static
    {
        $this->cloudMessage = $this->cloudMessage->withData($data);

        return $this;
    }

    public function setTopic(string $topic): static
    {
        $this->cloudMessage = $this->cloudMessage->toTopic($topic);

        return $this;
    }

    public function send()
    {
        try {
            $this->messaging->sendMulticast($this->cloudMessage, $this->deviceTokens);
        } catch (\Throwable $th) {
            $readableDeviceTokens = implode(',', $this->deviceTokens);

            Log::error("FCM failed with tokens: [$readableDeviceTokens] \n\n $th");
        }
    }

    public function massSend(string $topic)
    {
        try {
            $this->setTopic($topic);

            $this->messaging->send($this->cloudMessage);
        } catch (\Throwable $th) {
            Log::error("FCM failed for topic [$topic]: \n\n $th");
        }
    }
}
