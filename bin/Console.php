<?php

declare(strict_types=1);

namespace Asika\SimpleConsole {

    class Console implements \ArrayAccess
    {
        public const ParameterType STRING = ParameterType::STRING;

        public const ParameterType INT = ParameterType::INT;

        public const ParameterType NUMERIC = ParameterType::NUMERIC;

        public const ParameterType FLOAT = ParameterType::FLOAT;

        public const ParameterType BOOLEAN = ParameterType::BOOLEAN;

        public const ParameterType ARRAY = ParameterType::ARRAY;

        public const ParameterType LEVEL = ParameterType::LEVEL;

        public const int SUCCESS = 0;

        public const int FAILURE = 255;

        public int $verbosity = 0;

        public array $params = [];

        public array $boolMapping = [
            ['n', 'no', 'false', 0, '0'],
            ['y', 'yes', 'true', 1, '1'],
        ];

        public bool $disableDefaultParameters = false;

        public static function createArgvParser(\Closure|null $configure = null): ArgvParser
        {
            ($parser = new ArgvParser()) && (!$configure || $configure($parser));

            return $parser;
        }

        public static function parseArgv(
            \Closure|null $configure = null,
            ?array $argv = null,
            bool $validate = true
        ): array {
            return static::createArgvParser($configure)->parse($argv ?? $_SERVER['argv'], $validate);
        }

        public function __construct(
            public $stdout = STDOUT,
            public $stderr = STDERR,
            public $stdin = STDIN,
            public string $heading = '',
            public string $epilog = '',
            public ?string $commandName = null,
            public ArgvParser $parser = new ArgvParser(),
        ) {
        }

        public function addParameter(
            string|array $name,
            ParameterType $type,
            string $description = '',
            bool $required = false,
            mixed $default = null,
            bool $negatable = false,
        ): Parameter {
            return $this->parser->addParameter($name, $type, $description, $required, $default, $negatable);
        }

        public function addHelpParameter(): Parameter
        {
            return $this->addParameter('--help|-h', static::BOOLEAN, 'Show description of all parameters', false);
        }

        public function addVerbosityParameter(): Parameter
        {
            return $this->addParameter('--verbosity|-v', static::LEVEL, 'The verbosity level of the output');
        }

        public function get(string $name, mixed $default = null): mixed
        {
            return $this->params[$name] ?? $default;
        }

        protected function configure(): void
        {
        }

        protected function preprocess(): void
        {
        }

        protected function doExecute(): int|bool
        {
            return 0;
        }

        public function execute(?array $argv = null, ?\Closure $main = null): int
        {
            $argv = $argv ?? $_SERVER['argv'];
            $this->commandName ??= basename($argv[0]);
            try {
                $this->disableDefaultParameters || ($this->addHelpParameter() && $this->addVerbosityParameter());
                $this->configure();
                $this->params = $this->parser->parse($argv, false);
                if (!$this->disableDefaultParameters) {
                    $this->verbosity = (int) $this->get('verbosity');
                    if ($this->get('help')) {
                        $this->showHelp();

                        return static::SUCCESS;
                    }
                }
                $this->params = $this->parser->validateAndCastParams($this->params);
                $this->preprocess();
                $exitCode = $main ? $main->call($this, $this) : $this->doExecute();
                if ($exitCode === true || $exitCode === null) {
                    $exitCode = 0;
                } elseif ($exitCode === false) {
                    $exitCode = 255;
                }

                return (int) $exitCode;
            } catch (\Throwable $e) {
                return $this->handleException($e);
            }
        }

        public function showHelp(): void
        {
            $help = ParameterDescriptor::describe($this->parser, $this->commandName, $this->epilog);
            $this->writeln(ltrim($this->heading . "\n\n" . $help))->newLine();
        }

        public function write(string $message, bool $err = false): static
        {
            fwrite($err ? $this->stderr : $this->stdout, $message);

            return $this;
        }

        public function writeln(string $message = '', bool $err = false): static
        {
            return $this->write($message . "\n", $err);
        }

        public function newLine(int $lines = 1, bool $err = false): static
        {
            return $this->write(str_repeat("\n", $lines), $err);
        }

        public function in(): string
        {
            return rtrim(fread(STDIN, 8192), "\n\r");
        }

        public function ask(string $question = '', string $default = ''): string
        {
            $this->write($question);
            $in = rtrim(fread(STDIN, 8192), "\n\r");

            return $in === '' ? $default : $in;
        }

        public function askConfirm(string $question = '', string $default = ''): bool
        {
            return (bool) $this->mapBoolean($this->ask($question, $default));
        }

        public function mapBoolean($in): bool|null
        {
            $in = strtolower((string) $in);
            if (in_array($in, $this->boolMapping[0], true)) {
                return false;
            }
            if (in_array($in, $this->boolMapping[1], true)) {
                return true;
            }

            return null;
        }

        public function exec(string $cmd, \Closure|null|false $output = null, bool $showCmd = true): ExecResult
        {
            !$showCmd || $this->writeln('>> ' . $cmd);
            [$outFull, $errFull, $code] = ['', '', 255];
            if ($process = proc_open($cmd, [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes)) {
                $callback = $output ?: fn($data, $err) => ($output === false) || $this->write($data, $err);
                while (($out = fgets($pipes[1])) || $err = fgets($pipes[2])) {
                    if (isset($out[0])) {
                        $callback($out, false);
                        $outFull .= $output === false ? $out : '';
                    }
                    if (isset($err[0])) {
                        $callback($err, false);
                        $errFull .= $output === false ? $err : '';
                    }
                }

                $code = proc_close($process);
            }

            return new ExecResult($code, $outFull, $errFull);
        }

        public function mustExec(string $cmd, ?\Closure $output = null): ExecResult
        {
            $result = $this->exec($cmd, $output);
            $result->success || throw new \RuntimeException('Command "' . $cmd . '" failed with code ' . $result->code);

            return $result;
        }

        protected function handleException(\Throwable $e): int
        {
            if ($e instanceof InvalidParameterException) {
                $this->writeln('[Warning] ' . $e->getMessage(), true)->newLine(err: true)
                    ->writeln(
                        $this->commandName . ' ' . ParameterDescriptor::synopsis($this->parser, false),
                        true
                    );
            } else {
                $this->writeln('[Error] ' . $e->getMessage(), true);
            }
            if ($this->verbosity > 0) {
                $this->writeln('[Backtrace]:', true)
                    ->writeln($e->getTraceAsString(), true);
            }

            return $e->getCode() === 0 ? 255 : $e->getCode();
        }

        public function offsetExists(mixed $offset): bool
        {
            return array_key_exists($offset, $this->params);
        }

        public function offsetGet(mixed $offset): mixed
        {
            return $this->params[$offset] ?? null;
        }

        public function offsetSet(mixed $offset, mixed $value): void
        {
            throw new \BadMethodCallException('Cannot set params.');
        }

        public function offsetUnset(mixed $offset): void
        {
            throw new \BadMethodCallException('Cannot unset params.');
        }
    }

    class ExecResult
    {
        public bool $success {
            get => $this->code === 0;
        }

        public function __construct(public int $code = 0, public string $output = '', public string $errOutput = '')
        {
        }
    }

    class ArgvParser
    {
        private array $params = [];

        private array $tokens = [];

        private array $existsNames = [];

        private bool $parseOptions = false;

        public private(set) int $currentArgument = 0;

        /** @var array<Parameter> */
        public private(set) array $parameters = [];

        /** @var array<Parameter> */
        public array $arguments {
            get => array_filter($this->parameters, static fn($parameter) => $parameter->isArg);
        }

        /** @var array<Parameter> */
        public array $options {
            get => array_filter($this->parameters, static fn($parameter) => !$parameter->isArg);
        }

        public function addParameter(
            string|array $name,
            ParameterType $type,
            string $description = '',
            bool $required = false,
            mixed $default = null,
            bool $negatable = false,
        ): Parameter {
            if (is_string($name) && str_contains($name, '|')) {
                $name = explode('|', $name);
                foreach ($name as $n) {
                    if (!str_starts_with($n, '-')) {
                        throw new \InvalidArgumentException('Argument name cannot contains "|" sign.');
                    }
                }
            }
            $parameter = new Parameter($name, $type, $description, $required, $default, $negatable);
            foreach ((array) $parameter->name as $n) {
                if (in_array($n, $this->existsNames, true)) {
                    throw new \InvalidArgumentException('Duplicate parameter name "' . $n . '"');
                }
            }
            array_push($this->existsNames, ...((array) $parameter->name));
            ($this->parameters[$parameter->primaryName] = $parameter) && $parameter->selfValidate();

            return $parameter;
        }

        public function removeParameter(string $name): void
        {
            unset($this->parameters[$name]);
        }

        public function getArgument(string $name): ?Parameter
        {
            return array_find($this->arguments, static fn($n) => $n === $name);
        }

        public function getArgumentByIndex(int $index): ?Parameter
        {
            return array_values($this->arguments)[$index] ?? null;
        }

        public function getLastArgument(): ?Parameter
        {
            $args = $this->arguments;

            return $args[array_key_last($args)] ?? null;
        }

        public function getOption(string $name): ?Parameter
        {
            return array_find($this->options, static fn(Parameter $option) => $option->hasName($name));
        }

        public function mustGetOption(string $name): Parameter
        {
            if (!$option = $this->getOption($name)) {
                throw new InvalidParameterException(\sprintf('The "-%s" option does not exist.', $name));
            }

            return $option;
        }

        public function parse(array $argv, bool $validate = true): array
        {
            foreach ($this->parameters as $parameter) {
                $parameter->selfValidate();
            }
            array_shift($argv);
            $this->currentArgument = 0;
            $this->parseOptions = true;
            $this->params = [];
            $this->tokens = $argv;
            while (null !== $token = array_shift($this->tokens)) {
                $this->parseToken((string) $token);
            }

            if ($validate) {
                return $this->validateAndCastParams($this->params);
            }

            return $this->params;
        }

        public function validateAndCastParams(array $params): array
        {
            foreach ($this->parameters as $parameter) {
                if (!array_key_exists($parameter->primaryName, $params)) {
                    $parameter->assertInput(
                        !$parameter->isArg || !$parameter->required,
                        "Required argument \"{$parameter->primaryName}\" is missing."
                    );
                    $params[$parameter->primaryName] = $parameter->defaultValue ?? false;
                } else {
                    $parameter->validate($this->params[$parameter->primaryName]);
                    $params[$parameter->primaryName] = $parameter->castValue($params[$parameter->primaryName]);
                }
            }

            return $params;
        }

        protected function parseToken(string $token): void
        {
            if ($this->parseOptions && '' === $token) {
                $this->parseArgument($token);
            } elseif ($this->parseOptions && '--' === $token) {
                $this->parseOptions = false;
            } elseif ($this->parseOptions && str_starts_with($token, '--')) {
                $this->parseLongOption($token);
            } elseif ($this->parseOptions && '-' === $token[0] && '-' !== $token) {
                $this->parseShortOption($token);
            } else {
                $this->parseArgument($token);
            }
        }

        private function parseShortOption(string $token): void
        {
            $name = substr($token, 1);
            if (\strlen($name) > 1) {
                $option = $this->getOption($token);
                if ($option && $option->acceptValue) {
                    $this->setOptionValue($name[0], substr($name, 1)); // -n[value]
                } else {
                    $this->parseShortOptionSet($name);
                }
            } else {
                $this->setOptionValue($name, null);
            }
        }

        private function parseShortOptionSet(string $name): void
        {
            $len = \strlen($name);
            for ($i = 0; $i < $len; ++$i) {
                $option = $this->mustGetOption($name[$i]);
                if ($option->acceptValue) {
                    $this->setOptionValue($option->primaryName, $i === $len - 1 ? null : substr($name, $i + 1));
                    break;
                }
                $this->setOptionValue($option->primaryName, null);
            }
        }

        private function parseLongOption(string $token): void
        {
            $name = substr($token, 2);
            $pos = strpos($name, '=');
            if ($pos !== false) {
                $value = substr($name, $pos + 1);
                $value !== '' || array_unshift($this->params, $value);
                $this->setOptionValue(substr($name, 0, $pos), $value);
            } else {
                $this->setOptionValue($name, null);
            }
        }

        private function parseArgument(string $token): void
        {
            if ($arg = $this->getArgumentByIndex($this->currentArgument)) {
                $this->params[$arg->primaryName] = $arg->type === ParameterType::ARRAY ? [$token] : $token;
            } elseif (($last = $this->getLastArgument()) && $last->type === ParameterType::ARRAY) {
                $this->params[$last->primaryName][] = $token;
            } else {
                throw new InvalidParameterException("Unknown argument \"$token\".");
            }
            $this->currentArgument++;
        }

        public function setOptionValue(string $name, mixed $value = null): void
        {
            $option = $this->getOption($name);
            // If option not exists, make sure it is negatable
            if (!$option) {
                if (str_starts_with($name, 'no-')) {
                    $option = $this->getOption(substr($name, 3));
                    if ($option->type === ParameterType::BOOLEAN && $option->negatable) {
                        $this->params[$option->primaryName] = false;
                    }

                    return;
                }
                throw new InvalidParameterException(\sprintf('The "-%s" option does not exist.', $name));
            }
            $option->assertInput($value === null || $option->acceptValue, 'Option "%s" does not accept value.');
            // Try get option value from next token
            if (\in_array($value, ['', null], true) && $option->acceptValue && \count($this->tokens)) {
                $next = array_shift($this->tokens);
                if ((isset($next[0]) && '-' !== $next[0]) || \in_array($next, ['', null], true)) {
                    $value = $next;
                } else {
                    array_unshift($this->tokens, $next);
                }
            }
            if ($option->type === ParameterType::BOOLEAN) {
                $value = $value === null || $value;
            }
            if ($option->type === ParameterType::ARRAY) {
                $this->params[$option->primaryName][] = $value;
            } elseif ($option->type === ParameterType::LEVEL) {
                $this->params[$option->primaryName] ??= 0;
                $this->params[$option->primaryName]++;
            } else {
                $this->params[$option->primaryName] = $value;
            }
        }
    }

    /**
     * @method  self description(string $value)
     * @method  self required(bool $value)
     * @method  self negatable(bool $value)
     * @method  self default(mixed $value)
     */
    class Parameter
    {
        public bool $isArg {
            get => is_string($this->name);
        }

        public string $primaryName {
            get => is_string($this->name) ? $this->name : $this->name[0];
        }

        public string $synopsis {
            get {
                if (is_string($this->name)) {
                    return $this->name;
                }
                $shorts = [];
                $fulls = [];
                foreach ($this->name as $n) {
                    if (strlen($n) === 1) {
                        $shorts[] = '-' . $n;
                    } else {
                        $fulls[] = '--' . $n;
                    }
                }
                if ($this->negatable) {
                    $fulls[] = '--no-' . $this->primaryName;
                }

                return implode(', ', array_filter([implode('|', $shorts), implode('|', $fulls)]));
            }
        }

        public bool $acceptValue {
            get => $this->type !== ParameterType::BOOLEAN && $this->type !== ParameterType::LEVEL && !$this->negatable;
        }

        public mixed $defaultValue {
            get => match ($this->type) {
                ParameterType::ARRAY => $this->default ?? [],
                ParameterType::LEVEL => $this->default ?? 0,
                default => $this->default,
            };
        }

        public function __construct(
            public string|array $name,
            public ParameterType $type,
            public string $description = '',
            public bool $required = false,
            public mixed $default = null,
            public bool $negatable = false,
        ) {
            $this->name = is_string($this->name) && str_starts_with($this->name, '-') ? [$this->name] : $this->name;
            if (is_array($this->name)) {
                foreach ($this->name as $i => $n) {
                    $this->assertArg(str_starts_with($n, '--') || strlen($n) <= 2);
                    $this->name[$i] = ltrim($n, '-');
                }
            }
        }

        public function selfValidate(): void
        {
            $this->assertArg(
                $this->type !== ParameterType::ARRAY || is_array($this->defaultValue),
                "Default value of \"%s\" must be an array."
            );
            if ($this->isArg) {
                $this->assertArg(!$this->negatable, "Argument \"%s\" cannot be negatable.");
                $this->assertArg(
                    $this->type !== ParameterType::BOOLEAN && $this->type !== ParameterType::LEVEL,
                    "Argument \"%s\" cannot be type: {$this->type->name}."
                );
            } else {
                $this->assertArg(!$this->negatable || !$this->required, "Negatable option \"%s\" cannot be required.");
            }
            $this->assertArg(
                !$this->required || $this->default === null,
                "Default value of \"%s\" cannot be set when required is true."
            );
        }

        public function hasName(string $name): bool
        {
            $name = ltrim($name, '-');

            return is_string($this->name) ? $this->name === $name : array_any($this->name, fn($n) => $n === $name);
        }

        public function castValue(mixed $value): mixed
        {
            return match ($this->type) {
                ParameterType::INT, ParameterType::LEVEL => (int) $value,
                ParameterType::NUMERIC, ParameterType::FLOAT => (float) $value,
                ParameterType::BOOLEAN => (bool) $value,
                ParameterType::ARRAY => (array) $value,
                default => $value,
            };
        }

        public function validate(mixed $value): void
        {
            if ($value === null) {
                $this->assertInput(!$this->required, "Required value for \"%s\" is missing.");

                return;
            }
            $passed = match ($this->type) {
                ParameterType::INT => is_numeric($value) && ((string) (int) $value) === $value,
                ParameterType::FLOAT => is_numeric($value) && ((string) (float) $value) === $value,
                ParameterType::NUMERIC => is_numeric($value),
                ParameterType::BOOLEAN => is_bool($value) || $value === '1' || $value === '0',
                ParameterType::ARRAY => is_array($value),
                default => true,
            };
            $this->assertInput($passed, "Invalid value type for \"%s\". Expected %s.");
        }

        public function assertArg(mixed $value, ?string $message = ''): void
        {
            $value || throw new \InvalidArgumentException(sprintf($message, $this->primaryName, $this->type->name));
        }

        public function assertInput(mixed $value, ?string $message = ''): void
        {
            $value || throw new InvalidParameterException(sprintf($message, $this->primaryName, $this->type->name));
        }

        public function __call(string $name, array $args)
        {
            if (property_exists($this, $name)) {
                $this->{$name} = $args[0];
                $this->selfValidate();

                return $this;
            }
            throw new \BadMethodCallException("Method $name() does not exist.");
        }
    }

    class ParameterDescriptor
    {
        public static function describe(ArgvParser $parser, string $commandName, string $epilog = ''): string
        {
            $lines[] = sprintf("Usage:\n  %s %s", $commandName, static::synopsis($parser, true));
            if (count($parser->arguments)) {
                $lines[] = "\nArguments:";
                $maxColWidth = 0;
                foreach ($parser->arguments as $argument) {
                    $argumentLines[] = static::describeArgument($argument, $maxColWidth);
                }
                foreach ($argumentLines ?? [] as [$start, $end]) {
                    $lines[] = '  ' . $start . str_repeat(' ', $maxColWidth - strlen($start) + 4) . $end;
                }
            }
            if (count($parser->options)) {
                $lines[] = "\nOptions:";
                $maxColWidth = 0;
                foreach ($parser->options as $option) {
                    $optionLines[] = static::describeOption($option, $maxColWidth);
                }
                foreach ($optionLines ?? [] as [$start, $end]) {
                    $lines[] = '  ' . $start . str_repeat(' ', $maxColWidth - strlen($start) + 4) . $end;
                }
            }
            $epilog && ($lines[] = "\nHelp:\n$epilog");

            return implode("\n", $lines);
        }

        public static function describeArgument(Parameter $parameter, int &$maxWidth = 0): array
        {
            $default = !static::noDefault($parameter) ? ' [default: ' . static::format($parameter->default) . ']' : '';
            $maxWidth = max($maxWidth, strlen($parameter->synopsis));

            return [$parameter->synopsis, $parameter->description . $default];
        }

        public static function describeOption(Parameter $parameter, int &$maxWidth = 0): array
        {
            $default = ($parameter->acceptValue || $parameter->negatable) && !static::noDefault($parameter)
                ? ' [default: ' . static::format($parameter->default) . ']'
                : '';
            $value = '=' . strtoupper($parameter->primaryName);
            $value = $parameter->required ? $value : '[' . $value . ']';
            $synopsis = $parameter->synopsis . ($parameter->acceptValue ? $value : '');
            $maxWidth = max($maxWidth, strlen($synopsis));

            return [
                $synopsis,
                $parameter->description . $default . ($parameter->type === ParameterType::ARRAY ? ' (multiple values allowed)' : ''),
            ];
        }

        public static function noDefault(Parameter $parameter): bool
        {
            return $parameter->default === null || (is_array($parameter->default) && count($parameter->default) === 0);
        }

        public static function format(mixed $value): string
        {
            return str_replace('\\\\', '\\', json_encode($value, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE));
        }

        public static function synopsis(ArgvParser $parser, bool $simple = false): string
        {
            $elements = [];
            if ($simple) {
                $elements[] = '[options]';
            } else {
                foreach ($parser->options as $option) {
                    $value = strtoupper($option->primaryName);
                    $value = !$option->required ? '[' . $value . ']' : $value;
                    $element = str_replace(', ', '|', $option->synopsis) . ($option->acceptValue ? ' ' . $value : '');
                    $elements[] = '[' . $element . ']';
                }
            }
            if ($elements !== [] && $parser->arguments !== []) {
                $elements[] = '[--]';
            }
            $tail = '';
            foreach ($parser->arguments as $argument) {
                $element = ($argument->type === ParameterType::ARRAY ? '...' : '') . '<' . $argument->primaryName . '>';
                if (!$argument->required) {
                    $element = '[' . $element;
                    $tail .= ']';
                }
                $elements[] = $element;
            }

            return implode(' ', $elements) . $tail;
        }
    }

    enum ParameterType
    {
        case STRING;
        case INT;
        case NUMERIC;
        case FLOAT;
        case BOOLEAN;
        case LEVEL;
        case ARRAY;
    }

    class InvalidParameterException extends \RuntimeException
    {
    }
}
