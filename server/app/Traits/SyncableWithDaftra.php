<?php

namespace App\Traits;

trait SyncableWithDaftra
{
    /**
     * Check if the model has been synced with Daftra.
     */
    public function isSyncedWithDaftra(): bool
    {
        return !is_null($this->daftra_id);
    }

    /**
     * Alias for isSyncedWithDaftra (semantic convenience).
     */
    public function hasDaftraId(): bool
    {
        return $this->isSyncedWithDaftra();
    }

    /**
     * Set the Daftra ID.
     */
    public function setDaftraId(int $id): bool
    {
        return $this->update(['daftra_id' => $id]);
    }
}
