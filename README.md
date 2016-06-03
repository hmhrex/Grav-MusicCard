# Music Card

## Info

Place a Spotify reference to an album or track in a markdown file and it will beautifully pull in the necessary data.

*NOTE*: The play button links to Spotify, it does not play the track/album on the page.

This uses [jwilsson's Spotify Web API PHP library](https://github.com/jwilsson/spotify-web-api-php
) to interface with the Spotify API, and [Lokesh's Color Thief](https://github.com/lokesh/color-thief/) to retrieve colors from the album artwork.

The plan is to eventually support MusicBrainz links for albums that aren't on Spotify.

### Usage:

Album:

```markdown
![music-card](https://open.spotify.com/album/0wB19rOCyof5vPv6pYnCDH)
```

![Greys](assets/screenshot_greys.png)
![Greys - Hover](assets/screenshot_greys_hover.png)


Track:

```markdown
![music-card](https://open.spotify.com/track/3I50P6a8P4c9SxIYSeZUhS)
```

![Thrice](assets/screenshot_thrice.png)
![Thrice - Hover](assets/screenshot_thrice_hover.png)


## Installation

1. [Create a Spotify Application](https://developer.spotify.com/my-applications).

2. Copy this whole directory to your user/plugins directory.

3. In the plugins settings, fill in the Client ID, Client Secret, and Redirect URI from your Spotify Application.
    
### Thanks

Thanks to the Grav Team for writing the [MediaEmbed plugin](https://github.com/sommerregen/grav-plugin-mediaembed). I used this as a template to get things running at first.
