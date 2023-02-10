<?php
    ini_set('max_execution_time', '300');
    set_time_limit(300);
    date_default_timezone_set('America/Sao_Paulo');

    
    include "CnpjInfo.php";
    include "CnpjLister.php";
    include 'xlsGenerator.php';
    use CNPJ\{CnpjLister, CnpjInfo};
    
    /**
     * Cria a lista de CNPJ em TXT
     */

    $ini_file = parse_ini_file("config.ini");
    $cnpjLister = new CnpjLister($ini_file['limit_of_pages']);
    $cnpjLister->getAllCnpj($ini_file['url']);


    // Pega os dados da Lista de CNPJ em txt
    $crawlerCnpj = new CnpjInfo();

    $crawlerCnpj->start($cnpjLister->fileName); 

    print_r($crawlerCnpj->empresas);

    $empresas = $crawlerCnpj->empresas;

    
    ob_start();
    $xlsGenerator = new Xls($cnpjLister->fileName);
    $xlsGenerator->createTable($empresas);
    $xls = ob_get_contents();
    ob_end_clean();

    $xlsGenerator->createFile($xls);


 

    

?>