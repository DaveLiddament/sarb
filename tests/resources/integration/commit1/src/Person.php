<?php

declare(strict_types=1);

namespace DaveLiddament\DummyProject;

class Person
{
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
}
