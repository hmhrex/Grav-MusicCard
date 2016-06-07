<?php

namespace SoundCloud;

class FileDoesNotExistException extends \Exception
{
    protected $message = 'The given file does not exist.';
}
