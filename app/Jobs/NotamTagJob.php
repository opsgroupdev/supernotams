<?php

namespace App\Jobs;

use App\Contracts\NotamTagger;
use App\Enum\LLM;
use App\Enum\NotamStatus;
use App\Exceptions\TaggingConnectionException;
use App\Models\Notam;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class NotamTagJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    protected int $startTime;

    public function __construct(protected readonly Notam $notam, protected readonly LLM $llm)
    {
        $this->onQueue('tagging');
    }

    public function handle(NotamTagger $tagger): void
    {
        $this->startTime = hrtime(true);

        try {
            $tagger
                ->setLLM($this->llm)
                ->tag($this->notam);
        } catch (TaggingConnectionException $connectionException) {
            $this->retryWithDelay($connectionException);
        } catch (Throwable $errorException) {
            $this->retryImmediately($errorException);
        }

        $this->simpleRateLimit();
    }

    protected function retryWithDelay(TaggingConnectionException $exception): void
    {
        //We should retry this with an exponential delay.
        Log::error("{$this->notam->id} - Tagger Connection Issue: {$exception->getMessage()}");

        if ($this->attempts() === $this->tries) {
            $this->notam->update(['status' => NotamStatus::UNTAGGED]);
        } else {
            $this->release(
                now()->addMinutes(1 + ($this->attempts() - 1) * 15) //roughly 1, 15, 30, 45, 60min delays
            );
        }
    }

    protected function retryImmediately(Exception $errorException): void
    {
        Log::error("{$this->notam->id} - Tagger Error: {$errorException->getMessage()}");

        $this->attempts() === $this->tries
            ? $this->notam->update(['status' => NotamStatus::ERROR])
            : $this->release();
    }

    /**
     * Ensure that this job takes a minimum of 2 seconds to run.
     *
     * With 3 workers processing the queue we can only hit the api 90 times a minutes. Well under the
     * rate limits.
     */
    protected function simpleRateLimit(): void
    {
        $totalTime = (hrtime(true) - $this->startTime) / 1e3; // convert to microseconds

        if ($totalTime < 2000000) {
            usleep(2000000 - $totalTime);
        }
    }
}
