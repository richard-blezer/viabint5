<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2013 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */


defined('_VALID_CALL') or die('Direct Access is not allowed.');


class HermesException extends Exception{

    private $_codes = false;
    private $_msgs = false;

    private $_code = 0;

    function bak__construct($msg, $code, $previous, $msgs = false, $codes = false)
    {
        parent::__construct($msg, $code, $previous);

        if (is_array($msgs))
        {
            $this->_msgs = $msgs;
        }
        if (is_array($codes))
        {
            $this->_codes = $codes;
        }
    }

    function getHermesMessage()
    {
        $msg = $this->getMessage();
        if ($this->_msgs)
        {
            $msg = !empty($msg) ? $msg. ': ' : '';
            $c = count($this->_msgs);
            for ($i =0; $i<$c; $i++)
            {
                $msg .= $this->_codes[$i].'-'.$this->_msgs[$i];
                if ($i<$c-1)
                {
                    $msg .= ' | ';
                }
            }
        }
        return $msg;
    }

    public function __construct($e, $action)
    {
        $this->message = $action;

        if ($e->detail)
        {
            if (is_array($e->detail->ServiceException->exceptionItems->ExceptionItem))
            {
                $this->message = $e->detail->ServiceException->exceptionItems->ExceptionItem[0]->errorMessage;
                $this->_code = $e->detail->ServiceException->exceptionItems->ExceptionItem[0]->errorCode;

                $codes = array();
                $msgs = array();
                foreach($e->detail->ServiceException->exceptionItems->ExceptionItem as $excItem)
                {
                    $msgs[] = $excItem->errorMessage;
                    $codes[] = $excItem->errorCode;
                }
            }
            else{
                $this->message = $e->detail->ServiceException->exceptionItems->ExceptionItem->errorMessage;
                $this->_code = $e->detail->ServiceException->exceptionItems->ExceptionItem->errorCode;
                $this->code = $e->detail->ServiceException->exceptionItems->ExceptionItem->errorCode;
            }
        }
        else
        {
            $this->message =  ' | '. $e->getMessage();
        }

    }


}