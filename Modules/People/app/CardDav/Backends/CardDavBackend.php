<?php

namespace Modules\People\CardDav\Backends;

use Modules\People\CardDav\Serializers\VCardSerializer;
use Modules\People\Models\AddressBook;
use Modules\People\Models\Contact;
use Sabre\CardDAV\Backend\BackendInterface;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\PropPatch;

/**
 * CardDavBackend
 *
 * Bridges sabre/dav's CardDAV protocol layer to our own people_*
 * tables. sabre asks "show me the address books for this principal",
 * "give me the vCards in this book", etc.; this class translates
 * those into queries against AddressBook / Contact / Phone.
 *
 * READ-ONLY FROM THE PHONE (per design):
 *   - createCard / updateCard / deleteCard ALL throw 403 Forbidden.
 *   - createAddressBook / deleteAddressBook / updateAddressBook same.
 * The phone (DAVx5) is a pull-only consumer; all writes happen in
 * the web UI we'll build in Step 4.
 */
class CardDavBackend implements BackendInterface
{
    public function __construct(private VCardSerializer $serializer)
    {
    }

    /**
     * Return the list of address books visible to this principal.
     *
     * For our single-user setup every address book in
     * people_addressbooks is exposed to whichever user authenticated -
     * it's their own personal CRM, after all.
     */
    public function getAddressBooksForUser($principalUri): array
    {
        return AddressBook::query()->get()->map(function (AddressBook $ab) use ($principalUri) {
            return [
                'id'                                                            => $ab->addressbook_id,
                'uri'                                                           => $ab->uri,
                'principaluri'                                                  => $principalUri,
                '{DAV:}displayname'                                             => $ab->displayname,
                '{urn:ietf:params:xml:ns:carddav}addressbook-description'       => $ab->description ?? '',
                '{http://calendarserver.org/ns/}getctag'                        => (string) $ab->sync_token,
                '{http://sabredav.org/ns}sync-token'                            => (string) $ab->sync_token,
            ];
        })->all();
    }

    /**
     * Address-book property updates (display name etc.) are not
     * accepted from the phone. We respond by simply not handling any
     * mutations - sabre will then 403 on each property in the PropPatch.
     */
    public function updateAddressBook($addressBookId, PropPatch $propPatch): void
    {
        // intentionally a no-op (effectively read-only)
    }

    /**
     * Address-book creation from the phone is rejected outright.
     */
    public function createAddressBook($principalUri, $url, array $properties): void
    {
        throw new Forbidden('Address books are managed via the web app, not over CardDAV.');
    }

    /**
     * Address-book deletion from the phone is rejected outright.
     */
    public function deleteAddressBook($addressBookId): void
    {
        throw new Forbidden('Address books are managed via the web app, not over CardDAV.');
    }

    /**
     * Return a one-line summary per card in the book (id + etag +
     * lastmodified + size). sabre uses this for cheap sync diffing
     * before pulling the full vCards.
     */
    public function getCards($addressbookId): array
    {
        return Contact::query()->get()->map(function (Contact $c) {
            $card = $this->buildCardData($c);

            return [
                'id'           => $c->contact_id,
                'uri'          => $c->uuid.'.vcf',
                'lastmodified' => $c->updated_at?->getTimestamp() ?? time(),
                'etag'         => '"'.md5($card).'"',
                'size'         => strlen($card),
            ];
        })->all();
    }

    /**
     * Return the full vCard for a single card URI within an address
     * book. The URI is what the client received from getCards()
     * (`<uuid>.vcf`).
     */
    public function getCard($addressBookId, $cardUri)
    {
        $uuid = $this->stripVcfSuffix($cardUri);
        $contact = Contact::query()->where('uuid', $uuid)->first();

        if ($contact === null) {
            return false;   // sabre treats false here as "not found"
        }

        $card = $this->buildCardData($contact);

        return [
            'id'           => $contact->contact_id,
            'uri'          => $cardUri,
            'lastmodified' => $contact->updated_at?->getTimestamp() ?? time(),
            'etag'         => '"'.md5($card).'"',
            'size'         => strlen($card),
            'carddata'     => $card,
        ];
    }

    /**
     * Batch fetch multiple cards by URI in one go. Sabre calls this
     * during the addressbook-multiget REPORT (DAVx5 uses it heavily
     * to avoid N+1 requests during initial sync).
     *
     * The default sabre fallback would just loop and call getCard()
     * once per URI - we do better by issuing a single WHERE-IN query
     * against people_contacts.
     */
    public function getMultipleCards($addressBookId, array $uris): array
    {
        $uuids = array_map(fn ($u) => $this->stripVcfSuffix($u), $uris);

        return Contact::query()
            ->whereIn('uuid', $uuids)
            ->get()
            ->map(function (Contact $c) {
                $card = $this->buildCardData($c);

                return [
                    'id'           => $c->contact_id,
                    'uri'          => $c->uuid.'.vcf',
                    'lastmodified' => $c->updated_at?->getTimestamp() ?? time(),
                    'etag'         => '"'.md5($card).'"',
                    'size'         => strlen($card),
                    'carddata'     => $card,
                ];
            })
            ->all();
    }

    /**
     * Phone-side card creation is rejected (writes happen only in
     * the web UI). We respond with 403 so DAVx5 logs a clear reason.
     */
    public function createCard($addressBookId, $cardUri, $cardData): string
    {
        throw new Forbidden('Contacts are managed via the web app, not over CardDAV.');
    }

    public function updateCard($addressBookId, $cardUri, $cardData): string
    {
        throw new Forbidden('Contacts are managed via the web app, not over CardDAV.');
    }

    public function deleteCard($addressBookId, $cardUri): bool
    {
        throw new Forbidden('Contacts are managed via the web app, not over CardDAV.');
    }

    // --------------------------- helpers --------------------------

    /**
     * Serialise the contact (with eager-loaded phones) into the
     * canonical vCard string used for both etag and body output.
     */
    private function buildCardData(Contact $contact): string
    {
        return $this->serializer->serialize($contact);
    }

    /**
     * Card URIs are conventionally `<uuid>.vcf`. Strip the suffix
     * (some clients omit it; handle both forms).
     */
    private function stripVcfSuffix(string $cardUri): string
    {
        if (str_ends_with($cardUri, '.vcf')) {
            return substr($cardUri, 0, -4);
        }

        return $cardUri;
    }
}
