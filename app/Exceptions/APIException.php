<?php

namespace App\Exceptions;

class APIException extends \Exception
{
    protected $result;

    public function __construct($result)
    {
        $this->result = $result;

        $code = isset($result['status_code']) ? $result['status_code'] : 0;
        if (isset($result['success']) && $result['success'] !== '') {
            //open cart
            $message = $result['error']['warning'];
            $code = $result['error_code'];
        } elseif (isset($result['error']) && $result['error'] !== '') {
            // OAuth 2.0 Draft 10 style
            $message = $result['error'];
        } elseif (isset($result['message']) && $result['message'] !== '') {
            // cURL style
            $message = $result['message'];
        } else {
            $message = 'Unknown Error.';
        }
        parent::__construct($message, $code);
    }

    public function getResponseBody()
    {
        return $this->result;
    }

    public function getType()
    {
        $result = 'Exception';

        if (isset($this->result['error'])) {
            $message = $this->result['error'];

            if (is_string($message)) {
                // OAuth 2.0 Draft 10 style
                $result = $message;
            }
        }

        return $result;
    }

    /**
     * To make debugging easier.
     *
     * @return string The string representation of the error.
     */
    public function __toString()
    {
        $str = $this->getType().': ';

        if ($this->code != 0) {
            $str .= $this->code.': ';
        }

        return $str.$this->message;
    }
}
