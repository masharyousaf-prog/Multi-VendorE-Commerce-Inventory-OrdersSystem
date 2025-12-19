<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage; // Import this

class NewUserRegistered extends Notification
{
    use Queueable;
    public $user; // To store the new user details

    public function __construct($newUser)
    {
        $this->user = $newUser;
    }

    public function via($notifiable)
    {
        return ['database']; // Store in database table
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'New ' . $this->user->role . ' registered: ' . $this->user->name,
            'user_id' => $this->user->id
        ];
    }
}
