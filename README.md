csvToLanguageFile
=================

A PHP command line tool to convert csv files to php language files. By default this tool will output the files in php array format but can be configured to output files in a variety of formats that can be used by other languages besides php.

Basic Usage
-----------

    php csvToLang.php [-s][-d][-c [Json Config]][-C [Json Config File]][-f [CSV FILE]] 
    Command line options:
    -s: Silent
    -d: Debug output
    -c: Json config values 
    -C: Json config values in a file 
    -f: CSV file to parse

For example, running the command
    php csvToLang.php -f examples/simple.csv
    
will produce a file like this:
en_US.php
```php
    <?php
    return array(
    "hello"=>"hello",
    "colors"=>"colors",
    "fries"=>"fries",
    );
```
Along with 2 other files: en_UK.php and es_ES.php

[Source csv file for this example](examples/simple.csv)

[Other examples](examples/)

Advanced Usage
--------------


###Availible Json config values

* `ignore_columns` : An array of integers, these colums will ignored when parsing (default: `[ ]`)
* `ignore_rows` : An array of integers, these rows will be ignored when parsing (default: `[ ]`)
* `output_dir` : Relative path to a directory to place the output files, it will create the directory if it does not exist (default:`output/`)
* `output_mode`: An array, any values not present will fall back to the default
 - `start`: A string to start all output files with (default:`<?php\nreturn array(\n`)
 - `startline`: A string that starts every line in the output file (default: '')
 - `key_surround`: A string to go before and after the key, in most cases this will be a quote (default: `"` )
 - `text_surround`: A string to go before and after the text (default: `"` )
 - `glue`: A string that goes inbetween the key and the text for each line (default:`=>`)
 - `endline`: A string to go on the end of every line (default: `,\n`)
 - `end`: A string for the end of each file (default: `);` )
 - `blank`: If a value in the csv is blank, this will go in its place (default: '')
* `file_extension`: The file extension for the generated files (default:`.php`)
* `endline_output_on_final_line`: If you do not want the endline string to be present on the last line, set this to false (default:`true`)
* `key_column`: The column that contains the translation keys, keys are the strings you use to reference your translated text (default: `0`)
* `key_row`: The row that contains the language keys, these values are what files will be generated (default: `0`)


###Non CLI Usage
You can use this as a library instead of a php CLI script. Instead of passing a config json string or file, you will have to modify its public variables manually, see below for an example,

```php
        $ctl = new csvToLang('CSVFILE.csv');
        $ctl->ignore_columns = [1,3,5,7,9,11,12];
        $ctl->ignore_rows= [2];
        $ctl->output_dir = "Some-Output_directory-here/";
        $ctl->key_row = 0;
        $ctl->key_column = 0;
        $ctl->parse();
```


