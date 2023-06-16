<?php

declare(strict_types=1);

namespace DaveLiddament\DummyProject;

class Person
{
    /**
     * @var int
     */
    public const DEFAULT_AGE = 21;

    private $age = self::DEFAULT_AGE;

    /**
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setAge(int $age): void
    {
        $this->age = $age;
    }
}
