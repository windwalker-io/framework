<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Compiler;

use Windwalker\Edge\Compiler\Concern\CompileComponentTrait;
use Windwalker\Edge\Compiler\Concern\CompileConditional;

/**
 * The EdgeCompiler class.
 *
 * This is a modified version of Laravel Blade engine.
 *
 * @see    https://github.com/illuminate/view/blob/master/Compilers/BladeCompiler.php
 *
 * @since  3.0
 */
class EdgeCompiler implements EdgeCompilerInterface
{
    use CompileComponentTrait;
    use CompileConditional;

    /**
     * All custom "directive" handlers.
     *
     * @var  \callable[]
     */
    protected $directives = [];

    /**
     * Property parsers.
     *
     * @var  \callable[]
     */
    protected $parsers = [];

    /**
     * The file currently being compiled.
     *
     * @var string
     */
    protected $path;

    /**
     * Array of opening and closing tags for raw echos.
     *
     * @var array
     */
    protected $rawTags = ['{!!', '!!}'];

    /**
     * Array of opening and closing tags for regular echos.
     *
     * @var array
     */
    protected $contentTags = ['{{', '}}'];

    /**
     * Array of opening and closing tags for escaped echos.
     *
     * @var array
     */
    protected $escapedTags = ['{{{', '}}}'];

    /**
     * The "regular" / legacy echo string format.
     *
     * @var string
     */
    protected $echoFormat = '$this->escape(%s)';

    /**
     * Array of footer lines to be added to template.
     *
     * @var array
     */
    protected $footer = [];

    /**
     * Placeholder to temporary mark the position of verbatim blocks.
     *
     * @var string
     */
    protected $verbatimPlaceholder = '@__verbatim__@';

    /**
     * Array to temporary store the verbatim blocks found in the template.
     *
     * @var array
     */
    protected $verbatimBlocks = [];

    /**
     * Counter to keep track of nested forelse statements.
     *
     * @var int
     */
    protected $forelseCounter = 0;

    /**
     * All of the available compiler functions.
     *
     * @var array
     */
    protected $compilers = [
        'Parsers',
        'Statements',
        'Comments',
        'Echos',
    ];

    /**
     * compile
     *
     * @param string $value
     *
     * @return  string
     */
    public function compile($value)
    {
        $result = '';

        if (strpos($value, '@verbatim') !== false) {
            $value = $this->storeVerbatimBlocks($value);
        }

        $this->footer = [];

        // Here we will loop through all of the tokens returned by the Zend lexer and
        // parse each one into the corresponding valid PHP. We will then have this
        // template as the correctly rendered PHP that can be rendered natively.
        foreach (token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        if (!empty($this->verbatimBlocks)) {
            $result = $this->restoreVerbatimBlocks($result);
        }

        // If there are any footer lines that need to get added to a template we will
        // add them here at the end of the template. This gets used mainly for the
        // template inheritance via the extends keyword that should be appended.
        if (count($this->footer) > 0) {
            $result = ltrim($result, PHP_EOL)
                . PHP_EOL . implode(PHP_EOL, array_reverse($this->footer));
        }

        return $result;
    }

    /**
     * Store the verbatim blocks and replace them with a temporary placeholder.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function storeVerbatimBlocks($value)
    {
        return preg_replace_callback(
            '/(?<!@)@verbatim(.*?)@endverbatim/s',
            function ($matches) {
                $this->verbatimBlocks[] = $matches[1];

                return $this->verbatimPlaceholder;
            },
            $value
        );
    }

    /**
     * Replace the raw placeholders with the original code stored in the raw blocks.
     *
     * @param  string $result
     *
     * @return string
     */
    protected function restoreVerbatimBlocks($result)
    {
        $result = preg_replace_callback(
            '/' . preg_quote($this->verbatimPlaceholder) . '/',
            function () {
                return array_shift($this->verbatimBlocks);
            },
            $result
        );

        $this->verbatimBlocks = [];

        return $result;
    }

    /**
     * Parse the tokens from the template.
     *
     * @param  array $token
     *
     * @return string
     */
    protected function parseToken($token)
    {
        list($id, $content) = $token;

        if ($id == T_INLINE_HTML) {
            foreach ($this->compilers as $type) {
                $content = $this->{"compile{$type}"}($content);
            }
        }

        return $content;
    }

    /**
     * Compile Blade comments into valid PHP.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function compileComments($value)
    {
        $pattern = sprintf('/%s--(.*?)--%s/s', $this->contentTags[0], $this->contentTags[1]);

        return preg_replace($pattern, '<?php /*$1*/ ?>', $value);
    }

    /**
     * Compile Blade echos into valid PHP.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function compileEchos($value)
    {
        foreach ($this->getEchoMethods() as $method => $length) {
            $value = $this->$method($value);
        }

        return $value;
    }

    /**
     * Get the echo methods in the proper order for compilation.
     *
     * @return array
     */
    protected function getEchoMethods()
    {
        $methods = [
            'compileRawEchos' => strlen(stripcslashes($this->rawTags[0])),
            'compileEscapedEchos' => strlen(stripcslashes($this->escapedTags[0])),
            'compileRegularEchos' => strlen(stripcslashes($this->contentTags[0])),
        ];

        uksort(
            $methods,
            function ($method1, $method2) use ($methods) {
                // Ensure the longest tags are processed first
                if ($methods[$method1] > $methods[$method2]) {
                    return -1;
                }

                if ($methods[$method1] < $methods[$method2]) {
                    return 1;
                }

                // Otherwise give preference to raw tags (assuming they've overridden)
                if ($method1 === 'compileRawEchos') {
                    return -1;
                }

                if ($method2 === 'compileRawEchos') {
                    return 1;
                }

                if ($method1 === 'compileEscapedEchos') {
                    return -1;
                }

                if ($method2 === 'compileEscapedEchos') {
                    return 1;
                }

                return null;
            }
        );

        return $methods;
    }

    /**
     * compileParsers
     *
     * @param   string $value
     *
     * @return  string
     */
    protected function compileParsers($value)
    {
        foreach ($this->parsers as $parser) {
            $value = call_user_func($parser, $value, $this);
        }

        return $value;
    }

    /**
     * Compile Blade statements that start with "@".
     *
     * @param  string $value
     *
     * @return mixed
     */
    protected function compileStatements($value)
    {
        return preg_replace_callback(
            '/\B@(@?\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
            [$this, 'compileStatement'],
            $value
        );
    }

    /**
     * compileStatement
     *
     * @param array $match
     *
     * @return  string
     */
    protected function compileStatement(array $match)
    {
        if (strpos($match[1], '@') !== false) {
            $match[0] = isset($match[3]) ? $match[1] . $match[3] : $match[1];
        } elseif (isset($this->directives[$match[1]])) {
            $match[0] = call_user_func($this->directives[$match[1]], isset($match[3]) ? $match[3] : null);
        } elseif (method_exists($this, $method = 'compile' . ucfirst($match[1]))) {
            $match[0] = $this->$method(isset($match[3]) ? $match[3] : null);
        }

        return isset($match[3]) ? $match[0] : $match[0] . $match[2];
    }

    /**
     * Compile the "raw" echo statements.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function compileRawEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->rawTags[0], $this->rawTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

            return $matches[1] ? substr(
                $matches[0],
                1
            ) : '<?php echo ' . $this->compileEchoDefaults($matches[2]) . '; ?>' . $whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the "regular" echo statements.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function compileRegularEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

            $wrapped = sprintf($this->echoFormat, $this->compileEchoDefaults($matches[2]));

            return $matches[1] ? substr($matches[0], 1) : '<?php echo ' . $wrapped . '; ?>' . $whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the escaped echo statements.
     *
     * @param  string $value
     *
     * @return string
     */
    protected function compileEscapedEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

            return $matches[1] ? $matches[0] : '<?php echo e(' . $this->compileEchoDefaults(
                $matches[2]
            ) . '); ?>' . $whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the default values for the echo statement.
     *
     * @param  string $value
     *
     * @return string
     */
    public function compileEchoDefaults($value)
    {
        return preg_replace('/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/s', 'isset($1) ? $1 : $2', $value);
    }

    /**
     * Strip the parentheses from the given expression.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function stripParentheses($expression)
    {
        if (strpos($expression, '(') === 0) {
            $expression = substr($expression, 1, -1);
        }

        return $expression;
    }

    /**
     * Compile the each statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileEach($expression)
    {
        return "<?php echo \$this->renderEach{$expression}; ?>";
    }

    /**
     * Compile the yield statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileYield($expression)
    {
        return "<?php echo \$this->yieldContent{$expression}; ?>";
    }

    /**
     * Compile the show statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileShow($expression)
    {
        return '<?php echo $this->yieldSection(); ?>';
    }

    /**
     * Compile the section statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileSection($expression)
    {
        return "<?php \$this->startSection{$expression}; ?>";
    }

    /**
     * Compile the append statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileAppend($expression)
    {
        return '<?php $this->appendSection(); ?>';
    }

    /**
     * Compile the end-section statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileEndsection($expression)
    {
        return '<?php $this->stopSection(); ?>';
    }

    /**
     * Compile the stop statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileStop($expression)
    {
        return '<?php $this->stopSection(); ?>';
    }

    /**
     * Compile the overwrite statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileOverwrite($expression)
    {
        return '<?php $this->stopSection(true); ?>';
    }

    /**
     * Compile the for statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }

    /**
     * Compile the foreach statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileForeach($expression)
    {
        return "<?php foreach{$expression}: ?>";
    }

    /**
     * Compile the break statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileBreak($expression)
    {
        return $expression ? "<?php if{$expression} break; ?>" : '<?php break; ?>';
    }

    /**
     * Compile the continue statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileContinue($expression)
    {
        return $expression ? "<?php if{$expression} continue; ?>" : '<?php continue; ?>';
    }

    /**
     * Compile the forelse statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileForelse($expression)
    {
        $empty = '$__empty_' . ++$this->forelseCounter;

        return "<?php {$empty} = true; foreach{$expression}: {$empty} = false; ?>";
    }

    /**
     * Compile the has section statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileHasSection($expression)
    {
        return "<?php if (! empty(trim(\$this->yieldContent{$expression}))): ?>";
    }

    /**
     * Compile the while statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileWhile($expression)
    {
        return "<?php while{$expression}: ?>";
    }

    /**
     * Compile the end-while statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileEndwhile($expression)
    {
        return '<?php endwhile; ?>';
    }

    /**
     * Compile the end-for statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileEndfor($expression)
    {
        return '<?php endfor; ?>';
    }

    /**
     * Compile the end-for-each statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileEndforeach($expression)
    {
        return '<?php endforeach; ?>';
    }

    /**
     * Compile the end-for-else statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileEndforelse($expression)
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the raw PHP statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compilePhp($expression)
    {
        return $expression ? "<?php {$expression}; ?>" : '<?php ';
    }

    /**
     * Compile end-php statement into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileEndphp($expression)
    {
        return ' ?>';
    }

    /**
     * Compile the unset statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileUnset($expression)
    {
        return "<?php unset{$expression}; ?>";
    }

    /**
     * Compile the extends statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileExtends($expression)
    {
        $expression = $this->stripParentheses($expression);

        // @codingStandardsIgnoreStart
        $data = "<?php echo \$this->render($expression, \$this->arrayExcept(get_defined_vars(), array('__data', '__path'))); ?>";
        // @codingStandardsIgnoreEnd

        $this->footer[] = $data;

        return '';
    }

    /**
     * Compile the include statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileInclude($expression)
    {
        $expression = $this->stripParentheses($expression);

        // @codingStandardsIgnoreStart
        return "<?php echo \$this->render($expression, \$this->arrayExcept(get_defined_vars(), array('__data', '__path'))); ?>";
        // @codingStandardsIgnoreEnd
    }

    /**
     * Compile the include statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileIncludeIf($expression)
    {
        $expression = $this->stripParentheses($expression);

        // @codingStandardsIgnoreStart
        return "<?php if (\$this->exists($expression)) echo \$this->render($expression, \$this->arrayExcept(get_defined_vars(), array('__data', '__path'))); ?>";
        // @codingStandardsIgnoreEnd
    }

    /**
     * Compile the stack statements into the content.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileStack($expression)
    {
        return "<?php echo \$this->yieldPushContent{$expression}; ?>";
    }

    /**
     * Compile the push statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compilePush($expression)
    {
        return "<?php \$this->startPush{$expression}; ?>";
    }

    /**
     * Compile the endpush statements into valid PHP.
     *
     * @param  string $expression
     *
     * @return string
     */
    protected function compileEndpush($expression)
    {
        return '<?php $this->stopPush(); ?>';
    }

    /**
     * Register a handler for custom directives.
     *
     * @param  string   $name
     * @param  callable $handler
     *
     * @return static
     */
    public function directive($name, $handler)
    {
        $this->directives[$name] = $handler;

        return $this;
    }

    /**
     * Get the list of custom directives.
     *
     * @return array
     */
    public function getDirectives()
    {
        return $this->directives;
    }

    /**
     * Method to set property directives
     *
     * @param   callable[] $directives
     *
     * @return  static  Return self to support chaining.
     */
    public function setDirectives(array $directives)
    {
        $this->directives = $directives;

        return $this;
    }

    /**
     * parser
     *
     * @param   callable $handler
     *
     * @return  static
     */
    public function parser($handler)
    {
        $this->parsers[] = $handler;

        return $this;
    }

    /**
     * Method to set property parsers
     *
     * @param   \callable[] $parsers
     *
     * @return  static  Return self to support chaining.
     */
    public function setParsers($parsers)
    {
        $this->parsers = $parsers;

        return $this;
    }

    /**
     * getParsers
     *
     * @return  \callable[]
     */
    public function getParsers()
    {
        return $this->parsers;
    }

    /**
     * Gets the raw tags used by the compiler.
     *
     * @return array
     */
    public function getRawTags()
    {
        return $this->rawTags;
    }

    /**
     * Sets the raw tags used for the compiler.
     *
     * @param  string $openTag
     * @param  string $closeTag
     *
     * @return void
     */
    public function setRawTags($openTag, $closeTag)
    {
        $this->rawTags = [preg_quote($openTag), preg_quote($closeTag)];
    }

    /**
     * Sets the content tags used for the compiler.
     *
     * @param  string $openTag
     * @param  string $closeTag
     * @param  bool   $escaped
     *
     * @return void
     */
    public function setContentTags($openTag, $closeTag, $escaped = false)
    {
        $property = ($escaped === true) ? 'escapedTags' : 'contentTags';

        $this->{$property} = [preg_quote($openTag), preg_quote($closeTag)];
    }

    /**
     * Sets the escaped content tags used for the compiler.
     *
     * @param  string $openTag
     * @param  string $closeTag
     *
     * @return void
     */
    public function setEscapedContentTags($openTag, $closeTag)
    {
        $this->setContentTags($openTag, $closeTag, true);
    }

    /**
     * Gets the content tags used for the compiler.
     *
     * @return string
     */
    public function getContentTags()
    {
        return $this->getTags();
    }

    /**
     * Gets the escaped content tags used for the compiler.
     *
     * @return string
     */
    public function getEscapedContentTags()
    {
        return $this->getTags(true);
    }

    /**
     * Gets the tags used for the compiler.
     *
     * @param  bool $escaped
     *
     * @return array
     */
    protected function getTags($escaped = false)
    {
        $tags = $escaped ? $this->escapedTags : $this->contentTags;

        return array_map('stripcslashes', $tags);
    }

    /**
     * Set the echo format to be used by the compiler.
     *
     * @param  string $format
     *
     * @return void
     */
    public function setEchoFormat($format)
    {
        $this->echoFormat = $format;
    }
}
