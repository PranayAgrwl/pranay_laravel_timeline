<?php

namespace Modules\People\CardDav\Backends;

use App\Models\User;
use Sabre\DAV\PropPatch;
use Sabre\DAVACL\PrincipalBackend\BackendInterface;

/**
 * PrincipalBackend
 *
 * Tells sabre/dav about the "users" of the DAV server. A principal,
 * in sabre's terminology, is anything that can own resources - which
 * for us is the single web user.
 *
 * The principal URI follows the convention `principals/<email>`,
 * matching what AuthBackend hands back after Basic-auth success.
 *
 * Group features (group membership, role-based ACLs) are not used by
 * our single-user setup, so all the group-related methods return
 * empty / no-op.
 */
class PrincipalBackend implements BackendInterface
{
    /**
     * Return every principal whose URI starts with the given prefix.
     *
     * The prefix is typically 'principals' (sometimes with a trailing
     * slash, depending on sabre's caller). We support either form.
     */
    public function getPrincipalsByPrefix($prefixPath): array
    {
        if (rtrim($prefixPath, '/') !== 'principals') {
            return [];
        }

        return User::query()->get()->map(fn (User $u) => $this->toPrincipal($u))->all();
    }

    /**
     * Return a single principal by its full URI (e.g. 'principals/foo@bar.com').
     * Returns an empty array when the URI doesn't map to a known user.
     */
    public function getPrincipalByPath($path): array
    {
        $email = $this->emailFromPath($path);
        if ($email === null) {
            return [];
        }

        $user = User::query()->where('email', $email)->first();
        if ($user === null) {
            return [];
        }

        return $this->toPrincipal($user);
    }

    /**
     * Update principal properties. We do not allow remote edits to
     * the user account via DAV - any property change is silently
     * acknowledged but not persisted.
     */
    public function updatePrincipal($path, PropPatch $propPatch): void
    {
        // intentionally a no-op
    }

    /**
     * Principal search (used by clients to e.g. autocomplete users).
     * We don't expose a directory, so the result is always empty.
     */
    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof'): array
    {
        return [];
    }

    /**
     * Map an arbitrary URI (mailto:, etc.) to a principal URI.
     * We support mailto: lookups so clients that pass an email can
     * find the matching user.
     */
    public function findByUri($uri, $principalPrefix): ?string
    {
        if (str_starts_with($uri, 'mailto:')) {
            $email = substr($uri, 7);
            $user = User::query()->where('email', $email)->first();
            if ($user !== null) {
                return 'principals/'.$user->email;
            }
        }

        return null;
    }

    /**
     * Group membership not used in single-user setup.
     */
    public function getGroupMemberSet($principal): array
    {
        return [];
    }

    public function getGroupMembership($principal): array
    {
        return [];
    }

    public function setGroupMemberSet($principal, array $members): void
    {
        // intentionally a no-op
    }

    /**
     * Convert a User Eloquent model to the array shape sabre expects.
     */
    private function toPrincipal(User $user): array
    {
        return [
            'id'                                 => $user->id,
            'uri'                                => 'principals/'.$user->email,
            '{DAV:}displayname'                  => $user->name ?: $user->email,
            '{http://sabredav.org/ns}email-address' => $user->email,
        ];
    }

    /**
     * Pull the email out of a principal path like 'principals/foo@bar.com'.
     */
    private function emailFromPath(string $path): ?string
    {
        $path = trim($path, '/');
        if (! str_starts_with($path, 'principals/')) {
            return null;
        }

        $email = substr($path, strlen('principals/'));

        return $email !== '' ? $email : null;
    }
}
