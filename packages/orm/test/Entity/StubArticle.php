<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity;

use DateTimeImmutable;
use Windwalker\Data\Collection;
use Windwalker\DI\Attributes\Autowire;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastNullable;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\JsonCast;
use Windwalker\ORM\Event\AfterSaveEvent;
use Windwalker\ORM\Event\BeforeStoreEvent;
use Windwalker\ORM\Event\EnergizeEvent;
use Windwalker\Scalars\StringObject;

/**
 * The Article class.
 */
#[Table('articles')]
#[\AllowDynamicProperties]
class StubArticle
{
    #[Column('id'), PK, AutoIncrement]
    protected ?int $id = null;

    #[Column('category_id')]
    protected int $categoryId;

    #[Column('title')]
    protected string $title = '';

    #[Column('image')]
    protected string $image = '';

    #[Column('content')]
    protected string $content = '';

    #[Column('state')]
    protected int $state = 1;

    #[Column('hits')]
    protected int $hits = 0;

    #[Column('created')]
    #[CastNullable(DateTimeImmutable::class)]
    protected ?DateTimeImmutable $created;

    #[Column('created_by')]
    protected int $createdBy = 0;

    #[Column('params')]
    #[Cast(new JsonCast())]
    protected ?array $params = [];

    #[Cast(StubCategory::class, options: Cast::USE_HYDRATOR)]
    public StubCategory|Collection|null $c = null;

    public static int $counter = 0;

    public static array $diff = [];

    public static array $extra = [];

    #[AfterSaveEvent]
    public static function afterSave(
        AfterSaveEvent $event
    ): void {
        static::$counter++;

        // Keeping use getter to test the B/C works
        $data = $event->getData();
        $data['category_id'] = 2;

        $event->setData($data);

        static::$extra = $event->getExtra();
    }

    #[BeforeStoreEvent]
    public static function beforeStore(
        BeforeStoreEvent $event
    ): void {
        static::$diff = $event->getData();

        $event->setExtra(['content' => $event->getData()['content']]);
    }

    #[EnergizeEvent]
    public static function energize(
        EnergizeEvent $event
    ): void {
        $event->storeCallback(
            'str',
            fn() => \Windwalker\str('HAHA')
        );
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param  string  $title
     *
     * @return  static  Return self to support chaining.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param  string  $image
     *
     * @return  static  Return self to support chaining.
     */
    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param  string  $content
     *
     * @return  static  Return self to support chaining.
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @param  DateTimeImmutable|null  $created
     *
     * @return  static  Return self to support chaining.
     */
    public function setCreated(?DateTimeImmutable $created): static
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    /**
     * @param  int  $createdBy
     *
     * @return  static  Return self to support chaining.
     */
    public function setCreatedBy(int $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param  array|null  $params
     *
     * @return  static  Return self to support chaining.
     */
    public function setParams(?array $params): static
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @param  int  $categoryId
     *
     * @return  static  Return self to support chaining.
     */
    public function setCategoryId(int $categoryId): static
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param  int  $state
     *
     * @return  static  Return self to support chaining.
     */
    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param  int|null  $id
     *
     * @return  static  Return self to support chaining.
     */
    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    /**
     * @param  int  $hits
     *
     * @return  static  Return self to support chaining.
     */
    public function setHits(int $hits): static
    {
        $this->hits = $hits;

        return $this;
    }
}
