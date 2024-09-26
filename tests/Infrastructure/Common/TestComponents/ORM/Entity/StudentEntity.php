<?php

namespace Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\Entity;

use Unzer\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Unzer\Core\Infrastructure\ORM\Configuration\IndexMap;
use Unzer\Core\Infrastructure\ORM\Entity;

/**
 * Class StudentEntity.
 *
 * @package Unzer\Core\Tests\Infrastructure\Common\TestComponents\ORM\Entity
 */
class StudentEntity extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * @var ?int
     */
    public ?int $localId = null;

    /**
     * @var ?string
     */
    public ?string $username = null;

    /**
     * @var ?string
     */
    public ?string $email = null;

    /**
     * @var ?string
     */
    public ?string $firstName = null;

    /**
     * @var ?string
     */
    public ?string $lastName = null;

    /**
     * @var ?string
     */
    public ?string $gender = null;

    /**
     * @var array
     */
    public array $demographics = [];

    /**
     * @var array
     */
    public array $addresses = [];

    /**
     * @var array
     */
    public array $alerts = [];

    /**
     * @var array
     */
    public array $schoolEnrollment = [];

    /**
     * @var array
     */
    public array $contact = [];

    /**
     * Array of field names.
     *
     * @var array
     */
    protected array $fields = [
        'id',
        'localId',
        'username',
        'email',
        'firstName',
        'lastName',
        'gender',
        'demographics',
        'addresses',
        'alerts',
        'schoolEnrollment',
        'contact',
    ];

    /**
     * Returns entity configuration object
     *
     * @return EntityConfiguration
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();
        $indexMap->addIntegerIndex('localId')
            ->addStringIndex('username')
            ->addStringIndex('email')
            ->addStringIndex('gender')
            ->addStringIndex('firstName')
            ->addStringIndex('lastName');

        return new EntityConfiguration($indexMap, 'Student');
    }
}
