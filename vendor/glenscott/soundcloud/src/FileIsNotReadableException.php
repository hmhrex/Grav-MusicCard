<?php

namespace SoundCloud;

class FileIsNotReadableException extends \Exception
{
    protected $message = 'The given file cannot be read.';
}
