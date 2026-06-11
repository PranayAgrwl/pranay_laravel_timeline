<?php

namespace Modules\People\CardDav\Serializers;

use Modules\People\Models\Contact;
use Modules\People\Models\Phone;
use Sabre\VObject\Component\VCard;

/**
 * VCardSerializer
 *
 * Converts a People\Contact (with its phones) into a vCard 3.0 string
 * that DAVx5 + Android Contacts understand. vCard 3.0 (not 4.0) gives
 * us the broadest compatibility with Android's stock contact handlers.
 *
 * Fields emitted in this step (per Step-3 scope decision):
 *   UID    - the contact's uuid (the stable id CardDAV syncs on)
 *   FN     - "first middle last" joined human-readable name
 *   N      - structured name: lastname;firstname;middle;;
 *   TEL    - one TEL line per Phone row, with TYPE= from phone_label
 *   REV    - last-modified timestamp; lets clients detect updates
 *
 * Email/address/dates/work get added in later steps; this is the
 * minimum needed to prove the sync pipeline.
 */
class VCardSerializer
{
    /**
     * Build a vCard string for the given contact.
     */
    public function serialize(Contact $contact): string
    {
        return $this->buildVCard($contact)->serialize();
    }

    /**
     * Build the VCard object (separated so callers can introspect /
     * extend it before serialising if they want).
     */
    public function buildVCard(Contact $contact): VCard
    {
        $vcard = new VCard([
            'VERSION' => '3.0',
            'UID'     => $contact->uuid,
            'FN'      => $this->fullName($contact),
            'N'       => [
                $contact->last_name ?? '',
                $contact->first_name ?? '',
                $contact->middle_name ?? '',
                '', // honorific prefix - not stored yet
                '', // honorific suffix - not stored yet
            ],
            'REV'     => $contact->updated_at?->format('Ymd\THis\Z')
                          ?? now()->format('Ymd\THis\Z'),
        ]);

        // Phones - one TEL line per row, with the TYPE coming from
        // the phone label (Mobile, Home, Work, ...). Country code is
        // prepended to keep the number self-contained on the phone.
        $contact->loadMissing(['phones.label', 'phones.country']);
        foreach ($contact->phones as $phone) {
            $vcard->add('TEL', $this->formattedNumber($phone), [
                'TYPE' => $this->telTypeFromLabel($phone),
            ]);
        }

        return $vcard;
    }

    /**
     * Combine first / middle / last into a single display name.
     * Drops empty parts so a no-middle-name person reads naturally.
     */
    private function fullName(Contact $contact): string
    {
        return trim(implode(' ', array_filter([
            $contact->first_name,
            $contact->middle_name,
            $contact->last_name,
        ])));
    }

    /**
     * Build the dialable number string by prefixing the country code
     * when available, falling back to the raw number otherwise.
     */
    private function formattedNumber(Phone $phone): string
    {
        $cc = $phone->country?->country_code;

        if ($cc !== null && $cc !== '') {
            // ensure exactly one leading "+" and no spaces
            $cc = '+'.ltrim($cc, '+');

            return $cc.' '.$phone->phone;
        }

        return $phone->phone;
    }

    /**
     * Map the phone-label row to one of the standard vCard TEL types
     * (CELL / HOME / WORK / OTHER). Anything we don't recognise
     * becomes OTHER - safe default that all CardDAV clients accept.
     */
    private function telTypeFromLabel(Phone $phone): string
    {
        $name = strtolower($phone->label?->name ?? '');

        return match (true) {
            str_contains($name, 'mobile') || str_contains($name, 'cell') => 'CELL',
            str_contains($name, 'home')                                   => 'HOME',
            str_contains($name, 'work') || str_contains($name, 'office')  => 'WORK',
            str_contains($name, 'fax')                                    => 'FAX',
            default                                                       => 'OTHER',
        };
    }
}
