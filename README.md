# usfm-tools

Automate checks and changes to USFM Bible files

## Usage

You need an installed PHP interpreter to run the scripts:

    $ php sanitize.php <input.usfm> <output.usfm> [<config file.json>]

Without a config file, the script applies all available changes.

### Configuration

The optional **JSON configuration file** can have the following
entries (top-level keys; for an example see the `sample/` folder,
implementation in `Sanitizer.php`):

- `replaceEmptyS5`: processes `\s5` section tags without text. If true, ...
    * drops `\s5` in `\s5 \c \p`
    * converts `\s5 \c <not \p>` to `\c \p <not \p>`,
    * Motivation: `\s5` must have a title text, without one it may be intended as paragraph break (PortugueseBibliaLivre)..
- `replace`: (optional) array containing two sub-arrays with strings to replace element-wise in the input USFM file;
    * e.g. `[ ["@", "<<", ">>"], ["", "\u{00ab}", "\u{00bb}"] ]`
    * replaces `@` by an empty string, angle brackets `<<`/`>>` by typographic guillemets (Unicode U+00AB/U+00BB)
    * Note: `\u{00ab}` in PHP (since PHP 7) = `\u00ab` in JSON.
    * Note: the order of elements is relevant: you may want to replace `<<` before `<`.
    * Motivation: BibleThianghlim contains angle brackes for quotes.
- `dropIdeUtf8`: drops `\ide utf-8` (case insensitively)
    * Motivation: refdoc usfm2osis.py fails on it (unhandled `\\c`...).


## Sample Files

Subfolder `sample/` contains

- USFM files (few text taken from the free German Luther 1912),
- a sample configuration file


Run the script on the sample USFM files in folder `usfm-tools`:

~~~
php sanitize.php sample/Gn.usfm /tmp/Gn-sanitized.usfm sample/config.json
diff sample/Gn.usfm /tmp/Gn-sanitized.usfm
~~~

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/hsteeb/usfm-tools.

### Basics

- The implementation mainly uses regular expressions (PHP `preg_replace`), grouped in several methods which are under unit test.
- The development version of the script (`sanitizeSrc.php`) is located in subfolder `src/`.
- It uses class files in namespaces, managed by `composer`.
- Unit tests are using `PHPunit`.

### Prerequisites

For development (I'm working under Ubuntu Linux), you need:

- composer (PHP package manager)
    * `composer.json` installs PHPunit for unit tests
- make, cat, grep (GNU file utils, for building the single-file version)

### Steps

Clone the repository and get the necessary composer packages:

```
git clone https://github.com/hsteeb/usfm-tools.git
sudo apt-get install composer
composer install
```

Run the unit tests:

```
make phpunit
```

Build the single-file version `./sanitize.php`:

```
make build
```

### Integration test

The source code does not contain real Bible files, and therefore no integration test.

I'm using a simple integration test in a subfolder `itest/`, using a folder of
source USFM files and a folder of generated reference USFM files:

~~~
itest/
  src/          Source USFM files
  ref/          Generated USFM files
~~~

Run the integration test and show differences against previously saved reference output:

~~~
make itest
~~~

Later show differences again:

~~~
make idiff
~~~

Accept the generated output as new reference for future integration tests:

~~~
make isave
~~~

## License

GPL3. See the LICENSE file.

## References

Info:

- [USFM format](https://ubsicap.github.io/usfm/about/index.html) ("Unified Standard Format Markers")
- [OSIS page](https://www.crosswire.org/osis/) (at Cross Wire)
- [Converting SFM Bibles to OSIS](https://wiki.crosswire.org/Converting_SFM_Bibles_to_OSIS) (at Cross Wire)

Tools:

- [openenglishbible / USFM-Tools](https://github.com/openenglishbible/USFM-Tools) (github)
- [chrislit/usfm2osis](https://github.com/chrislit/usfm2osis) USFM to OSIS converter, by Chris Little (Python)
- [refdoc/Module-tools](https://github.com/refdoc/Module-tools) USFM to OSIS converter, by Peter von Kaehne, used by Cross Wire (Python)
- [osis2html5](https://github.com/tadd/osis2html5) by Tadashi Saito (Ruby)
