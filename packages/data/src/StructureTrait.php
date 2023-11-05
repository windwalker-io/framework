<?php

namespace Windwalker\Data;

use Psr\Http\Message\StreamInterface;
use SplFileInfo;
use Windwalker\Data\Format\FormatRegistry;
use Windwalker\Filesystem\FileObject;
use Windwalker\Scalars\StringObject;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\TypeCast;

use function Windwalker\str;

/**
 * The Structure class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait StructureTrait
{
    /**
     * @var FormatRegistry
     */
    protected ?FormatRegistry $formatRegistry = null;

    /**
     * Method to get property FormatRegistry
     *
     * @return  FormatRegistry
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getFormatRegistry(): FormatRegistry
    {
        if (!$this->formatRegistry) {
            $this->formatRegistry = new FormatRegistry();
        }

        return $this->formatRegistry;
    }

    /**
     * Method to set property formatRegistry
     *
     * @param  FormatRegistry  $formatRegistry
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setFormatRegistry(FormatRegistry $formatRegistry): static
    {
        $this->formatRegistry = $formatRegistry;

        return $this;
    }

    /**
     * load
     *
     * @param  mixed        $data
     * @param  string|null  $format
     * @param  array        $options
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function load(mixed $data, ?string $format = null, array $options = []): static
    {
        $this->storage = Arr::mergeRecursive($this->storage, $this->loadData($data, $format, $options));

        return $this;
    }

    public function loadFile(string|SplFileInfo $file, ?string $format = null, array $options = []): static
    {
        if ($file instanceof SplFileInfo) {
            $file = $file->getPathname();
        }

        $registry = $this->getFormatRegistry();
        $this->storage = Arr::mergeRecursive($this->storage, $registry->loadFile($file, $format, $options));

        return $this;
    }

    /**
     * withLoad
     *
     * @param  mixed        $data
     * @param  string|null  $format
     * @param  array        $options
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function withLoad(mixed $data, ?string $format = null, array $options = []): StructureTrait|static
    {
        $new = clone $this;

        $new->storage = Arr::mergeRecursive($new->storage, $this->loadData($data, $format, $options));

        return $new;
    }

    /**
     * loadData
     *
     * @param  mixed        $data
     * @param  string|null  $format
     * @param  array        $options
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function loadData(mixed $data, ?string $format = null, array $options = []): array
    {
        if ($data instanceof FileObject) {
            $data = $data->getStream();
        }

        if ($data instanceof SplFileInfo) {
            $data = $data->getPathname();
        }

        if ($data instanceof StreamInterface) {
            $data = $data->getContents();
        }

        if (is_array($data) || is_object($data)) {
            $storage = TypeCast::toArray($data, $options['to_array'] ?? false);
        } else {
            $registry = $this->getFormatRegistry();

            $storage = TypeCast::toArray(
                $registry->load($data, $format, $options),
                $options['to_array'] ?? false
            );
        }

        return $storage;
    }

    /**
     * toString
     *
     * @param  string|null  $format
     * @param  array        $options
     *
     * @return StringObject
     *
     * @since  __DEPLOY_VERSION__
     */
    public function toString(?string $format = null, array $options = []): string
    {
        return $this->getFormatRegistry()->dump($this->storage, $format ?? 'json', $options);
    }

    public function toStringObject(?string $format = null, array $options = []): StringObject
    {
        return str($this->toString($format, $options));
    }

    /**
     * __toString
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
