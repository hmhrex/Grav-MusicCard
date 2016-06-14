# Music Card

Place a Spotify, SoundCloud or Bandcamp link to an album or track in a markdown file and it will beautifully pull in the necessary data.

This uses [jwilsson's Spotify Web API PHP library](https://github.com/jwilsson/spotify-web-api-php
) to interface with the Spotify API,w and [Lokesh's Color Thief](https://github.com/lokesh/color-thief/) to retrieve colors from the album artwork.

The plan is to eventually support MusicBrainz links for albums that aren't on Spotify.

### Usage:

Spotify Album:

```markdown
![music-card](https://open.spotify.com/album/0wB19rOCyof5vPv6pYnCDH)
```

![Greys](assets/screenshot_greys.png)
![Greys - Hover](assets/screenshot_greys_hover.png)


Spotify Track:

```markdown
![music-card](https://open.spotify.com/track/3I50P6a8P4c9SxIYSeZUhS)
```

![Thrice](assets/screenshot_thrice.png)
![Thrice - Hover](assets/screenshot_thrice_hover.png)


SoundCloud Track:

```markdown
![music-card](https://soundcloud.com/hearspeakhere/gates)
```

![Speak](assets/screenshot_speak.png)
![Speak - Hover](assets/screenshot_speak_hover.png)

Bandcamp Album:
```markdown
![music-card](http://thehotelier.bandcamp.com/album/home-like-noplace-is-there-2)
```

![The Hotelier](assets/screenshot_the_hotelier.png)
![The Hotelier - Hover](assets/screenshot_the_hotelier_hover.png)

## Installation

1. [Create a Spotify Application](https://developer.spotify.com/my-applications).

2. [Create a SoundCloud Application](http://soundcloud.com/you/apps).

3. Copy this whole directory to your user/plugins directory.

4. In the plugins settings, fill in the Client ID, Client Secret, and Redirect URI from your Spotify Application.
    
### Thanks

Thanks to the Grav Team for writing the [MediaEmbed plugin](https://github.com/sommerregen/grav-plugin-mediaembed). I used this as a template to get things running at first.
