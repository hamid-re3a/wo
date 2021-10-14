<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Events\JobFailed;

class QueueFailed extends Notification
{
    use Queueable;

    /**
     * @var JobFailed
     */
    public $event;

    /**
     * Create a new notification instance.
     *
     * @param JobFailed $event
     */

    public function __construct(JobFailed $event)
    {
        $this->event = $event;
    }

    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage())
            ->to("#general")
            ->content("Job failed at ". config('app.name'))
            ->attachment(function(SlackAttachment $attachment) use ($notifiable) {
                $attachment->fields([
                    'Payload' => $this->event->job->getRawBody(),
                   'Exception' => $this->event->exception->getTraceAsString(),
                   'Job' => $this->event->job->resolveName()
               ]);
            });

    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
