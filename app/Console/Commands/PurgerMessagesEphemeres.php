<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use Illuminate\Console\Command;

class PurgerMessagesEphemeres extends Command
{
    protected $signature   = 'chat:purger-ephemeres';
    protected $description = 'Supprime tous les messages de chat de plus de 48 heures';

    public function handle(): void
    {
        $count = ChatMessage::where('created_at', '<', now()->subHours(48))->delete();
        $this->info("Messages éphémères supprimés : {$count}");
    }
}
