<?php
ini_set("auto_detect_line_endings", true);


/**
 *
 */
class csvToLang
{
    public  $ignore_columns = array();
    public  $ignore_rows = array();
    public  $output_dir = 'output/';
    public  $output_mode = array('start'=>"<?php\nreturn array(\n","startline"=>'','key_surround'=>'"','text_surround'=>'"','glue'=>'=>','endline'=>",\n",'end'=>');','blank'=>'');
    public  $file_extension = ".php";
    public  $endline_output_on_final_line = true;
    public  $key_column = 0;
    public  $key_row = 0;
    public  $mode_silent = false;
    public  $mode_debug = false;


    protected $config_json = null;
    protected $config_json_file = null;
    protected $file = null;
    protected $cli_mode = false;

    private $help_msg;
    private $invalidparam_error;
    private $fileparam_error;


    public function __construct($csvFile=null)
    {
        $this->setMsg();
        if ($csvFile == null)//Command Line
        {
            $this->cli_mode = true;
            $this->parseCommandLineValues();
            if ($this->file == null)
            {
                $this->consoleOutput($this->fileparam_error);
                die();
            }
        }
        else
        {
            $this->file = $csvFile;
            $this->mode_silent = true;
        }

        if (!is_file($this->file))
        {
            if ($this->cli_mode)
            {
                $this->consoleOutput($this->fileparam_error);
                die();
            }
            else
                throw new Exception("Invalid File");
        }

    }


    public function parse()
    {
        $error = false;
        $this->createDir();
        $file_start ='';
        $out_core = array();
        $column_keys = array();
        $row = $column = 0;
        $out_core_touched = false;
        $file_start .= $this->output_mode['start'];

        $handle = fopen($this->file, "r");
        if ($handle === FALSE)
        {
            $this->consoleOutput("Could not open csv file");
            return false;
        }

        while (($data = fgetcsv($handle, 0, ",", '"')) !== FALSE)
        {
            if (is_array($data) && count($data) >= 2 && (!in_array($row, $this->ignore_rows)))
            {
                if ($data[$this->key_column] != "")
                {
                    $column = 0;
                    if ($this->key_row == $row)
                    {
                        foreach($data as $k => $v)
                            $column_keys[$k]=$v;
                    }
                    else
                    {
                        foreach($data as $k => $v)
                        {
                            if (($k != $this->key_column) && (!in_array($column,$this->ignore_columns)))
                            {
                                if ((!$this->endline_output_on_final_line) && ($out_core_touched))
                                    $out_core[$column] .= $this->output_mode['endline'];//Only add endlines when we know there is another line
                                if ($out_core_touched == false)
                                    $out_core[$column] = $file_start;

                                $out_core[$column] .= $this->output_mode['startline'] .
                                    $this->output_mode['key_surround'] . $data[$this->key_column] . $this->output_mode['key_surround'] .   //Key
                                    $this->output_mode['glue'] .                                                                           //Glue
                                    $this->output_mode['text_surround'] .$this->characterFilter($v). $this->output_mode['text_surround'] ; //Text

                                if ($this->endline_output_on_final_line)
                                    $out_core[$column] .= $this->output_mode['endline'];
                            }
                            $column++;
                        }
                        $out_core_touched = true;
                    }
                }
            }
            $row++;
        }

        $this->consoleOutput("\n $row rows parsed, each containing $column columns\n");
        foreach ($out_core as $k=>$v)
        {
            $out_core[$k] .= $this->output_mode['end'];
            $file = $this->output_dir .$column_keys[$k]. $this->file_extension;
            $status = file_put_contents($file,$out_core[$k]);
            $this->consoleOutput($file. ' output, status='.$status. "\n");
        }

        $this->consoleOutput("Parse Complete\n");
        return ($error) ? 1 : 0;
    }

    private function parseCommandLineValues()
    {
        $ignore_next = true;//ignore the first param because its the filename
        if ($_SERVER['argc'] > 1)
        {
            foreach($_SERVER['argv'] as $k => $v)
            {
                if ($ignore_next)
                    $ignore_next = false;
                else if ($v == '-s')
                    $this->mode_silent = true;
                else if ($v == '-d')
                    $this->mode_debug = true;
                else if ($v == '-f')
                {
                    $this->file = $_SERVER['argv'][$k+1];
                    $ignore_next = true;
                }
                else if ($v == '-c')
                {
                    $this->config_json = $_SERVER['argv'][$k+1];
                    $this->parseConfigJson();
                    $ignore_next = true;
                }
                else if ($v == '-C')
                {
                    $this->config_json_file = $_SERVER['argv'][$k+1];
                    $this->parseConfigJsonFile();
                    $ignore_next = true;
                }
                else if (($v == 'help') || ($v == '-h'))
                {
                    print $this->help_msg;
                    die();
                }
                else
                {
                    print $v;
                    print $this->invalidparam_error;
                    die();
                }
            }
        }
    }

    private function parseConfigJson()
    {
        $json_array = json_decode($this->config_json,true);
        if (empty($json_array))
            die('Empty or malformed json object passed for config');
        $this->_initialValues = $json_array;

        $this->consoleOutput("Configuring...\n");
        foreach ($json_array as $key => $value)
        {
            if ($key == "output_mode")
                $value = array_merge($this->output_mode,$value);
            $this->$key = $value;
            if (is_array($value))
                $value = json_encode($value);
            $this->consoleOutput("$key \t - $value \n");
        }
    }

    private function parseConfigJsonFile()
    {
        if (!is_file($this->config_json_file))
            die('Invalid file passed for json config');
        $json = file_get_contents($this->config_json_file);
        $this->config_json = $json;
        $this->parseConfigJson();
    }

    private function setMsg()
    {
        $this->help_msg =
            "Usage:\n".
                " php ".$_SERVER['argv'][0]." [-s][-d][-c [Json Config]][-C [Json Config File]][-f [CSV FILE]] \n".
                "Command line options:\n".
                " s: Silent\n d: Debug output\n -c: Json config values \n -C: Json config values in a file \n -f: CSV file to parse \n";
        $this->invalidparam_error =
            "Invalid parameter passed in command line, use -h for help\n";
        $this->fileparam_error =
            "Invalid file parameter passed or file does not exist\n";
    }

    private function createDir()
    {
        if (!is_dir($this->output_dir)) {
            mkdir($this->output_dir);
        }
    }

    private function consoleOutput($text)
    {
        if (!$this->mode_silent)
            print $text;
    }

    private function characterFilter($string)
    {
        $pos = strpos($string,'"');

        if ($pos !== false)
        {

            if (substr($string,$pos-1,1) == "\\")// is it already escaped with a \
                return substr($string,0,$pos+1) . $this->characterFilter(substr($string,$pos+1));//skip to next segment
            else
            {
                $string = preg_replace('/"/', '\\"', $string, 1);// add an escape slash
                return substr($string,0,$pos+2) . $this->characterFilter(substr($string,$pos+2));//next segment is 2+pos because 1 char was added during replace
            }
        }
        return $string;
    }


}

// auto instantiate and parse if command line
if (php_sapi_name() == 'cli')
{
    if (!isset($csvToLang_UNIT_TEST))//PHPUnit runs as CLI but we want to test functionality as if we instantiated
    {
        $csvToLang = new csvToLang();
        $csvToLang->parse();
    }
}