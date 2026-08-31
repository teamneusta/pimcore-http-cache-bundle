# PER Coding Style 3.0 — Complete Reference

Source: https://www.php-fig.org/per/coding-style/

---

## 1. Overview

PER Coding Style 3.0 extends PSR-12 and requires PSR-1. Uses RFC 2119 terminology (MUST, SHOULD, MAY).

---

## 2. General

### 2.1 Files
- MUST use Unix LF line endings
- MUST end with a single blank line (LF)
- MUST omit closing `?>` tag in PHP-only files

### 2.2 Lines
- No hard limit on line length
- Soft limit: 120 characters (MUST NOT exceed)
- Lines over 80 characters SHOULD be split
- MUST NOT have trailing whitespace
- One statement per line maximum
- Blank lines MAY be added for readability

### 2.3 Indentation
- MUST use 4 spaces per indent level
- MUST NOT use tabs

### 2.4 Keywords and Types
- All PHP keywords and types MUST be lowercase
- Short forms MUST be used: `bool` not `boolean`, `int` not `integer`, `float` not `double`
- Compound types: no spaces around `|` or `&`
- Multi-line compound types: operator at line start
- `null` MUST be last in union types
- Single type + null: use `?T` notation

### 2.5 Trailing Commas
- Single-line argument/parameter/array lists: MUST NOT have trailing comma
- Multi-line argument/parameter/array lists: MUST have trailing comma after last item

### 2.6 Naming
- Abbreviations treated as regular words: `HttpClient` not `HTTPClient`, `XmlParser` not `XMLParser`
- Exception: widely established abbreviations (HTTP, URL, ID etc.) are acceptable

---

## 3. Declare, Namespace, Imports

File structure — each separated by a single blank line:
1. `<?php` on its own line (lowercase)
2. File-level docblock
3. Declare statements
4. Namespace declaration
5. Class `use` imports
6. Function `use` imports
7. Constant `use` imports
8. Code

Rules:
- `declare(strict_types=1)` — no spaces inside parentheses
- Import statements MUST be fully qualified (no leading `\`)
- Compound namespaces: maximum depth of 2 sub-namespace levels (`use Foo\Bar\{Baz, Qux}`)
- Block declare: `declare(ticks=1) { ... }`

---

## 4. Classes, Properties, Methods

### 4.1 Class Declaration
- `extends` and `implements` on same line as class name
- Opening brace: own line
- Closing brace: own line after body (no blank line before it)
- Instantiation: parentheses always present — `new Foo()` not `new Foo`
- Empty class body: `{}` on same line when no other declarations

### 4.2 Extends and Implements
Multi-line implements list:
```php
class Foo extends Bar implements
    BazInterface,
    QuxInterface,
{
}
```

### 4.3 Traits
- `use` on first line after class opening brace
- One trait per `use` statement
- Blank line after trait block if class body follows

Conflict resolution:
```php
class Foo
{
    use A;
    use B {
        B::hello insteadof A;
        A::hello as helloA;
    }
}
```

### 4.4 Properties and Constants
- MUST declare visibility (`public`, `protected`, `private`)
- MUST NOT use `var`
- One property/constant per statement
- MUST NOT use underscore prefix to indicate protected/private
- Space between type and property name

### 4.5 Methods and Functions
- MUST declare visibility
- No space between name and `(`
- Opening brace: own line
- Closing brace: own line
- No space inside `()`
- Empty constructor body with promoted properties: `{}` on same line

### 4.6 Method and Function Parameters
- No space before `,`; one space after `,`
- Default values at end of list
- Multi-line: first param on next line, one per line, trailing comma required
- Return type: one space after `:`, colon on same line as closing `)`
- Nullable: no space between `?` and type
- Reference: no space after `&`
- Variadic: no space after `...`

```php
// single-line
function foo(int $a, string $b = 'default'): void {}

// multi-line
function foo(
    int $a,
    string $b = 'default',
): void {
}
```

### 4.7 Modifier Keyword Order

Order (all on one line, single space between each):

1. `abstract` or `final`  ← inheritance
2. `public`, `protected`, `private`  ← visibility
3. `public(set)`, `protected(set)`, `private(set)`  ← set-visibility (PHP 8.4+)
4. `static`  ← scope
5. `readonly`  ← mutation
6. Type declaration
7. Name

Examples:
```php
abstract protected static readonly string $value;
final public static function foo(): void {}
public private(set) readonly int $count = 0;
```

Note: `public` MAY be omitted when set-visibility is specified on a public-read property.

### 4.8 Method and Function Calls
- No space between name and `(`
- No space inside `()`
- Named arguments: no space before `:`, one space after `:`
- Method chaining: each call on its own line, indented once

```php
$result = $object
    ->methodOne($a)
    ->methodTwo($b)
    ->methodThree();

// named arguments
foo(name: 'value', other: 42);
```

`exit()` and `die()` MUST always be called with parentheses.

### 4.9 First-Class Callable Syntax
No surrounding whitespace around `...`:
```php
$fn = strlen(...);
array_map(strlen(...), $strings);
```

### 4.10 Property Hooks (PHP 8.4+)

Long form:
```php
public string $name {
    get {
        return $this->name;
    }
    set(string $value) {
        $this->name = $value;
    }
}
```

Short form:
```php
public string $name {
    get => $this->rawName;
    set => $this->rawName = $value;
}
```

Inline (single hook, short form only, no wrapping):
```php
public string $uppercaseName { get => strtoupper($this->name); }
```

Constructor-promoted properties: only single-line, single-hook inline allowed.

### 4.11 Abstract/Interface Properties

No body (semicolons):
```php
abstract public string $name { get; set; }
```
- `{}` with single space inside: `{ get; set; }`
- `get` before `set`
- No space before `;`

---

## 5. Control Structures

General:
- One space after keyword
- No space inside `()`
- One space between closing `)` and `{`
- Body indented once
- Closing `}` on own line
- Bodies MUST use braces (even single-line)

### 5.1 if/elseif/else
- MUST use `elseif`, not `else if`
- Multi-line conditions: boolean operators at line start, closing `)` and `{` together on own line

```php
if (
    $condition1
    && $condition2
    || $condition3
) {
    // ...
} elseif ($other) {
    // ...
} else {
    // ...
}
```

### 5.2 switch/case
- `case` indented once from `switch`
- Break at same indentation as case body
- Intentional fall-through: `// no break` comment required

```php
switch ($value) {
    case 'a':
        doA();
        break;
    case 'b':
    case 'c':
        doBC();
        // no break
    default:
        doDefault();
}
```

### 5.3 match
- Same brace/spacing rules as other control structures
- Trailing comma required in multi-line match

```php
$result = match ($value) {
    'a', 'b' => doAB(),
    'c' => doC(),
    default => doDefault(),
};
```

### 5.4 while/do-while

```php
while ($condition) {
    // ...
}

do {
    // ...
} while ($condition);
```

Multi-line conditions: same rules as `if`.

### 5.5 for

```php
for ($i = 0; $i < 10; $i++) {
    // ...
}
```

### 5.6 foreach

```php
foreach ($items as $key => $value) {
    // ...
}
```

### 5.7 try/catch/finally
- `catch` and `finally` on same line as preceding `}`
- Union catch types allowed

```php
try {
    // ...
} catch (FooException|BarException $e) {
    // ...
} finally {
    // ...
}
```

---

## 6. Operators

### 6.1 Unary
- Increment/decrement: no space — `$i++`, `--$i`
- Type cast: no space inside cast, one space before variable — `(int) $value`

### 6.2 Binary
All binary operators (arithmetic, comparison, assignment, bitwise, logical, string, type) MUST have one space before and after:
```php
$a = $b + $c;
$x = $y === $z ? 'a' : 'b';
$result = $a instanceof Foo;
```

### 6.3 Ternary
- Spaces around both `?` and `:`
- Elvis (`?:`): same as binary
- Multi-line: operator at line start, minimum 3 lines (never split into 2)

```php
// single-line
$result = $condition ? 'yes' : 'no';

// multi-line (3 lines minimum)
$result = $longCondition
    ? 'yes value'
    : 'no value';
```

---

## 7. Closures

```php
$closure = function (int $a, int $b) use ($c, &$d): int {
    return $a + $b + $c + $d;
};
```

Rules:
- Space after `function`
- Space before and after `use`
- Opening brace same line as closing `)`
- No space inside `()`
- Multi-line: trailing comma required

### 7.1 Arrow Functions (Short Closures)
- `fn` — no space before `(`
- `=>` surrounded by single spaces
- Expression MAY wrap; if so, `=>` indented once on next line

```php
$fn = fn (int $x) => $x + $y;

// wrapped
$fn = fn (int $x)
    => $x + $y;
```

---

## 8. Anonymous Classes

```php
// no constructor args: omit ()
$obj = new class {
    // ...
};

// with args
$obj = new class($arg) {
    // ...
};

// implements: opening brace on next line if list wraps
$obj = new class implements
    FooInterface,
    BarInterface,
{
};
```

---

## 9. Enumerations

- Follow class formatting rules
- Non-public methods use `private` (not `protected` — enums cannot be extended)
- Backed enum: no space before `:`, one space before type

```php
enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

enum Direction
{
    case North;
    case South;
    case East;
    case West;
}
```

- Case names: PascalCase (MUST)
- Constants: PascalCase recommended (UPPER_CASE also acceptable)

---

## 10. Heredoc and Nowdoc

- Prefer nowdoc (`<<<'EOT'`) where string interpolation not needed
- Declaration MUST be on same line as context usage (assignment/argument)
- Content lines indented once past scope indentation
- Closing identifier at scope level

```php
$text = <<<'EOT'
    Content here indented once.
    More content.
    EOT;

foo(<<<'EOT'
    Argument content.
    EOT);
```

MUST NOT place the heredoc/nowdoc on a separate line from its assignment.

---

## 11. Arrays

- MUST use short syntax `[]` (not `array()`)
- Single-line: no trailing comma
- Multi-line: trailing comma required after last element

```php
// single-line
$a = ['foo', 'bar', 'baz'];

// multi-line
$a = [
    'foo',
    'bar',
    'baz',  // ← required
];

// associative multi-line
$a = [
    'key1' => 'value1',
    'key2' => 'value2',
];
```

---

## 12. Attributes

### 12.1 Syntax
- No space after `#[`
- No space before `]`
- Omit `()` when no arguments

```php
#[Attribute]          // no ()
#[Route('/path')]     // with args
#[Assert\NotNull]
```

### 12.2 Placement

**Classes, methods, functions, properties, constants:** own line, before the structure. Docblock first, then attributes, then structure — no blank lines between.

```php
/** Docblock */
#[Route('/foo')]
#[Authorize]
public function foo(): void {}
```

**Parameters (single-line parameter list):** inline before parameter:
```php
function foo(#[SomeAttr] string $bar): void {}
```

**Parameters (multi-line parameter list):** own line before the parameter, at same indentation:
```php
function foo(
    #[SomeAttr]
    string $bar,
    #[OtherAttr]
    int $baz,
): void {}
```

**Multiple attributes:**
- Same line: `#[Foo, Bar]` — space after comma, no space before
- Multi-line context: separate `#[]` blocks on separate lines

### 12.3 Multi-line Attribute Arguments
When attribute arguments wrap, the attribute must be alone in its block and follow function argument formatting:
```php
#[
    Route(
        path: '/very/long/path',
        methods: ['GET', 'POST'],
    )
]
public function action(): void {}
```
