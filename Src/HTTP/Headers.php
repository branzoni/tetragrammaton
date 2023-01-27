<?php

namespace Tet\HTTP;

use Tet\Params;

class Headers extends Params
{
    function setContentDescription($value){
        $this->data["Content-Description"] = $value;
    }

    function setContentType($value){
        $this->data["Content-Type"] = $value;
    }

    function setContentLength($value){
        $this->data["Content-Length"] = $value;
    }

    function setContentDisposition($value){
        $this->data["Content-Disposition"] = $value;
    }

    function setContentTransferEncoding($value){
        $this->data["Content-Transfer-Encoding"] = $value;
    }

    function setExpires($value){
        $this->data["Expires"] = $value;
    }

    function setCacheControl($value){
        $this->data["Cache-Control"] = $value;
    }

    function setPragma($value){
        $this->data["Pragma"] = $value;
    }    

    function getContentDescription(){
        return $this->data["Content-Description"];
    }

    function getContentType(){
        return  $this->data["Content-Type"];
    }

    function getContentLength(){
        return $this->data["Content-Length"];
    }

    function getContentDisposition(){
        return $this->data["Content-Disposition"];
    }

    function getContentTransferEncoding(){
        return $this->data["Content-Transfer-Encoding"];
    }   

    function getCacheControl(){
        return $this->data["Cache-Control"];
    }

    function getPragma(){
        return $this->data["Pragma"];
    }    

    function getExpires(){
        return $this->data["Expires"];
    }
}