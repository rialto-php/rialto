# API usage

## Options

The entry points of Rialto bridges accept [multiple options](https://github.com/extractr-io/rialto/blob/2a2dbc7b431fb11a95945ed57caa1b88f7f2c76b/src/Process.php#L40-L60), here are some descriptions with the default values:

```php
[
    // Node's executable path
    'executable_path' => 'node',

    // How much time (in seconds) the process can stay inactive before being killed
    'idle_timeout' => 60,

    // How much time (in seconds) an instruction can take to return a value
    'read_timeout' => 30,

    // How much time (in seconds) the process can take to shutdown properly before being killed
    'stop_timeout' => 3,

    // A logger instance for debugging (must implement \Psr\Log\LoggerInterface)
    'logger' => null,

    // Enables debugging mode:
    //   - adds the --inspect flag to Node's command
    //   - appends stack traces to Node exception messages
    'debug' => false,
]
```

You can define an option in your entry point using the third parameter of the parent constructor:

```php
class MyEntryPoint extends AbstractEntryPoint
{
    public function __construct()
    {
        // ...

        $myOptions = [
            'idle_timeout' => 300, // 5 minutes
        ];

        parent::__construct($connectionDelegate, $processDelegate, $myOptions);
    }
}
```

### Accepting user options

If you want your users to define some of Rialto's options, you can use the fourth parameter:

```php
class MyEntryPoint extends AbstractEntryPoint
{
    public function __construct(array $userOptions = [])
    {
        // ...

        parent::__construct($connectionDelegate, $processDelegate, $myOptions, $userOptions);
    }
}
```

User options will override your own defaults. To prevent a user to define some specific options, use the `$forbiddenOptions` property:

```php
class MyEntryPoint extends AbstractEntryPoint
{
    protected $forbiddenOptions = ['idle_timeout', 'stop_timeout'];

    public function __construct(array $userOptions = [])
    {
        // ...

        parent::__construct($connectionDelegate, $processDelegate, $myOptions, $userOptions);
    }
}
```

By default, the user is forbidden to define the `stop_timeout` option.

**Note:** You should authorize your users to define, at least, the `logger` and `debug` options.

## Node errors

A Node error or an unhandled rejection will throw a

If an error (or a unhandled rejection) occurs in the context of Node, a `Node\FatalException` will be thrown and the process closed, you will have to create a new instance of your entry point.

To avoid that, you can ask Node to catch these errors by prepending your instruction with `->tryCatch`:

```php
use ExtractrIo\Rialto\Exceptions\Node;

try {
    $someResource->tryCatch->inexistantMethod();
} catch (Node\Exception $exception) {
    // Handle the exception...
}
```

Instead, a `Node\Exception` will be thrown, the Node process will stay alive and usable.

## JavaScript functions

With Rialto you can create JavaScript functions and pass them to the Node process, this can be useful to map some values or any other actions based on callbacks (as long as it is run synchronously). Here's some examples:

- A function returning a value:

```php
use ExtractrIo\Rialto\Data\JsFunction;

$jsFunction = JsFunction::create("
    return process.uptime();
");

$someResource->someMethodWithCallback($jsFunction);
```

- A function with arguments:

```php
use ExtractrIo\Rialto\Data\JsFunction;

$jsFunction = JsFunction::create(['str'], "
    return 'This is my string: ' + str;
");

$someResource->someMethodWithCallback($jsFunction);
```

- A function with arguments and some scoped values:

```php
use ExtractrIo\Rialto\Data\JsFunction;

$functionScope = ['stringtoPrepend' => 'This is another string: ']

$jsFunction = JsFunction::create(['str'], "
    return stringToPrepend + str;
", $functionScope);

$someResource->someMethodWithCallback($jsFunction);
```

## Destruction

If you're worried about the destruction of the Node process, here's two things you need to know:

- Once the entry point and all the resources (like the `BasicResource` class) are unset, the Node process is automatically terminated.
- If, for any reason, the Node process doesn't terminate, it will kill itself once the `idle_timeout` is exceeded.
