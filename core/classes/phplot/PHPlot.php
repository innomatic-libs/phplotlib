<?php

function phplot_regression(
    $x,
    $y
    )
{
    $result = array();

    $myarray = $x;
    $otherarray = $y;

        $sumX=0;
        $sumY=0;
        $sumXX=0;
        $sumXY=0;
                $n=count( $myarray );   //number of items
            for ($i=0; $i<$n; $i++) {
                $sumX +=$myarray[$i];
            $sumY +=$otherarray[$i];
            $meanX = $sumX/$n;
            $meanY = $sumY/$n;
            $sumXX += pow($myarray[$i],2);
            $sumXY += ($myarray[$i])*($otherarray[$i]);
            $m =($sumXY - $meanY*$sumX)/($sumXX-$meanX*$sumX);
            $b =$meanY-$meanX*$m;

                }

        for ( $i = 0; $i < $n; $i++ )
        {
            $result[$i] = array(
                $x[$i],
                $y[$i],
                $x[$i] * $m + $b
                );
        }

        return $result;
}

/*
Copyright (C) 1998, 1999, 2000, 2001 Afan Ottenheimer.  Released under
the GPL and PHP licenses as stated in the the README file which
should have been included with this document.

World Coordinates are the XY coordinates relative to the
axis origin that can be drawn. Not the device (pixel) coordinates
which in GD is relative to the origin at the upper left
side of the image.
*/

//PHPLOT Version 4.4.6
//Requires PHP 3.0.2 or later

define('MINY', -1);        // Indexes in $data (for DrawXDataLine())
define('MAXY', -2);

error_reporting(E_ALL);


class PHPlot {

    /* I have removed internal variable declarations, some isset() checking was required, 
     * but now the variables left are those which can be tweaked by the user. This is intended to
     * be the first step towards moving most of the Set...() methods into a subclass which will be 
     * used only when strictly necessary. Many users will be able to put default values here in the
     * class and thus avoid memory overhead and reduce parsing times.
     */
    //////////////// CONFIG PARAMETERS //////////////////////

    var $is_inline = FALSE;             // FALSE = Sends headers, TRUE = sends just raw image data
    var $browser_cache = FALSE;         // FALSE = Sends headers for browser to not cache the image,
                                        // (only if is_inline = FALSE also)

    var $safe_margin = 5;               // Extra margin used in several places. In pixels

    var $x_axis_position = '';          // Where to draw both axis (world coordinates),
    var $y_axis_position = '';          // leave blank for X axis at 0 and Y axis at left of plot.

    var $xscale_type = 'linear';        // linear, log
    var $yscale_type = 'linear';

//Fonts
    var $use_ttf  = FALSE;                  // Use True Type Fonts?
    var $ttf_path = '.';                    // Default path to look in for TT Fonts. 
    var $default_ttfont = 'benjamingothic.ttf';
    var $line_spacing = 4;                  // Pixels between lines.

    // Font angles: 0 or 90 degrees for fixed fonts, any for TTF
    var $x_label_angle = 0;                 // For labels on X axis (tick and data)
    var $y_label_angle = 0;                 // For labels on Y axis (tick and data)
    var $x_title_angle = 0;                 // Don't change this if you don't want to screw things up!
    var $y_title_angle = 90;                // Nor this.
    var $title_angle = 0;                   // Or this.

//Formats
    var $file_format = 'png';
    var $output_file = '';                  // For output to a file instead of stdout

//Data
    var $data_type = 'text-data';           // text-data, data-data-error, data-data, text-data-pie
    var $plot_type= 'linepoints';           // bars, lines, linepoints, area, points, pie, thinbarline, squared

    var $label_scale_position = 0.5;        // Shifts data labes in pie charts. 1 = top, 0 = bottom
    var $group_frac_width = 0.7;            // value from 0 to 1 = width of bar groups
    var $bar_width_adjust = 1;              // 1 = bars of normal width, must be > 0

    var $y_precision = 1;
    var $x_precision = 1;

    var $data_units_text = '';              // Units text for 'data' labels (i.e: '�', '$', etc.)

// Titles
    var $title_txt = '';

    var $x_title_txt = '';
    var $x_title_pos = 'plotdown';          // plotdown, plotup, both, none

    var $y_title_txt = '';
    var $y_title_pos = 'plotleft';          // plotleft, plotright, both, none


//Labels
    // There are two types of labels in PHPlot:
    //    Tick labels: they follow the grid, next to ticks in axis.   (DONE)
    //                 they are drawn at grid drawing time, by _DrawXTicks() and _DrawYTicks()
    //    Data labels: they follow the data points, and can be placed on the axis or the plot (x/y)  (TODO)
    //                 they are drawn at graph plotting time, by DrawDataLabel(), called by DrawLines(), etc.
    //                 Draw*DataLabel() also draws H/V lines to datapoints depending on draw_*_data_label_line

    // Tick Labels
    var $x_tick_label_pos = 'plotdown';     // plotdown, plotup, both, xaxis, none
    var $y_tick_label_pos = 'plotleft';     // plotleft, plotright, both, yaxis, none

    // Data Labels:
    var $x_data_label_pos = 'plotdown';     // plotdown, plotup, both, plot, all, none
    var $y_data_label_pos = 'plotleft';     // plotleft, plotright, both, plot, all, none

    var $draw_x_data_label_lines = FALSE;   // Draw a line from the data point to the axis?
    var $draw_y_data_label_lines = FALSE;   // TODO

    // Label types: (for tick, data and plot labels)
    var $x_label_type = '';                 // data, time. Leave blank for no formatting.
    var $y_label_type = '';                 // data, time. Leave blank for no formatting.
    var $x_time_format = '%H:%m:%s';        // See http://www.php.net/manual/html/function.strftime.html
    var $y_time_format = '%H:%m:%s';        // SetYTimeFormat() too... 

    // Skipping labels
    var $x_label_inc = 1;                   // Draw a label every this many (1 = all) (TODO)
    var $y_label_inc = 1;

    // Legend
    var $legend = '';                       // An array with legend titles
    var $legend_x_pos = '';
    var $legend_y_pos = '';


//Ticks
    var $x_tick_length = 5;                 // tick length in pixels for upper/lower axis
    var $y_tick_length = 5;                 // tick length in pixels for left/right axis

    var $x_tick_cross = 3;                  // ticks cross x axis this many pixels
    var $y_tick_cross = 3;                  // ticks cross y axis this many pixels

    var $x_tick_pos = 'plotdown';           // plotdown, plotup, both, xaxis, none 
    var $y_tick_pos = 'plotleft';           // plotright, plotleft, both, yaxis, none 

    var $num_x_ticks = '';
    var $num_y_ticks = '';

    var $x_tick_increment = '';             // Set num_x_ticks or x_tick_increment, not both.
    var $y_tick_increment = '';             // Set num_y_ticks or y_tick_increment, not both.

    var $skip_top_tick = FALSE;
    var $skip_bottom_tick = FALSE;
    var $skip_left_tick = FALSE;
    var $skip_right_tick = FALSE;

//Grid Formatting
    var $draw_x_grid = FALSE;
    var $draw_y_grid = TRUE;

    var $dashed_grid = TRUE;

//Colors and styles       (all colors can be array (R,G,B) or named color)
    var $color_array = 'small';             // 'small', 'large' or array (define your own colors)
                                            // See rgb.inc.php and SetRGBArray()
    var $i_border = array(194, 194, 194);
    var $plot_bg_color = 'white';
    var $bg_color = 'white';
    var $label_color = 'black';
    var $text_color = 'black';
    var $grid_color = 'black';
    var $light_grid_color = 'gray';
    var $tick_color = 'black';
    var $title_color = 'black';
    var $data_colors = array('SkyBlue', 'green', 'orange', 'blue', 'orange', 'red', 'violet', 'azure1');
    var $error_bar_colors = array('SkyBlue', 'green', 'orange', 'blue', 'orange', 'red', 'violet', 'azure1');
    var $data_border_colors = array('black');

    var $line_widths = 1;                  // single value or array
    var $line_styles = array('solid', 'solid', 'dashed');   // single value or array
    var $dashed_style = '2-4';              // colored dots-transparent dots

    var $point_size = 5;
    var $point_shape = 'diamond';           // rect, circle, diamond, triangle, dot, line, halfline, cross

    var $error_bar_size = 5;                // right and left size of tee
    var $error_bar_shape = 'tee';           // 'tee' or 'line'
    var $error_bar_line_width = 1;          // single value (or array TODO)

    var $plot_border_type = 'sides';        // left, sides, none, full
    var $image_border_type = 'none';        // 'raised', 'plain', 'none'

    var $shading = 5;                       // 0 for no shading, > 0 is size of shadows in pixels

    var $draw_plot_area_background = FALSE;
    var $draw_broken_lines = FALSE;          // Tells not to draw lines for missing Y data.


//////////////////////////////////////////////////////
//BEGIN CODE
//////////////////////////////////////////////////////

    /*!
     * Constructor: Setup img resource, colors and size of the image, and font sizes.
     * 
     * \param which_width       int    Image width in pixels.
     * \param which_height      int    Image height in pixels.
     * \param which_output_file string Filename for output.
     * \param which_input_fule  string Path to a file to be used as background.
     */
    function PHPlot($which_width=600, $which_height=400, $which_output_file=NULL, $which_input_file=NULL) 
    {
        /*
         * Please see http://www.php.net/register_shutdown_function
         * PLEASE NOTE: register_shutdown_function() will take a copy of the object rather than a reference
         * so we put an ampersand. However, the function registered will work on the object as it
         * was upon registration. To solve this, one of two methods can be used:
         *      $obj = new object();
         *      register_shutdown_function(array(&$obj,'shutdown'));
         * OR
         *      $obj = &new object();
         * HOWEVER, as the second statement assigns $obj a reference to the current object, it might be that
         * several instances mess things up... (CHECK THIS)
         *
         * AND
         *    as $this->img is set upon construction of the object, problems will not arise for us (for the
         *    moment maybe, so I put all this here just in case)
         */
        register_shutdown_function(array(&$this, '_PHPlot'));

		$this->ttf_path = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/applications/phplot/';
		
        $this->SetRGBArray($this->color_array);

        $this->background_done = FALSE;     // Set to TRUE after background image first drawn

        if ($which_output_file)
            $this->SetOutputFile($which_output_file);

        if ($which_input_file)
            $this->SetInputFile($which_input_file);
        else {
            $this->image_width = $which_width;
            $this->image_height = $which_height;

            $this->img = ImageCreate($this->image_width, $this->image_height);
            if (! $this->img)
                $this->PrintError('PHPlot(): Could not create image resource.');

        }

        $this->SetDefaultStyles();
        $this->SetDefaultFonts();

/*
        $this->font = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/applications/phplot/benjamingothic.ttf';
        $this->title_ttffont = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/applications/phplot/benjamingothic.ttf';
        $this->axis_ttffont = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/applications/phplot/benjamingothic.ttf';
        $this->x_label_ttffont = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/applications/phplot/benjamingothic.ttf';
        $this->y_label_ttffont = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/applications/phplot/benjamingothic.ttf';
*/

        $this->SetTitle('');
        $this->SetXTitle('');
        $this->SetYTitle('');

        $this->print_image = TRUE;      // Use for multiple plots per image (TODO: automatic)
    }

    /*!
     * Destructor. Image resources not deallocated can be memory hogs, I think
     * it is safer to automatically call imagedestroy upon script termination than
     * do it ourselves.
     * See notes in the constructor code.
     */
    function _PHPlot () 
    {
        ImageDestroy($this->img);
        return;
    }


/////////////////////////////////////////////    
//////////////                         COLORS
/////////////////////////////////////////////

    /*!
     * Returns an index to a color passed in as anything (string, hex, rgb)
     *
     * \param which_color * Color (can be '#AABBCC', 'Colorname', or array(r,g,b))
     */
    function setIndexColor($which_color) 
    {
        list ($r, $g, $b) = $this->SetRGBColor($which_color);  //Translate to RGB
        $index = ImageColorExact($this->img, $r, $g, $b);
        if ($index == -1) {
            return ImageColorResolve($this->img, $r, $g, $b);
        } else {
            return $index;
        }
    }


    /*!
     * Returns an index to a slightly darker color than the one requested. 
     */
    function setIndexDarkColor($which_color) 
    {
        list ($r, $g, $b) = $this->SetRGBColor($which_color);

        $r -= 0x30;     $r = ($r < 0) ? 0 : $r;
        $g -= 0x30;     $g = ($g < 0) ? 0 : $g;
        $b -= 0x30;     $b = ($b < 0) ? 0 : $b;

        $index = ImageColorExact($this->img, $r, $g, $b);
        if ($index == -1) {
            return ImageColorResolve($this->img, $r, $g, $b);
        } else {
            return $index;
        }
    }

    /*!
     * Sets/reverts all colors and styles to their defaults. If session is set, then only updates indices,
     * as they are lost with every script execution, else, sets the default colors by name or value and
     * then updates indices too.
     *
     * FIXME Isn't this too slow?
     *
     */
    function setDefaultStyles() 
    {
        /* Some of the Set*() functions use default values when they get no parameters. */

        if (! isset($this->session_set)) {
            // If sessions are enabled, this variable will be preserved, so upon future executions, we
            // will have it set, as well as color names (though not color indices, that's why we 
            // need to rebuild them)
            $this->session_set = TRUE;

            // These only need to be set once
            $this->SetLineWidths();
            $this->SetLineStyles();
            $this->SetDefaultDashedStyle($this->dashed_style);
            $this->SetPointSize($this->point_size);
        }

        $this->SetImageBorderColor($this->i_border);
        $this->SetPlotBgColor($this->plot_bg_color);
        $this->SetBackgroundColor($this->bg_color);
        $this->SetLabelColor($this->label_color);
        $this->SetTextColor($this->text_color);
        $this->SetGridColor($this->grid_color);
        $this->SetLightGridColor($this->light_grid_color);
        $this->SetTickColor($this->tick_color);
        $this->SetTitleColor($this->title_color);
        $this->SetDataColors();
        $this->SetErrorBarColors();
        $this->SetDataBorderColors();
    }


    /*
     *
     */
    function setBackgroundColor($which_color) 
    {
        $this->bg_color= $which_color;
        $this->ndx_bg_color= $this->SetIndexColor($this->bg_color);
        return TRUE;
    }

    /*
     *
     */
    function setPlotBgColor($which_color) 
    {
        $this->plot_bg_color= $which_color;
        $this->ndx_plot_bg_color= $this->SetIndexColor($this->plot_bg_color);
        return TRUE;
    }

   /*
    *
    */
    function setTitleColor($which_color) 
    {
        $this->title_color= $which_color;
        $this->ndx_title_color= $this->SetIndexColor($this->title_color);
        return TRUE;
    }

    /*
     *
     */
    function setTickColor ($which_color) 
    {
        $this->tick_color= $which_color;
        $this->ndx_tick_color= $this->SetIndexColor($this->tick_color);
        return TRUE;
    }

    
    /*
     *
     */
    function setLabelColor ($which_color) 
    {
        $this->label_color = $which_color;
        $this->ndx_title_color= $this->SetIndexColor($this->label_color);
        return TRUE;
    }

    
    /*
     *
     */
    function setTextColor ($which_color) 
    {
        $this->text_color= $which_color;
        $this->ndx_text_color= $this->SetIndexColor($this->text_color);
        return TRUE;
    }

    
    /*
     *
     */
    function setLightGridColor ($which_color) 
    {
        $this->light_grid_color= $which_color;
        $this->ndx_light_grid_color= $this->SetIndexColor($this->light_grid_color);
        return TRUE;
    }

    
    /*
     *
     */
    function setGridColor ($which_color) 
    {
        $this->grid_color = $which_color;
        $this->ndx_grid_color= $this->SetIndexColor($this->grid_color);
        return TRUE;
    }

    
    /*
     *
     */
    function setImageBorderColor($which_color)
    {
        $this->i_border = $which_color;
        $this->ndx_i_border = $this->SetIndexColor($this->i_border);
        $this->ndx_i_border_dark = $this->SetIndexDarkColor($this->i_border);
        return TRUE;
    }

    
    /*
     *
     */   
    function setTransparentColor($which_color) 
    { 
        ImageColorTransparent($this->img, $this->SetIndexColor($which_color));
        return TRUE;
    }
    

    /*!
     * Sets the array of colors to be used. It can be user defined, a small predefined one 
     * or a large one included from 'rgb.inc.php'.
     *
     * \param which_color_array If an array, the used as color array. If a string can 
     *        be one of 'small' or 'large'.
     */
    function setRGBArray ($which_color_array) 
    { 
        if ( is_array($which_color_array) ) {           // User defined array
            $this->rgb_array = $which_color_array;
            return TRUE;
        } elseif ($which_color_array == 'small') {      // Small predefined color array
            $this->rgb_array = array(
                'white'          => array(255, 255, 255),
                'snow'           => array(255, 250, 250),
                'PeachPuff'      => array(255, 218, 185),
                'ivory'          => array(255, 255, 240),
                'lavender'       => array(230, 230, 250),
                'black'          => array(  0,   0,   0),
                'DimGrey'        => array(105, 105, 105),
                'gray'           => array(190, 190, 190),
                'grey'           => array(190, 190, 190),
                'navy'           => array(  0,   0, 128),
                'SlateBlue'      => array(106,  90, 205),
                'blue'           => array(  0,   0, 255),
                'SkyBlue'        => array(135, 206, 235),
                'cyan'           => array(  0, 255, 255),
                'DarkGreen'      => array(  0, 100,   0),
                'green'          => array(  0, 255,   0),
                'YellowGreen'    => array(154, 205,  50),
                'yellow'         => array(255, 255,   0),
                'orange'         => array(255, 165,   0),
                'gold'           => array(255, 215,   0),
                'peru'           => array(205, 133,  63),
                'beige'          => array(245, 245, 220),
                'wheat'          => array(245, 222, 179),
                'tan'            => array(210, 180, 140),
                'brown'          => array(165,  42,  42),
                'salmon'         => array(250, 128, 114),
                'red'            => array(255,   0,   0),
                'pink'           => array(255, 192, 203),
                'maroon'         => array(176,  48,  96),
                'magenta'        => array(255,   0, 255),
                'violet'         => array(238, 130, 238),
                'plum'           => array(221, 160, 221),
                'orchid'         => array(218, 112, 214),
                'purple'         => array(160,  32, 240),
                'azure1'         => array(240, 255, 255),
                'aquamarine1'    => array(127, 255, 212)
                );
            return TRUE;
        } elseif ($which_color_array === 'large')  {    // Large color array
            include("./rgb.inc.php");
            $this->rgb_array = $RGBArray;
        } else {                                        // Default to black and white only.
            $this->rgb_array = array('white' => array(255, 255, 255), 'black' => array(0, 0, 0));
        }

        return TRUE;
    }

    /*!
     * Returns an array in R, G, B format 0-255
     *
     *  \param color_asked array(R,G,B) or string (named color or '#AABBCC')
     */
    function setRGBColor($color_asked) 
    {
        if ($color_asked == '') { $color_asked = array(0, 0, 0); };

        if ( count($color_asked) == 3 ) {    // already array of 3 rgb
               $ret_val =  $color_asked;
        } else {                             // asking for a color by string
            if(substr($color_asked, 0, 1) == '#') {         // asking in #FFFFFF format. 
                $ret_val = array(hexdec(substr($color_asked, 1, 2)), hexdec(substr($color_asked, 3, 2)), 
                                  hexdec(substr($color_asked, 5, 2)));
            } else {                                        // asking by color name
                $ret_val = $this->rgb_array[$color_asked];
            }
        }
        return $ret_val;
    }


    /*!
     * Sets the colors for the data.
     */
    function setDataColors($which_data = NULL, $which_border = NULL) 
    {
        if (is_null($which_data) && is_array($this->data_colors)) {
            // use already set data_colors
        } else if (! is_array($which_data)) {
            $this->data_colors = ($which_data) ? array($which_data) : array('blue', 'red', 'green', 'orange');
        } else {
            $this->data_colors = $which_data;
        }

        $i = 0;
        foreach ($this->data_colors as $col) {
            $this->ndx_data_colors[$i] = $this->SetIndexColor($col);
            $this->ndx_data_dark_colors[$i] = $this->SetIndexDarkColor($col);
            $i++;
        }

        // For past compatibility:
        $this->SetDataBorderColors($which_border);
    } // function setDataColors()


    /*!
     *
     */
    function setDataBorderColors($which_br = NULL)
    {
        if (is_null($which_br) && is_array($this->data_border_colors)) {
            // use already set data_border_colors
        } else if (! is_array($which_br)) {
            // Create new array with specified color
            $this->data_border_colors = ($which_br) ? array($which_br) : array('black');
        } else {
            $this->data_border_colors = $which_br;
        }

        $i = 0;
        foreach($this->data_border_colors as $col) {
            $this->ndx_data_border_colors[$i] = $this->SetIndexColor($col);
            $i++;
        }
    } // function setDataBorderColors()


    /*!
     * Sets the colors for the data error bars.
     */
    function setErrorBarColors($which_err = NULL)
    {
        if (is_null($which_err) && is_array($this->error_bar_colors)) {
            // use already set error_bar_colors
        } else if (! is_array($which_err)) {
            $this->error_bar_colors = ($which_err) ? array($which_err) : array('black');
        } else {
            $this->error_bar_colors = $which_err;
        }

        $i = 0;
        foreach($this->error_bar_colors as $col) {
            $this->ndx_error_bar_colors[$i] = $this->SetIndexColor($col);
            $i++;
        }       
        return TRUE;

    } // function setErrorBarColors()


    /*!
     * Sets the default dashed style.
     *  \param which_style A string specifying order of colored and transparent dots, 
     *         i.e: '4-3' means 4 colored, 3 transparent; 
     *              '2-3-1-2' means 2 colored, 3 transparent, 1 colored, 2 transparent.
     */
    function setDefaultDashedStyle($which_style) 
    {
        // String: "numcol-numtrans-numcol-numtrans..."
        $asked = explode('-', $which_style);

        if (count($asked) < 2) {
            $this->DrawError("SetDefaultDashedStyle(): Wrong parameter '$which_style'.");
            return FALSE;
        }

        // Build the string to be eval()uated later by SetDashedStyle()
        $this->default_dashed_style = 'array( ';

        $t = 0;
        foreach($asked as $s) {
            if ($t % 2 == 0) {
                $this->default_dashed_style .= str_repeat('$which_ndxcol,', $s);
            } else {
                $this->default_dashed_style .= str_repeat('IMG_COLOR_TRANSPARENT,', $s);
            }
            $t++;
        }
        // Remove trailing comma and add closing parenthesis
        $this->default_dashed_style = substr($this->default_dashed_style, 0, -1);
        $this->default_dashed_style .= ')';

        return TRUE;
    }


    /*!
     * Sets the style before drawing a dashed line. Defaults to $this->default_dashed_style
     *   \param which_ndxcol Color index to be used.
     */
    function setDashedStyle($which_ndxcol)
    {
        // See SetDefaultDashedStyle() to understand this.
        eval ("\$style = $this->default_dashed_style;");
        return imagesetstyle($this->img, $style);
    }


    /*!
     * Sets line widths on a per-line basis.
     */
    function setLineWidths($which_lw=NULL) 
    {
        if (is_null($which_lw)) {
            // Do nothing, use default value.
        } else if (is_array($which_lw)) {
            // Did we get an array with line widths?
            $this->line_widths = $which_lw;
        } else {
            $this->line_widths = array($which_lw);
        }
        return TRUE;
    }

    /*!
     *
     */
    function setLineStyles($which_ls=NULL)
    {
        if (is_null($which_ls)) {
            // Do nothing, use default value.
        } else if (! is_array($which_ls)) {
            // Did we get an array with line styles?
            $this->line_styles = $which_ls;
        } else {
            $this->line_styles = ($which_ls) ? array($which_ls) : array('solid');
        }
        return TRUE;
    }


/////////////////////////////////////////////
//////////////                          FONTS
/////////////////////////////////////////////


    /*!
     * Sets number of pixels between lines of the same text.
     */
    function setLineSpacing($which_spc) 
    {
        $this->line_spacing = $which_spc;
    }


    /*!
     * Enables use of TrueType fonts in the graph. Font initialisation methods
     * depend on this setting, so when called, SetUseTTF() resets the font
     * settings
     */
    function setUseTTF($which_ttf) 
    {
        $this->use_ttf = $which_ttf;
        if ($which_ttf)
            $this->SetDefaultFonts();
        return TRUE;
    }

    /*!
     * Sets the directory name to look into for TrueType fonts.
     */
    function setTTFPath($which_path)
    {
        // Maybe someone needs really dynamic config. He'll need this:
        // clearstatcache();

        if (is_dir($which_path) && is_readable($which_path)) {
            $this->ttf_path = $which_path;
            return TRUE;
        } else {
            $this->PrintError("SetTTFPath(): $which_path is not a valid path.");
            return FALSE;
        }
    }

    /*!
     * Sets the default TrueType font and updates all fonts to that.
     */
    function setDefaultTTFont($which_font) 
    {
        if (is_file($which_font) && is_readable($which_font)) {
            $this->default_ttfont = $which_font;
            return $this->SetDefaultFonts();
        } else {
            $this->PrintError("SetDefaultTTFont(): $which_font is not a valid font file.");
            return FALSE;
        }
    }

    /*!
     * Sets fonts to their defaults
     */
    function setDefaultFonts() 
    {
        // TTF:
        if ($this->use_ttf) {
            //$this->SetTTFPath(dirname($_SERVER['PHP_SELF']));
            $this->SetTTFPath(getcwd());
            $this->SetFont('generic', $this->default_ttfont, 8);
            $this->SetFont('title', $this->default_ttfont, 14);
            $this->SetFont('legend', $this->default_ttfont, 8);
            $this->SetFont('x_label', $this->default_ttfont, 6);
            $this->SetFont('y_label', $this->default_ttfont, 6);
            $this->SetFont('x_title', $this->default_ttfont, 10);
            $this->SetFont('y_title', $this->default_ttfont, 10);
        } 
        // Fixed:
        else {
            $this->SetFont('generic', 2);
            $this->SetFont('title', 5);
            $this->SetFont('legend', 2);
            $this->SetFont('x_label', 1);
            $this->SetFont('y_label', 1);           
            $this->SetFont('x_title', 3);
            $this->SetFont('y_title', 3);
        }

        return TRUE;
    }

    /*!
     * Sets Fixed/Truetype font parameters.
     *  \param $which_elem Is the element whose font is to be changed.
     *         It can be one of 'title', 'legend', 'generic', 
     *         'x_label', 'y_label', x_title' or 'y_title'
     *  \param $which_font Can be a number (for fixed font sizes) or 
     *         a string with the filename when using TTFonts.
     *  \param $which_size Point size (TTF only)
     * Calculates and updates internal height and width variables.
     */
    function setFont($which_elem, $which_font, $which_size = 12) 
    {
        // TTF:
        if ($this->use_ttf) {
            $path = $this->ttf_path.'/'.$which_font;

            if (! is_file($path) || ! is_readable($path) ) {
                $this->DrawError("SetFont(): True Type font $path doesn't exist");
                return FALSE;
            }

            switch ($which_elem) {
            case 'generic':
                $this->generic_font['font'] = $path;
                $this->generic_font['size'] = $which_size;
                break;
            case 'title':
                $this->title_font['font'] = $path;
                $this->title_font['size'] = $which_size;
                break;
            case 'legend':
                $this->legend_font['font'] = $path;
                $this->legend_font['size'] = $which_size;
                break;
            case 'x_label':
                $this->x_label_font['font'] = $path;
                $this->x_label_font['size'] = $which_size;
                break;
            case 'y_label':
                $this->y_label_font['font'] = $path;
                $this->y_label_font['size'] = $which_size;
                break;                   
            case 'x_title':
                $this->x_title_font['font'] = $path;
                $this->x_title_font['size'] = $which_size;
                break;
            case 'y_title':
                $this->y_title_font['font'] = $path;
                $this->y_title_font['size'] = $which_size;
                break;
            default:
                $this->DrawError("SetFont(): Unknown element '$which_elem' specified.");
                return FALSE;
            }
            return TRUE;

        } 

        // Fixed fonts:
        if ($which_font > 5 || $which_font < 0) {
            $this->DrawError('SetFont(): Non-TTF font size must be 1, 2, 3, 4 or 5');
            return FALSE;
        }

        switch ($which_elem) {
        case 'generic':
            $this->generic_font['font'] = $which_font;
            $this->generic_font['height'] = ImageFontHeight($which_font);
            $this->generic_font['width'] = ImageFontWidth($which_font);
            break;
        case 'title':
           $this->title_font['font'] = $which_font;
           $this->title_font['height'] = ImageFontHeight($which_font);
           $this->title_font['width'] = ImageFontWidth($which_font);
           break;
        case 'legend':
            $this->legend_font['font'] = $which_font;
            $this->legend_font['height'] = ImageFontHeight($which_font);
            $this->legend_font['width'] = ImageFontWidth($which_font);
            break;
        case 'x_label':
            $this->x_label_font['font'] = $which_font;
            $this->x_label_font['height'] = ImageFontHeight($which_font);
            $this->x_label_font['width'] = ImageFontWidth($which_font);
            break;
        case 'y_label':
            $this->y_label_font['font'] = $which_font;
            $this->y_label_font['height'] = ImageFontHeight($which_font);
            $this->y_label_font['width'] = ImageFontWidth($which_font);
            break;               
        case 'x_title':
            $this->x_title_font['font'] = $which_font;
            $this->x_title_font['height'] = ImageFontHeight($which_font);
            $this->x_title_font['width'] = ImageFontWidth($which_font);
            break;
        case 'y_title':
            $this->y_title_font['font'] = $which_font;
            $this->y_title_font['height'] = ImageFontHeight($which_font);
            $this->y_title_font['width'] = ImageFontWidth($which_font);
            break;
        default:
            $this->DrawError("SetFont(): Unknown element '$which_elem' specified.");
            return FALSE;
        }
        return TRUE;
    }


    /*!
     * Returns an array with the size of the bounding box of an
     * arbitrarily placed (rotated) TrueType text string.
     */
    function TTFBBoxSize($size, $angle, $font, $string) 
    {
        // First, assume angle < 90
        $arr = ImageTTFBBox($size, 0, $font, $string);
        $flat_width  = $arr[2] - $arr[0];
        $flat_height = abs($arr[3] - $arr[5]);

        // Now the bounding box
        $angle = deg2rad($angle);
        $width  = ceil(abs($flat_width*cos($angle) + $flat_height*sin($angle))); //Must be integer
        $height = ceil(abs($flat_width*sin($angle) + $flat_height*cos($angle))); //Must be integer

        return array($width, $height);
    }


    /*!
     * Draws a string of text. Horizontal and vertical alignment are relative to 
     * to the drawing. That is: vertical text (90 deg) gets centered along y-axis 
     * with v_align = 'center', and adjusted to the left of x-axis with h_align = 'right',
     * 
     * \note Original multiple lines code submitted by Remi Ricard.
     * \note Original vertical code submitted by Marlin Viss.
     */
    function DrawText($which_font, $which_angle, $which_xpos, $which_ypos, $which_color, $which_text,
                      $which_halign = 'left', $which_valign = 'bottom') 
    {
        // TTF:
        if ($this->use_ttf) { 
            $size = $this->TTFBBoxSize($which_font['size'], $which_angle, $which_font['font'], $which_text); 
            $rads = deg2rad($which_angle);

            if ($which_valign == 'center')
                $which_ypos += $size[1]/2;

            if ($which_valign == 'bottom')
                $which_ypos += $size[1];

            if ($which_halign == 'center')
                $which_xpos -= ($size[0]/2) * cos($rads);

            if ($which_halign == 'left')
                $which_xpos += $size[0] * sin($rads);

            if ($which_halign == 'right')
                $which_xpos -= $size[0] * cos($rads);

            ImageTTFText($this->img, $which_font['size'], $which_angle, 
                         $which_xpos, $which_ypos, $which_color, $which_font['font'], $which_text); 
        }
        // Fixed fonts:
        else { 
            // Split the text by its lines, and count them
            $which_text = ereg_replace("\r", "", $which_text);
            $str = split("\n", $which_text);
            $nlines = count($str);
            $spacing = $this->line_spacing * ($nlines - 1);

            // Vertical text:
            // (Remember the alignment convention with vertical text)
            if ($which_angle == 90) {
                // The text goes around $which_xpos.
                if ($which_halign == 'center') 
                    $which_xpos -= ($nlines * ($which_font['height'] + $spacing))/2;

                // Left alignment requires no modification to $xpos...
                // Right-align it. $which_xpos designated the rightmost x coordinate.
                else if ($which_halign == 'right')
                    $which_xpos += ($nlines * ($which_font['height'] + $spacing));

                $ypos = $which_ypos;
                for($i = 0; $i < $nlines; $i++) { 
                    // Center the text vertically around $which_ypos (each line)
                    if ($which_valign == 'center')
                        $ypos = $which_ypos + (strlen($str[$i]) * $which_font['width']) / 2;
                    // Make the text finish (vertically) at $which_ypos
                    if ($which_valign == 'bottom')
                        $ypos = $which_ypos + strlen($str[$i]) * $which_font['width'];

                    ImageStringUp($this->img, $which_font['font'],
                                  $i * ($which_font['height'] + $spacing) + $which_xpos,
                                  $ypos, $str[$i], $which_color);
                } 
            } 
            // Horizontal text:
            else {
                // The text goes above $which_ypos
                if ($which_valign == 'top')
                    $which_ypos -= $nlines * ($which_font['height'] + $spacing);
                // The text is centered around $which_ypos
                if ($which_valign == 'center')
                    $which_ypos -= ($nlines * ($which_font['height'] + $spacing))/2;
                // valign = 'bottom' requires no modification

                $xpos = $which_xpos;
                for($i = 0; $i < $nlines; $i++) { 
                    // center the text around $which_xpos
                    if ($which_halign == 'center')
                        $xpos = $which_xpos - (strlen($str[$i]) * $which_font['width'])/2;
                    // make the text finish at $which_xpos
                    if ($which_halign == 'right')
                        $xpos = $which_xpos - strlen($str[$i]) * $which_font['width'];

                    ImageString($this->img, $which_font['font'], $xpos, 
                                $i * ($which_font['height'] + $spacing) + $which_ypos,
                                $str[$i], $which_color);
                }                 
            }
        } 
        return TRUE; 
    } // function DrawText()


/////////////////////////////////////////////
///////////            INPUT / OUTPUT CONTROL
/////////////////////////////////////////////

    /*!
     * Sets output format.
     * inline condition, cacheable, etc.
     */
    function setFileFormat($which_file_format) 
    {
        // I know rewriting this was unnecessary, but it didn't work for me, I don't
        // understand why. 
        $asked = strtolower($which_file_format);
        switch ($asked) {
        case 'jpg':
            if (imagetypes() & IMG_JPG)
                return TRUE;
            break;
        case 'png':
            if (imagetypes() & IMG_PNG)
                return TRUE;
            break;
        case 'gif':
            if (imagetypes() & IMG_GIF)
                return TRUE;
            break;
        case 'wbmp':
            if (imagetypes() & IMG_WBMP)
                return TRUE;
            break;
        default:
            $this->PrintError("SetFileFormat(): Unrecognized option '$which_file_format'");
            return FALSE;
        }
        $this->PrintError("SetFileFormat():File format '$which_file_format' not supported");
        return FALSE;
    }    


    /*!
     * Selects an input file to be used as background for the whole graph.
     */
    function setInputFile($which_input_file) 
    { 
        $size = GetImageSize($which_input_file);
        $input_type = $size[2]; 

        switch($input_type) {
        case 1:
            $im = @ImageCreateFromGIF ($which_input_file);
            if (!$im) { // See if it failed 
                $this->PrintError("Unable to open $which_input_file as a GIF");
                return FALSE;
            }
        break;
        case 3:
            $im = @ImageCreateFromPNG ($which_input_file); 
            if (!$im) { // See if it failed 
                $this->PrintError("Unable to open $which_input_file as a PNG");
                return FALSE;
            }
        break;
        case 2:
            $im = @ImageCreateFromJPEG ($which_input_file); 
            if (!$im) { // See if it failed 
                $this->PrintError("Unable to open $which_input_file as a JPG");
                return FALSE;
            }
        break;
        default:
            $this->PrintError('SetInputFile(): Please select gif, jpg, or png for image type!');
            return FALSE;
        break;
        }

        // Set Width and Height of Image
        $this->image_width = $size[0];
        $this->image_height = $size[1];

        // Deallocate any resources previously allocated
        if ($this->img)
            imagedestroy($this->img);

        $this->img = $im;

        return TRUE;

    }

    function setOutputFile($which_output_file) 
    { 
        $this->output_file = $which_output_file;
        return TRUE;
    }

    /*!
     * Sets the output image as 'inline', ie. no Content-Type headers are sent
     * to the browser. Very useful if you want to embed the images.
     */
    function setIsInline($which_ii) 
    {
        $this->is_inline = $which_ii;
        return TRUE;
    }


    /*!
     * Performs the actual outputting of the generated graph, and
     * destroys the image resource.
     */
    function PrintImage() 
    {
        // Browser cache stuff submitted by Thiemo Nagel
        if ( (! $this->browser_cache) && (! $this->is_inline)) {
            WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
            WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Cache-Control: no-cache, must-revalidate');
            WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Pragma: no-cache');
        }

        switch($this->file_format) {
        case 'png':
            if (! $this->is_inline) {
                WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Content-type: image/png');
            }
            if ($this->is_inline && $this->output_file != '') {
                ImagePng($this->img, $this->output_file);
            } else {
                ImagePng($this->img);
            }
            break;
        case 'jpg':
            if (! $this->is_inline) {
                WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Content-type: image/jpeg');
            }
            if ($this->is_inline && $this->output_file != '') {
                ImageJPEG($this->img, $this->output_file);
            } else {
                ImageJPEG($this->img);
            }
            break;
        case 'gif':
            if (! $this->is_inline) {
                WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Content-type: image/gif');
            }
            if ($this->is_inline && $this->output_file != '') {
                ImageGIF($this->img, $this->output_file);
            } else {
                ImageGIF($this->img);
            }

            break;
        case 'wbmp':        // wireless bitmap, 2 bit.
            if (! $this->is_inline) {
                WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Content-type: image/wbmp');
            }
            if ($this->is_inline && $this->output_file != '') {
                ImageWBMP($this->img, $this->output_file);
            } else {
                ImageWBMP($this->img);
            }

            break;
        default:
            $this->PrintError('PrintImage(): Please select an image type!');
            break;
        }
        return TRUE;
    }

    /*! 
     * Prints an error message to stdout and dies 
     */
    function PrintError($error_message) 
    {
        echo "<p><b>Fatal error</b>: $error_message<p>";
        die;
    }

    /*!
     * Prints an error message inline into the generated image and draws it centered
     * around the given coordinates (defaults to center of the image)
     *   \param error_message Message to be drawn
     *   \param where_x       X coordinate
     *   \param where_y       Y coordinate
     */
    function DrawError($error_message, $where_x = NULL, $where_y = NULL) 
    {
        if (! $this->img)
            $this->PrintError('_DrawError(): Warning, no image resource allocated. '.
                              'The message to be written was: '.$error_message);

        $ypos = (! $where_y) ? $this->image_height/2 : $where_y;
        $xpos = (! $where_x) ? $this->image_width/2 : $where_x;
        ImageRectangle($this->img, 0, 0, $this->image_width, $this->image_height,
                       ImageColorAllocate($this->img, 255, 255, 255));

        $this->DrawText($this->generic_font, 0, $xpos, $ypos, ImageColorAllocate($this->img, 0, 0, 0),
                        $error_message, 'center', 'center');

        $this->PrintImage();
        exit;
//        return TRUE;
    }

/////////////////////////////////////////////
///////////                            LABELS
/////////////////////////////////////////////


    /*!
     * Sets position for X labels following data points.
     */
    function setXDataLabelPos($which_xdlp) 
    {
        $this->x_data_label_pos = $this->CheckOption($which_xdlp, 'plotdown, plotup, both, xaxis, all, none',
                                                      __FUNCTION__);
        if ($which_xdlp != 'none')
            $this->x_tick_label_pos == 'none';

        return TRUE;
    }

    /*!
     * Sets position for Y labels following data points.
     */
    function setYDataLabelPos($which_ydlp) 
    {
        $this->y_data_label_pos = $this->CheckOption($which_ydlp, 'plotleft, plotright, both, yaxis, all, none',
                                                      __FUNCTION__);
        if ($which_ydlp != 'none')
            $this->y_tick_label_pos == 'none';

        return TRUE;
    }


    /*!
     * Sets position for X labels following ticks (hence grid lines)
     */
    function setXTickLabelPos($which_xtlp) 
    {
        $this->x_tick_label_pos = $this->CheckOption($which_xtlp, 'plotdown, plotup, both, xaxis, all, none',
                                                      __FUNCTION__);
        if ($which_xtlp != 'none')
            $this->x_data_label_pos == 'none';

        return TRUE;
    }

    /*!
     * Sets position for Y labels following ticks (hence grid lines)
     */
    function setYTickLabelPos($which_ytlp) 
    {
        $this->y_tick_label_pos = $this->CheckOption($which_ytlp, 'plotleft, plotright, both, yaxis, all, none',
                                                      __FUNCTION__);
        if ($which_ytlp != 'none')
            $this->y_data_label_pos == 'none';

        return TRUE;
    }

    /*!
     * Sets type for tick and data labels on X axis.
     * \note 'title' type left for backwards compatibility.
     */
    function setXLabelType($which_xlt) 
    {
        $this->x_label_type = $this->CheckOption($which_xlt, 'data, time, title', __FUNCTION__);
        return TRUE;
    }

    /*!
     * Sets type for tick and data labels on Y axis.
     */
    function setYLabelType($which_ylt) 
    {
        $this->y_label_type = $this->CheckOption($which_ylt, 'data, time', __FUNCTION__);
        return TRUE;
    }

    function setXTimeFormat($which_xtf) 
    {
        $this->x_time_format = $which_xtf;
        return TRUE;
    }
    function setYTimeFormat($which_ytf) 
    {
        $this->y_time_format = $which_ytf;
        return TRUE;
    }

    function setXLabelAngle($which_xla) 
    {
        $this->x_label_angle = $which_xla;
        return TRUE;
    }

    function setYLabelAngle($which_yla)
    {
        $this->y_label_angle = $which_yla;
        return TRUE;
    }

/////////////////////////////////////////////
///////////                              MISC
/////////////////////////////////////////////

    /*!
     * ON THE WORKS:        XXX XXX XXX XXX
     *
     *****************                       __FUNCTION__ needs PHP 4.3.0
     *
     * Checks the valididy of an option.
     *  \param which_opt  String to check.
     *  \param which_acc  String of accepted choices.
     *  \param which_func Name of the calling function, for error messages.
     *  \note If checking everywhere for correctness slows things down, we could provide a
     *        child class overriding every Set...() method which uses CheckOption(). Those new
     *        methods could proceed in the unsafe but faster way.
     */
    function CheckOption($which_opt, $which_acc, $which_func)
    {
        $asked = trim($which_opt);

        // FIXME: this for backward compatibility, as eregi() fails with empty strings.
        if ($asked == '')
            return '';

        if (@eregi($asked, $which_acc)) {
            return $asked;
        } else {
            $this->DrawError("$which_func(): '$which_opt' not in available choices: '$which_acc'.");
            return NULL;
        } 
    }


    /*!
     *  \note Submitted by Thiemo Nagel
     */
    function setBrowserCache($which_browser_cache) 
    {
        $this->browser_cache = $which_browser_cache;
        return TRUE;
    }

    /*!
     * Whether to show the final image or not
     */
    function setPrintImage($which_pi) 
    {
        $this->print_image = $which_pi;
        return TRUE;
    }

    /*!
     * Sets the graph's legend. If argument is not an array, appends it to the legend.
     */
    function setLegend($which_leg)
    {
        if (is_array($which_leg)) {             // use array
            $this->legend = $which_leg;
            return TRUE;
        } else if (! is_null($which_leg)) {     // append string
            $this->legend[] = $which_leg;
            return TRUE;
        } else {
            $this->DrawError("SetLegend(): argument must not be null.");
            return FALSE;
        }
    }

    /*!
     * Specifies the absolute (relative to image's up/left corner) position
     * of the legend's upper/leftmost corner.
     *  $which_type not yet used (TODO)
     */
    function setLegendPixels($which_x, $which_y, $which_type=NULL) 
    { 
        $this->legend_x_pos = $which_x;
        $this->legend_y_pos = $which_y;

        return TRUE;
    }

    /*!
     * Specifies the relative (to graph's origin) position of the legend's
     * upper/leftmost corner. MUST be called after scales are set up.
     *   $which_type not yet used (TODO)
     */
    function setLegendWorld($which_x, $which_y, $which_type=NULL) 
    { 
        if (! $this->scale_is_set) 
            $this->CalcTranslation();

        $this->legend_x_pos = $this->xtr($which_x);
        $this->legend_y_pos = $this->ytr($which_y);

        return TRUE;
    }

    /*!
     * Accepted values are: left, sides, none, full
     */
    function setPlotBorderType($pbt) 
    {
        $this->plot_border_type = $this->CheckOption($pbt, 'left, sides, none, full', __FUNCTION__);
    }

    /*!
     * Accepted values are: raised, plain
     */
    function setImageBorderType($sibt) 
    {
        $this->image_border_type = $this->CheckOption($sibt, 'raised, plain', __FUNCTION__);
    }


    /*!
     * \param dpab bool
     */
    function setDrawPlotAreaBackground($dpab) 
    {
        $this->draw_plot_area_background = (bool)$dpab;
    }


    /*!
     * \param dyg bool 
     */
    function setDrawYGrid($dyg) 
    {
        $this->draw_y_grid = (bool)$dyg;
        return TRUE;
    }


    /*!
     * \param dxg bool
     */
    function setDrawXGrid($dxg) 
    {
        $this->draw_x_grid = (bool)$dxg;
        return TRUE;
    }


    /*!
     * \param ddg bool 
     */
    function setDrawDashedGrid($ddg) 
    {
        $this->dashed_grid = (bool)$ddg;
        return TRUE;
    }


    /*!
     * \param dxdl bool
     */
    function setDrawXDataLabelLines($dxdl)
    {
        $this->draw_x_data_label_lines = (bool)$dxdl;
        return TRUE;
    }

    
    /*!
     * TODO: draw_y_data_label_lines not implemented.
     * \param dydl bool
     */
    function setDrawYDataLabelLines($dydl)
    {
        $this->draw_y_data_label_lines = $dydl;
        return TRUE;
    }
    /*!
     * Sets the graph's title.
     */
    function setTitle($which_title) 
    {
        $this->title_txt = $which_title;

        if ($which_title == '') {
            $this->title_height = 0;
            return TRUE;
        }            

        $str = split("\n", $which_title);
        $lines = count($str);
        $spacing = $this->line_spacing * ($lines - 1);

        if ($this->use_ttf) {
            $size = $this->TTFBBoxSize($this->title_font['size'], 0, $this->title_font['font'], $which_title);
            $this->title_height = $size[1] * $lines;
        } else {
            $this->title_height = ($this->title_font['height'] + $spacing) * $lines;
        }   
        return TRUE;
    }

    /*!
     * Sets the X axis title and position.
     */
    function setXTitle($which_xtitle, $which_xpos = 'plotdown') 
    {
        if ($which_xtitle == '')
            $which_xpos = 'none';

        $this->x_title_pos = $this->CheckOption($which_xpos, 'plotdown, plotup, both, none', __FUNCTION__);

        $this->x_title_txt = $which_xtitle;

        $str = split("\n", $which_xtitle);
        $lines = count($str);
        $spacing = $this->line_spacing * ($lines - 1);

        if ($this->use_ttf) {
            $size = $this->TTFBBoxSize($this->x_title_font['size'], 0, $this->x_title_font['font'], $which_xtitle);
            $this->x_title_height = $size[1] * $lines;
        } else {
            $this->x_title_height = ($this->y_title_font['height'] + $spacing) * $lines;
        }

        return TRUE;
    }


    /*!
     * Sets the Y axis title and position.
     */
    function setYTitle($which_ytitle, $which_ypos = 'plotleft') 
    {
        if ($which_ytitle == '')
            $which_ypos = 'none';

        $this->y_title_pos = $this->CheckOption($which_ypos, 'plotleft, plotright, both, none', __FUNCTION__);

        $this->y_title_txt = $which_ytitle;

        $str = split("\n", $which_ytitle);
        $lines = count($str);
        $spacing = $this->line_spacing * ($lines - 1);

        if ($this->use_ttf) {
            $size = $this->TTFBBoxSize($this->y_title_font['size'], 90, $this->y_title_font['font'], 
                                       $which_ytitle);
            $this->y_title_width = $size[0] * $lines;
        } else {
            $this->y_title_width = ($this->y_title_font['height'] + $spacing) * $lines;
        }

        return TRUE;
    }

    /*!
     * Sets the size of the drop shadow for bar and pie charts.
     * \param which_s int Size in pixels.
     */
    function setShading($which_s) 
    { 
        $this->shading = (int)$which_s;
        return TRUE;
    }

    function setPlotType($which_pt) 
    {
        $this->plot_type = $this->CheckOption($which_pt, 
                                  'bars, lines, linepoints, area, points, pie, thinbarline, squared', 
                                  __FUNCTION__);
    }

    /*!
     * Sets the position of Y axis.
     * \param pos int Position in world coordinates. 
     */
    function setYAxisPosition($pos) 
    {
        $this->y_axis_position = (int)$pos;
        if (isset($this->scale_is_set)) {
            $this->CalcTranslation();
        }
        return TRUE;
    }
    
    /*!
     * Sets the position of X axis.
     * \param pos int Position in world coordinates. 
     */
    function setXAxisPosition($pos) 
    {
        $this->x_axis_position = (int)$pos;
        if (isset($this->scale_is_set)) {
            $this->CalcTranslation();
        }
        return TRUE;
    }


    function setXScaleType($which_xst) 
    { 
        $this->xscale_type = $this->CheckOption($which_xst, 'linear, log', __FUNCTION__);
        return TRUE;
    }

    function setYScaleType($which_yst) 
    { 
        $this->yscale_type = $this->CheckOption($which_yst, 'linear, log',  __FUNCTION__);
        return TRUE;
    }

    function setPrecisionX($which_prec) 
    {
        $this->x_precision = $which_prec;
        return TRUE;
    }
    function setPrecisionY($which_prec) 
    {
        $this->y_precision = $which_prec;
        return TRUE;
    }

    function setErrorBarLineWidth($which_seblw) 
    {
        $this->error_bar_line_width = $which_seblw;
        return TRUE;
    }

    function setLabelScalePosition($which_blp) 
    {
        //0 to 1
        $this->label_scale_position = $which_blp;
        return TRUE;
    }

    function setErrorBarSize($which_ebs) 
    {
        //in pixels
        $this->error_bar_size = $which_ebs;
        return TRUE;
    }

    /*!
     * Can be one of: 'tee', 'line'
     */
    function setErrorBarShape($which_ebs) 
    {
        $this->error_bar_shape = $this->CheckOption($which_ebs, 'tee, line', __FUNCTION__);
    }

    /*!
     * Can be one of: 'halfline', 'line', 'plus', 'cross', 'rect', 'circle', 'dot',
     * 'diamond', 'triangle', 'trianglemid'
     */
    function setPointShape($which_pt) 
    {
        $this->point_shape = $this->CheckOption($which_pt, 
                              'halfline, line, plus, cross, rect, circle, dot, diamond, triangle, trianglemid',
                              __FUNCTION__);
    }

    /*!
     * Sets the point size for point plots.
     * \param ps int Size in pixels.
     */
    function setPointSize($ps) 
    {
        $this->point_size = (int)$ps;

        if ($this->point_shape == 'diamond' or $this->point_shape == 'triangle') {
            if ($this->point_size % 2 != 0) {
                $this->point_size++;
            }
        }
        return TRUE;
    }


    /*!
     * Tells not to draw lines for missing Y data. Only works with 'lines' and 'squared' plots.
     * \param bl bool
     */
    function setDrawBrokenLines($bl)
    {
        $this->draw_broken_lines = (bool)$bl;
    }


    /*!
     *  text-data: ('label', y1, y2, y3, ...)
     *  text-data-pie: ('label', y1), for pie charts. See DrawPieChart()
     *  data-data: ('label', x, y1, y2, y3, ...)
     *  data-data-error: ('label', x1, y1, e1+, e2-, y2, e2+, e2-, y3, e3+, e3-, ...)
     */
    function setDataType($which_dt) 
    {
        //The next three lines are for past compatibility.
        if ($which_dt == 'text-linear') { $which_dt = 'text-data'; };
        if ($which_dt == 'linear-linear') { $which_dt = 'data-data'; };
        if ($which_dt == 'linear-linear-error') { $which_dt = 'data-data-error'; };

        $this->data_type = $this->CheckOption($which_dt, 'text-data, text-data-pie, data-data, data-data-error',
                                              __FUNCTION__);
        return TRUE;
    }

    /*!
     * Copy the array passed as data values. We convert to numerical indexes, for its
     * use for (or while) loops, which sometimes are faster. Performance improvements
     * vary from 28% in DrawLines() to 49% in DrawArea() for plot drawing functions.
     */
    function setDataValues(&$which_dv) 
    {
        $this->num_data_rows = count($which_dv);
        $this->total_records = 0;               // Perform some useful calculations.
        $this->records_per_group = 1;           
        for ($i = 0, $recs = 0; $i < $this->num_data_rows; $i++) {
            // Copy
            $this->data[$i] = array_values($which_dv[$i]);   // convert to numerical indices.

            // Compute some values
            $recs = count($this->data[$i]); 
            $this->total_records += $recs;

            if ($recs > $this->records_per_group)
                $this->records_per_group = $recs;

            $this->num_recs[$i] = $recs;
        }
    }

    /*!
     * Pad styles arrays for later use by plot drawing functions:
     * This removes the need for $max_data_colors, etc. and $color_index = $color_index % $max_data_colors
     * in DrawBars(), DrawLines(), etc.
     */
    function PadArrays()
    {
        array_pad_array($this->line_widths, $this->records_per_group);
        array_pad_array($this->line_styles, $this->records_per_group);

        array_pad_array($this->data_colors, $this->records_per_group);
        array_pad_array($this->data_border_colors, $this->records_per_group); 
        array_pad_array($this->error_bar_colors, $this->records_per_group);

        $this->SetDataColors();
        $this->SetDataBorderColors();
        $this->SetErrorBarColors();

        return TRUE;
    }


//////////////////////////////////////////////////////////
///////////         DATA ANALYSIS, SCALING AND TRANSLATION
//////////////////////////////////////////////////////////

    /*!
     * Analizes data and sets up internal maxima and minima
     * Needed by: CalcMargins(), ...
     *   Text-Data is different than data-data graphs. For them what
     *   we have, instead of X values, is # of records equally spaced on data.
     *   text-data is passed in as $data[] = (title, y1, y2, y3, y4, ...)
     *   data-data is passed in as $data[] = (title, x, y1, y2, y3, y4, ...) 
     */
    function FindDataLimits() 
    {
        // Set some default min and max values before running through the data
        switch ($this->data_type) {
        case 'text-data':
            $minx = 0;
            $maxx = $this->num_data_rows - 1 ;
            $miny = $this->data[0][1];
            $maxy = $miny;
            break;
        default:  //Everything else: data-data, etc, take first value
            $minx = $this->data[0][1];
            $maxx = $minx;
            $miny = $this->data[0][2];
            $maxy = $miny;
            break;
        }
        
        $mine = 0;  // Maximum value for the -error bar (assume error bars always > 0) 
        $maxe = 0;  // Maximum value for the +error bar (assume error bars always > 0) 
        $maxt = 0;  // Maximum number of characters in text labels
        
        $minminy = $miny;
        $maxmaxy = $maxy;
        // Process each row of data
        for ($i=0; $i < $this->num_data_rows; $i++) {
            $j=0;
            // Extract maximum text label length
            $val = @strlen($this->data[$i][$j++]);
            $maxt = ($val > $maxt) ? $val : $maxt;

            switch ($this->data_type) {
            case 'text-data':           // Data is passed in as (title, y1, y2, y3, ...)
            case 'text-data-pie':       // This one is for some pie charts, see DrawPieChart()
                // $numrecs = @count($this->data[$i]);
                $miny = $maxy = (double)$this->data[$i][$j];
                for (; $j < $this->num_recs[$i]; $j++) {
                    $val = (double)$this->data[$i][$j];
                    $maxy = ($val > $maxy) ? $val : $maxy;
                    $miny = ($val < $miny) ? $val : $miny;
                }
                break;
            case 'data-data':           // Data is passed in as (title, x, y, y2, y3, ...) 
                // X value:
                $val = (double)$this->data[$i][$j++];
                $maxx = ($val > $maxx) ? $val : $maxx;
                $minx = ($val < $minx) ? $val : $minx;
                
                $miny = $maxy = (double)$this->data[$i][$j];
                // $numrecs = @count($this->data[$i]);
                for (; $j < $this->num_recs[$i]; $j++) {
                    $val = (double)$this->data[$i][$j];
                    $maxy = ($val > $maxy) ? $val : $maxy;
                    $miny = ($val < $miny) ? $val : $miny;
                }
                break;
            case 'data-data-error':     // Data is passed in as (title, x, y, err+, err-, y2, err2+, err2-,...)
                // X value:
                $val = (double)$this->data[$i][$j++];
                $maxx = ($val > $maxx) ? $val : $maxx;
                $minx = ($val < $minx) ? $val : $minx;

                $miny = $maxy = (double)$this->data[$i][$j];
                // $numrecs = @count($this->data[$i]);
                for (; $j < $this->num_recs[$i];) {
                    // Y value:
                    $val = (double)$this->data[$i][$j++];
                    $maxy = ($val > $maxy) ? $val : $maxy;
                    $miny = ($val < $miny) ? $val : $miny;
                    // Error +:
                    $val = (double)$this->data[$i][$j++];
                    $maxe = ($val > $maxe) ? $val : $maxe;
                    // Error -:
                    $val = (double)$this->data[$i][$j++];
                    $mine = ($val > $mine) ? $val : $mine;
                }
                $maxy = $maxy + $maxe;
                $miny = $miny - $mine;      // assume error bars are always > 0
                break;
            default:
                $this->PrintError("FindDataLimits(): Unknown data type '$data_type'.");
            break;
            }
            $this->data[$i][MINY] = $miny;      // This row's min Y, for DrawXDataLine()
            $this->data[$i][MAXY] = $maxy;      // This row's max Y, for DrawXDataLine()
            $minminy = ($miny < $minminy) ? $miny : $minminy;   // global min
            $maxmaxy = ($maxy > $maxmaxy) ? $maxy : $maxmaxy;   // global max
        }

        $this->min_x = $minx;
        $this->max_x = $maxx;
        $this->min_y = $minminy;
        $this->max_y = $maxmaxy;
        $this->max_t = $maxt;

        $this->data_limits_done = TRUE;
        
        return TRUE;
    }


    /*!
     * Calculates image margins on the fly from title positions and sizes,
     * and tick labels positions and sizes.
     *
     * FIXME: fix x_data_label_pos behaviour. Now we are leaving room for it AND x_tick_label_pos
     *        maybe it shouldn't be so...
     *
     * TODO: add x_tick_label_width and y_tick_label_height and use them to calculate
     *       max_x_labels and max_y_labels, to be used by drawing functions t avoid overlapping.
     */
    function CalcMargins() 
    {
        // Temporary variables for label size calculation
        $xlab = $this->FormatLabel('x', $this->max_x);
        $ylab = $this->FormatLabel('y', $this->max_y);

        //////// Calculate maximum X/Y axis label height and width:

        // TTFonts:
        if ($this->use_ttf) {
            // Maximum X axis label height
            $size = $this->TTFBBoxSize($this->x_label_font['size'], $this->x_label_angle,
                                       $this->x_label_font['font'], $xlab);
            $this->x_tick_label_height = $size[1];

            // Maximum Y axis label width
            $size = $this->TTFBBoxSize($this->y_label_font['size'], $this->y_label_angle, 
                                        $this->y_label_font['font'], $ylab);
            $this->y_tick_label_width = $size[0];
        }
        // Fixed fonts:
        else { 
            // Maximum X axis label height
            if ($this->x_label_angle == 90)
                $this->x_tick_label_height = strlen($xlab) * $this->x_label_font['width'];
            else 
                $this->x_tick_label_height = $this->x_label_font['height'];

            // Maximum Y axis label width
            $this->y_tick_label_width = strlen($ylab) * $this->y_label_font['width'];
        }


        ///////// Calculate margins:

        // Upper title, ticks and tick labels, and data labels:
        $this->y_top_margin = $this->title_height + $this->safe_margin * 2;

        if ($this->x_title_pos == 'plotup' || $this->x_title_pos == 'both')
            $this->y_top_margin += $this->x_title_height + $this->safe_margin;

        if ($this->x_tick_label_pos == 'plotup' || $this->x_tick_label_pos == 'both')
            $this->y_top_margin += $this->x_tick_label_height;

        if ($this->x_tick_pos == 'plotup' || $this->x_tick_pos == 'both')
            $this->y_top_margin += $this->x_tick_length * 2;

        if ($this->x_data_label_pos == 'plotup' || $this->x_data_label_pos == 'both')
            $this->y_top_margin += $this->x_tick_label_height;

        // Lower title, ticks and tick labels, and data labels:
        $this->y_bot_margin = $this->safe_margin * 2; 

        if ($this->x_title_pos == 'plotdown' || $this->x_title_pos == 'both')
            $this->y_bot_margin += $this->x_title_height;

        if ($this->x_tick_pos == 'plotdown' || $this->x_tick_pos == 'both')
            $this->y_bot_margin += $this->x_tick_length * 2;

        if ($this->x_tick_pos == 'xaxis' && ($this->x_axis_position == '' || $this->x_axis_position == 0))
            $this->y_bot_margin += $this->x_tick_length * 2;

        if ($this->x_tick_label_pos == 'plotdown' || $this->x_tick_label_pos == 'both')            
            $this->y_bot_margin += $this->x_tick_label_height;

        if ($this->x_tick_label_pos == 'xaxis' && ($this->x_axis_position == '' || $this->x_axis_position == 0))
            $this->y_bot_margin += $this->x_tick_label_height;

        if ($this->x_data_label_pos == 'plotdown' || $this->x_data_label_pos == 'both')
            $this->y_bot_margin += $this->x_tick_label_height;

        // Left title, ticks and tick labels:
        $this->x_left_margin = $this->safe_margin * 2;

        if ($this->y_title_pos == 'plotleft' || $this->y_title_pos == 'both')
            $this->x_left_margin += $this->y_title_width + $this->safe_margin;

        if ($this->y_tick_label_pos == 'plotleft' || $this->y_tick_label_pos == 'both')
            $this->x_left_margin += $this->y_tick_label_width;

        if ($this->y_tick_pos == 'plotleft' || $this->y_tick_pos == 'both')
            $this->x_left_margin += $this->y_tick_length * 2 ;

        // Right title, ticks and tick labels:
        $this->x_right_margin = $this->safe_margin * 2;

        if ($this->y_title_pos == 'plotright' || $this->y_title_pos == 'both')
            $this->x_right_margin += $this->y_title_width + $this->safe_margin;

        if ($this->y_tick_label_pos == 'plotright' || $this->y_tick_label_pos == 'both')
            $this->x_right_margin += $this->y_tick_label_width;

        if ($this->y_tick_pos == 'plotright' || $this->y_tick_pos == 'both')
            $this->x_right_margin += $this->y_tick_length * 2;


        $this->x_tot_margin = $this->x_left_margin + $this->x_right_margin;
        $this->y_tot_margin = $this->y_top_margin + $this->y_bot_margin;

        return;
    }


    /*!
     * Set the margins in pixels (left, right, top, bottom)
     */
    function setMarginsPixels($which_lm, $which_rm, $which_tm, $which_bm) 
    { 

        $this->x_left_margin = $which_lm;
        $this->x_right_margin = $which_rm;
        $this->x_tot_margin = $which_lm + $which_rm;

        $this->y_top_margin = $which_tm;
        $this->y_bot_margin = $which_bm;
        $this->y_tot_margin = $which_tm + $which_bm;

        $this->SetPlotAreaPixels();

        return;
    }


    /*!
     * Sets the limits for the plot area. If no arguments are supplied, uses
     * values calculated from _CalcMargins();
     * Like in GD, (0,0) is upper left
     *
     * This resets the scale if SetPlotAreaWorld() was already called
     */
    function setPlotAreaPixels($x1=NULL, $y1=NULL, $x2=NULL, $y2=NULL) 
    {
        if ($x2 && $y2) {
            $this->plot_area = array($x1, $y1, $x2, $y2);
        } else {
            if (! isset($this->x_tot_margin))
                $this->CalcMargins();

            $this->plot_area = array($this->x_left_margin, $this->y_top_margin,
                                     $this->image_width - $this->x_right_margin,
                                     $this->image_height - $this->y_bot_margin);
        }
        $this->plot_area_width = $this->plot_area[2] - $this->plot_area[0];
        $this->plot_area_height = $this->plot_area[3] - $this->plot_area[1];

        // Reset the scale with the new plot area.
        if (isset($this->plot_max_x))
            $this->CalcTranslation();

        return TRUE;

    }


    /*!
     * Sets minimum and maximum x and y values in the plot using FindDataLimits()
     * or from the supplied parameters, if any.
     *
     * This resets the scale if SetPlotAreaPixels() was already called
     */
    function setPlotAreaWorld($xmin=NULL, $ymin=NULL, $xmax=NULL, $ymax=NULL) 
    {
        if ((! $xmin)  && (! $xmax) ) {
            // For automatic setting of data we need data limits
            if (! isset($this->data_limits_done)) {
                $this->FindDataLimits() ;
            }
            if ($this->data_type == 'text-data') {
                $xmax = $this->max_x + 1 ;  // valid for BAR CHART TYPE GRAPHS ONLY
                $xmin = 0 ;                 // valid for BAR CHART TYPE GRAPHS ONLY
            } else {
                $xmax = $this->max_x;
                $xmin = $this->min_x;
            }

            $ymax = ceil($this->max_y * 1.1);
            if ($this->min_y < 0) {
                $ymin = floor($this->min_y * 1.1);
            } else {
                $ymin = 0;
            }
        }

        $this->plot_min_x = $xmin;
        $this->plot_max_x = $xmax;

        if ($ymin == $ymax) {
            $ymax += 1;
        }
        if ($this->yscale_type == 'log') { 
            //extra error checking
            if ($ymin <= 0) { 
                $ymin = 1;
            }
            if ($ymax <= 0) {
                $this->PrintError('SetPlotAreaWorld(): Log plots need data greater than 0');
                return FALSE;
            }
        }

        $this->plot_min_y = $ymin;
        $this->plot_max_y = $ymax;

        if ($ymax <= $ymin) {
            $this->DrawError('SetPlotAreaWorld(): Error in data - max not greater than min');
            return FALSE;
        }

        // Reset the scale with the new maxs and mins
        if (isset($this->plot_area_width)) {
            $this->CalcTranslation();
        }

        return TRUE;
    } //function setPlotAreaWorld


    /*!
     * For plots that have equally spaced x variables and multiple bars per x-point.
     */
    function setEqualXCoord() 
    {
        $space = ($this->plot_area[2] - $this->plot_area[0]) / 
                 ($this->num_data_rows * 2) * $this->group_frac_width;
        $group_width = $space * 2;
        $bar_width = $group_width / $this->records_per_group;
        //I think that eventually this space variable will be replaced by just graphing x.
        $this->data_group_space = $space;
        $this->record_bar_width = $bar_width;
        return TRUE;
    }

    /*!
     * Calculates scaling stuff...
     */
    function CalcTranslation() 
    {
        if ($this->xscale_type == 'log') { 
            $this->xscale = ($this->plot_area_width)/(log10($this->plot_max_x) - log10($this->plot_min_x));
        } else { 
            $this->xscale = ($this->plot_area_width)/($this->plot_max_x - $this->plot_min_x);
        }
        if ($this->yscale_type == 'log') { 
            $this->yscale = ($this->plot_area_height)/(log10($this->plot_max_y) - log10($this->plot_min_y));
        } else { 
            $this->yscale = ($this->plot_area_height)/($this->plot_max_y - $this->plot_min_y);
        }

        // GD defines x = 0 at left and y = 0 at TOP so -/+ respectively
        if ($this->xscale_type == 'log') { 
            $this->plot_origin_x = $this->plot_area[0] - ($this->xscale * log10($this->plot_min_x) );
        } else { 
            $this->plot_origin_x = $this->plot_area[0] - ($this->xscale * $this->plot_min_x);
        }
        if ($this->yscale_type == 'log') { 
            $this->plot_origin_y = $this->plot_area[3] + ($this->yscale * log10($this->plot_min_y));
        } else { 
            $this->plot_origin_y = $this->plot_area[3] + ($this->yscale * $this->plot_min_y);
        }

        $this->scale_is_set = TRUE;

        /************** FIXME?? *************/
        // There should be a better place for this.

        // User provided y axis position?
        if ($this->y_axis_position != '') { 
            // Make sure we draw our axis inside the plot
            $this->y_axis_position = ($this->y_axis_position < $this->plot_min_x) 
                                     ? $this->plot_min_x : $this->y_axis_position;
            $this->y_axis_position = ($this->y_axis_position > $this->plot_max_x) 
                                     ? $this->plot_max_x : $this->y_axis_position;
            $this->y_axis_x_pixels = $this->xtr($this->y_axis_position);
        } else { 
            // Default to left axis
            $this->y_axis_x_pixels = $this->xtr($this->plot_min_x);
        }
        // User provided x axis position?
        if ($this->x_axis_position != '') { 
            // Make sure we draw our axis inside the plot
            $this->x_axis_position = ($this->x_axis_position < $this->plot_min_y) 
                                     ? $this->plot_min_y : $this->x_axis_position;
            $this->x_axis_position = ($this->x_axis_position > $this->plot_max_y) 
                                     ? $this->plot_max_y : $this->x_axis_position;
            $this->x_axis_y_pixels = $this->ytr($this->x_axis_position);
        } else { 
            if ($this->yscale_type == 'log')
                $this->x_axis_y_pixels = $this->ytr(1);
            else 
                // Default to axis at 0 or plot_min_y (should be 0 anyway, from SetPlotAreaWorld())
                $this->x_axis_y_pixels = ($this->plot_min_y <= 0) && (0 <= $this->plot_max_y) 
                                         ? $this->ytr(0) : $this->ytr($this->plot_min_y);
        }

    } // function CalcTranslation()


    /*!
     * Translate X world coordinate into pixel coordinate
     * Needs values calculated by _CalcTranslation()
     */
    function xtr($x_world) 
    {
        //$x_pixels =  $this->x_left_margin + ($this->image_width - $this->x_tot_margin)*
        //      (($x_world - $this->plot_min_x) / ($this->plot_max_x - $this->plot_min_x)) ;
        //which with a little bit of math reduces to ...
        if ($this->xscale_type == 'log') { 
            $x_pixels = $this->plot_origin_x + log10($x_world) * $this->xscale ;
        } else { 
            $x_pixels = $this->plot_origin_x + $x_world * $this->xscale ;
        }
        return round($x_pixels);
    }


    /*!
     * Translate Y world coordinate into pixel coordinate.
     * Needs values calculated by _CalcTranslation()
     */
    function ytr($y_world) 
    {
        if ($this->yscale_type == 'log') { 
            //minus because GD defines y = 0 at top. doh!
            $y_pixels =  $this->plot_origin_y - log10($y_world) * $this->yscale ;  
        } else { 
            $y_pixels =  $this->plot_origin_y - $y_world * $this->yscale ;  
        }
        return round($y_pixels);
    }

    /*!
     * Formats a tick or data label.
     *
     * \note Time formatting suggested by Marlin Viss
     */
    function FormatLabel($which_pos, $which_lab) 
    { 
        switch ($which_pos) {
        case 'x':
        case 'plotx':
            switch ($this->x_label_type) {
            case 'title':
                $lab = $this->data[$which_lab][0];
                break;
            case 'data':
                $lab = number_format($which_lab, $this->x_precision, '.', ', ').$this->data_units_text;
                break;
            case 'time':
                $lab = strftime($this->x_time_format, $which_lab);
                break;
            default:
                // Unchanged from whatever format it is passed in
                $lab = $which_lab;
            break;
            }    
            break;
        case 'y':
        case 'ploty':
            switch ($this->y_label_type) {
            case 'data':
                $lab = number_format($which_lab, $this->y_precision, '.', ', ').$this->data_units_text;
                break;
            case 'time':
                $lab = strftime($this->y_time_format, $which_lab);
                break;
            default:
                // Unchanged from whatever format it is passed in
                $lab = $which_lab;
                break;
            }
            break;
        default:
            $this->PrintError("FormatLabel(): Unknown label type $which_type");
            return NULL;
        } 

        return $lab;
    } //function FormatLabel



/////////////////////////////////////////////    
///////////////                         TICKS
/////////////////////////////////////////////    

    /*!
     * Use either this or SetNumXTicks() to set where to place x tick marks
     */
    function setXTickIncrement($which_ti=NULL) 
    {
        if ($which_ti) {
            $this->x_tick_increment = $which_ti;  //world coordinates
        } else {
            if (! isset($this->data_limits_done)) {
                $this->FindDataLimits();  //Get maxima and minima for scaling
            }
            //$this->x_tick_increment = ( ceil($this->max_x * 1.2) - floor($this->min_x * 1.2) )/10;
            $this->x_tick_increment =  ($this->plot_max_x  - $this->plot_min_x  )/10;
        }
        $this->num_x_ticks = ''; //either use num_y_ticks or y_tick_increment, not both
        return TRUE;
    }

    /*!
     * Use either this or SetNumYTicks() to set where to place y tick marks
     */
    function setYTickIncrement($which_ti=NULL) 
    {
        if ($which_ti) {
            $this->y_tick_increment = $which_ti;  //world coordinates
        } else {
            if (! isset($this->data_limits_done)) {
                $this->FindDataLimits();  //Get maxima and minima for scaling
            }
            if (! isset($this->plot_max_y))
                $this->SetPlotAreaWorld();

            //$this->y_tick_increment = ( ceil($this->max_y * 1.2) - floor($this->min_y * 1.2) )/10;
            $this->y_tick_increment =  ($this->plot_max_y  - $this->plot_min_y  )/10;
        }
        $this->num_y_ticks = ''; //either use num_y_ticks or y_tick_increment, not both
        return TRUE;
    }


    function setNumXTicks($which_nt) 
    {
        $this->num_x_ticks = $which_nt;
        $this->x_tick_increment = '';  //either use num_x_ticks or x_tick_increment, not both
        return TRUE;
    }

    function setNumYTicks($which_nt) 
    {
        $this->num_y_ticks = $which_nt;
        $this->y_tick_increment = '';  //either use num_y_ticks or y_tick_increment, not both
        return TRUE;
    }

    /*!
     *
     */
    function setYTickPos($which_tp) 
    { 
        $this->y_tick_pos = $this->CheckOption($which_tp, 'plotleft, plotright, both, yaxis, none', __FUNCTION__);
        return TRUE;
    }
    /*!
     *
     */
    function setXTickPos($which_tp) 
    { 
        $this->x_tick_pos = $this->CheckOption($which_tp, 'plotdown, plotup, both, xaxis, none', __FUNCTION__); 
        return TRUE;
    }

    /*!
     * \param skip bool
     */ 
    function setSkipTopTick($skip)
    {
        $this->skip_top_tick = (bool)$skip;
        return TRUE;
    }

    /*!
     * \param skip bool
     */
    function setSkipBottomTick($skip) 
    {
        $this->skip_bottom_tick = (bool)$skip;
        return TRUE;
    }

    function setXTickLength($which_xln) 
    {
        $this->x_tick_length = $which_xln;
        return TRUE;
    }

    function setYTickLength($which_yln) 
    {
        $this->y_tick_length = $which_yln;
        return TRUE;
    }

    function setXTickCrossing($which_xc) 
    {
        $this->x_tick_cross = $which_xc;
        return TRUE;
    }

    function setYTickCrossing($which_yc) 
    {
        $this->y_tick_cross = $which_yc;
        return TRUE;
    }


/////////////////////////////////////////////
////////////////////          GENERIC DRAWING
/////////////////////////////////////////////

    /*!
     * Fills the background of the image with a solid color
     */
    function DrawBackground() 
    {
        if (! $this->background_done) {     //Don't draw it twice if drawing two plots on one image
            ImageFilledRectangle($this->img, 0, 0, $this->image_width, $this->image_height, 
                                 $this->ndx_bg_color);
            $this->background_done = TRUE;
        }
        return TRUE;
    }

    /*!
     * Draws a border around the final image.
     */
    function DrawImageBorder() 
    {
        switch ($this->image_border_type) {
        case 'raised':
            ImageLine($this->img, 0, 0, $this->image_width-1, 0, $this->ndx_i_border);
            ImageLine($this->img, 1, 1, $this->image_width-2, 1, $this->ndx_i_border);
            ImageLine($this->img, 0, 0, 0, $this->image_height-1, $this->ndx_i_border);
            ImageLine($this->img, 1, 1, 1, $this->image_height-2, $this->ndx_i_border);
            ImageLine($this->img, $this->image_width-1, 0, $this->image_width-1,
                      $this->image_height-1, $this->ndx_i_border_dark);
            ImageLine($this->img, 0, $this->image_height-1, $this->image_width-1,
                      $this->image_height-1, $this->ndx_i_border_dark);
            ImageLine($this->img, $this->image_width-2, 1, $this->image_width-2,
                      $this->image_height-2, $this->ndx_i_border_dark);
            ImageLine($this->img, 1, $this->image_height-2, $this->image_width-2,
                      $this->image_height-2, $this->ndx_i_border_dark);
            break;
        case 'plain':
            ImageLine($this->img, 0, 0, $this->image_width, 0, $this->ndx_i_border_dark);
            ImageLine($this->img, $this->image_width-1, 0, $this->image_width-1,
                      $this->image_height, $this->ndx_i_border_dark);
            ImageLine($this->img, $this->image_width-1, $this->image_height-1, 0, $this->image_height-1,
                      $this->ndx_i_border_dark);
            ImageLine($this->img, 0, 0, 0, $this->image_height, $this->ndx_i_border_dark);
            break;
        case 'none':
            break;
        default:
            $this->DrawError("DrawImageBorder(): unknown image_border_type: '$this->image_border_type'");
            return FALSE;
        }
        return TRUE;
    }


    /*!
     * Adds the title to the graph.
     */
    function DrawTitle() 
    {
        // Center of the plot area
        //$xpos = ($this->plot_area[0] + $this->plot_area_width )/ 2;

        // Center of the image:
        $xpos = $this->image_width / 2;

        // Place it at almost at the top
        $ypos = $this->safe_margin;

        $this->DrawText($this->title_font, $this->title_angle, $xpos, $ypos,
                        $this->ndx_title_color, $this->title_txt, 'center', 'bottom'); 

        return TRUE; 

    }


    /*!
     * Draws the X-Axis Title
     */
    function DrawXTitle() 
    {
        if ($this->x_title_pos == 'none')
            return;

        // Center of the plot
        $xpos = ($this->plot_area[2] + $this->plot_area[0]) / 2;

        // Upper title
        if ($this->x_title_pos == 'plotup' || $this->x_title_pos == 'both') {
            $ypos = $this->safe_margin + $this->title_height + $this->safe_margin;
            $this->DrawText($this->x_title_font, $this->x_title_angle,
                            $xpos, $ypos, $this->ndx_title_color, $this->x_title_txt, 'center');
        }
        // Lower title
        if ($this->x_title_pos == 'plotdown' || $this->x_title_pos == 'both') {
            $ypos = $this->image_height - $this->x_title_height - $this->safe_margin;
            $this->DrawText($this->x_title_font, $this->x_title_angle,
                            $xpos, $ypos, $this->ndx_title_color, $this->x_title_txt, 'center');
        } 
        return TRUE;
    }

    /*!
     * Draws the Y-Axis Title
     */
    function DrawYTitle() 
    {
        if ($this->y_title_pos == 'none')
            return;

        // Center the title vertically to the plot
        $ypos = ($this->plot_area[3] + $this->plot_area[1]) / 2;

        if ($this->y_title_pos == 'plotleft' || $this->y_title_pos == 'both') {
            $xpos = $this->safe_margin;
            $this->DrawText($this->y_title_font, 90, $xpos, $ypos, $this->ndx_title_color, 
                            $this->y_title_txt, 'left', 'center');
        }
        if ($this->y_title_pos == 'plotright' || $this->y_title_pos == 'both') {
            $xpos = $this->image_width - $this->safe_margin - $this->y_title_width - $this->safe_margin;
            $this->DrawText($this->y_title_font, 90, $xpos, $ypos, $this->ndx_title_color, 
                            $this->y_title_txt, 'left', 'center');
        }

        return TRUE;
    }


    /*!
     * Fills the plot area with a solid color
     */
    function DrawPlotAreaBackground() 
    {
        ImageFilledRectangle($this->img, $this->plot_area[0], $this->plot_area[1], 
                             $this->plot_area[2], $this->plot_area[3],
                             $this->ndx_plot_bg_color);
        return TRUE;
    }

    /*
     * \note Horizontal grid lines overwrite horizontal axis with y=0, so call this first, then DrawXAxis()
     */
    function DrawYAxis() 
    {
        // Draw ticks, labels and grid, if any
        $this->DrawYTicks();

        // Draw Y axis at X = y_axis_x_pixels
        ImageLine($this->img, $this->y_axis_x_pixels, $this->plot_area[1], 
                  $this->y_axis_x_pixels, $this->plot_area[3], $this->ndx_grid_color);
                  
        return TRUE;
    }

    /*
     *
     */
    function DrawXAxis() 
    {
        // Draw ticks, labels and grid
        $this->DrawXTicks();

        //Draw Tick and Label for Y axis
        if (! $this->skip_bottom_tick) { 
            $ylab =$this->FormatLabel('y', $this->x_axis_position);
            $this->DrawYTick($ylab, $this->x_axis_y_pixels);
        }

        //Draw X Axis at Y = x_axis_y_pixels
        ImageLine($this->img, $this->plot_area[0]+1, $this->x_axis_y_pixels,
                  $this->plot_area[2]-1, $this->x_axis_y_pixels, $this->ndx_grid_color);

        return TRUE;
    }

    /*!
     * Draw Just one Tick, called from DrawYTicks() and DrawXAxis()
     * TODO? Move this inside DrawYTicks() and Modify DrawXAxis() ?
     */
    function DrawYTick($which_ylab, $which_ypix) 
    {
        // Ticks on Y axis 
        if ($this->y_tick_pos == 'yaxis') { 
            ImageLine($this->img, $this->y_axis_x_pixels - $this->y_tick_length, $which_ypix, 
                      $this->y_axis_x_pixels + $this->y_tick_cross, $which_ypix, 
                      $this->ndx_tick_color);
        }

        // Labels on Y axis
        if ($this->y_tick_label_pos == 'yaxis') {
            $this->DrawText($this->y_label_font, $this->y_label_angle,
                            $this->y_axis_x_pixels - $this->y_tick_length * 1.5, $which_ypix, 
                            $this->ndx_text_color, $which_ylab, 'right', 'center');
        }

        // Ticks to the left of the Plot Area
        if (($this->y_tick_pos == 'plotleft') || ($this->y_tick_pos == 'both') ) { 
            ImageLine($this->img, $this->plot_area[0] - $this->y_tick_length,
                      $which_ypix, $this->plot_area[0] + $this->y_tick_cross,
                      $which_ypix, $this->ndx_tick_color);
        }

        // Ticks to the right of the Plot Area
        if (($this->y_tick_pos == 'plotright') || ($this->y_tick_pos == 'both') ) { 
            ImageLine($this->img, ($this->plot_area[2] + $this->y_tick_length),
                      $which_ypix, $this->plot_area[2] - $this->y_tick_cross,
                      $which_ypix, $this->ndx_tick_color);
        }

        // Labels to the left of the plot area
        if ($this->y_tick_label_pos == 'plotleft' || $this->y_tick_label_pos == 'both') {
            $this->DrawText($this->y_label_font, $this->y_label_angle, 
                            $this->plot_area[0] - $this->y_tick_length * 1.5, $which_ypix, 
                            $this->ndx_text_color, $which_ylab, 'right', 'center');
        }
        // Labels to the right of the plot area
        if ($this->y_tick_label_pos == 'plotright' || $this->y_tick_label_pos == 'both') {
            $this->DrawText($this->y_label_font, $this->y_label_angle, 
                            $this->plot_area[2] + $this->y_tick_length * 1.5, $which_ypix, 
                            $this->ndx_text_color, $which_ylab, 'left', 'center');
        }
   } // Function DrawYTick()


    /*!
     * Draws Grid, Ticks and Tick Labels along Y-Axis
     * Ticks and ticklabels can be left of plot only, right of plot only, 
     * both on the left and right of plot, or crossing a user defined Y-axis
     */
    function DrawYTicks() 
    {
        // Sets the line style for IMG_COLOR_STYLED lines (grid)
        if ($this->dashed_grid) {
            $this->SetDashedStyle($this->ndx_light_grid_color);
            $style = IMG_COLOR_STYLED;
        } else {
            $style = $this->ndx_light_grid_color;
        }

        // maxy is always > miny so delta_y is always positive
        if ($this->y_tick_increment) {
            $delta_y = $this->y_tick_increment;
        } elseif ($this->num_y_ticks) {
            $delta_y = ($this->plot_max_y - $this->plot_min_y) / $this->num_y_ticks;
        } else {
            $delta_y = ($this->plot_max_y - $this->plot_min_y) / 10 ;
        }

        // NOTE: When working with floats, because of approximations when adding $delta_y, 
        // $y_tmp never equals $y_end  at the for loop, so one spurious line would  get drawn where 
        // not for the substraction to $y_end here.
        $y_tmp = (double)$this->plot_min_y;
        $y_end = (double)$this->plot_max_y - ($delta_y/2);

        if ($this->skip_bottom_tick)
            $y_tmp += $delta_y;

        if ($this->skip_top_tick)
            $y_end -= $delta_y;

        for (;$y_tmp < $y_end; $y_tmp += $delta_y) {
            $ylab = $this->FormatLabel('y', $y_tmp);
            $y_pixels = $this->ytr($y_tmp);

            // Horizontal grid line
            if ($this->draw_y_grid) {
                ImageLine($this->img, $this->plot_area[0]+1, $y_pixels, $this->plot_area[2]-1, $y_pixels, $style);
            }

            // Draw ticks
            $this->DrawYTick($ylab, $y_pixels);
        }
        return TRUE;
    } // function DrawYTicks


    /*!
     * Draws Grid, Ticks and Tick Labels along X-Axis
     * Ticks and tick labels can be down of plot only, up of plot only, 
     * both on up and down of plot, or crossing a user defined X-axis 
     *
     * \note Original vertical code submitted by Marlin Viss
     */
    function DrawXTicks() 
    {
        // Sets the line style for IMG_COLOR_STYLED lines (grid)
        if ($this->dashed_grid) {
            $this->SetDashedStyle($this->ndx_light_grid_color);
            $style = IMG_COLOR_STYLED;
        } else {
            $style = $this->ndx_light_grid_color;
        }

        // Calculate x increment between ticks
        if ($this->x_tick_increment) {
            $delta_x = $this->x_tick_increment;
        } elseif ($this->num_x_ticks) {
            $delta_x = ($this->plot_max_x - $this->plot_min_x) / $this->num_x_ticks;
        } else {
            $delta_x =($this->plot_max_x - $this->plot_min_x) / 10 ;
        }

        // NOTE: When working with decimals, because of approximations when adding $delta_x, 
        // $x_tmp never equals $x_end  at the for loop, so one spurious line would  get drawn where 
        // not for the substraction to $x_end here.
        $x_tmp = (double)$this->plot_min_x;
        $x_end = (double)$this->plot_max_x - ($delta_x/2);

        // Should the leftmost tick be drawn?
        if ($this->skip_left_tick)
            $x_tmp += $delta_x;

        // And the rightmost?
        if (! $this->skip_right_tick)
            $x_end += $delta_x;

        for (;$x_tmp < $x_end; $x_tmp += $delta_x) { 
            $xlab = $this->FormatLabel('x', $x_tmp);
            $x_pixels = $this->xtr($x_tmp);

            // Vertical grid lines
            if ($this->draw_x_grid) {
                ImageLine($this->img, $x_pixels, $this->plot_area[1], $x_pixels, $this->plot_area[3], $style);
            }

            // Tick on X Axis 
            if ($this->x_tick_pos == 'xaxis') { 
                ImageLine($this->img, $x_pixels, $this->x_axis_y_pixels - $this->x_tick_cross, 
                          $x_pixels, $this->x_axis_y_pixels + $this->x_tick_length, $this->ndx_tick_color);
            }

            // Label on X axis
            if ($this->x_tick_label_pos == 'xaxis') {
                 $this->DrawText($this->x_label_font, $this->x_label_angle, $x_pixels, 
                                $this->x_axis_y_pixels + $this->x_tick_length*1.5, $this->ndx_text_color, 
                                $xlab, 'center', 'bottom');
            }              

            // Top of the plot area tick
            if ($this->x_tick_pos == 'plotup' || $this->x_tick_pos == 'both') {
                ImageLine($this->img, $x_pixels, $this->plot_area[1] - $this->x_tick_length,
                          $x_pixels, $this->plot_area[1] + $this->x_tick_cross, $this->ndx_tick_color);
            }
            // Bottom of the plot area tick
            if ($this->x_tick_pos == 'plotdown' || $this->x_tick_pos == 'both') {
                ImageLine($this->img, $x_pixels, $this->plot_area[3] + $this->x_tick_length,
                          $x_pixels, $this->plot_area[3] - $this->x_tick_cross, $this->ndx_tick_color);
            }

            // Top of the plot area tick label
            if ($this->x_tick_label_pos == 'plotup' || $this->x_tick_label_pos == 'both') {
                $this->DrawText($this->x_label_font, $this->x_label_angle, $x_pixels, 
                                $this->plot_area[1] - $this->x_tick_length*1.5, $this->ndx_text_color, 
                                $xlab, 'center', 'top');
            }

            // Bottom of the plot area tick label
            if ($this->x_tick_label_pos == 'plotdown' || $this->x_tick_label_pos == 'both') {
                $this->DrawText($this->x_label_font, $this->x_label_angle, $x_pixels, 
                                $this->plot_area[3] + $this->x_tick_length*1.5, $this->ndx_text_color, 
                                $xlab, 'center', 'bottom');
            }
        }
        return;
    } // function DrawXTicks


    /*!
     * 
     */
    function DrawPlotBorder() 
    {
        switch ($this->plot_border_type) {
        case 'left':    // for past compatibility
        case 'plotleft':
            ImageLine($this->img, $this->plot_area[0], $this->ytr($this->plot_min_y),
                      $this->plot_area[0], $this->ytr($this->plot_max_y), $this->ndx_grid_color);
            break;
        case 'right':
        case 'plotright':
            ImageLine($this->img, $this->plot_area[2], $this->ytr($this->plot_min_y),
                      $this->plot_area[2], $this->ytr($this->plot_max_y), $this->ndx_grid_color);
            break;
        case 'both':
        case 'sides':
             ImageLine($this->img, $this->plot_area[0], $this->ytr($this->plot_min_y),
                      $this->plot_area[0], $this->ytr($this->plot_max_y), $this->ndx_grid_color);
            ImageLine($this->img, $this->plot_area[2], $this->ytr($this->plot_min_y),
                      $this->plot_area[2], $this->ytr($this->plot_max_y), $this->ndx_grid_color);
            break;
        case 'none':
            //Draw No Border
            break;
        case 'full':
        default:
            ImageRectangle($this->img, $this->plot_area[0], $this->ytr($this->plot_min_y),
                           $this->plot_area[2], $this->ytr($this->plot_max_y), $this->ndx_grid_color);
            break;
        }
        return TRUE;
    }


    /*!
     * Draws the data label associated with a point in the plot.
     * This is different from x_labels drawn by DrawXTicks() and care
     * should be taken not to draw both, as they'd probably overlap.
     * Calling of this function in DrawLines(), etc is decided after x_data_label_pos value.
     * Leave the last parameter out, to avoid the drawing of vertical lines, no matter
     * what the setting is (for plots that need it, like DrawSquared())
     */
    function DrawXDataLabel($xlab, $xpos, $row=FALSE) 
    {
        $xlab = $this->FormatLabel('x', $xlab);
   
        // Labels below the plot area
        if ($this->x_data_label_pos == 'plotdown' || $this->x_data_label_pos == 'both')
            $this->DrawText($this->x_label_font, $this->x_label_angle, $xpos, 
                            $this->plot_area[3] + $this->x_tick_length, 
                            $this->ndx_text_color, $xlab, 'center', 'bottom');
        
        // Labels above the plot area
        if ($this->x_data_label_pos == 'plotup' || $this->x_data_label_pos == 'both')
            $this->DrawText($this->x_label_font, $this->x_label_angle, $xpos, 
                            $this->plot_area[1] - $this->x_tick_length , 
                            $this->ndx_text_color, $xlab, 'center', 'top');

        if ($row && $this->draw_x_data_label_lines)
            $this->DrawXDataLine($xpos, $row);
    }
    
    /*!
     * Draws Vertical lines from data points up and down.
     * Which lines are drawn depends on the value of x_data_label_pos,
     * and whether this is at all done or not, on draw_x_data_label_lines
     *
     * \param xpos int position in pixels of the line.
     * \param row int index of the data row being drawn.
     */
    function DrawXDataLine($xpos, $row) 
    {
        // Sets the line style for IMG_COLOR_STYLED lines (grid)
        if($this->dashed_grid) {
            $this->SetDashedStyle($this->ndx_light_grid_color);
            $style = IMG_COLOR_STYLED;
        } else {
            $style = $this->ndx_light_grid_color;
        }

        // Lines from the bottom up
        if ($this->x_data_label_pos == 'both') {
            ImageLine($this->img, $xpos, $this->plot_area[3], $xpos, $this->plot_area[1], $style);
        }         
        // Lines coming from the bottom of the plot
        else if ($this->x_data_label_pos == 'plotdown') {
            // See FindDataLimits() to see why 'MAXY' index.
            $ypos = $this->ytr($this->data[$row][MAXY]);
            ImageLine($this->img, $xpos, $ypos, $xpos, $this->plot_area[3], $style);
        }
        // Lines coming from the top of the plot
        else if ($this->x_data_label_pos == 'plotup') {
            // See FindDataLimits() to see why 'MINY' index.
            $ypos = $this->ytr($this->data[$row][MINY]);
            ImageLine($this->img, $xpos, $this->plot_area[1], $xpos, $ypos, $style);
        }
    } 
    
/*    
    function DrawPlotLabel($xlab, $xpos, $ypos) 
    {
        $this->DrawText($this->x_label_font, $this->x_label_angle, $xpos, $this
*/

    /*!
     * Draws the graph legend
     *
     * \note Base code submitted by Marlin Viss
     * FIXME: maximum label length should be calculated more accurately for TT fonts
     *        Performing a BBox calculation for every legend element, for example.
     */
    function DrawLegend($which_x1, $which_y1, $which_boxtype) 
    {
        // Find maximum legend label length
        $max_len = 0;
        foreach ($this->legend as $leg) {
            $len = strlen($leg);
            $max_len = ($len > $max_len) ? $len : $max_len;
        }
        $max_len += 5;          // Leave room for the boxes and margins

        /////// Calculate legend labels sizes:  FIXME - dirty hack - FIXME
        // TTF:
        if ($this->use_ttf) {
            $size = $this->TTFBBoxSize($this->legend_font['size'], 0,
                                       $this->legend_font['font'], '_');
            $char_w = $size[0];

            $size = $this->TTFBBoxSize($this->legend_font['size'], 0,
                                       $this->legend_font['font'], '|');
            $char_h = $size[1];                                       
        } 
        // Fixed fonts:
        else {
            $char_w = $this->legend_font['width'];
            $char_h = $this->legend_font['height'];
        }

        $v_margin = $char_h/2;                         // Between vertical borders and labels
        $dot_height = $char_h + $this->line_spacing;   // Height of the small colored boxes
        $width = $char_w * $max_len;

        //////// Calculate box size
        // upper Left
        if ( (! $which_x1) || (! $which_y1) ) {
            $box_start_x = $this->plot_area[2] - $width;
            $box_start_y = $this->plot_area[1] + 5;
        } else { 
            $box_start_x = $which_x1;
            $box_start_y = $which_y1;
        }

        // Lower right corner
        $box_end_y = $box_start_y + $dot_height*(count($this->legend)) + 2*$v_margin; 
        $box_end_x = $box_start_x + $width - 5;


        // Draw outer box
        ImageFilledRectangle($this->img, $box_start_x, $box_start_y, $box_end_x, $box_end_y, $this->ndx_bg_color);
        ImageRectangle($this->img, $box_start_x, $box_start_y, $box_end_x, $box_end_y, $this->ndx_grid_color);

        $color_index = 0;
        $max_color_index = count($this->ndx_data_colors) - 1;

        $dot_left_x = $box_end_x - $char_w * 2;
        $dot_right_x = $box_end_x - $char_w;
        $y_pos = $box_start_y + $v_margin;

        foreach ($this->legend as $leg) {
            // Text right aligned to the little box
            $this->DrawText($this->legend_font, 0, $dot_left_x - $char_w, $y_pos, 
                            $this->ndx_text_color, $leg, 'right');
            // Draw a box in the data color
            ImageFilledRectangle($this->img, $dot_left_x, $y_pos + 1, $dot_right_x,
                                 $y_pos + $dot_height-1, $this->ndx_data_colors[$color_index]);
            // Draw a rectangle around the box
            ImageRectangle($this->img, $dot_left_x, $y_pos + 1, $dot_right_x,
                           $y_pos + $dot_height-1, $this->ndx_text_color);

            $y_pos += $char_h + $this->line_spacing;

            $color_index++;
            if ($color_index > $max_color_index) 
                $color_index = 0;
        }
    } // Function DrawLegend()


    /*!
     * TODO Draws a legend over (or below) an axis of the plot.
     */
    function DrawAxisLegend()
    {
        // Calculate available room
        // Calculate length of all items (boxes included)
        // Calculate number of lines and room it would take. FIXME: this should be known in CalcMargins()
        // Draw.
    }

/////////////////////////////////////////////
////////////////////             PLOT DRAWING
/////////////////////////////////////////////


    /*!
     * Draws a pie chart. Data has to be 'text-data' type.
     * 
     *  This can work in two ways: the classical, with a column for each sector 
     *  (computes the column totals and draws the pie with that) 
     *  OR
     *  Takes each row as a sector and uses it's first value. This has the added
     *  advantage of using the labels provided, which is not the case with the
     *  former method. This might prove useful for pie charts from GROUP BY sql queries
     */
    function DrawPieChart() 
    {
        $xpos = $this->plot_area[0] + $this->plot_area_width/2;
        $ypos = $this->plot_area[1] + $this->plot_area_height/2;
        $diameter = min($this->plot_area_width, $this->plot_area_height);
        $radius = $diameter/2;

        // Get sum of each column? One pie slice per column
        if ($this->data_type === 'text-data') {
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                for ($j = 1; $j < $this->num_recs[$i]; $j++) {      // Label ($row[0]) unused in these pie charts
                    @$sumarr[$j] += abs($this->data[$i][$j]);      // NOTE!  sum > 0 to make pie charts
                }
            }
        }
        // Or only one column per row, one pie slice per row?
        else if ($this->data_type == 'text-data-pie') {
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                $legend[$i] = $this->data[$i][0];                   // Set the legend to column labels
                $sumarr[$i] = $this->data[$i][1];
            }
        }
        else if ($this->data_type == 'data-data') {
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                for ($j = 2; $j < $this->num_recs[$i]; $j++) {
                    @$sumarr[$j] += abs($this->data[$i][$j]);
                }
            }
        }            
        else {
            $this->DrawError("DrawPieChart(): Data type '$this->data_type' not supported.");
            return FALSE;
        }

        $total = array_sum($sumarr);

        if ($total == 0) {
            $this->DrawError('DrawPieChart(): Empty data set');
            return FALSE;
        }

        if ($this->shading) {
            $diam2 = $diameter / 2;
        } else {
            $diam2 = $diameter;
        }
        $max_data_colors = count ($this->data_colors);

        for ($h = $this->shading; $h >= 0; $h--) {
            $color_index = 0;
            $start_angle = 0;
            $end_angle = 0;
            foreach ($sumarr as $val) {
                // For shaded pies: the last one (at the top of the "stack") has a brighter color:
                if ($h == 0)
                    $slicecol = $this->ndx_data_colors[$color_index];
                else                
                    $slicecol = $this->ndx_data_dark_colors[$color_index];

                $label_txt = number_format(($val / $total * 100), $this->y_precision, '.', ', ') . '%';
                $val = 360 * ($val / $total);

                // NOTE that imagefilledarc measures angles CLOCKWISE (go figure why),
                // so the pie chart would start clockwise from 3 o'clock, would it not be
                // for the reversal of start and end angles in imagefilledarc()
                $start_angle = $end_angle;
                $end_angle += $val;
                $mid_angle = deg2rad($end_angle - ($val / 2));

                // Draw the slice       
                ImageFilledArc($this->img, $xpos, $ypos+$h, $diameter, $diam2, 
                               360-$end_angle, 360-$start_angle,
                               $slicecol, IMG_ARC_PIE);

                // Draw the labels only once
                if ($h == 0) {
                    // Draw the outline
                    if (! $this->shading)
                        ImageFilledArc($this->img, $xpos, $ypos+$h, $diameter, $diam2, 
                                       360-$end_angle, 360-$start_angle,
                                       $this->ndx_grid_color, IMG_ARC_PIE | IMG_ARC_EDGED |IMG_ARC_NOFILL);


                    // The '* 1.2' trick is to get labels out of the pie chart so there are more 
                    // chances they can be seen in small sectors.
                    $label_x = $xpos + ($diameter * 1.2 * cos($mid_angle)) * $this->label_scale_position;
                    $label_y = $ypos+$h - ($diam2 * 1.2 * sin($mid_angle)) * $this->label_scale_position;

                    $this->DrawText($this->generic_font, 0, $label_x, $label_y, $this->ndx_grid_color,
                                    $label_txt, 'center', 'center');           
                }                                
                $color_index++;
                $color_index = $color_index % $max_data_colors;
            }   // end for
        }   // end for
    }


    /*!
     * Supported data formats: data-data-error, text-data-error (doesn't exist yet)
     * ( data comes in as array("title", x, y, error+, error-, y2, error2+, error2-, ...) )
     */
    function DrawDotsError() 
    {
        $this->CheckOption($this->data_type, 'data-data-error', __FUNCTION__);

        for($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                // Skip record #0 (title)

            // Do we have a value for X?
            if ($this->data_type == 'data-data-error')
                $x_now = $this->data[$row][$record++];  // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;                  // Place text-data at X = 0.5, 1.5, 2.5, etc...
                
            // Draw X Data labels?
            if ($this->x_data_label_pos != 'none')
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            while ($record < $this->num_recs[$row]) {
                    // Y:
                    $y_now = $this->data[$row][$record++];
                    $this->DrawDot($x_now, $y_now, $this->point_shape, $this->ndx_data_colors[$record]);

                    // Error +
                    $val = $this->data[$row][$record++];
                    $this->DrawYErrorBar($x_now, $y_now, $val, $this->error_bar_shape,
                                         $this->ndx_error_bar_colors[$record]);
                    // Error -
                    $val = $this->data[$row][$record];
                    $this->DrawYErrorBar($x_now, $y_now, -$val, $this->error_bar_shape,
                                         $this->ndx_error_bar_colors[$record++]);
            }
        }
    } // function DrawDotsError()


    /*
     * Supported data types: 
     *  - data-data ("title", x, y1, y2, y3, ...)
     *  - text-data ("title", y1, y2, y3, ...)
     */
    function DrawDots() 
    {
        $this->CheckOption($this->data_type, 'text-data, data-data', __FUNCTION__);

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $rec = 1;                    // Skip record #0 (data label)

            // Do we have a value for X?
            if ($this->data_type == 'data-data')
                $x_now = $this->data[$row][$rec++];  // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;       // Place text-data at X = 0.5, 1.5, 2.5, etc...

            $x_now_pixels = $this->xtr($x_now);

            // Draw X Data labels?
            if ($this->x_data_label_pos != 'none')
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            // Proceed with Y values
            for($idx = 0;$rec < $this->num_recs[$row]; $rec++, $idx++) {
                if (is_numeric($this->data[$row][$rec])) {              // Allow for missing Y data 
                    $this->DrawDot($x_now, $this->data[$row][$rec], 
                                   $this->point_shape, $this->ndx_data_colors[$idx]);
                }
            }
        }
    } //function DrawDots


    /*!
     * A clean, fast routine for when you just want charts like stock volume charts
     */
    function DrawThinBarLines() 
    {
        $this->CheckOption($this->data_type, 'text-data, data-data', __FUNCTION__);

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $rec = 1;                    // Skip record #0 (data label)

            // Do we have a value for X?
            if ($this->data_type == 'data-data')
                $x_now = $this->data[$row][$rec++];  // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;       // Place text-data at X = 0.5, 1.5, 2.5, etc...

            $x_now_pixels = $this->xtr($x_now);

            // Draw X Data labels?
            if ($this->x_data_label_pos != 'none')
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);

            // Proceed with Y values
            for($idx = 0;$rec < $this->num_recs[$row]; $rec++, $idx++) {
                if (is_numeric($this->data[$row][$rec])) {              // Allow for missing Y data 
                    ImageSetThickness($this->img, $this->line_widths[$idx]);
                    // Draws a line from user defined x axis position up to ytr($val)
                    ImageLine($this->img, $x_now_pixels, $this->x_axis_y_pixels, $x_now_pixels, 
                              $this->ytr($this->data[$row][$rec]), $this->ndx_data_colors[$idx]);
                }                              
            }
        }

        ImageSetThickness($this->img, 1);
    }  //function DrawThinBarLines

    /*!
     *
     */
    function DrawYErrorBar($x_world, $y_world, $error_height, $error_bar_type, $color) 
    {
        /* 
        // TODO: add a parameter to show datalabels next to error bars?
        // something like this:
        if ($this->x_data_label_pos == 'plot') {
            $this->DrawText($this->error_font, 90, $x1, $y2, 
                            $color, $label, 'center', 'top');
        */

        $x1 = $this->xtr($x_world);
        $y1 = $this->ytr($y_world);
        $y2 = $this->ytr($y_world+$error_height) ;

        ImageSetThickness($this->img, $this->error_bar_line_width);
        ImageLine($this->img, $x1, $y1 , $x1, $y2, $color);

        switch ($error_bar_type) {
        case 'line':
            break;
        case 'tee':
            ImageLine($this->img, $x1-$this->error_bar_size, $y2, $x1+$this->error_bar_size, $y2, $color);
            break;
        default:
            ImageLine($this->img, $x1-$this->error_bar_size, $y2, $x1+$this->error_bar_size, $y2, $color);
            break;
        }

        ImageSetThickness($this->img, 1);
        return TRUE;
    }

    /*!
     * Draws a styled dot. Uses world coordinates.
     * Supported types: 'halfline', 'line', 'plus', 'cross', 'rect', 'circle', 'dot',
     * 'diamond', 'triangle', 'trianglemid'
     */
    function DrawDot($x_world, $y_world, $dot_type, $color) 
    {
        $half_point = $this->point_size / 2;

        $x_mid = $this->xtr($x_world);
        $y_mid = $this->ytr($y_world);

        $x1 = $x_mid - $half_point;
        $x2 = $x_mid + $half_point;
        $y1 = $y_mid - $half_point;
        $y2 = $y_mid + $half_point;

        switch ($dot_type) {
        case 'halfline':
            ImageLine($this->img, $x1, $y_mid, $x_mid, $y_mid, $color);
            break;
        case 'line':
            ImageLine($this->img, $x1, $y_mid, $x2, $y_mid, $color);
            break;
        case 'plus':
            ImageLine($this->img, $x1, $y_mid, $x2, $y_mid, $color);
            ImageLine($this->img, $x_mid, $y1, $x_mid, $y2, $color);
            break;
        case 'cross':
            ImageLine($this->img, $x1, $y1, $x2, $y2, $color);
            ImageLine($this->img, $x1, $y2, $x2, $y1, $color);
            break;
        case 'rect':
            ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $color);
            break;
        case 'circle':
            ImageArc($this->img, $x_mid, $y_mid, $this->point_size, $this->point_size, 0, 360, $color);
            break;
        case 'dot':
            ImageFilledArc($this->img, $x_mid, $y_mid, $this->point_size, $this->point_size, 0, 360, 
                           $color, IMG_ARC_PIE);
            break;
        case 'diamond':
            $arrpoints = array( $x1, $y_mid, $x_mid, $y1, $x2, $y_mid, $x_mid, $y2);
            ImageFilledPolygon($this->img, $arrpoints, 4, $color);
            break;
        case 'triangle':
            $arrpoints = array( $x1, $y_mid, $x2, $y_mid, $x_mid, $y2);
            ImageFilledPolygon($this->img, $arrpoints, 3, $color);
            break;
        case 'trianglemid':
            $arrpoints = array( $x1, $y1, $x2, $y1, $x_mid, $y_mid);
            ImageFilledPolygon($this->img, $arrpoints, 3, $color);
            break;
        default:
            ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $color);
            break;
        }
        return TRUE;
    }

    /*!
     * Draw an area plot. Supported data types:
     *      'text-data'
     *      'data-data'
     * NOTE: This function used to add first and last data values even on incomplete
     *       sets. That is not the behaviour now. As for missing data in between, 
     *       there are two posibilities: replace the point with one on the X axis (previous
     *       way), or forget about it and use the preceding and following ones to draw the polygon.
     *       There is the possibility to use both, we just need to add the method to set
     *       it. Something like SetMissingDataBehaviour(), for example.
     */
    function DrawArea() 
    {
        $incomplete_data_defaults_to_x_axis = FALSE;        // TODO: make this configurable

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $rec = 1;                                       // Skip record #0 (data label)

            if ($this->data_type == 'data-data')            // Do we have a value for X?
                $x_now = $this->data[$row][$rec++];         // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;                      // Place text-data at X = 0.5, 1.5, 2.5, etc...

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates


            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);                   

            // Proceed with Y values
            // Create array of points for imagefilledpolygon()
            for($idx = 0; $rec < $this->num_recs[$row]; $rec++, $idx++) {
                if (is_numeric($this->data[$row][$rec])) {              // Allow for missing Y data 
                    $y_now_pixels = $this->ytr($this->data[$row][$rec]);

                    $posarr[$idx][] = $x_now_pixels;
                    $posarr[$idx][] = $y_now_pixels;

                    $num_points[$idx] = isset($num_points[$idx]) ? $num_points[$idx]+1 : 1;
                }
                // If there's missing data...
                else {
                    if (isset ($incomplete_data_defaults_to_x_axis)) {
                        $posarr[$idx][] = $x_now_pixels;
                        $posarr[$idx][] = $this->x_axis_y_pixels;
                        $num_points[$idx] = isset($num_points[$idx]) ? $num_points[$idx]+1 : 1;
                    }
                }
            }
        }   // end for

        $end = count($posarr);
        for ($i = 0; $i < $end; $i++) {
            // Prepend initial points. X = first point's X, Y = x_axis_y_pixels
            $x = $posarr[$i][0];
            array_unshift($posarr[$i], $x, $this->x_axis_y_pixels);

            // Append final points. X = last point's X, Y = x_axis_y_pixels
            $x = $posarr[$i][count($posarr[$i])-2];
            array_push($posarr[$i], $x, $this->x_axis_y_pixels);

            $num_points[$i] += 2;

            // Draw the poligon
            ImageFilledPolygon($this->img, $posarr[$i], $num_points[$i], $this->ndx_data_colors[$i]);
        }

    } // function DrawArea()


    /*!
     * Draw Lines. Supported data-types:
     *      'data-data', 
     *      'text-data'
     * NOTE: Please see the note regarding incomplete data sets on DrawArea()
     */
    function DrawLines() 
    {
        // This will tell us if lines have already begun to be drawn.
        // It is an array to keep separate information for every line, with a single
        // variable we would sometimes get "undefined offset" errors and no plot...
        $start_lines = array_fill(0, $this->records_per_group, FALSE);

        if ($this->data_type == 'text-data') { 
            $lastx[0] = $this->xtr(0);
            $lasty[0] = $this->xtr(0);
        }

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            if ($this->data_type == 'data-data')            // Do we have a value for X?
                $x_now = $this->data[$row][$record++];      // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;                      // Place text-data at X = 0.5, 1.5, 2.5, etc...

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (is_numeric($this->data[$row][$record])) {           //Allow for missing Y data 
                    $y_now_pixels = $this->ytr($this->data[$row][$record]);

                    if ($start_lines[$idx] == TRUE) {
                        // Set line width, revert it to normal at the end
                        ImageSetThickness($this->img, $this->line_widths[$idx]);

                        if ($this->line_styles[$idx] == 'dashed') {
                            $this->SetDashedStyle($this->ndx_data_colors[$idx]);
                            ImageLine($this->img, $x_now_pixels, $y_now_pixels, $lastx[$idx], $lasty[$idx], 
                                      IMG_COLOR_STYLED);
                        } else {
                            ImageLine($this->img, $x_now_pixels, $y_now_pixels, $lastx[$idx], $lasty[$idx], 
                                      $this->ndx_data_colors[$idx]);
                        }

                    }
                    $lasty[$idx] = $y_now_pixels;
                    $lastx[$idx] = $x_now_pixels;
                    $start_lines[$idx] = TRUE;
                } 
                // Y data missing... should we leave a blank or not?
                else if ($this->draw_broken_lines) {
                    $start_lines[$idx] = FALSE;
                }
            }   // end for
        }   // end for

        ImageSetThickness($this->img, 1);       // Revert to original state for lines to be drawn later. 
    } // function DrawLines()


    /*!
     * Draw lines with error bars - data comes in as 
     *      array("label", x, y, error+, error-, y2, error2+, error2-, ...);
     */
    function DrawLinesError() 
    {
        if ($this->data_type != 'data-data-error') {
            $this->DrawError("DrawLinesError(): Data type '$this->data_type' not supported.");
            return FALSE;
        }

        $start_lines = array_fill(0, $this->records_per_group, FALSE);

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            $x_now = $this->data[$row][$record++];          // Read X value, advance record index

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates.
            

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels? 
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            // Now go for Y, E+, E-
            for ($idx = 0; $record < $this->num_recs[$row]; $idx++) {
                // Y
                $y_now = $this->data[$row][$record++];
                $y_now_pixels = $this->ytr($y_now);

                if ($start_lines[$idx] == TRUE) {
                    ImageSetThickness($this->img, $this->line_widths[$idx]);

                    if ($this->line_styles[$idx] == 'dashed') {
                        $this->SetDashedStyle($this->ndx_data_colors[$idx]);
                        ImageLine($this->img, $x_now_pixels, $y_now_pixels, $lastx[$idx], $lasty[$idx], 
                                  IMG_COLOR_STYLED);
                    } else {
                        ImageLine($this->img, $x_now_pixels, $y_now_pixels, $lastx[$idx], $lasty[$idx], 
                                  $this->ndx_data_colors[$idx]);
                    }
                }

                // Error+
                $val = $this->data[$row][$record++];
                $this->DrawYErrorBar($x_now, $y_now, $val, $this->error_bar_shape, 
                                     $this->ndx_error_bar_colors[$idx]);

                // Error-
                $val = $this->data[$row][$record++];
                $this->DrawYErrorBar($x_now, $y_now, -$val, $this->error_bar_shape, 
                                     $this->ndx_error_bar_colors[$idx]);

                // Update indices:
                $start_lines[$idx] = TRUE;   // Tells us if we already drew the first column of points, 
                                             // thus having $lastx and $lasty ready for the next column.
                $lastx[$idx] = $x_now_pixels;
                $lasty[$idx] = $y_now_pixels;
            }   // end while
        }   // end for

        ImageSetThickness($this->img, 1);   // Revert to original state for lines to be drawn later.
    }   // function DrawErrorLines()



    /*!
     * This is a mere copy of DrawLines() with one more line drawn for each point
     */
    function DrawSquared() 
    {
        // This will tell us if lines have already begun to be drawn.
        // It is an array to keep separate information for every line, for with a single
        // variable we could sometimes get "undefined offset" errors and no plot...
        $start_lines = array_fill(0, $this->records_per_group, FALSE);

        if ($this->data_type == 'text-data') { 
            $lastx[0] = $this->xtr(0);
            $lasty[0] = $this->xtr(0);
        }
        
        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            if ($this->data_type == 'data-data')            // Do we have a value for X?
                $x_now = $this->data[$row][$record++];      // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;                      // Place text-data at X = 0.5, 1.5, 2.5, etc...

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels); // notice there is no last param.
                
            // Draw Lines
            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (is_numeric($this->data[$row][$record])) {               // Allow for missing Y data 
                    $y_now_pixels = $this->ytr($this->data[$row][$record]);

                    if ($start_lines[$idx] == TRUE) {
                        // Set line width, revert it to normal at the end
                        ImageSetThickness($this->img, $this->line_widths[$idx]);

                        if ($this->line_styles[$idx] == 'dashed') {
                            $this->SetDashedStyle($this->ndx_data_colors[$idx]);
                            ImageLine($this->img, $lastx[$idx], $lasty[$idx], $x_now_pixels, $lasty[$idx], 
                                      IMG_COLOR_STYLED);
                            ImageLine($this->img, $x_now_pixels, $lasty[$idx], $x_now_pixels, $y_now_pixels, 
                                      IMG_COLOR_STYLED);
                        } else {
                            ImageLine($this->img, $lastx[$idx], $lasty[$idx], $x_now_pixels, $lasty[$idx],
                                      $this->ndx_data_colors[$idx]);
                            ImageLine($this->img, $x_now_pixels, $lasty[$idx], $x_now_pixels, $y_now_pixels,
                                      $this->ndx_data_colors[$idx]);
                        }
                    }
                    $lastx[$idx] = $x_now_pixels;
                    $lasty[$idx] = $y_now_pixels;
                    $start_lines[$idx] = TRUE;
                } 
                // Y data missing... should we leave a blank or not?
                else if ($this->draw_broken_lines) {
                    $start_lines[$idx] = FALSE;
                } 
            }
        }   // end while

        ImageSetThickness($this->img, 1); 
    } // function DrawSquared()


    /*!    
     * Data comes in as array("title", x, y, y2, y3, ...)
     */
    function DrawBars() 
    {
        if ($this->data_type != 'text-data') { 
            $this->DrawError('DrawBars(): Bar plots must be text-data: use function setDataType("text-data")');
            return FALSE;
        }

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            $x_now_pixels = $this->xtr(0.5 + $row);         // Place text-data at X = 0.5, 1.5, 2.5, etc...

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);

            // Draw the bar
            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (is_numeric($this->data[$row][$record])) {       // Allow for missing Y data 
                    $x1 = $x_now_pixels - $this->data_group_space + ($idx * $this->record_bar_width);
                    $x2 = $x1 + ($this->bar_width_adjust * $this->record_bar_width); 

                    if ($this->data[$row][$record] < $this->x_axis_position) {
                        $y1 = $this->x_axis_y_pixels;
                        $y2 = $this->ytr($this->data[$row][$record]);
                    } else {
                        $y1 = $this->ytr($this->data[$row][$record]);
                        $y2 = $this->x_axis_y_pixels;
                    }

                    if ($this->shading) {                           // Draw the shade?
                        ImageFilledPolygon($this->img, array($x1, $y1, 
                                                       $x1 + $this->shading, $y1 - $this->shading,
                                                       $x2 + $this->shading, $y1 - $this->shading,
                                                       $x2 + $this->shading, $y2 - $this->shading,
                                                       $x2, $y2,
                                                       $x2, $y1),
                                           6, $this->ndx_data_dark_colors[$idx]);
                    } 
                    // Or draw a border?
                    else {
                        ImageRectangle($this->img, $x1, $y1, $x2,$y2, $this->ndx_data_border_colors[$idx]);
                    }
                    // Draw the bar
                    ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $this->ndx_data_colors[$idx]);
                } 
            }   // end for
        }   // end for
    } //function DrawBars    


    /*!
     *
     */
    function DrawGraph() 
    {
        if (! $this->img) {
            $this->DrawError('DrawGraph(): No image resource allocated');
            return FALSE;
        }

        if (! is_array($this->data)) {
            $this->DrawError("DrawGraph(): No array of data in \$data");
            return FALSE;
        }

        if (! isset($this->data_limits_done)) 
            $this->FindDataLimits();                // Get maxima and minima for scaling

        if ($this->total_records == 0) {            // Check for empty data sets
            $this->DrawError('Empty data set');
            return FALSE;
        }

        $this->CalcMargins();                       // Calculate margins

        if (! isset($this->plot_area_width))        // Set plot area pixel values (plot_area[])
            $this->SetPlotAreaPixels();

        if (! isset($this->plot_max_y))             // Set plot area world values (plot_max_x, etc.)
            $this->SetPlotAreaWorld();

        if ($this->data_type == 'text-data')
            $this->SetEqualXCoord();

        if ($this->x_data_label_pos != 'none') {    // Default: do not draw tick stuff if 
            $this->x_tick_label_pos = 'none';       // there are data labels.
            $this->x_tick_pos = 'none';
        }

        $this->PadArrays();                         // Pad color and style arrays to fit records per group.

        $this->DrawBackground();

        $this->DrawImageBorder();

        if ($this->draw_plot_area_background)
            $this->DrawPlotAreaBackground();

        $this->DrawTitle();
        $this->DrawXTitle();
        $this->DrawYTitle();

        switch ($this->plot_type) {
        case 'thinbarline':
            $this->DrawThinBarLines();
            break;
        case 'area':
            $this->DrawArea();
            break;
        case 'squared':
            $this->DrawSquared();
            break;
        case 'lines':
            if ( $this->data_type == 'data-data-error') {
                $this->DrawLinesError();
            } else {
                $this->DrawLines();
            }
            break;
        case 'linepoints':          // FIXME !!! DrawXDataLabel gets called in DrawLines() and DrawDots()
            if ( $this->data_type == 'data-data-error') {
                $this->DrawLinesError();
                $this->DrawDotsError();
            } else {
                $this->DrawLines();
                $this->DrawDots();
            }
            break;
        case 'points';
            if ( $this->data_type == 'data-data-error') {
                $this->DrawDotsError();
            } else {
                $this->DrawDots();
            }
            break;
        case 'pie':
            // Pie charts can maximize image space usage.
            $this->SetPlotAreaPixels($this->safe_margin, $this->title_height,
                                     $this->image_width - $this->safe_margin,
                                     $this->image_height - $this->safe_margin);
            $this->DrawPieChart();
            break;
        case 'bars':
        default:
            $this->plot_type = 'bars';  // Set it if it wasn't already set.
            $this->DrawYAxis();     // We don't want the grid to overwrite bar charts
            $this->DrawXAxis();     // so we draw it first. Also, Y must be drawn before X (see DrawYAxis())
            $this->DrawBars();
            $this->DrawPlotBorder();
            break;
        }   // end switch

        if ($this->plot_type != 'pie' && $this->plot_type != 'bars') {
            $this->DrawYAxis();
            $this->DrawXAxis();
            $this->DrawPlotBorder();
        }
        if ($this->legend)
            $this->DrawLegend($this->legend_x_pos, $this->legend_y_pos, '');

        if ($this->print_image)
            $this->PrintImage();

    } //function DrawGraph()

/////////////////////////////////////////////
//////////////////         DEPRECATED METHODS
/////////////////////////////////////////////

    /*!
     * Deprecated, use SetYTickPos()
     */
    function setDrawVertTicks($which_dvt) 
    {
        if ($which_dvt != 1)
            $this->SetYTickPos('none');
        return TRUE;
    } 

    /*!
     * Deprecated, use SetXTickPos()
     */
    function setDrawHorizTicks($which_dht) 
    {
        if ($which_dht != 1)
           $this->SetXTickPos('none');
        return TRUE;
    }

    /*!
     * \deprecated Use SetNumXTicks()
     */
    function setNumHorizTicks($n) 
    {
        return $this->SetNumXTicks($n);
    }

    /*!
     * \deprecated Use SetNumYTicks()
     */
    function setNumVertTicks($n) 
    {
        return $this->SetNumYTicks($n);
    }

    /*!
     * \deprecated Use SetXTickIncrement()
     */
    function setHorizTickIncrement($inc) 
    {
        return $this->SetXTickIncrement($inc);
    }


    /*!
     * \deprecated Use SetYTickIncrement()
     */
    function setVertTickIncrement($inc) 
    {
        return $this->SetYTickIncrement($inc);
    }

    /*!
     * \deprecated Use SetYTickPos()
     */
    function setVertTickPosition($which_tp) 
    { 
        return $this->SetYTickPos($which_tp); 
    }

    /*!
     * \deprecated Use SetXTickPos()
     */
    function setHorizTickPosition($which_tp) 
    { 
        return $this->SetXTickPos($which_tp); 
    }

    /*!
     * \deprecated Use SetFont()
     */
    function setTitleFontSize($which_size) 
    {
        return $this->SetFont('title', $which_size);
    }

    /*!
     * \deprecated Use SetFont()
     */
    function setAxisFontSize($which_size) 
    {
        $this->SetFont('x_label', $which_size);
        $this->SetFont('y_label', $whic_size);
    }

    /*!
     * \deprecated Use SetFont()
     */
    function setSmallFontSize($which_size) 
    {
        return $this->SetFont('generic', $which_size);
    }

    /*!
     * \deprecated Use SetFont()
     */
    function setXLabelFontSize($which_size) 
    {
        return $this->SetFont('x_title', $which_size);
    }

    /*!
     * \deprecated Use SetFont()
     */
    function setYLabelFontSize($which_size) 
    {
        return $this->SetFont('y_title', $which_size);
    }

    /*!
     * \deprecated Use SetXTitle()
     */ 
    function setXLabel($which_xlab) 
    {
        return $this->SetXTitle($which_xlab);
    }

    /*!
     * \deprecated Use SetYTitle()
     */ 
    function setYLabel($which_ylab) 
    {
        return $this->SetYTitle($which_ylab);
    }   

    /*!
     * \deprecated This is now an Internal function - please set width and 
     *             height via PHPlot() upon object construction
     */
    function setImageArea($which_iw, $which_ih) 
    {
        $this->image_width = $which_iw;
        $this->image_height = $which_ih;

        return TRUE;
    }

    /*!
     * \deprecated Use SetXTickLength() and SetYTickLength() instead.
     */
    function setTickLength($which_tl) 
    {
        $this->SetXTickLength($which_tl);
        $this->SetYTickLength($which_tl);
        return TRUE;
    }

    /*!
     * \deprecated  Use SetYLabelType()
     */
    function setYGridLabelType($which_yglt) 
    {
        return $this->SetYLabelType($which_yglt);
    }

    /*!
     * \deprecated  Use SetXLabelType()
     */
    function setXGridLabelType($which_xglt) 
    {
        return $this->SetXLabelType($which_xglt);
    }
    /*!
     * \deprecated Use SetYTickLabelPos()
     */
    function setYGridLabelPos($which_yglp) 
    {
        return $this->SetYTickLabelPos($which_yglp);
    }
    /*!
     * \deprecated Use SetXTickLabelPos()
     */
    function setXGridLabelPos($which_xglp) 
    {
        return $this->SetXTickLabelPos($which_xglp);
    }


    /*!
     * \deprecated Use SetXtitle()
     */
    function setXTitlePos($xpos) 
    {
        $this->x_title_pos = $xpos;
        return TRUE;
    }

    /*!
     * \deprecated Use SetYTitle()
     */
    function setYTitlePos($xpos) 
    {
        $this->y_title_pos = $xpos;
        return TRUE;
    }

    /*!
     * \deprecated  Use DrawDots()
     */
    function DrawDotSeries() 
    {
        $this->DrawDots();
    }

    /*!
     * \deprecated Use SetXLabelAngle()
     */
    function setXDataLabelAngle($which_xdla) 
    {
        return $this->SetXLabelAngle($which_xdla);
    }

    /*!
     * Draw Labels (not grid labels) on X Axis, following data points. Default position is 
     * down of plot. Care must be taken not to draw these and x_tick_labels as they'd probably overlap.
     *
     * \deprecated Use SetXDataLabelPos()
     */
    function setDrawXDataLabels($which_dxdl) 
    {
        if ($which_dxdl == '1' )
            $this->SetXDataLabelPos('plotdown');
        else
            $this->SetXDataLabelPos('none');
    }

    /*!
     * \deprecated This method was intended to improve performance by being specially 
     * written for 'data-data'. However, the improvement didn't pay. Use DrawLines() instead
     */
    function DrawLineSeries() 
    {
        return $this->DrawLines();
    }

    /*!
     * \deprecated Calculates maximum X-Axis label height. Now inside _CalcMargins()
     */
    function CalcXHeights() 
    {
        // TTF
        if ($this->use_ttf) {
            $xstr = str_repeat('.', $this->max_t);
            $size = $this->TTFBBoxSize($this->x_label_font['size'], $this->x_label_angle,
                                       $this->x_label_font['font'], $xstr);
            $this->x_tick_label_height = $size[1];
        } 
        // Fixed font
        else { // For Non-TTF fonts we can have only angles 0 or 90
            if ($this->x_label_angle == 90)
                $this->x_tick_label_height = $this->max_t * $this->x_label_font['width'];
            else 
                $this->x_tick_label_height = $this->x_label_font['height'];
        }

        return TRUE;
    }


    /*!
     * \deprecated Calculates Maximum Y-Axis tick label width. Now inside CalcMargins()
     */
    function CalcYWidths() 
    {
        //the "." is for space. It isn't actually printed
        $ylab = number_format($this->max_y, $this->y_precision, '.', ', ') . $this->data_units_text . '.';

        // TTF
        if ($this->use_ttf) {
            // Maximum Y tick label width
            $size = $this->TTFBBoxSize($this->y_label_font['size'], 0, $this->y_label_font['font'], $ylab);
            $this->y_tick_label_width = $size[0];

        } 
        // Fixed font
        else {
            // Y axis title width
            $this->y_tick_label_width = strlen($ylab) * $this->y_label_font['width'];
        }

        return TRUE;
    }

    /*!
     * \deprecated Superfluous.
     */
    function DrawLabels() 
    {
        $this->DrawTitle();
        $this->DrawXTitle();
        $this->DrawYTitle();
    }

    /*! 
     * Set up the image resource 'img'
     * \deprecated The constructor should init 'img'
     */
    function InitImage() 
    {
        $this->img = ImageCreate($this->image_width, $this->image_height);

        if (! $this->img)
            $this->PrintError('InitImage(): Could not create image resource');
        return TRUE;
    }

    /*!
     * \deprecated
     */
    function setNewPlotAreaPixels($x1, $y1, $x2, $y2) 
    {
        //Like in GD 0, 0 is upper left set via pixel Coordinates
        $this->plot_area = array($x1, $y1, $x2, $y2);
        $this->plot_area_width = $this->plot_area[2] - $this->plot_area[0];
        $this->plot_area_height = $this->plot_area[3] - $this->plot_area[1];
        $this->y_top_margin = $this->plot_area[1];

        if (isset($this->plot_max_x))
            $this->CalcTranslation();

        return TRUE;
    }

    /*!
     * \deprecated Use _SetRGBColor()
     */
    function setColor($which_color) 
    { 
        $this->SetRGBColor($which_color);
        return TRUE;
    }

    /* 
     * \deprecated Use SetLineWidths().
     */
    function setLineWidth($which_lw) 
    {

        $this->SetLineWidths($which_lw);

        if (!$this->error_bar_line_width) { 
            $this->SetErrorBarLineWidth($which_lw);
        }
        return TRUE;
    }

    /*!
     * \deprecated 
     */
    function DrawDashedLine($x1, $y1, $x2, $y2 , $dash_length, $dash_space, $color)
    {
        if ($dash_length)
            $dashes = array_fill(0, $dash_length, $color);
        else
            $dashes = array();
        if ($dash_space)
            $spaces = array_fill(0, $dash_space, IMG_COLOR_TRANSPARENT);
        else
            $spaces = array();

        $style = array_merge($dashes, $spaces);
        ImageSetStyle($this->img, $style);
        ImageLine($this->img, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
    }        
}  // class PHPlot



//////////////////////// 


/*!
 * Pads an array with another or with itself.
 *  \param arr array  Original array (reference)
 *  \param size int   Size of the resulting array.
 *  \param arr2 array If specified, array to use for padding. If unspecified, pad with $arr.
 */
function array_pad_array(&$arr, $size, $arr2=NULL)
{
    if (! is_array($arr2)) {
        $arr2 = $arr;                           // copy the original array
    }
    while (count($arr) < $size)
        $arr = array_merge($arr, $arr2);        // append until done
}

?>
