<?php

/**
*
*/
class Core_Model_Upload_Document
{

    public function upload($document)
    {
        if ($_FILES[$document]["error"] > 0) {
            print_r($_FILES[$document]["error"]);
        } else {
            $permitidos = array("application/pdf", "application/xls", "application/doc", "application/docx", "application/vnd.ms-excel", "application/msword","application/octet-stream","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.openxmlformats-officedocument.wordprocessingml.document");
            $permitidas_ext = array("pdf", "xls", "xlsx", "doc", "docx");
            $extension = pathinfo($_FILES[$document]['name'], PATHINFO_EXTENSION);

            $limite_kb = 100000;
            if (in_array($_FILES[$document]['type'], $permitidos) && $_FILES[$document]['size'] <= $limite_kb * 1024 && in_array($extension, $permitidas_ext)) {
                $filename = ''.pathinfo($_FILES[$document]['name'], PATHINFO_FILENAME);
                $filename = $this->clearName($filename);
                $extension = pathinfo($_FILES[$document]['name'], PATHINFO_EXTENSION);
                $name = $filename.'.'.$extension;
                $ruta = FILE_PATH .$name;
                if (file_exists($ruta)) {
                    $increment = 0;
                    while (file_exists($ruta)) {
                        $increment++;
                        $name =$filename.$increment.'.'.$extension;
                        $ruta = FILE_PATH .$name;
                    }
                }
                if (move_uploaded_file($_FILES[$document]['tmp_name'], $ruta)) {
                    return $name;
                }
            }
        }
        return false;
    }

    public function delete($document)
    {
        if (file_exists(FILE_PATH.$document)) {
            unlink(FILE_PATH.$document);
            return true;
        }
        return false;
    }

    public function uploadpublic($image,$name)
    {
        if ($_FILES[$image]["error"] > 0) {
            print_r($_FILES[$image]["error"]);
        } else {
            $origen = $_FILES[$image]['tmp_name'];
            $ruta = PUBLIC_PATH.$name;
            unlink($ruta);
            move_uploaded_file($origen, $ruta);
            return $name;
        }
        return false;
    }

    private function clearName($str)
    {
        //Quitar tildes y ñ
        $tildes = array('á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ');
        $vocales = array('a','e','i','o','u','n','A','E','I','O','U','N');
        $str = str_replace($tildes,$vocales,$str);
        //Quitar símbolos
        $simbolos = array("=","¿","?","¡","!","'","%","$","€","(",")","[","]","{","}","*","+","·",".","&lt; ","&gt;");
        $i = 0;
        while($simbolos[$i]){
        $str = str_replace($simbolos[$i], "", $str);
        $i++;
        }
        //Quitar espacios
        $str = str_replace(" ","_",$str);
        //Pasar a minúsculas
        $str = strtolower($str);
        return $str;
    }
}