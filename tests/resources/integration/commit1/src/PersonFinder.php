<?php

namespace DaveLiddament\DummyProject;

class PersonFinder
{
    public function findBobs(array $people): array
    {
        $bobs = [];
        foreach ($people as $person) {
            if ('Bob' === $person->getName()) {
                $bobs[] = $person;
            }
        }

        return $bobs;
    }
}
