<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PdfResultEvent implements ShouldBroadcast
{
    public string $queue = 'broadcast';

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        protected string $channelName,
        public string $key,
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel($this->channelName);
    }
}
