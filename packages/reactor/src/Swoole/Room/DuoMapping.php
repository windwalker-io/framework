<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Room;

use Windwalker\Reactor\Memory\MemoryTableInterface;

/**
 * The DoubleMapping class.
 */
class DuoMapping
{
    public function __construct(
        protected MemoryTableInterface $aToBMap,
        protected MemoryTableInterface $bToAMap,
        protected int $mapSize = 32768,
    ) {
        $this->aToBMap = $this->configureMap($this->aToBMap);
        $this->bToAMap = $this->configureMap($this->bToAMap);
    }

    public function getBListOfA(string $a): array
    {
        $listString = $this->aToBMap->get($a)['map'] ?? '[]';

        return (array) json_decode($listString);
    }

    public function getAListOfB(string $b): array
    {
        $listString = $this->bToAMap->get($b)['map'] ?? '[]';

        return (array) json_decode($listString);
    }

    public function addMap(string $a, string $b): void
    {
        $this->addAToB($a, $b);
        $this->addBToA($b, $a);
    }

    public function addAToB(string $a, string $b): array
    {
        $aToBMap = $this->aToBMap->get($a)['map'] ?? '[]';

        $bList = (array) json_decode($aToBMap);

        if (!in_array($b, $bList, true)) {
            $bList[] = $b;
        }

        $this->aToBMap->set($a, ['map' => json_encode($bList)]);

        return $bList;
    }

    public function addBToA(string $b, string $a): array
    {
        $bToAMap = $this->bToAMap->get($b)['map'] ?? '[]';

        $aList = (array) json_decode($bToAMap);

        if (!in_array($a, $aList, true)) {
            $aList[] = $a;
        }

        $this->bToAMap->set($b, ['map' => json_encode($aList)]);

        return $aList;
    }

    public function removeMap(string $a, string $b): void
    {
        $this->removeAToB($a, $b);
        $this->removeBToA($b, $a);
    }

    public function removeAToB(string $a, string $b): void
    {
        $listString = $this->aToBMap->get($a)['map'] ?? null;

        if (!$listString) {
            return;
        }

        $list = (array) json_decode($listString);
        $list = array_filter($list, static fn($item) => $item !== $b);

        $this->aToBMap->set($a, ['map' => json_encode($list)]);
    }

    public function removeBToA(string $b, string $a): void
    {
        $listString = $this->bToAMap->get($b)['map'] ?? null;

        if (!$listString) {
            return;
        }

        $list = (array) json_decode($listString);
        $list = array_filter($list, static fn($item) => $item !== $a);

        $this->bToAMap->set($b, ['map' => json_encode($list)]);
    }

    public function removeA(string $a): array
    {
        $bList = $this->getBListOfA($a);

        $this->aToBMap->delete($a);

        foreach ($bList as $b) {
            $this->removeBToA($b, $a);
        }

        return $bList;
    }

    public function removeB(string $b): array
    {
        $aList = $this->getAListOfB($b);

        $this->bToAMap->delete($b);

        foreach ($aList as $a) {
            $this->removeAToB($a, $b);
        }

        return $aList;
    }

    protected function configureMap(MemoryTableInterface $table): MemoryTableInterface
    {
        $table->column('map', MemoryTableInterface::TYPE_STRING, $this->mapSize);

        $table->create();

        return $table;
    }
}
