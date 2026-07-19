<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserToPlatformAdminCommand extends Command
{
    protected $signature = 'user:promote-admin {email}';

    protected $description = 'Grant a user platform admin rights (manage the transversal component library)';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error("Aucun utilisateur trouvé pour l'adresse {$this->argument('email')}.");

            return self::FAILURE;
        }

        if ($user->is_platform_admin) {
            $this->info("{$user->email} est déjà admin plateforme.");

            return self::SUCCESS;
        }

        $user->is_platform_admin = true;
        $user->save();

        $this->info("{$user->email} est maintenant admin plateforme.");

        return self::SUCCESS;
    }
}
