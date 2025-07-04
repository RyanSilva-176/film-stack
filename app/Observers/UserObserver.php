<?php

namespace App\Observers;

use App\Models\User;
use App\Services\Movie\Contracts\MovieListServiceInterface;

class UserObserver
{
    public function __construct(
        protected MovieListServiceInterface $movieListService
    ) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->movieListService->createDefaultListsForUser($user);
    }
}
