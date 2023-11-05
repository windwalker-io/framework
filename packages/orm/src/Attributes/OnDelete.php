<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;
use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Relation\Strategy\RelationConfigureInterface;

/**
 * The OnDelete class.
 */
#[Attribute]
class OnDelete implements RelationConfigureAttributeInterface
{
    /**
     * OnUpdate constructor.
     *
     * @param  string  $action
     */
    public function __construct(
        #[ExpectedValues(Action::ACTIONS)]
        protected string $action = Action::IGNORE
    ) {
        //
    }

    /**
     * @inheritDoc
     */
    public function __invoke(RelationConfigureInterface $relation): void
    {
        $relation->onDelete($this->action);
    }
}
