jQuery(window).load(function(){
    
    // Determine colors for Album Cards
    jQuery('.album-cover img').each(function(){
        
        // Create Palette
        var image = new Image();
        image.src = jQuery(this).attr('src');
        var colorThief = new ColorThief();
        var secondary = colorThief.getPalette(image, 3);

        //var color1 = 'rgb('+dominant[0]+','+dominant[1]+','+dominant[2]+')';
        var color1 = 'rgb('+secondary[0][0]+','+secondary[0][1]+','+secondary[0][2]+')';
        var color2 = 'rgb('+secondary[1][0]+','+secondary[1][1]+','+secondary[1][2]+')';
        var color3 = 'rgb('+secondary[2][0]+','+secondary[2][1]+','+secondary[2][2]+')';
        
        var textColor = '';
        var lightest = '';
        
        // set Text Color
        if (color1 == color2) {
            textColor = color3;
        } else {
            textColor = color2;
        }
        
        // Set colors
        jQuery(this).parents('.music-card').find('.album-details').css('background-color', color1);
        jQuery(this).parents('.music-card').find('p, .album-rating a, i').css('color', textColor);
        
        // Choose the lighter color to use for play button
        // Reference: http://thesassway.com/intermediate/dynamically-change-text-color-based-on-its-background-with-sass
	    var bgyiq = ((parseInt(secondary[0][0])*299)+(parseInt(secondary[0][1])*587)+(parseInt(secondary[0][2])*114))/1000;
        var txtyiq = '';
        if (textColor == color2) {
            txtyiq = ((parseInt(secondary[1][0])*299)+(parseInt(secondary[1][1])*587)+(parseInt(secondary[1][2])*114))/1000;
        } else {
            txtyiq = ((parseInt(secondary[2][0])*299)+(parseInt(secondary[2][1])*587)+(parseInt(secondary[2][2])*114))/1000;
        }
        if (bgyiq > txtyiq) {
            lightest = color1;
        } else {
            lightest = textColor;
        }
        
        jQuery(this).parents('.music-card').find('.icon-play').css('color', lightest);
        
    });
});