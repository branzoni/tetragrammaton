<?php

namespace Tet\HTTP;

use Tet\Collection;

class Headers extends Collection
{
    function setContentDescription($value){
        $this->values["Content-Description"] = $value;
    }

    function setContentType($value){
        $this->values["Content-Type"] = $value;
    }

    function setContentLength($value){
        $this->values["Content-Length"] = $value;
    }

    function setContentDisposition($value){
        $this->values["Content-Disposition"] = $value;
    }

    function setContentTransferEncoding($value){
        $this->values["Content-Transfer-Encoding"] = $value;
    }

    function setExpires($value){
        $this->values["Expires"] = $value;
    }

    function setCacheControl($value){
        $this->values["Cache-Control"] = $value;
    }

    function setPragma($value){
        $this->values["Pragma"] = $value;
    }    

    function getContentDescription(){
        return $this->values["Content-Description"];
    }

    function getContentType(){
        return  $this->values["Content-Type"];
    }

    function getContentLength(){
        return $this->values["Content-Length"];
    }

    function getContentDisposition(){
        return $this->values["Content-Disposition"];
    }

    function getContentTransferEncoding(){
        return $this->values["Content-Transfer-Encoding"];
    }   

    function getCacheControl(){
        return $this->values["Cache-Control"];
    }

    function getPragma(){
        return $this->values["Pragma"];
    }    

    function getExpires(){
        return $this->values["Expires"];
    }
}