<?php

namespace App\Events;

use App\Message;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Message $message */
    public $message;

    /** @var int $receiverId */
    private $receiverId;

    public function __construct($receiverId, $message)
    {
        $this->message = $message;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('messages-channel-'.$this->receiverId);
    }

    public function broadcastAs()
    {
        return 'messages-event';
    }
}