<?php
namespace CNPJ;
use DOMDocument;

Class CnpjInfo{
    public $empresas = [];
    public $linkList = [];
    public function start($file){
        $this->getListLinks($file);

        foreach($this->linkList as $link){
            $this->getInfos($link);
        }
    }
    public function getInfos($url){
        $html = file_get_contents($url);
        $doc = new DOMDocument();
        $doc->strictErrorChecking = false;
        @$doc->loadHTML( $html);

        $empresa = [];
        $p = $doc->getElementsByTagName('p');

        // echo "<pre>";
        foreach($p as $info){
            $info->getElementsByTagName('b');

            $informacao = explode(":", $info->nodeValue);

            $index = $this->formatIndex($informacao[0]);

            @$empresa[$index] = $informacao[1]; 
        }
        @$empresa["e-mail"] = $this->getEmail($doc);

        array_push($this->empresas, $this->formatEmpresa($empresa));
    }

    private function formatEmpresa($empresa){
        
        @$empresa['telefones'] = str_replace("(Ligar)", "", $empresa['telefones']);
            if($this->isWpp($empresa['telefones'])){
                $empresa['whatsapp'] = str_replace("(Whatsapp)","",$empresa['telefones']);
            }else{
                $empresa['whatsapp'] = '';
            }

            if(!isset($empresa['inscricao_estadual_sp'])){
                $empresa['inscricao_estadual_sp'] = '';
            }
        
        $linkWhatsapp = isset($empresa['whatsapp'])? $this->linkWpp($empresa['whatsapp']): [];
        
        $empresaFormatada = [
            "cnpj" => isset($empresa['cnpj'])? $empresa['cnpj']: '',

            "razao_social" => isset($empresa['razao_social'])? $empresa['razao_social']: '',

            "nome_fantasia" =>isset($empresa['nome_fantasia'])? $empresa['nome_fantasia']: '',

            "data_da_abertura" =>isset($empresa['data_da_abertura'])? $empresa['data_da_abertura']: '',

            "porte" =>isset($empresa['porte'])? $empresa['porte']: '',

            "natureza_juridica" =>isset($empresa['natureza_juridica'])? $empresa['natureza_juridica']: '',

            "opcao_pelo_mei" =>isset($empresa['opcao_pelo_mei'])? $empresa['opcao_pelo_mei']: '',

            "opcao_pelo_simples" =>isset($empresa['opcao_pelo_simples'])? $empresa['opcao_pelo_simples']: '',

            "data_opcao_simples" =>isset($empresa['data_opcao_simples'])? $empresa['data_opcao_simples']: '',

            "capital_social" =>isset($empresa['capital_social'])? $empresa['capital_social']: '',

            "tipo" =>isset($empresa['tipo'])? $empresa['tipo']: '',

            "situacao" =>isset($empresa['situacao'])? $empresa['situacao']: '',

            "data_situacao_cadastral" =>isset($empresa['data_situacao_cadastral'])? $empresa['data_situacao_cadastral']: '',

            "e-mail" =>isset($empresa['e-mail'])? $empresa['e-mail']: '',

            "telefones" =>isset($empresa['telefones'])? $empresa['telefones']: '',

            "logradouro" =>isset($empresa['logradouro'])? $empresa['logradouro']: '',

            "bairro" =>isset($empresa['bairro'])? $empresa['bairro']: '',

            "cep" =>isset($empresa['cep'])? $empresa['cep']: '',

            "municipio" =>isset($empresa['municipio'])? $empresa['municipio']: '',

            "estado" =>isset($empresa['estado'])? $empresa['estado']: '',

            "para_correspondencia" =>isset($empresa['para_correspondencia'])? $empresa['para_correspondencia']: '',

            "qualificacao_do_responsavel_pela_empresa" =>isset($empresa['qualificacao_do_responsavel_pela_empresa'])? $empresa['qualificacao_do_responsavel_pela_empresa']: '',

            "whatsapp" =>isset($empresa['whatsapp'])? $empresa['whatsapp']: '',

            "link_whatsapp1" => isset($linkWhatsapp[0])? $linkWhatsapp[0] : '',
            "link_whatsapp2" =>  isset($linkWhatsapp[1])? $linkWhatsapp[1] : '',

            "inscricao_estadual_sp" => isset($empresa['inscricao_estadual_sp'])? $empresa['inscricao_estadual_sp']: '',
        ];
        foreach(array_keys($empresa) as $key){
            if(strlen($key) > 40)
                $empresaFormatada['descricao'] = str_replace("_", " ", $key);
        }

        return $empresaFormatada;
    }

    private function linkWpp($wpp){
        $wpp = explode("-", $wpp);
        if(count($wpp) > 1){
            $wpp1 = $this->formatNumber($wpp[0], $wpp[1]);
            $linkWpp[0] = "https://api.whatsapp.com/send?phone=55$wpp1&text=Ol%C3%A1!%20Eu%20me%20chamo%20Lucas%20e%20trabalho%20em%20uma%20ag%C3%AAncia%20de%20Social%20m%C3%ADdia%20e%20Marketing%20Digital,%20encontrei%20sua%20empresa%20e%20acredito%20que%20nossos%20servi%C3%A7os%20possam%20contribuir%20em%20muito%20no%20crescimento%20de%20sua%20empresa,%20tanto%20em%20visibilidade%20quanto%20em%20vendas!%20%20%20";

            $linkWpp[0] = str_replace(" ", "", $linkWpp[0]);
        }else{
            return [];
        }
        

        if(count($wpp) > 2){
            $wpp2 = $this->formatNumber($wpp[2], $wpp[3]);
            $linkWpp[1] = "https://api.whatsapp.com/send?phone=55$wpp2&text=Ol%C3%A1!%20Eu%20me%20chamo%20Lucas%20e%20trabalho%20em%20uma%20ag%C3%AAncia%20de%20Social%20m%C3%ADdia%20e%20Marketing%20Digital,%20encontrei%20sua%20empresa%20e%20acredito%20que%20nossos%20servi%C3%A7os%20possam%20contribuir%20em%20muito%20no%20crescimento%20de%20sua%20empresa,%20tanto%20em%20visibilidade%20quanto%20em%20vendas!%20%20%20";
            $linkWpp[1] = str_replace(" ", "", $linkWpp[1]);
        }

        return $linkWpp;
    }

    private function formatNumber($number1, $number2){
        $number1 = str_replace("(","",$number1);
        $number1 = str_replace(")","",$number1);
        $number1 = str_replace(" ","",$number1);

        return $number1.$number2;

    }

    private function isWpp($tel){
        return strpos($tel, "(Whatsapp)")?  true:false;
    }

    private function getEmail($doc){
        $a = $doc->getElementsByTagName('a');

        foreach($a as $link){
            if($link->getAttribute("data-cfemail") != null){
                $encodedString = $link->getAttribute("data-cfemail");
            }
        }
        return $this->cfDecodeEmail($encodedString);
    }
    private function formatIndex($index){
        $index = trim($index);
        $index = strtolower($this->removeAccentuation($index));
        $index = str_replace(" ", "_", $index);

        if($index == "telefone(s)")
            $index = "telefones";

        return $index;
    }

    private function removeAccentuation($string){
        $caracteres_sem_acento = array(
            'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Â'=>'Z', 'Â'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
            'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
            'Ï'=>'I', 'Ñ'=>'N', 'Å'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
            'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
            'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
            'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'Å'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
            'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
            'Ä'=>'a', 'î'=>'i', 'â'=>'a', 'È'=>'s', 'È'=>'t', 'Ä'=>'A', 'Î'=>'I', 'Â'=>'A', 'È'=>'S', 'È'=>'T',
        );
        $nova_string = strtr($string, $caracteres_sem_acento);

        return $nova_string;
        
    }

    private function cfDecodeEmail($encodedString){
        $k = hexdec(substr($encodedString,0,2));
        for($i=2,$email='';$i<strlen($encodedString)-1;$i+=2){
          $email.=chr(hexdec(substr($encodedString,$i,2))^$k);
        }
        return $email;
    }

    private function getListLinks($file){
    
        $handle = @fopen(__DIR__.'/links/'.$file, "r");
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                array_push($this->linkList , trim($buffer));
            }
            if (!feof($handle)) {
                echo "Erro: falha inexperada de fgets()\n";
            }
        
            fclose($handle);
        }
    
    }

}

?>