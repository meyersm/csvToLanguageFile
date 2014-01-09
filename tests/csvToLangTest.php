<?php




class csvToLangTest extends \PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        //*
        $language_dir = dirname(__FILE__).'/unit-test-language';
        $objects = scandir($language_dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                unlink($language_dir."/".$object);
            }
        }
        rmdir($language_dir);
        //*/
    }

    public function testCliFunctionality()
    {
        $dir = dirname(__FILE__);
        $command = 'php '. $dir . '/../csvToLang.php -C '.$dir.'/config.json -f '.$dir.'/example.csv';
        $output = '';
        exec($command,$output);
        $this->assertEquals('Parse Complete',$output[11]);

        $this->sharedTests();

    }

    public function testParse()
    {
        $dir = dirname(__FILE__);
        $csvToLang_UNIT_TEST = true;//hack to get library to act as if its NOT in cli mode, even though unit tests run as CLI
        require ($dir . '/../csvToLang.php');
        $ctl = new csvToLang($dir.'/example.csv');
        $ctl->ignore_columns = [1,3,5,7,9,11,12];
        $ctl->ignore_rows= [2];
        $ctl->output_dir = "unit-test-language/";
        $ctl->key_row = 0;
        $ctl->key_column = 0;
        $ctl->parse();

        $this->sharedTests();
    }

    public function sharedTests()
    {
        $dir = dirname(__FILE__);
        $en = include ($dir.'/unit-test-language/en.php');
        $this->assertEquals('1',$en['unit_test']);
        $this->assertFalse(isset($en['ignore_this_row']));
        $es = include ($dir.'/unit-test-language/es.php');
        $this->assertEquals('1',$es['unit_test']);
        $de = include ($dir.'/unit-test-language/de.php');
        $this->assertEquals('1',$de['unit_test']);
        $fr = include ($dir.'/unit-test-language/fr.php');
        $this->assertEquals('1',$fr['unit_test']);
        $pt = include ($dir.'/unit-test-language/pt.php');
        $this->assertEquals('1',$pt['unit_test']);
    }


}
 