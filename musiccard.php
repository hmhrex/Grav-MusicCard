<?php
/**
 * Music Card
 *
 * Place a Spotify or MusicBrainz reference to an album or track in a markdown file 
 * and it will pull in the necessary data. 
 *
 * Read musiccard.md for instructions.
 *
 * Based on Grav Team's "Grav Page Inject"
 * Original work licensed under MIT.
 */

namespace Grav\Plugin;
use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Twig\Twig;
use Grav\Common\Page\Page;
use Grav\Common\Page\Collection;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\MusicCard\MusicCard;

class MusicCardPlugin extends Plugin
{
    
    /**
     * @var MusicCardPlugin
     */

    /** ---------------------------
     * Private/protected properties
     * ----------------------------
     */

    /**
     * Instance of MusicCard class
     *
     * @var object
     */

    protected $musiccard;

    /** -------------
     * Public methods
     * --------------
     */
    
    /**
     * Return a list of subscribed events.
     *
     * @return array    The list of events of the plugin of the form
     *                      'name' => ['method_name', priority].
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }
    
    /**
     * Initialize configuration.
     */
    public function onPluginsInitialized()
    {
        if ($this->config->get('plugins.musiccard.enabled')) {
            // Initialize Autoloader
            require_once(__DIR__ . '/vendor/autoload.php');
            // Initialize MusicCard
            require_once(__DIR__ . '/classes/MusicCard.php');
            // Initialize BCScraper
            require_once(__DIR__ . '/classes/BCScraper.php');
            
            // Initialize MusicCard class
            $this->musiccard = new MusicCard($this->config);
        
            $this->enable([
                'onPageContentRaw' => ['onPageContentRaw', 0],
                'onPageContentProcessed' => ['onPageContentProcessed', 0],
                'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            ]);
        }
    }
    
    /**
     * Add content after page content was read into the system.
     *
     * @param  Event  $event An event object, when `onPageContentRaw` is fired.
     */
    public function onPageContentRaw(Event $event)
    {        
        /** @var Page $page */
        $page = $event['page'];
        $config = $this->mergeConfig($page);
        
        if ($config->get('enabled')) {
            // Get raw content and substitute all formulas by a unique token
            $raw_content = $page->getRawContent();
            
            // Save modified page content with tokens as placeholders
            $page->setRawContent(
                $this->musiccard->prepare($raw_content, $page->id())
            );
        }
    }
    
    /**
     * Apply musiccard filter to content, when each page has not been
     * cached yet.
     *
     * @param  Event  $event The event when 'onPageContentProcessed' was
     *                       fired.
     */
    public function onPageContentProcessed(Event $event)
    {
        /** @var Page $page */
        $page = $event['page'];
        
        $config = $this->mergeConfig($page);
        if ($config->get('enabled') && $this->compileOnce($page)) {
            // Get content
            $content = $page->getRawContent();
            
            $pages = $this->grav['pages']->all();
            $collection = new Collection();
            $albumreviews = $collection->append($pages->ofType('albumreview'));
            $spotifyAPI = array(
                "spotify_id" => $this->config->get('plugins.musiccard.spotify_id'),
                "spotify_secret" => $this->config->get('plugins.musiccard.spotify_secret'),
                "spotify_redirect" => $this->config->get('plugins.musiccard.spotify_redirect')
            );
            $soundcloudAPI = array(
                "soundcloud_id" => $this->config->get('plugins.musiccard.soundcloud_id'),
                "soundcloud_secret" => $this->config->get('plugins.musiccard.soundcloud_secret'),
                "soundcloud_redirect" => $this->config->get('plugins.musiccard.soundcloud_redirect')
            );
            
            // Apply MusicCard filter and save modified page content
            $page->setRawContent(
                $this->musiccard->process($content, $config, $albumreviews, $spotifyAPI, $soundcloudAPI)
            );
        }
    }
    
    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        // Register MusicCard Twig templates
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
        
        // Fire event for MusicCard plugins
        $this->musiccard->fireEvent('onTwigTemplatePaths');
    }
    
    /**
     * Set needed variables to display music cards.
     */
    public function onTwigSiteVariables()
    {
        // Register built-in CSS assets
        if ($this->config->get('plugins.musiccard.built_in_css')) {
            $this->grav['assets']
                 ->add('plugin://musiccard/assets/css/music-card.css');
        }
        
        // Register built-in JS assets
        if ($this->config->get('plugins.musiccard.built_in_js')) {
            $this->grav['assets']
                 ->add('plugin://musiccard/assets/js/color-thief.js');
            $this->grav['assets']
                 ->add('plugin://musiccard/assets/js/music-card.js');
        }
        // Register built-in font assets
        $this->grav['assets']
             ->add('plugin://musiccard/assets/fonts/css/logos.css');
        
        // Register assets from MusicCard Services
        $assets = $this->musiccard->getAssets();
        foreach ($assets as $asset) {
            $this->grav['assets']->add($asset);
        }
    }
    
    protected function compileOnce(Page $page)
    {
        static $processed = [];
        
        $id = md5($page->path());
        // Make sure that contents is only processed once
        if (!isset($processed[$id]) || ($processed[$id] < $page->modified())) {
            $processed[$id] = $page->modified();
            return true;
        }
        
        return false;
    }
}
