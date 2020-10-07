<?php
namespace Mezon\CrudService\Tests;

//TODO replace it with standart mock security provider, I am sure we already have one

/**
 * Class FakeSecurityProviderForCrudService
 *
 * @package CrudService
 * @subpackage CrudServiceUnitTests
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/13)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Fake security provider
 */
class FakeSecurityProviderForCrudService implements \Mezon\Security\AuthenticationProviderInterface
{

    public function getSelfLogin(): string
    {
        // nop
    }

    public function getLoginFieldName(): string
    {
        // nop
    }

    public function getSessionIdFieldName(): string
    {
        // nop
    }

    public function getSelfId(): int
    {
        // nop
    }

    public function createSession(string $token): string
    {
        // nop
    }

    public function connect(string $login, string $password): string
    {
        // nop
    }
}
