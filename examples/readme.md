Running examples:
Ensure you have php cli available, try running "php -v" on your command line
While inside the examples directory, run these commands to generate translation files

    php ../csvToLang.php -f simple.csv
    php ../csvToLang.php -f simple.csv -C asJson.json
    php ../csvToLang.php -f simple.csv -C asDefine.json
    php ../csvToLang.php -f simple.csv -C asJSVar.json
