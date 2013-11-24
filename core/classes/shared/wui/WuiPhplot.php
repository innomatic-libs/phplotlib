<?php
/*
--------------------------------------------------------------------
Copyright (c) 2003 Alex Pagnoni. All rights reserved.
--------------------------------------------------------------------
*/

class WuiPhplot extends WuiWidget {
    // Base

    var $mWidth = 500;
    var $mHeight = 250;
    var $mData = array();
    var $mPlotType = 'linepoints'; // bars, lines, linepoints, area, points, pie

    // Appearance

    var $mPointShape = 'dot'; // rect, circle, diamond, triangle, dot, line, halfline
    var $mPointSize = 5;
    var $mTitle = '';

    // Color

    var $mBackgroundColor = 'white';
    var $mGridColor = 'black';
    var $mLegend = array();
    var $mLineWidth = 1;
    var $mTextColor = 'black';

    public function __construct(
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
        )
    {
        parent::__construct(
            $elemName,
            $elemArgs,
            $elemTheme,
            $dispEvents
            );

        // Base

        if ( isset($this->mArgs['width'] ) ) $this->mWidth = $this->mArgs['width'];
        if ( isset($this->mArgs['height'] ) ) $this->mHeight = $this->mArgs['height'];
        if ( isset($this->mArgs['data'] ) and is_array( $this->mArgs['data'] ) ) $this->mData = &$this->mArgs['data'];
        if ( isset($this->mArgs['plottype'] ) and strlen( $this->mArgs['plottype'] ) ) $this->mPlotType = $this->mArgs['plottype'];

        // Appearance

        if ( isset($this->mArgs['pointshape'] ) ) $this->mPointShape = $this->mArgs['pointshape'];
        if ( isset($this->mArgs['pointsize'] ) and is_numeric( $this->mArgs['pointsize'] ) ) $this->mPointSize = $this->mArgs['pointsize'];
        if ( isset($this->mArgs['title'] ) ) $this->mTitle = $this->mArgs['title'];

        // Color

        if ( isset($this->mArgs['backgroundcolor'] ) ) $this->mBackgroundColor = $this->mArgs['backgroundcolor'];
        if ( isset($this->mArgs['gridcolor'] ) ) $this->mGridColor = $this->mArgs['gridcolor'];
        if ( isset($this->mArgs['legend'] ) and is_array( $this->mArgs['legend'] ) ) $this->mLegend = &$this->mArgs['legend'];
        if ( isset($this->mArgs['linewidth'] ) and is_numeric( $this->mArgs['linewidth'] ) ) $this->mLineWidth = $this->mArgs['linewidth'];
        if ( isset($this->mArgs['textcolor'] ) ) $this->mTextColor = $this->mArgs['textcolor'];

        //if ( isset($this->mArgs['label'] ) ) $this->mLabel = $this->mArgs['label'];

    }

    protected function generateSource() {
    	require_once('phplot/PHPlot.php');
		$id = md5('phplot_'.microtime().rand(1, 9999));

        // Base

        $args['width'] = $this->mWidth;
        $args['height'] = $this->mHeight;
        $args['data'] = $this->mArgs['data'];
        $args['plottype'] = $this->mPlotType;

        // Appearance

        $args['pointshape'] = $this->mPointShape;
        $args['pointsize'] = $this->mPointSize;
        $args['title'] = $this->mTitle;

        // Color

        $args['backgroundcolor'] = $this->mBackgroundColor;
        $args['gridcolor'] = $this->mGridColor;
        $args['legend'] = $this->mLegend;
        $args['linewidth'] = $this->mLineWidth;
        $args['textcolor'] = $this->mTextColor;

        if ( $fh = fopen( InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/phplot/'.$id, 'w' ) ) {
            fwrite( $fh, serialize( $args ) );
            fclose( $fh );
        }

        $this->mLayout = ( $this->mComments ? '<!-- begin '.$this->mName.' phplot -->' : '' ).
            '<img src="'.InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(true).'/phplot/?id='.$id.'" width="'.$this->mWidth.'" height="'.$this->mHeight.'">'.
            ( $this->mComments ? '<!-- end '.$this->mName." phplot -->\n" : '' );
        return true;
    }
}

?>
