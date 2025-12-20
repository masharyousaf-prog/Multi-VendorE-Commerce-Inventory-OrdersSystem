<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductDeletedNotification extends Notification
{
    use Queueable;

    private $productName;
    private $type; // "Soft Deleted" or "Permanently Deleted"

    public function __construct($productName, $type)
    {
        $this->productName = $productName;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database']; // Saves to your existing 'notifications' table
    }

    public function toArray($notifiable)
    {
        // This structure matches your existing system
        return [
            'message' => "Your product '{$this->productName}' was {$this->type} by the Admin.",
            'product_name' => $this->productName,
            'status' => $this->type,
        ];
    }
}
