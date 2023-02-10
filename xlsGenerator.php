<?php


Class Xls{

    public $file_name;

    
    public function __construct($file_name)
    {
        $this->createName($file_name);
    }
    public function createName($name){
        $name = explode(".", $name);
        $this->file_name = $name[0].".xls";
    }
    function createTable($data){
         
        //To define column name in first row.
        $column_names = false;
        // run loop through each row in $customers_data
        foreach($data as $row) {
        if(!$column_names) {
            echo implode("\t", array_keys($row)) . "\n";
            $column_names = true;
        }
        // The array_walk() function runs each array element in a user-defined function.
            echo implode("\t", array_values($row)) . "\n";
        }
    }

    function createFile($content){
        $myfile = fopen(__DIR__.'/planilhas/'.$this->file_name, "a") or die("Unable to open file!");
        fwrite($myfile, $content); 
        fclose($myfile);
    }

}






?>