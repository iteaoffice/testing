<?php

namespace Testing\Util;

use Admin\Entity\Access;
use Contact\Entity\Contact;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class GenerateContactTrait
 *
 * @package Testing\Util
 */
trait GenerateContactTrait
{
    public static function generateContactDummy(array $accessRoles = []): Contact
    {
        $contact = new Contact();
        $contact->setId(1);
        $contact->setFirstName('Test');
        $contact->setLastName('Tester');

        $accessCollection = new ArrayCollection();

        foreach ($accessRoles as $id => $roleName) {
            $access = new Access();
            $access->setId($id + 1);
            $access->setAccess(ucfirst($roleName));
            $accessCollection->add($access);
        }

        $contact->setAccess($accessCollection);

        return $contact;
    }
}
