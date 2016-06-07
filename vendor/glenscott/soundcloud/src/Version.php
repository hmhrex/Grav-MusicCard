<?php

namespace SoundCloud;

/**
 * Soundcloud package version
 *
 * @category  Services
 * @package   Services_Soundcloud
 * @author    Anton Lindqvist <anton@qvister.se>
 * @copyright 2010 Anton Lindqvist <anton@qvister.se>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://github.com/mptre/php-soundcloud
 */
class Version
{

    const MAJOR = 3;
    const MINOR = 0;
    const PATCH = 0;

    /**
     * Magic to string method
     *
     * @return string
     *
     * @access public
     */
    public function __toString()
    {
        return implode('.', array(self::MAJOR, self::MINOR, self::PATCH));
    }
}
