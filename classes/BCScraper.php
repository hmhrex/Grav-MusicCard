<?php
/**
 * BCScraper
 *
 * Scrapes Bandcamp page for metadata.
 *
 * This file is part of Music Card plugin
 */

namespace Grav\Plugin\MusicCard;

class BCScraper
{    
    
    /**
     * Scrape metadata from Bandcamp
     *
     * @param  string $url URL of track or album
     *
     * @return array       Metadata object
     */
    public function scrape($url)
    {
        // Test URL
        if (preg_match("/http[s]?:\/\/.*\.bandcamp\.com\/(.+)\/.*/", $url, $type)){
            // Get Type (album or track)
            $type = $type[1];
            // Get HTML
            $html = file_get_contents($url);

            // Get EmbedData and TralbumData
            $embedData = $this->getEmbedData($html);
            $tralbumData = $this->getTralbumData($html);

            // Find metadata values
            preg_match('/artist[ ]?: "([^"]*)"/', $embedData, $artist);
            preg_match('/album_title[ ]?: "([^"]*)"/', $embedData, $albumTitle);
            preg_match('/art_id[ ]?: ([0-9]*)/', $embedData, $cover);
            
            // Set cover image to 150x150.
            if (!is_null($cover)){
                $cover = "http://f4.bcbits.com/img/a" . $cover[1] . "_7.jpg";
            }
            
            // Set track information.
            if (strcmp($type, "track") == 0) {
                preg_match('/"title":"([^"]*)"/', $tralbumData, $trackTitle);
                preg_match('/linkback[ ]?: "([^"]*)" \+ "([^"]*)"/', $embedData, $albumLink);
                $html2 = file_get_contents($albumLink[1] . $albumLink[2]);
                $tralbumData2 = $this->getTralbumData($html2);
                preg_match('/album_release_date: "([^"]*)"/', $tralbumData2, $releaseDate);
            } else {
                preg_match('/album_release_date: "([^"]*)"/', $tralbumData, $releaseDate);
            }

            // Build Metadata object
            $metadata = array(
                "type" => $type,
                "url" => $url,
                "artist" => $artist[1],
                "trackTitle" => $trackTitle[1],
                "albumTitle" => $albumTitle[1],
                "cover" => $cover,
                "releaseDate" => $releaseDate[1]
            );

            return $metadata;
        } else {
            echo "This is not a proper Bandcamp track or album URL";
        }
    }
    
    /**
     * Get EmbedData JSON object
     *
     * @param  string $html Full HTML doc
     *
     * @return string       EmbedData
     */
    private function getEmbedData($html)
    {
        $indexStart =  strpos($html, 'var EmbedData = {');
        $indexFinish = strpos($html, '};', $indexStart);
        $length = $indexFinish - $indexStart;
        $embedData = substr($html, $indexStart, $length);
        return $embedData;
    }
    
    /**
     * Get TralbumData JSON object
     *
     * @param  string $html Full HTML doc
     *
     * @return string       TralbumData
     */
    private function getTralbumData($html)
    {
        $indexStart =  strpos($html, 'var TralbumData = {');
        $indexFinish = strpos($html, '};', $indexStart);
        $length = $indexFinish - $indexStart;
        $tralbumData = substr($html, $indexStart, $length);
        return $tralbumData;
    }
}