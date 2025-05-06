**Title: How to Handle HTTP Responses with the Stream+JSON Content Type Using PHP Generators**

In this article, I'll discuss: 
- What Content-Type: stream+json is
- How to handle stream+json responses with PHP

The sample code is fully open source and available here:  
👉 [GitHub Repository](https://github.com/karintomania/php-stream-example)

If you have Docker Compose installed on your machine, you can run the code without installing anything extra.

## What is Content-Type: stream+json?

Before we dive into coding, let's quickly explore why `stream+json` exists. Often, you need to send large arrays of JSON via APIs. You could send this large array from the server all at once, but when PHP receives it, PHP loads everything into memory.

While this method works, it can cause issues such as:
- High memory consumption
- Risk of timeout ⏳

How to solve these problems?

**💡Solution: Content-Type: stream+json**  
To tackle these issues, the `stream+json` content type is used. For this content type, the server sends a "stream" of data, which consists of lines of JSON in this case.  
See the GIF below, which shows how a server can send JSONs on a stream with Content-Type: stream+json.  
[GIF of stream]

As you can see, the server is sending JSON line-by-line.  
This format is called JSON Lines (or JSONL for short), which is a newline-separated JSON. JSONL allows data to be processed line-by-line, making it easier to handle large JSON objects efficiently. 
Because each line is a valid JSON, PHP can start processing the object as it receives it, without waiting for the whole response.

In summary, using Content-Type: stream+json allows the program to:
- Start processing immediately without waiting for the entire response ⏩
- Use less memory by processing JSON one by one, instead of dealing with a massive array of JSON

## Receiving the Stream+JSON Response as a Stream with Guzzle

Now, let's talk about how to handle a `stream+json` response.

Before diving into the implementation, it's important to clarify a common point of confusion:  
PHP has built-in functions called Streams. This stream refers to a way to manage resources like files or HTTP in a unified manner. However, the "stream" discussed in this article refers to HTTP responses using the `stream+json` content type.  
More about PHP's built-in streams: [PHP Streams Documentation](https://www.php.net/manual/en/book.stream.php)

Fortunately, the Guzzle library has a handy "stream" option to get the response as a stream. All you need to do is set this `'stream'` option to `true`.

```php
use GuzzleHttp\Client;

$client = new Client();
// Enable the stream option
$response = $client->get('http://localhost', ['stream' => true]);

// getBody() retrieves the response as a stream
$stream = $response->getBody();
```

If you run the code above, you will have the response as a stream, more specifically as a `StreamInterface`. `StreamInterface` is defined by PSR-7 as an interface for data streams. If you want to know more about this interface, see the link below for details:  
[StreamInterface Documentation](https://github.com/php-fig/http-message/blob/master/src/StreamInterface.php)

One of the important methods of "StreamInterface" is the `read(int $length)` method, which allows you to retrieve content of the specified length. We will see how to handle the JSON stream line by line using this method in the next section.

Note that if you don't set the stream option, the `getBody()` function will wait for the whole response and return it as a string.

## Handling the Stream with PHP Generators

Since the stream sends JSON line-by-line, using a Generator is an efficient approach, as it allows the consumer to process each object one by one.

Here's a class that demonstrates how to process the stream and return JSON-decoded objects using a Generator:

```php
<?php

namespace StreamTest;

use Psr\Http\Message\StreamInterface;
use \Generator;

class StreamScanner {
    private const CHUNK_SIZE = 8192;

    /**
    * @return Generator<object>
    *
    * Read each line from the stream and return it as an object.
    * Each line will be a JSON object.
    */
    public function __invoke(StreamInterface $stream): Generator
    {
        foreach ($this->nextLine($stream) as $line) {
            yield json_decode($line);
        }
    }

    /**
     * @return Generator<string>
     *
     * Read each line from the stream.
     */
    private function nextLine(StreamInterface $stream): Generator
    {
        $buffer = '';

        // Read chunks until EOF
        while (!$stream->eof()) {
            $buffer .= $stream->read(self::CHUNK_SIZE);

            // If buffer has a newline, yield a JSON line
            while (($pos = strpos($buffer, "\n")) !== false) {
                // Get the complete JSON
                $line = substr($buffer, 0, $pos + 1);
                $buffer = substr($buffer, $pos + 1);
                yield $line;
            }
        }

        // Process any remaining buffer
        if ($buffer !== '') {
            yield $buffer;
        }
    }
}
```

Let's break down this code a bit.

#### `__invoke` Method

This method returns a generator of JSON-decoded objects. It retrieves each line from the `nextLine` method and applies `json_decode()` to convert the JSON string into an object.

#### `nextLine` Method

This method processes the stream in chunks and identifies complete lines by searching for newline characters. It continues reading until the end of the file is reached, ensuring that all lines are processed.

### Note on `GuzzleHttp\Psr7\Utils::readLine` Method
Guzzle offers a function like my StreamScanner.  
https://github.com/guzzle/psr7/blob/2.7/src/Utils.php#L234

This function, as the name suggests, reads one line from the stream. The difference from the StreamScanner in this article is:
- It returns a string, instead of a Generator.
- The implementation reads the stream one character at a time.

Because it reads the stream one character at a time instead of reading chunks, this function can be slow.  
I processed 100K JSONL with both implementations and the result is like below:  
StreamScanner: 0.049s  
Utils::readLine: 0.416s

`Utils::readLine` is very useful when the file is not large or the speed is not your concern (like batch jobs, for example). Also, if you can't use Generators, you should use `Utils::readLine`.

## Testing the Implementation

Let's talk about testing. Mocking the stream for testing purposes is actually not too hard.

For testing purposes, Guzzle offers a convenient function called `Utils::streamFor()`, which converts a resource into a `StreamInterface`. This is particularly useful for testing your stream handling logic.

For example, if you have a JSON file that resembles the actual payload of the response, you can write something like this:

```php
$f = fopen(__DIR__ . '/data.json', 'r');
$stream = Utils::streamFor($f);
```

Now you have the stream. If you are using Guzzle to make the request, you can mock the Guzzle client to return this stream. It's another benefit of using PSR-defined interfaces, as it protects the code from implementation details.

## Conclusion

The `stream+json` content type, utilizing the JSONL format, is a powerful solution for managing extensive HTTP responses. By employing streams and generators, you can create memory-efficient code capable of handling large data sets with ease.

All of the sample code and more examples are available in my GitHub repo:  
👉 [GitHub Repository](https://github.com/karintomania/php-stream-example)

I hope this article helps you. Happy coding! 😊