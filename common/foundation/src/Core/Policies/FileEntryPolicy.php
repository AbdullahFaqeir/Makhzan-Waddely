<?php

namespace Common\Core\Policies;

use App\Models\User;
use Common\Files\FileEntry;
use Illuminate\Support\Arr;
use Common\Files\FileEntryUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Class FileEntryPolicy.
 *
 * @package Common\Core\Policies
 * @date    07/01/2026
 * @author  Abdullah Al-Faqeir <abdullah@devloops.net>
 */
class FileEntryPolicy extends BasePolicy
{
    public function index(
        ?User $user,
        array|null $entryIds = null,
        int|null $userId = null,
    ): bool {
        if ($entryIds) {
            return $this->userCan($user, 'files.view', $entryIds);
        }

        return $user?->hasPermission('files.update')
               || $userId === $user?->id;
    }

    public function show(?User $user, FileEntry $entry): bool
    {
        if (request('policy')
            && Gate::allows('show', [request('policy'), $entry])) {
            return true;
        }

        $token = $this->getAccessTokenFromRequest();

        if ($token) {
            if ($entry->preview_token === $token) {
                return true;
            }

            if ($accessToken = app(PersonalAccessToken::class)->findToken($token)) {
                $user = $accessToken->tokenable;
            }
        }

        return $user && $this->userCan($user, 'files.view', $entry);
    }

    public function download(User $user, $entries): bool
    {
        if (request('policy')
            && Gate::allows('show', [request('policy'), $entries[0]])) {
            return true;
        }

        $token = $this->getAccessTokenFromRequest();
        if ($token) {
            $previewTokenMatches = collect($entries)->every(function (
                $entry,
            ) use ($token) {
                return $entry['preview_token'] === $token;
            });
            if ($previewTokenMatches) {
                return true;
            }

            if ($accessToken = app(PersonalAccessToken::class)->findToken($token)) {
                $user = $accessToken->tokenable;
            }
        }

        return $this->userCan($user, 'files.download', $entries);
    }

    public function store(User $user, int|null $parentId = null): bool
    {
        //check if user can modify parent entry (if specified)
        if ($parentId) {
            return $this->userCan($user, 'files.update', [$parentId]);
        }

        return $user->hasPermission('files.create')
               || $user->hasPermission('files.update');
    }

    public function update(
        User $user,
        Collection|array|FileEntry $entries
    ): bool {
        return $this->userCan($user, 'files.update', $entries);
    }

    /**
     * @param User                       $user
     * @param Collection|array|FileEntry $entries
     *
     * @return bool
     */
    public function destroy(User $user, $entries): bool
    {
        return $this->userCan($user, 'files.delete', $entries)
               || $user->hasPermission('files.update');
    }

    /**
     * @param User                       $currentUser
     * @param string                     $permission
     * @param FileEntry|array|Collection $entries
     *
     * @return bool
     */
    protected function userCan(
        User $currentUser,
        string $permission,
        $entries
    ): bool {
        if ($currentUser->hasPermission($permission)
            || $currentUser->hasPermission('files.update')) {
            return true;
        }

        $entries = $this->findEntries($entries);

        // extending class might use "findEntries" method so we load users here
        if (!$entries->every->relationLoaded('users')) {
            $entries->load([
                'users' => function (MorphToMany $builder) use ($currentUser) {
                    $builder->where('users.id', $currentUser->id);
                },
            ]);
        }

        return $entries->every(function (FileEntry $entry) use (
            $permission,
            $currentUser,
        ) {
            $user = $entry->users->find($currentUser->id);
            return $this->userOwnsEntryOrWasGrantedPermission($user,
                $permission,);
        });
    }

    /**
     * @param null|array|FileEntryUser $user
     * @param string                   $permission
     *
     * @return bool
     */
    public function userOwnsEntryOrWasGrantedPermission(
        $user,
        string $permission,
    ): bool {
        return $user
               && ($user['owns_entry']
                   || Arr::get($user['entry_permissions'],
                    $this->sharedFilePermission($permission),));
    }

    protected function findEntries(
        FileEntry|array|Collection $entries,
    ): Collection {
        if ($entries instanceof FileEntry) {
            return $entries->newCollection([$entries]);
        }

        if (isset($entries[0]) && is_numeric($entries[0])) {
            return app(FileEntry::class)
                ->whereIn('id', $entries)
                ->get();
        }

        return $entries;
    }

    protected function sharedFilePermission($fullPermission): string
    {
        return match ($fullPermission) {
            'files.view' => 'view',
            'files.create', 'files.update' => 'edit',
            'files.delete' => 'delete',
            'files.download' => 'download',
            default => '',
        };
    }

    protected function getAccessTokenFromRequest(): ?string
    {
        if ($token = request()->bearerToken()) {
            return $token;
        }

        if ($token = request()->get('preview_token')) {
            return $token;
        }

        if ($token = request()->get('accessToken')) {
            return $token;
        }

        return null;
    }
}
