<?php

namespace Modules\People\CardDav;

use Modules\People\CardDav\Backends\AuthBackend;
use Modules\People\CardDav\Backends\CardDavBackend;
use Modules\People\CardDav\Backends\PrincipalBackend;
use Modules\People\CardDav\Serializers\VCardSerializer;
use Sabre\CardDAV\AddressBookRoot;
use Sabre\CardDAV\Plugin as CardDavPlugin;
use Sabre\DAV\Auth\Plugin as AuthPlugin;
use Sabre\DAV\Server as SabreServer;
use Sabre\DAV\Sync\Plugin as SyncPlugin;
use Sabre\DAVACL\Plugin as AclPlugin;
use Sabre\DAVACL\PrincipalCollection;

/**
 * DavServer
 *
 * Builds the sabre/dav server with our 3 backends + the standard
 * Auth / DAV-ACL / CardDAV / Sync plugins. The controller calls
 * build() and then executes the request via sabre's invokeMethod().
 *
 * Tree layout exposed to clients:
 *
 *     /dav/
 *         principals/
 *             <user-email>/                    (one per user; for us: 1)
 *         addressbooks/
 *             <user-email>/
 *                 my-contacts/                 (the seeded address book)
 *                     <contact-uuid>.vcf       (one per contact)
 *                     ...
 *
 * The base URI ('/dav/') is set by the controller before invokeMethod.
 */
class DavServer
{
    public function build(): SabreServer
    {
        // Backends - the only DB-aware code in this stack.
        $authBackend       = new AuthBackend();
        $principalBackend  = new PrincipalBackend();
        $cardBackend       = new CardDavBackend(new VCardSerializer());

        // The tree: principals collection + address-books root.
        $nodes = [
            new PrincipalCollection($principalBackend),
            new AddressBookRoot($principalBackend, $cardBackend),
        ];

        $server = new SabreServer($nodes);

        // ----- plugins -----

        // Auth plugin handles 401 challenges and stashes the principal
        // URI for downstream plugins to consume.
        $server->addPlugin(new AuthPlugin($authBackend));

        // ACL plugin enforces "you can only see your own stuff" and
        // wires the principal collection into the access-control checks.
        $aclPlugin = new AclPlugin();
        $aclPlugin->principalCollectionSet = ['principals'];
        $aclPlugin->defaultUsernamePath    = 'principals';
        $server->addPlugin($aclPlugin);

        // CardDAV plugin = the actual addressbook-query / multiget /
        // address-data report handlers that DAVx5 calls.
        $server->addPlugin(new CardDavPlugin());

        // Sync plugin = WebDAV-Sync REPORT support so clients can do
        // incremental sync via sync-tokens instead of refetching all
        // vCards each time. Big win for DAVx5 over slow connections.
        $server->addPlugin(new SyncPlugin());

        return $server;
    }
}
