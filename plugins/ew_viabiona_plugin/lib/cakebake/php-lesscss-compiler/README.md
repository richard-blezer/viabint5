PHP LessCss Compiler
=====================

Compiles less code to css code. 
Generating static css, or dynamic php file which outputs css... 
the use depends on your ideas and the project requirements! ;)

### Features

- Compiles less to css code with ```oyejorge/less.php``` lib (Bootstrap 3 support)
- Caching
- Observes the less files to changes and generates only when necessary
- Minify (optional)
- Output CSS and/or write Stylesheet file

### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install the package using the following command:

    php composer.phar require --prefer-dist cakebake/php-lesscss-compiler "*"

or add

    "cakebake/php-lesscss-compiler": "*"

to the require section of your ```composer.json``` file and run ```php composer.phar update```.

### Usage Example

For more options see source code comments in file ```src/LessConverter.php```.

    $less = new \cakebake\lesscss\LessConverter();
    $less->init([
        [
            'input' => __DIR__ . '/example-1.less',
            'webFolder' => '../tests',
        ],
        [
            'input' => __DIR__ . '/example-2.less',
            'webFolder' => '../tests',
        ],
    ], __DIR__ . '/css/output.css');