<?php
/**
 * Music Card
 *
 * This file is part of Music Card plugin
 */

namespace Grav\Plugin\MusicCard;

use Grav\Common\Grav;
use Grav\Common\GravTrait;
use Grav\Common\Page\Collection;
use RocketTheme\Toolbox\Event\Event;

/**
 * MusicCard
 *
 * Helper class to embed Spotify data by only 
 * providing the URL to the medium.
 */
class MusicCard
{
    /**
     * @var MusicCard
     */
        use GravTrait;
  
    /** ---------------------------
     * Private/protected properties
     * ----------------------------
     */
  
    /**
     * A unique identifier
     *
     * @var string
     */
    protected $id;
  
    /**
     * A key-valued array used for hashing math formulas of a page
     *
     * @var array
     */
    protected $hashes;
  
    /**
     * @var array
     */
    protected $config;
  
    /**
     * @var array
     */
    protected $assets = [];
    
    /**
     * @var Grav\Plugin\MediaEmbed\Service
     */
    protected $service;
    
    /**
     * @var Grav\Plugin\MediaEmbed\ServiceProvider
     */
    protected $services = [];
  
    /** -------------
     * Public methods
     * --------------
     */
  
    /**
     * Gets and sets the identifier for hashing.
     *
     * @param  string $var the identifier
     *
     * @return string      the identifier
     */
     public function id($var = null)
     {
        if ($var !== null) {
            $this->id = $var;
        }
        return $this->id;
     }
    
    public function prepare($content, $id = '')
    {
        // Set unique identifier based on page content
        $this->id(md5(time() . $id . md5($content)));
        
        // Reset class hashes before processing
        $this->reset();
        
        $regex = "/!\[music\-card\]\((.+)\)/i";
        
        // Replace all musiccard links by a (unique) hash
        $content = preg_replace_callback($regex, function($matches) {
            // Get the url and parse it
            $url = $matches[1];
            
            return $this->hash($matches[0], $matches);
        }, $content);
        
        return $content;
    }
    
	public function process($content, $config = [], $reviews, $spotifyAPI, $soundcloudAPI)
    {           
        /** @var Twig $twig */
        $twig = self::getGrav()['twig'];
        
        // Initialize unique per-page counter
        $uid = 1;

        $content = preg_replace_callback(
            '~musiccard::([0-9a-z]+)::([0-9]+)::M~i',
            function($match) use ($twig, &$uid, $config, $reviews, $spotifyAPI, $soundcloudAPI) {
                
                list($embed, $data) = $this->hashes[$match[0]];
                
                if (preg_match("/https:\/\/.*open\.spotify\.com\/(.+)\/(.+)/i", $data[1], $results)) {
                    $link = $results[0];
                    $type = $results[1];
                    $id = $results[2];

                    // Spotify API setup and authentication
                    $session = new \SpotifyWebAPI\Session($spotifyAPI["spotify_id"], $spotifyAPI["spotify_secret"], $spotifyAPI["spotify_redirect"]);
                    $api = new \SpotifyWebAPI\SpotifyWebAPI();
                    $session->requestCredentialsToken();
                    $accessToken = $session->getAccessToken();
                    $api->setAccessToken($accessToken);

                    if (strcmp($type, "album") == 0) {

                        $album = $api->getAlbum($id);

                        $cover = $album->images[1]->url;
                        $artist = $album->artists[0]->name;
                        $albumTitle = $album->name;
                        $releaseDate = $album->release_date;

                    } else if (strcmp($type, "track") == 0) {

                        $track = $api->getTrack($id);
                        $album = $api->getAlbum($track->album->id);

                        $cover = $track->album->images[1]->url;
                        $artist = $track->artists[0]->name;
                        $trackTitle = $track->name;
                        $albumTitle = $album->name;
                        $releaseDate = $album->release_date;
                    }

                    // Copy the image file locally to avoid Cross-Origin issues
                    $localCover = "user/pages/albums/images/" . $artist . " - " . $albumTitle . ".jpeg";        
                    if (!(file_exists($localCover))) {
                        file_put_contents($localCover, file_get_contents($cover));
                    }

                    // Fix date formatting
                    $releaseDate = date("F Y", strtotime($releaseDate));
                    
                    $source = "spotify";
                    
                } else if (preg_match("/https:\/\/.*soundcloud\.com\/.*/i", $data[1], $result)) {
                    // Get Soundcloud track info.
                    $track = $this->get_url('http://api.soundcloud.com/resolve?url=' . $result[0] . "&client_id=" . $soundcloudAPI["soundcloud_id"]);
                    $track = json_decode($track);
                    
                    $link = $track->permalink_url;
                    $cover = preg_replace('/-large.jpg/', '-t300x300.jpg', $track->artwork_url);
                    $artist = $track->user->username;
                    $trackTitle = $track->title;
                    
                    if (!is_null($track->release_year)) {
                        $releaseMonth = $track->release_month;
                        $releaseYear = $track->release_year;
                        
                        $releaseDate = $releaseMonth + " " + $releaseYear;
                    } else {
                        $releaseDate = $track->created_at;
                    }
                    
                    // Fix date formatting
                    $releaseDate = date("F Y", strtotime($releaseDate));
                                        
                    // Copy the image file locally to avoid Cross-Origin issues
                    $localCover = "user/pages/albums/images/" . $artist . " - " . $albumTitle . ".jpeg";        
                    if (!(file_exists($localCover))) {
                        file_put_contents($localCover, file_get_contents($cover));
                    }
                    
                    $source = "soundcloud";
                    
                } else if (preg_match("/http[s]?:\/\/.*\.bandcamp\.com\/(.+)\/.*/", $data[1], $results)) {
                    $bcscraper = new BCScraper();
                    
                    $metadata = $bcscraper->scrape($results[0]);
                    $link = $metadata["url"];
                    $cover = $metadata["cover"];
                    $artist = $metadata["artist"];
                    $trackTitle = $metadata["trackTitle"];
                    $albumTitle = $metadata["albumTitle"];
                    $releaseDate = $metadata["releaseDate"];
                    $source = "bandcamp";
                    
                    // Fix date formatting
                    $releaseDate = date("F Y", strtotime($releaseDate));
                                        
                    // Copy the image file locally to avoid Cross-Origin issues
                    $localCover = "user/pages/albums/images/" . $artist . " - " . $albumTitle . ".jpeg";        
                    if (!(file_exists($localCover))) {
                        file_put_contents($localCover, file_get_contents($cover));
                    }                
                }
                
                $musiccard = [
                    'uid' => $uid++,
                    'service' => null,
                    'config' => $config,

                    'raw' => [
                        'link' => $link,
                        'cover' => '/' . $localCover,
                        'artist' => $artist,
                        'track_title' => $trackTitle,
                        'album_title' => $albumTitle,
                        'release_date' => $releaseDate,
                        'source' => $source
                    ],
                ];

                $vars = ['musiccard' => $musiccard];
                $template = 'partials/musiccard' . TEMPLATE_EXT;
                $embed = $twig->processTemplate($template, $vars);
                
                return $embed;
                
            }, $content);
        
        $this->reset();
        // Write content back to page
        return $content;
    }
    
    /**
     * Fires an event with optional parameters.
     *
     * @param  string $eventName The name of the event.
     * @param  Event  $event     Optional parameter to be passed to the
     *                           called methods.
     * @return Event
     */
    
    public function fireEvent($eventName, Event $event = null)
    {
        /* //Dispatch event; just propagate it to service class
         * return $this->service->call($eventName, $event);
         *
         * $eventName = $method
         * $event = $params
         */
        
        $result = [];
        foreach ($this->services as $key => $service) {
            if (method_exists($service['provider'], $eventName)) {
                $data = call_user_method_array([$service['provider'], $eventName], $event);
                if ($data) {
                    $result[] = $data;
                }
            }
        }
        return $result;
    }
    
    
    /**
     * Get assets of loaded media services.
     *
     * @param boolean $reset Toggle whether to reset assets after retrieving
     *                       or not.
     */
    public function getAssets($reset = true)
    {
        $assets = $this->assets;
        if ($reset) {
            $this->assets = [];
        }
        
        return $assets;
    }
    
    /**
     * Add assets to the queue of MusicCard plugin
     *
     * @param array   $assets An array of assets to add.
     * @param boolean $append Append assets to array or reset assets.
     */
    public function addAssets($assets, $append = true)
    {
        // Append or reset assets
        if (!$append) {
            $this->assets = [];
        }
        
        // Wrap non-array assets in an array
        if (!is_array($assets)) {
            $assets = array($assets);
        }
        
        // Merge assets
        $assets = array_merge($this->assets, $assets);
        
        // Remove duplicates
        $this->assets = array_keys(array_flip($assets));
    }
    
    /**
     * Add assets to the queue of MusicCard plugin
     *
     * Alias for `addAssets`
     *
     * @param array   $assets An array of assets to add.
     * @param boolean $append Append assets to array or reset assets.
    */
    public function add($assets, $append = true)
    {
        return $this->addAssets($assets, $append);
    }
    
    /**
     * Add assets to the queue of MusicCard plugin
     *
     * Alias for `addAssets`
     *
     * @param array   $assets An array of assets to add.
     * @param boolean $append Append assets to array or reset assets.
     */
    public function addCss($assets, $append = true)
    {
        return $this->addAssets($assets, $append);
    }
    
    /**
     * Add assets to the queue of MusicCard plugin
     *
     * Alias for `addAssets`
     *
     * @param array   $assets An array of assets to add.
     * @param boolean $append Append assets to array or reset assets.
     */
    public function addJs($assets, $append = true)
    {
        return $this->addAssets($assets, $append);
    }
    
    /** -------------------------------
     * Private/protected helper methods
     * --------------------------------
     */
    
    /**
     * Reset MathJax class
     */
    protected function reset()
    {
        $this->hashes = [];
    }
    
    /**
     * Hash a given text.
     *
     * Called whenever a tag must be hashed when a function insert an
     * atomic element in the text stream. Passing $text to through this
     * function gives a unique text-token which will be reverted back when
     * calling unhash.
     *
     * @param  string $text The text to be hashed
     * @param  string $type The type (category) the text should be saved
     *
     * @return string       Return a unique text-token which will be
     *                      reverted back when calling unhash.
     */
    protected function hash($text, $data = [])
    {
        static $counter = 0;
        
        // Swap back any tag hash found in $text so we do not have to `unhash`
        // multiple times at the end.
        $text = $this->unhash($text);
        
        // Then hash the block
        $key = implode('::', array('musiccard', $this->id, ++$counter, 'M'));
        $this->hashes[$key] = [$text, $data];
        
        // String that will replace the tag
        return $key;
    }
    
    /**
     * Swap back in all the tags hashed by hash.
     *
     * @param  string $text The text to be un-hashed
     *
     * @return string       A text containing no hash inside
     */
    protected function unhash($text)
    {
        $text = preg_replace_callback(
            '~musiccard::([0-9a-z]+)::([0-9]+)::M~i', function($atches) {
            return $this->hashes[$matches[0]][0];
        }, $text);
        
        return $text;
    }
    
    protected function get_url($url) 
    {
        $ch = curl_init();

        if($ch === false)
        {
            die('Failed to create curl object');
        }

        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
}