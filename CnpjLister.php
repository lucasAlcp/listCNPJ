<?php
namespace CNPJ;
use DOMDocument;


Class CnpjLister{

    private $nextPage;
    private $links = [];
    public $fileName;

    public $limitOfPages = 50;
    public function __construct($limit)
    {
        $this->limitOfPages = $limit;   
    }
    public function getAllCnpj($url){
        $this->nextPage = $url;
        $cont = 0;
        do{
            $this->getPage($this->nextPage);
            print_r("$cont -> ".$this->nextPage."\n");
            // print_r($cont);
            $cont++;
            if($cont >= $this->limitOfPages)
                break;
        }while($this->nextPage != "dont" );
        echo "<pre>";
        
        $this->createName($url);
        $this->cleanListLinks();
        print_r($this->links);
        $this->writeOnListLinks();
    }

    private function createName($url){
        $url = explode("/", $url);
        $nameFile = end($url);
        $date = date("Y-m-d-h-i-s");
        $this->fileName = "$nameFile-$date.txt";
    }
    private function cleanListLinks(){
        $clearList = [];
        foreach($this->links as $link){
            if(!in_array($link, $clearList))
                array_push($clearList, $link);
            $this->links = $clearList;
        }
    }
    public function getPage($url){

        $context = stream_context_create(
            array(
                "http" => array(
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                )
            )
        );
        
        $html = file_get_contents($url,false,$context);
        $doc = new DOMDocument();
        $doc->strictErrorChecking = false;
        @$doc->loadHTML( $html);

        $this->nextPage = $this->searchNextPage($doc);

        $ul = $doc->getElementsByTagName('ul');
        $li = $ul[0]->getElementsByTagName('li');

        $this->getLinksFromList($li);

        
    }
    private function writeOnListLinks(){
        
        $file = fopen(__DIR__.'/links/'.$this->fileName, 'a');
        foreach($this->links as $link){
            if($link != '')
                fwrite($file, $link."\n");
        }
        fclose($file);
    }

    private function writeDirectOnListLinks($link){
        $file = fopen(__DIR__.'/links/'.$this->fileName, 'a');

        if($link != '')
            fwrite($file, $link."\n");

        fclose($file);
    }


    private function getLinksFromList($li){
        foreach($li as $link){
            $link_empresa = $link->getElementsByTagName('a')[0];
            if(!!$link_empresa and $this->isActive($link_empresa))
                $link_empresa = $link_empresa->getAttribute('href');
            else
                $link_empresa = '';
            // $this->writeDirectOnListLinks($link_empresa);
            array_push($this->links, $link_empresa);
        }
    }

    private function isActive($link){
       return strpos($link->nodeValue, "ATIVA")? true: false;
    }

    private function searchNextPage($doc){
        $searchNextPage = $doc->getElementsByTagName('a');
        $nextPage = 'dont';
        foreach($searchNextPage as $link){
            if(strpos($link->nodeValue, "Próxima Página")){
                $nextPage = $link->getAttribute('href'); 
            }
        }
        return $nextPage;
    }




}

?>