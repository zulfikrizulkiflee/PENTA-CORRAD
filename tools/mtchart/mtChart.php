<?php
/**
 * mtChart - Nice looking charts in PHP
 *
 * Based on pChart V1.27d (30.09.2008). Includes the two helper classes pData and pChache.
 *
 * @author Jean-Damien Poglotti
 * @author Christian Studer <christian.studer@meteotest.ch>
 * @package mtChart
 * @license GPL 3.0
 * @version 0.1.2
 */

/*
 ORIGINAL HEADER

 pChart - a PHP class to build charts!
 Copyright (C) 2008 Jean-Damien POGOLOTTI
 Version  1.27d last updated on 09/30/08

 http://pchart.sourceforge.net

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 1,2,3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Script wide constant declarations
 */
define('SCALE_NORMAL',1);
define('SCALE_ADDALL',2);
define('SCALE_START0',3);
define('SCALE_ADDALLSTART0',4);
define('PIE_PERCENTAGE', 1);
define('PIE_LABELS',2);
define('PIE_NOLABEL',3);
define('PIE_PERCENTAGE_LABEL', 4);
define('TARGET_GRAPHAREA',1);
define('TARGET_BACKGROUND',2);
define('ALIGN_TOP_LEFT',1);
define('ALIGN_TOP_CENTER',2);
define('ALIGN_TOP_RIGHT',3);
define('ALIGN_LEFT',4);
define('ALIGN_CENTER',5);
define('ALIGN_RIGHT',6);
define('ALIGN_BOTTOM_LEFT',7);
define('ALIGN_BOTTOM_CENTER',8);
define('ALIGN_BOTTOM_RIGHT',9);
define('GRADIENT_VERTICAL', 1);
define('GRADIENT_HORIZONTAL', 2);

/**
 * Class mtChart
 */
class mtChart {
    /**
     * Class variables
     */
    // Standard palette definition (Greenish)
    protected $Palette = array(
                        '0' => array('R' => 188, 'G' => 224, 'B' => 46),
                        '1' => array('R' => 224, 'G' => 100, 'B' => 46),
                        '2' => array('R' => 224, 'G' => 214, 'B' => 46),
                        '3' => array('R' => 46,  'G' => 151, 'B' => 224),
                        '4' => array('R' => 176, 'G' => 46,  'B' => 224),
                        '5' => array('R' => 224, 'G' => 46,  'B' => 117),
                        '6' => array('R' => 92,  'G' => 224, 'B' => 46),
                        '7' => array('R' => 224, 'G' => 176, 'B' => 46)
    );

    // Image information and static variables
    public    $Picture        = NULL;     // Image resource
    protected $XSize          = NULL;     // Total image size x in pixels
    protected $YSize          = NULL;     // Total image size y in pixels

    // Error management configuration
    protected $ErrorReporting = FALSE;    // Error reporting flag
    protected $ErrorInterface = 'CLI';    // Error output interface (Either 'CLI' or 'GD')
    protected $Errors         = array();  // Error array collection of warning and errors
    protected $ErrorFontName  = 'Fonts/DejaVuSansMono-Bold.ttf'; // Error font filename
    protected $ErrorFontSize  = 6;        // Error font size in pixels

    // Graphing area information
    protected $GArea_X1        = 70;      // Graphing area left X in pixels
    protected $GArea_Y1        = 30;      // Graphing area top Y in pixels
    protected $GArea_X2        = 680;     // Graphing area right X in pixels
    protected $GArea_Y2        = 200;     // Graphing area lower Y in pixels
    protected $GAreaXOffset    = NULL;    // X-offset for graphs when margin is enabled
    protected $VMax            = NULL;    // Maximum value of the y-scale
    protected $VMin            = NULL;    // Minimum value of the y-scale
    protected $VXMax           = NULL;    // Maximum value of the x-scale
    protected $VXMin           = NULL;    // Minimum value of the x-scale
    protected $Divisions       = NULL;    // Manually set number of divisions of the y-scale
    protected $XDivisions      = NULL;    // Manually set number of divisions of the x-scale
    protected $DivisionHeight  = NULL;    // Pixel height of a division on the y-scale
    protected $XDivisionHeight = NULL;    // Pixel width of a division on the x-scale
    protected $DivisionCount   = NULL;    // Effective number of divisions on the y-axis
    protected $XDivisionCount  = NULL;    // Effective number of divisions on the x-axis
    protected $DivisionRatio   = NULL;    // Value-to-pixel ratio on the y-scale
    protected $XDivisionRatio  = NULL;    // Value-to-pixel ratio on the x-scale
    protected $DivisionWidth   = NULL;    // Pixel width of a division on the y-axis
    protected $XDivisionWidth  = NULL;    // Pixel width of a division on the x-axis
    protected $XInterval       = 1;       // Interval for skipping labels and grid lines on the x-axis
    protected $DataCount       = NULL;    // Number of items in the data array
    protected $Currency        = '$';     // Current currency symbol
    protected $MinDivHeight    = 25;      // Minimum pixel heigth of a division

    // Text configuration
    protected $FontName       = NULL;     // Font filename
    protected $DefaultFont    = 'Fonts/DejaVuSansCondensed.ttf'; // Default font filename
    protected $FontSize       = 8;        // Font size in pixels
    protected $DateFormat     = 'm/d/Y';  // Current date format string
    protected $UseStrftime    = FALSE;    // Flag to switch between the date function and strftime for dates
    protected $TimeFormat     = 'H:i:s';  // Current time format string

    // Line configuration
    protected $LineWidth      = 1;        // Line width in pixels
    protected $LineDotSize    = 0;        // Dot length in pixels for dotted lines

    // Layer configuration
    protected $Layers         = array();  // Image layers for composite images array

    // Antialiasing configuration
    protected $AntialiasQuality = 0;         // Quality of the anti aliasing, 0 is maxmimum, 100 is minimum

    // Shadows configuration
    protected $ShadowActive    = FALSE;   // Flag to activate shadows
    protected $ShadowXDistance = 1;       // x-offset of the shadow in pixels
    protected $ShadowYDistance = 1;       // y-offset of the shadow in pixels
    protected $ShadowRColor    = 60;      // Shadow color R component
    protected $ShadowGColor    = 60;      // Shadow color G component
    protected $ShadowBColor    = 60;      // Shadow color B component
    protected $ShadowAlpha     = 50;      // Shadow alpha component
    protected $ShadowBlur      = 0;       // Shadow blur radius in pixels (Warning: Performance hog)

    // Image map settings
    protected $BuildMap         = FALSE;  // Flag to enable image map building
    protected $ImageMap         = array(); // Image map points array
    protected $MapFunction      = NULL;   // Used internally to identify the currently built image map
    protected $tmpFolder        = 'Cache/'; // Directory to save image maps to
    protected $MapID            = NULL;   // Current image map name

    // Data arrays
    protected $Data = array();            // Main data array
    protected $DataDescription = array(); // Main data description array

    // Caching settings
    protected $CacheFolder = 'Cache/';    // Current cache directory
    protected $ScriptID = NULL;           // Current script ID
    protected $CacheEnabled = FALSE;      // Flag to enable caching
    protected $Hash = NULL;               // Current hash

    /**
     * Construct an new image
     *
     * Draws a transparent background and sets the
     * font to DejaVuSansCondensed, 8 pixels. It will also
     * set up the graphing area with default borders.
     *
     * @param int $XSize = 700
     * @param int $YSize = 230
     */
    public function __construct($XSize = 700, $YSize = 230) {
        $this->XSize   = $XSize;
        $this->YSize   = $YSize;
        $this->Picture = imagecreatetruecolor($XSize, $YSize);

        $C_White = $this->AllocateColor($this->Picture, 255, 255, 255);
        imagefilledrectangle($this->Picture, 0, 0, $XSize, $YSize, $C_White);
        imagecolortransparent($this->Picture, $C_White);

        $this->setFontProperties($this->DefaultFont, $this->FontSize);
        $this->setGraphArea(70, 30, $XSize - 20, $YSize - 30);

        // Set up default data descriptions
        $this->DataDescription['Position']    = 'Name';
        $this->DataDescription['Format']['X'] = 'number';
        $this->DataDescription['Format']['Y'] = 'number';
        $this->DataDescription['Unit']['X']   = NULL;
        $this->DataDescription['Unit']['Y']   = NULL;
    }

    /**
     * Activate warnings and set interface
     *
     * Activates the error reporting and defaults to the command line echoing of
     * error and warnings. Alternatively you can choose to output warnings and errors
     * directly to the image with the interface 'GD'.
     *
     * $Interface accepts the following values:
     *   - CLI (Standard out, default)
     *   - GD (Graphical output)
     *
     * @param string $Interface = 'CLI'
     */
    public function reportWarnings($Interface = 'CLI') {
        $this->ErrorReporting = TRUE;
        $this->ErrorInterface = $Interface;
    }

    /**
     * Set current font name and the font size
     *
     * First checks if a .ttf-filename has been given. If not,
     * searches for the font in the Fonts subdirectory. If yes,
     * will look for the font first at the given path, then in the
     * Fonts subdirectory. If no found was found, will switch back
     * to the default font (Deja Vu Sans Condensed).
     *
     * Example: To load Deja Vu Sans Mono, you could call with the
     * following $FontNames:
     *  - 'DejaVuSansMono'
     *  - './Fonts/DejaVuSansMono.ttf'
     *  - 'DejaVuSansMono.ttf'
     *
     * Default FontSize is 8px.
     *
     * @param string $FontName
     * @param int $FontSize = NULL
     * @return boolean $FontFound
     */
    public function setFontProperties($FontName, $FontSize = NULL) {
        $currentDir = realpath(dirname(__FILE__)) . '/';
        $FontFound = FALSE;

        if(strtolower(substr($FontName, -4)) == '.ttf') {
            // A file name has been given
            if(file_exists($FontName)) {
                // Absolute file name has been given
                $this->FontName = realpath($FontName);
                $FontFound = TRUE;
            } else if(file_exists($currentDir . $FontName)) {
                // Relative file name has been given
                $this->FontName = realpath($currentDir . $FontName);
                $FontFound = TRUE;
            } else if(file_exists($currentDir . 'Fonts/' . $FontName)) {
                // File name for Fonts directory has been given
                $this->FontName = realpath($currentDir . 'Fonts/' . $FontName);
                $FontFound = TRUE;
            }  else {
                // Font file not found
                $this->Errors[] = '[Warning] setFontProperties - Font file ' . $FontName . ' not found.';
                $this->FontName = realpath($currentDir . $this->DefaultFont);
            }
        } else if(file_exists($currentDir . 'Fonts/' . $FontName . '.ttf')) {
            // Only font name has been given
            $this->FontName = realpath($currentDir . 'Fonts/' . $FontName . '.ttf');
            $FontFound = TRUE;
        } else {
            // Font not found
            $this->Errors[] = '[Warning] setFontProperties - Font ' . $FontName . ' not found.';
            $this->FontName = realpath($currentDir . $this->DefaultFont);
        }

        if(isset($FontSize)) {
            $this->FontSize = $FontSize;
        }

        return $FontFound;
    }

    /**
     * Activate shadows for the following drawing operations
     *
     * Defaults to a half visible slight lower right
     * grey shadow.
     *
     * $XDistance and $YDistance describe the offset (default to 1 pixels)
     * The parameters $R, $G, $B and $Alpha describe color and transparency.
     * Optionally blurring of the shadow can be activated by giving a blurring
     * width in pixels.
     *
     * Warning: Blurring is rather resource intense.
     *
     * @param int $XDistance = 1
     * @param int $YDistance = 1
     * @param int $R = 60
     * @param int $G = 60
     * @param int $B = 60
     * @param int $Alpha = 50
     * @param int $Blur = 0
     */
    public function setShadowProperties($XDistance = 1, $YDistance = 1, $R = 60, $G = 60, $B = 60, $Alpha = 50, $Blur = 0) {
        $this->ShadowActive    = TRUE;
        $this->ShadowXDistance = $XDistance;
        $this->ShadowYDistance = $YDistance;
        $this->ShadowRColor    = $R;
        $this->ShadowGColor    = $G;
        $this->ShadowBColor    = $B;
        $this->ShadowAlpha     = $Alpha;
        $this->ShadowBlur      = $Blur;
    }

    /**
     * Disable shadows for the following drawing operations
     *
     */
    public function clearShadow() {
        $this->ShadowActive = FALSE;
    }

    /**
     * Set color for a series
     *
     * Either pass the series number (0, 1, 2) or the name
     * of the series as first parameter.
     *
     * @param string $ID
     * @param int $R
     * @param int $G
     * @param int $B
     */
    public function setColorPalette($ID, $R, $G, $B) {
        if($R < 0) { $R = 0; } if($R > 255) { $R = 255; }
        if($G < 0) { $G = 0; } if($G > 255) { $G = 255; }
        if($B < 0) { $B = 0; } if($B > 255) { $B = 255; }

        $this->Palette[$ID]['R'] = $R;
        $this->Palette[$ID]['G'] = $G;
        $this->Palette[$ID]['B'] = $B;
    }

    /**
     * Create a gradient color palette between two colors
     *
     * The parameter $Shades choses how many colors will be
     * created between color one and two. (Defaults to 8).
     *
     * Colors will then be applied to the series starting with
     * id 0.
     *
     * @param int $R1
     * @param int $G1
     * @param int $B1
     * @param int $R2
     * @param int $G2
     * @param int $B2
     * @param int $Shades = 8
     */
    public function createColorGradientPalette($R1, $G1, $B1, $R2, $G2, $B2, $Shades = 8) {
        if($Shades < 1) { $Shades = 1; }
        if($R1 < 0) { $R1 = 0; } if($R1 > 255) { $R1 = 255; }
        if($G1 < 0) { $G1 = 0; } if($G1 > 255) { $G1 = 255; }
        if($B1 < 0) { $B1 = 0; } if($B1 > 255) { $B1 = 255; }
        if($R2 < 0) { $R2 = 0; } if($R2 > 255) { $R2 = 255; }
        if($G2 < 0) { $G2 = 0; } if($G2 > 255) { $G2 = 255; }
        if($B2 < 0) { $B2 = 0; } if($B2 > 255) { $B2 = 255; }

        $RFactor = ($R2 - $R1) / $Shades;
        $GFactor = ($G2 - $G1) / $Shades;
        $BFactor = ($B2 - $B1) / $Shades;

        for($i = 0; $i < $Shades; ++$i) {
            $this->Palette[$i]['R'] = (int) $R1 + ($RFactor * $i);
            $this->Palette[$i]['G'] = (int) $G1 + ($GFactor * $i);
            $this->Palette[$i]['B'] = (int) $B1 + ($BFactor * $i);
        }
    }

    /**
     * Load color palette from file
     *
     * The file needs to be in the format:
     * R,G,B
     * ...
     *
     * All other lines are ignored. The delimiter can be optionally
     * changed to any other character.
     *
     * Colors will then be applied to the series starting with
     * id 0.
     *
     * @param string $FileName
     * @param string $Delimiter = ','
     * @return boolean $success
     */
    public function loadColorPalette($FileName, $Delimiter = ',') {
        $success = FALSE;
        $handle  = @fopen($FileName, 'r');
        $ColorID = 0;

        if($handle) {
            $success = TRUE;
            while($buffer = fgets($handle)) {
                $Values = split($Delimiter, trim($buffer));
                if(count($Values) == 3) {
                    $this->Palette[$ColorID]['R'] = $Values[0];
                    $this->Palette[$ColorID]['G'] = $Values[1];
                    $this->Palette[$ColorID]['B'] = $Values[2];
                    ++$ColorID;
                }
            }
        } else {
            // File not readable
            $this->Errors[] = '[Warning] loadColorPalette - File ' . $FileName . ' not found or not readable.';
        }

        return $success;
    }

    /**
     * Set line style
     *
     * All following line drawing operations will use this line
     * width and optionally draw dotted lines.
     *
     * All measurements in pixels.
     *
     * Example:
     *  $DotSize = 4 will draw a dotted line with 4 pixels set
     *  and 4 pixels not set alternatively.
     *
     * @param int $Width = 1
     * @param int $DotSize = 0
     */
    public function setLineStyle($Width = 1, $DotSize = 0) {
        $this->LineWidth   = $Width;
        $this->LineDotSize = $DotSize;
    }

    /**
     * Set currency symbol
     *
     * Change the current currency symbol. The default is $.
     *
     * @param string $Currency
     */
    public function setCurrency($Currency) {
        $this->Currency = $Currency;
    }

    /**
     * Set graph area in pixels
     *
     * This area will be used to draw graphs, grid, axis & more. Calling this function
     * will not draw anything this will only set the graph area boundaries.
     *
     * The default values are chosen for the default graph size.
     *
     * @param int $X1 = 70
     * @param int $Y1 = 30
     * @param int $X2 = 680
     * @param int $Y2 = 200
     */
    public function setGraphArea($X1 = 70, $Y1 = 30, $X2 = 680, $Y2 = 200) {
        $this->GArea_X1 = $X1;
        $this->GArea_Y1 = $Y1;
        $this->GArea_X2 = $X2;
        $this->GArea_Y2 = $Y2;
    }

    /**
     * Prepare graph area
     *
     * Fill the graph area with the background color (Defaults to
     * a light grey) and surrounds it with a thin border.
     *
     * Optionally draw diagonal stripes over the graph.
     *
     * @param int $R = 250
     * @param int $G = 250
     * @param int $B = 250
     * @param boolean $Stripe = FALSE
     * @param int $StripeDistance = 4
     */
    public function drawGraphArea($R = 250, $G = 250, $B = 250, $Stripe = FALSE, $StripeDistance = 4) {
        // Draw background
        $this->drawFilledRectangle($this->GArea_X1, $this->GArea_Y1, $this->GArea_X2, $this->GArea_Y2, $R, $G, $B, FALSE);
        $this->drawRectangle($this->GArea_X1, $this->GArea_Y1, $this->GArea_X2, $this->GArea_Y2, $R - 40, $G - 40, $B - 40);

        if($Stripe) {
            // Draw stripes
            $LineColor = $this->AllocateColor($this->Picture, $R, $G, $B, -15);
            $SkewWidth = $this->GArea_Y2 - $this->GArea_Y1 - 1;

            for($i = $this->GArea_X1 - $SkewWidth; $i <= $this->GArea_X2; $i += $StripeDistance) {
                $X1 = $i;
                $Y1 = $this->GArea_Y2;
                $X2 = $i + $SkewWidth;
                $Y2 = $this->GArea_Y1;

                if($X1 < $this->GArea_X1) {
                    $X1 = $this->GArea_X1;
                    $Y1 = $this->GArea_Y1 + $X2 - $this->GArea_X1 + 1;
                }

                if($X2 >= $this->GArea_X2) {
                    $Y2 = $this->GArea_Y1 + $X2 - $this->GArea_X2 +1;
                    $X2 = $this->GArea_X2 - 1;
                }

                imageline($this->Picture, $X1, $Y1, $X2, $Y2 + 1, $LineColor);
            }
        }
    }

    /**
     * Clear current scale
     *
     * Call this to clear the current scale before drawing a second graph with
     * a different scale onto the same picture.
     */
    public function clearScale() {
        $this->VMin       = NULL;
        $this->VMax       = NULL;
        $this->VXMin      = NULL;
        $this->VXMax      = NULL;
        $this->Divisions  = NULL;
        $this->XDivisions = NULL;
    }

    /**
     * Configure fixed scale
     *
     * Use this to bypass automatic scaling for both the Y as
     * well the X axis.
     *
     * $VMin/$VXMin is the starting value.
     * $XMax/$VXMax is the highest value.
     * $Divisions/$XDivisions are the value-distances between the ticks
     * for the scale on this axis.
     *
     * VXMax and XDivisions is only set if VXMin is not NULL.
     *
     * @param $VMin
     * @param $VMax
     * @param $Divisions = 5
     * @param $VXMin = NULL
     * @param $VXMax = NULL
     * @param $XDivisions = 5
     */
    public function setFixedScale($VMin, $VMax, $Divisions = 5, $VXMin = NULL, $VXMax = NULL, $XDivisions = 5) {
        $this->VMin      = $VMin;
        $this->VMax      = $VMax;
        $this->Divisions = $Divisions;

        if(isset($VXMin)) {
            $this->VXMin      = $VXMin;
            $this->VXMax      = $VXMax;
            $this->XDivisions = $XDivisions;
        }
    }

    /**
     * Draw right scale
     *
     * Computes and draws a scale on the right side of the current plot.
     *
     * Refer to the documention of drawScale() for parameter details.
     *
     * @param int $ScaleMode = SCALE_NORMAL
     * @param int $R = 150
     * @param int $G = 150
     * @param int $B = 150
     * @param boolean $DrawTicks = TRUE
     * @param int $Angle = 0
     * @param int $Decimals = 1
     * @param boolean $WithMargin = FALSE
     */
    public function drawRightScale($ScaleMode = SCALE_NORMAL, $R = 150, $G = 150, $B = 150, $DrawTicks = TRUE, $Angle = 0, $Decimals = 1, $WithMargin = FALSE) {
        $this->drawScale($ScaleMode, $R, $G, $B, $DrawTicks, $Angle, $Decimals, $WithMargin, TRUE);
    }

    /**
     * Draw scale
     *
     * Computes and draws a scale for the current plot and the given data.
     *
     * Available scale modes:
     *  - SCALE_NORMAL (Scale determined by plotted values)
     *  - SCALE_START0 (Starts at 0)
     *  - SCALE_ADDALL (Scale determined by summed values)
     *  - SCALE_ADDALLSTART0 (Summed scale starts at 0)
     *  - Manually setting the scale with setFixedScale()
     *
     * @param int $ScaleMode = SCALE_NORMAL
     * @param int $R = 150
     * @param int $G = 150
     * @param int $B = 150
     * @param bool $DrawTicks = TRUE
     * @param int $Angle = 0
     * @param int $Decimals = 1
     * @param bool $WithMargin = FALSE
     * @param bool $RightScale = FALSE
     */
    public function drawScale($ScaleMode = SCALE_NORMAL, $R = 150, $G = 150, $B = 150, $DrawTicks = TRUE, $Angle = 0, $Decimals = 1, $WithMargin = FALSE, $RightScale = FALSE) {
        $this->validateData('drawScale');
        $C_TextColor = $this->AllocateColor($this->Picture, $R, $G, $B);

        // Draw axis's
        $this->drawLine($this->GArea_X1, $this->GArea_Y1, $this->GArea_X1, $this->GArea_Y2, $R, $G, $B);
        $this->drawLine($this->GArea_X1, $this->GArea_Y2, $this->GArea_X2, $this->GArea_Y2, $R, $G, $B);

        // If scale is not set manually
        if(is_null($this->VMin) || is_null($this->VMax)) {
            // Set arbitrary limits
            if(isset($this->DataDescription['Values'][0])) {
                if(is_null($this->VMin)) {
                    $this->VMin = $this->Data[0][$this->DataDescription['Values'][0]];
                }
                if(is_null($this->VMax)) {
                    $this->VMax = $this->Data[0][$this->DataDescription['Values'][0]];
                }
            }

            // Compute min/max
            switch($ScaleMode) {
                case SCALE_START0:
                    $this->VMin = 0;
                    // Break intentionally omitted.
                default:
                    // Break intentionally omitted.
                case SCALE_NORMAL:
                    foreach($this->Data as $Key => $Values) {
                        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
                            if(isset($this->Data[$Key][$ColName])) {
                                $Value = $this->Data[$Key][$ColName];

                                if(is_numeric($Value)) {
                                    if(is_null($this->VMax) || $this->VMax < $Value) {
                                        $this->VMax = $Value;
                                    }
                                    if(is_null($this->VMin) || $this->VMin > $Value) {
                                        $this->VMin = $Value;
                                    }
                                }
                            }
                        }
                    }
                    break;
                case SCALE_ADDALLSTART0:
                    $this->VMin = 0;
                    // Break intentionally omitted.
                case SCALE_ADDALL:
                    foreach($this->Data as $Key => $Values) {
                        $Sum = 0;
                        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
                            if(isset($this->Data[$Key][$ColName])) {
                                $Value = $this->Data[$Key][$ColName];
                                if(is_numeric($Value)) {
                                    $Sum  += $Value;
                                }
                            }
                        }

                        if(is_null($this->VMax) || $this->VMax < $Sum) {
                            $this->VMax = $Sum;
                        }

                        if(is_null($this->VMin) || $this->VMin > $Sum) {
                            $this->VMin = $Sum;
                        }
                    }
                    break;
            }

            // Special case: All values are the same
            if($this->VMax == $this->VMin) {
                if($this->VMax >= 0) {
                    ++$this->VMax;
                    $this->VMin = $this->VMax - 1;
                } else {
                    --$this->VMin;
                    $this->VMax = $this->VMin + 1;
                }
            }

            // Compute automatic scaling
            $DataRange = $this->VMax - $this->VMin;
            $MaxDivs = ($this->GArea_Y2 - $this->GArea_Y1) / $this->MinDivHeight;
            $Divisions = floor($MaxDivs);

            $scale = $this->computeScale($this->VMin, $this->VMax, $Divisions);

            $this->VMax = $scale['max'];
            $this->VMin = $scale['min'];
            $Divisions = $scale['divisions'];
        } else {
            // Scale set manually
            $Divisions = $this->Divisions;
        }

        $this->DataRange = $this->VMax - $this->VMin;
        if($this->DataRange == 0) {
            $this->DataRange = 0.1;
        }

        $this->DivisionCount = max($Divisions, 1);
        $this->DivisionHeight = ($this->GArea_Y2 - $this->GArea_Y1) / $this->DivisionCount;
        $this->DivisionRatio  = ($this->GArea_Y2 - $this->GArea_Y1) / $this->DataRange;
        $this->GAreaXOffset  = 0;
        $this->DataCount = count($this->Data);

         
        if($this->DataCount > 1) {
            if($WithMargin == FALSE) {
                $this->DivisionWidth = ($this->GArea_X2 - $this->GArea_X1) / ($this->DataCount - 1);
            } else {
                $this->DivisionWidth = ($this->GArea_X2 - $this->GArea_X1) / $this->DataCount;
                $this->GAreaXOffset  = $this->DivisionWidth / 2;
            }
        } else {
            $this->DivisionWidth = $this->GArea_X2 - $this->GArea_X1;
            $this->GAreaXOffset  = $this->DivisionWidth / 2;
        }

        // Draw ticks and labels if necessary
        if($DrawTicks) {
            $YPos = $this->GArea_Y2;
            $XMin = NULL;
            for($i = 1;  $i <= $Divisions + 1; ++$i) {
                if($RightScale) {
                    $this->drawLine($this->GArea_X2, $YPos, $this->GArea_X2 + 5, $YPos, $R, $G, $B);
                } else {
                    $this->drawLine($this->GArea_X1, $YPos, $this->GArea_X1 - 5, $YPos, $R, $G, $B);
                }

                $Value = $this->VMin + ($i - 1) * (($this->VMax - $this->VMin) / $Divisions);
                $Value = round($Value, $Decimals);
                $Value = $this->formatValue($Value, $this->DataDescription['Format']['Y'], $this->DataDescription['Unit']['Y']);

                $Position  = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Value));
                $TextWidth = $Position[2] - $Position[0];

                if($RightScale) {
                    imagettftext($this->Picture, $this->FontSize, 0, $this->GArea_X2 + 10, $YPos + ($this->FontSize / 2), $C_TextColor, $this->FontName, utf8_decode($Value));
                    if($XMin < $this->GArea_X2 + 15 + $TextWidth || $XMin == NULL) {
                        $XMin = $this->GArea_X2 + 15 + $TextWidth;
                    }
                } else {
                    imagettftext($this->Picture, $this->FontSize, 0, $this->GArea_X1 - 10 - $TextWidth, $YPos + ($this->FontSize / 2), $C_TextColor, $this->FontName, utf8_decode($Value));
                    if($XMin > $this->GArea_X1 - 10 - $TextWidth || $XMin == NULL) {
                        $XMin = $this->GArea_X1 - 10 - $TextWidth;
                    }
                }

                $YPos = $YPos - $this->DivisionHeight;
            }

            // Write the y-axis caption if set
            if(isset($this->DataDescription['Axis']['Y'])) {
                $Position   = imageftbbox($this->FontSize, 90, $this->FontName, $this->DataDescription['Axis']['Y']);
                $TextHeight = abs($Position[1]) + abs($Position[3]);
                $TextTop    = (($this->GArea_Y2 - $this->GArea_Y1) / 2) + $this->GArea_Y1 + ($TextHeight / 2);

                if($RightScale) {
                    imagettftext($this->Picture, $this->FontSize, 90, $XMin + $this->FontSize, $TextTop, $C_TextColor, $this->FontName, utf8_decode($this->DataDescription['Axis']['Y']));
                } else {
                    imagettftext($this->Picture, $this->FontSize, 90, $XMin - $this->FontSize, $TextTop, $C_TextColor, $this->FontName, utf8_decode($this->DataDescription['Axis']['Y']));
                }
            }

            // Horizontal axis
            $XPos = $this->GArea_X1 + $this->GAreaXOffset;
            $ID = 0;
            $YMax = NULL;
            foreach($this->Data as $Key => $Values) {
                // Only draw every $XInterval'st label
                if($ID == 0 || $ID % $this->XInterval == 0) {
                    $this->drawLine(floor($XPos), $this->GArea_Y2, floor($XPos), $this->GArea_Y2 + 5, $R, $G, $B);
                    $Value = $this->formatValue($this->Data[$Key][$this->DataDescription['Position']], $this->DataDescription['Format']['X'], $this->DataDescription['Unit']['X']);

                    $Position   = imageftbbox($this->FontSize, $Angle, $this->FontName, utf8_decode($Value));
                    $TextWidth  = abs($Position[2]) + abs($Position[0]);
                    $TextHeight = abs($Position[1]) + abs($Position[3]);

                    if($Angle == 0) {
                        $YPos = $this->GArea_Y2 + 18;
                        imagettftext($this->Picture, $this->FontSize, 0, floor($XPos) - floor($TextWidth / 2), $YPos, $C_TextColor, $this->FontName, utf8_decode($Value));
                    } else {
                        $YPos = $this->GArea_Y2 + 10 + $TextHeight;
                        if($Angle <= 90) {
                            imagettftext($this->Picture, $this->FontSize, $Angle, floor($XPos) - $TextWidth + 5, $YPos, $C_TextColor, $this->FontName, utf8_decode($Value));
                        } else {
                            imagettftext($this->Picture, $this->FontSize, $Angle, floor($XPos) + $TextWidth + 5, $YPos, $C_TextColor, $this->FontName, utf8_decode($Value));
                        }
                    }

                    if($YMax < $YPos || $YMax == NULL) {
                        $YMax = $YPos;
                    }
                }

                $XPos += $this->DivisionWidth;
                ++$ID;
            }

            // Write the x-axis caption if set
            if(isset($this->DataDescription['Axis']['X'])) {
                $Position   = imageftbbox($this->FontSize, 90, $this->FontName, utf8_decode($this->DataDescription['Axis']['X']));
                $TextWidth  = abs($Position[2]) + abs($Position[0]);
                $TextLeft   = (($this->GArea_X2 - $this->GArea_X1) / 2) + $this->GArea_X1 + ($TextWidth / 2);
                imagettftext($this->Picture, $this->FontSize, 0, $TextLeft, $YMax+$this->FontSize + 5, $C_TextColor, $this->FontName, utf8_decode($this->DataDescription['Axis']['X']));
            }
        }
    }

    /**
     * Compute a scale for these values
     *
     * Needs at least a minumum and maximum value which
     * should be included, a number of intervals ($nint) and
     * a base set of round numbers (Defaults to natural numbers.)
     *
     * @param float $min
     * @param float $max
     * @param int $nint = 0
     * @param array $p = NULL
     * @param float $origin = NULL
     * @return array $scale = array('min' => ..., 'max' => ...)
     */
    private function computeScale($min, $max, $nint = 0, $p = NULL, $origin = NULL) {
        // Correct max/min if necessary
        if($max < $min) {
            list($max, $min) = array($min, $max);
        }

        // Estimate a number of intervals if necessary
        if(0 == $nint) {
            if($max - $min) {
                $nint = min(floor(10 * log10($max - $min)), 17);
            } else {
                $nint = 1;
            }
        }

        // Prepare values
        $A = $min;
        $B = $max;
        $R = $B - $A;

        // If all values equal, abort somehow
        if(! $R) {
            return array('min' => $A, 'max' => $A + 1, 'divisions' => 1);
        }

        // Prepare nice numbers if necessary
        if(is_null($p)) {
            $p = range(1, 9);
        } else {
            sort($p);
            $p_temp = array();
            foreach($p as $thisP) {
                if($thisP >= 1 && $thisP < 10) {
                    $p_temp[] = $thisP;
                }
            }

            $p = $p_temp;
        }

        $n = count($p);
        $t_i = floor(log10($R / $nint));

        if(($R / $nint) / pow(10, $t_i) <= max($p)) {
            $k = $t_i;
        } else {
            $k = $t_i + 1;
        }

        $i = 0;
        foreach($p as $index => $thisP) {
            if($thisP >= ($R / $nint) / pow(10, $k)) {
                break;
            }
            $i = $index;
        }

        $b = $B - 1; // To enter the while loop at least once

        while($B > $b) {
            $s = $p[$i] * pow(10, $k);
            $a = $s * floor((((($B + $A) / 2) - ($s * $nint / 2)) / $s));
            $b = $a + ($s * $nint);

            if($i < ($n - 1)) {
                ++$i;
            } else {
                $i = 0;
                ++$k;
            }
        }

        if(is_null($origin) && (0 == $A || 0 == $B)) {
            $origin = 0;
        }

        if(!is_null($origin)) {
            if($origin <= $A) {
                $a = $origin;
                $b = $B + $s;
            } else if($origin >= $B) {
                $a = -$A + $s;
                $b = $origin;
            } else {
                $a = $A - $s;
                $b = $B + $s;
            }
        }

        // Normalize scale
        $a += fmod($a, $s);
        $b -= fmod($b, $s);
        
        while($a <= $A - $s) {
            $a += $s;
        }
        
        while($b >= $B + $s) {
            $b -= $s;
        }
        
        if(fmod($b - $a, $nint)) {
            $nint = ($b - $a) / $s;
        }

        return array('min' => $a, 'max' => $b, 'divisions' => $nint);
    }

    /**
     * Format value according to current format
     *
     * @param float $Value
     * @param string $Format
     * @param string $Unit
     * @return string $FormattedValue
     */
    private function formatValue($Value, $Format, $Unit) {
        switch($Format) {
            default: // Unknown formatting function
                $this->Errors[] = '[Warning] formatValue - Formatting ' . $Format . ' unknown.';
                break;
            case 'number':
                $Value .= $Unit;
                break;
            case 'time':
                $Value = $this->ToTime($Value);
                break;
            case 'date':
                $Value = $this->ToDate($Value);
                break;
            case 'metric':
                $Value = $this->ToMetric($Value);
                break;
            case 'currency':
                $Value = $this->ToCurrency($Value);
                break;
        }
        return $Value;
    }

    /**
     * Compute and draw the scale for X/Y charts
     *
     * @param string $YSerieName
     * @param string $XSerieName
     * @param int $R
     * @param int $G
     * @param int $B
     * @param bool $WithMargin = FALSE
     * @param int $Angle = 0
     * @param int $Decimals = 1
     */
    public function drawXYScale($YSerieName, $XSerieName, $R, $G, $B, $WithMargin = 0, $Angle = 0, $Decimals = 1) {
        $this->validateData('drawScale');

        $C_TextColor =$this->AllocateColor($this->Picture, $R, $G, $B);
        $this->drawLine($this->GArea_X1, $this->GArea_Y1, $this->GArea_X1, $this->GArea_Y2, $R, $G, $B);
        $this->drawLine($this->GArea_X1, $this->GArea_Y2, $this->GArea_X2, $this->GArea_Y2, $R, $G, $B);

        // Y-scale first
        // If scale is not set manually
        if(is_null($this->VMin) || is_null($this->VMax)) {
            // Set arbitrary limits
            if(isset($this->Data[0][$YSerieName])) {
                if(is_null($this->VMin)) {
                    $this->VMin = $this->Data[0][$YSerieName];
                }
                if(is_null($this->VMax)) {
                    $this->VMax = $this->Data[0][$YSerieName];
                }
            } 

            foreach($this->Data as $Key => $Values) {
                if(isset($this->Data[$Key][$YSerieName])) {
                    $Value = $this->Data[$Key][$YSerieName];
                    if(is_numeric($Value)) {
                        if(is_null($this->VMax) || $this->VMax < $Value) {
                            $this->VMax = $Value;
                        }
                        if(is_null($this->VMin) || $this->VMin > $Value) {
                            $this->VMin = $Value;
                        }
                    }
                }
            }

            // Special case: All values are the same
            if($this->VMax == $this->VMin) {
                if($this->VMax >= 0) {
                    ++$this->VMax;
                    $this->VMin = $this->VMax - 1;
                } else {
                    --$this->VMin;
                    $this->VMax = $this->VMin + 1;
                }
            }

            // Compute automatic scaling
            $DataRange = $this->VMax - $this->VMin;
            $MaxDivs = ($this->GArea_Y2 - $this->GArea_Y1) / $this->MinDivHeight;
            $Divisions = floor($MaxDivs);

            $scale = $this->computeScale($this->VMin, $this->VMax, $Divisions);

            $this->VMax = $scale['max'];
            $this->VMin = $scale['min'];
            $Divisions = $scale['divisions'];
        } else {
            $Divisions = $this->Divisions;
        }

        $this->DivisionCount = $Divisions;

        $this->DataRange = $this->VMax - $this->VMin;
        if($this->DataRange == 0) {
            $this->DataRange = 0.1;
        }

        $this->DivisionCount = max($Divisions, 1);
        $this->DivisionHeight = ($this->GArea_Y2 - $this->GArea_Y1) / $this->DivisionCount;
        $this->DivisionRatio  = ($this->GArea_Y2 - $this->GArea_Y1) / $this->DataRange;
        $this->GAreaXOffset  = 0;
        $this->DataCount = count($this->Data);

        $YPos = $this->GArea_Y2;
        $XMin = NULL;

        for($i = 1; $i <= $Divisions + 1; ++$i) {
            $this->drawLine($this->GArea_X1, $YPos, $this->GArea_X1 - 5, $YPos, $R, $G, $B);
            $Value     = $this->VMin + ($i - 1) * (($this->VMax - $this->VMin) / $Divisions);
            $Value     = round($Value, $Decimals);
            $Value     = $this->formatValue($Value, $this->DataDescription['Format']['Y'], $Value.$this->DataDescription['Unit']['Y']);

            $Position  = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Value));
            $TextWidth = $Position[2] - $Position[0];
            imagettftext($this->Picture, $this->FontSize, 0, $this->GArea_X1 - 10 - $TextWidth, $YPos + ($this->FontSize / 2), $C_TextColor, $this->FontName, utf8_decode($Value));

            if($XMin > $this->GArea_X1 - 10 - $TextWidth || $XMin == NULL) {
                $XMin = $this->GArea_X1 - 10 - $TextWidth;
            }

            $YPos -= $this->DivisionHeight;
        }

        // X-scale second
        // If scale is not set manually
        if(is_null($this->VXMin) || is_null($this->VXMax)) {
            // Set arbitrary limits
            if(isset($this->Data[0][$XSerieName])) {
                if(is_null($this->VXMin)) {
                    $this->VXMin = $this->Data[0][$XSerieName];
                }
                if(is_null($this->VXMax)) {
                    $this->VXMax = $this->Data[0][$XSerieName];
                }
            } 

            foreach($this->Data as $Key => $Values) {
                if(isset($this->Data[$Key][$XSerieName])) {
                    $Value = $this->Data[$Key][$XSerieName];
                    if(is_numeric($Value)) {
                        if(is_null($this->VXMax) || $this->VXMax < $Value) {
                            $this->VXMax = $Value;
                        }
                        if(is_null($this->VXMin) || $this->VXMin > $Value) {
                            $this->VXMin = $Value;
                        }
                    }
                }
            }

            // Special case: All values are the same
            if($this->VXMax == $this->VXMin) {
                if($this->VXMax >= 0) {
                    ++$this->VXMax;
                    $this->VXMin = $this->VXMax - 1;
                } else {
                    --$this->VXMin;
                    $this->VXMax = $this->VXMin + 1;
                }
            }

            // Compute automatic scaling
            $DataRange = $this->VXMax - $this->VXMin;
            $MaxDivs = ($this->GArea_X2 - $this->GArea_X1) / $this->MinDivHeight;
            $Divisions = floor($MaxDivs);

            $scale = $this->computeScale($this->VXMin, $this->VXMax, $Divisions);

            $this->VXMax = $scale['max'];
            $this->VXMin = $scale['min'];
            $Divisions = $scale['divisions'];
        } else {
            $Divisions = $this->XDivisions;
        }

        $this->XDivisionCount = $Divisions;

        $this->XDataRange = $this->VXMax - $this->VXMin;
        if($this->XDataRange == 0) {
            $this->XDataRange = 0.1;
        }

        $this->XDivisionCount = max($Divisions, 1);
        $this->XDivisionHeight = ($this->GArea_X2 - $this->GArea_X1) / $this->XDivisionCount;
        $this->XDivisionRatio  = ($this->GArea_X2 - $this->GArea_X1) / $this->XDataRange;

        $XPos = $this->GArea_X1;
        $YMax = NULL;
        
        for($i = 1; $i <= $Divisions + 1; ++$i) {
            $this->drawLine($XPos, $this->GArea_Y2, $XPos, $this->GArea_Y2 + 5, $R, $G, $B);

            $Value     = $this->VXMin + ($i - 1) * (($this->VXMax - $this->VXMin) / $Divisions);
            $Value     = round($Value, $Decimals);
            $Value     = $this->formatValue($Value, $this->DataDescription['Format']['Y'], $this->DataDescription['Unit']['Y']);

            $Position   = imageftbbox($this->FontSize, $Angle, $this->FontName, utf8_decode($Value));
            $TextWidth  = abs($Position[2]) + abs($Position[0]);
            $TextHeight = abs($Position[1]) + abs($Position[3]);

            if($Angle == 0) {
                $YPos = $this->GArea_Y2 + 18;
                imagettftext($this->Picture, $this->FontSize, 0, floor($XPos) - floor($TextWidth / 2), $YPos, $C_TextColor, $this->FontName, utf8_decode($Value));
            } else {
                $YPos = $this->GArea_Y2 + 10 + $TextHeight;
                if($Angle <= 90) {
                    imagettftext($this->Picture, $this->FontSize, $Angle, floor($XPos) - $TextWidth + 5, $YPos, $C_TextColor, $this->FontName, utf8_decode($Value));
                } else {
                    imagettftext($this->Picture, $this->FontSize, $Angle, floor($XPos) + $TextWidth + 5, $YPos, $C_TextColor, $this->FontName, utf8_decode($Value));
                }
            }

            if($YMax < $YPos || $YMax == NULL) {
                $YMax = $YPos;
            }

            $XPos += $this->XDivisionHeight;
        }
                
        // Write the y-axis caption if set
        if(isset($this->DataDescription['Axis']['Y'])) {
            $Position   = imageftbbox($this->FontSize, 90, $this->FontName, utf8_decode($this->DataDescription['Axis']['Y']));
            $TextHeight = abs($Position[1]) + abs($Position[3]);
            $TextTop    = (($this->GArea_Y2 - $this->GArea_Y1) / 2) + $this->GArea_Y1 + ($TextHeight / 2);
            imagettftext($this->Picture, $this->FontSize, 90, $XMin-$this->FontSize, $TextTop, $C_TextColor, $this->FontName, utf8_decode($this->DataDescription['Axis']['Y']));
        }

        // Write the x-axis caption if set
        if(isset($this->DataDescription['Axis']['X'])) {
            $Position   = imageftbbox($this->FontSize, 90, $this->FontName, utf8_decode($this->DataDescription['Axis']['X']));
            $TextWidth  = abs($Position[2]) + abs($Position[0]);
            $TextLeft   = (($this->GArea_X2 - $this->GArea_X1) / 2) + $this->GArea_X1 + ($TextWidth / 2);
            imagettftext($this->Picture, $this->FontSize, 0, $TextLeft, $YMax+$this->FontSize + 5, $C_TextColor, $this->FontName, utf8_decode($this->DataDescription['Axis']['X']));
        }
    }

    /**
     * Draw grid and an optional mosaic over the plotting area
     *
     * Defaults to a grey grid with mosaic. Before calling
     * this method, please set up a scale.
     *
     * @param int $LineWidth = 4
     * @param boolean $Mosaic = TRUE
     * @param int $R = 220
     * @param int $G = 200
     * @param int $B = 200
     * @param int $Alpha = 100
     */
    public function drawGrid($LineWidth = 4, $Mosaic = TRUE, $R = 220, $G = 220, $B = 220, $Alpha = 100) {
        if($Mosaic) {
            $LayerWidth  = $this->GArea_X2 - $this->GArea_X1;
            $LayerHeight = $this->GArea_Y2 - $this->GArea_Y1;

            $this->Layers[0] = imagecreatetruecolor($LayerWidth, $LayerHeight);
            $C_White = $this->AllocateColor($this->Layers[0], 255, 255, 255);
            imagefilledrectangle($this->Layers[0], 0, 0, $LayerWidth, $LayerHeight, $C_White);
            imagecolortransparent($this->Layers[0], $C_White);

            $C_Rectangle = $this->AllocateColor($this->Layers[0], 250, 250, 250);

            $YPos  = $LayerHeight;
            $LastY = $YPos;
            for($i = 0; $i <= $this->DivisionCount; ++$i) {
                $LastY = $YPos;
                $YPos  -= $this->DivisionHeight;

                if($YPos <= 0) {
                    $YPos = 1;
                }

                if($i % 2 == 0) {
                    imagefilledrectangle($this->Layers[0], 1, $YPos, $LayerWidth-1, $LastY, $C_Rectangle);
                }
            }
            imagecopymerge($this->Picture, $this->Layers[0], $this->GArea_X1, $this->GArea_Y1, 0, 0, $LayerWidth, $LayerHeight, $Alpha);
            imagedestroy($this->Layers[0]);
        }

        // Horizontal lines
        $YPos = $this->GArea_Y2 - $this->DivisionHeight;
        for($i = 1; $i <= $this->DivisionCount; ++$i) {
            if($YPos > $this->GArea_Y1 && $YPos < $this->GArea_Y2) {
                $this->drawDottedLine($this->GArea_X1, $YPos, $this->GArea_X2, $YPos, $LineWidth, $R, $G, $B);
            }
            $YPos -= $this->DivisionHeight;
        }

        // Vertical lines
        if($this->GAreaXOffset == 0) {
            // Start with the first line not directly on the y-axis
            $XPos = $this->GArea_X1 + ($this->DivisionWidth * $this->XInterval);
            $ColCount = $this->DataCount - 2;
        } else {
            $XPos = $this->GArea_X1 + $this->GAreaXOffset;
            $ColCount = floor(($this->GArea_X2 - $this->GArea_X1) / $this->DivisionWidth);
        }

        for($i = 1; $i <= $ColCount; ++$i) {
            if($XPos > $this->GArea_X1 && $XPos < $this->GArea_X2) {
                $this->drawDottedLine(floor($XPos), $this->GArea_Y1, floor($XPos), $this->GArea_Y2, $LineWidth, $R, $G, $B);
            }
            $XPos += ($this->DivisionWidth * $this->XInterval);
        }
    }

    /**
     * Get the size of the legend box
     *
     * @param boolean $forPie = FALSE
     * @return array $MaxWidthHeight = array($MaxWidth, $MaxHeight)
     */
    private function getLegendBoxSize($forPie = FALSE) {
        $fieldname = NULL;
        $MaxWidth = 0;
        $MaxHeight = 8;

        if($forPie) {
            if(!isset($this->DataDescription['Position'])) {
                return NULL;
            } else {
                $myDescription = $this->DataDescription['Position'];
            }

            foreach($this->Data as $Key => $Value) {
                $Position   = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Value[$myDescription]));
                $TextWidth  = $Position[2] - $Position[0];
                $TextHeight = $Position[1] - $Position[7];
                $MaxWidth = max($TextWidth, $MaxWidth);
                $MaxHeight += $TextHeight + 4;
            }
        } else {
            if(!isset($this->DataDescription['Description'])) {
                return NULL;
            }
            foreach($this->DataDescription['Description'] as $Key => $Value) {
                $Position   = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Value));
                $TextWidth  = $Position[2] - $Position[0];
                $TextHeight = $Position[1] - $Position[7];
                $MaxWidth = max($TextWidth, $MaxWidth);
                $MaxHeight += $TextHeight + 4;
            }
        }

        $MaxHeight -= 3;
        $MaxWidth  += 32;

        return(array($MaxWidth, $MaxHeight));
    }

    /**
     * Draw legend into graph
     *
     * First RGB-tripel describes the color of the optional
     * border (defaults to white), the second its shadow (grey)
     * and the third the text color (black).
     *
     * @param int $XPos
     * @param int $YPos
     * @param int $R = 255
     * @param int $G = 255
     * @param int $B = 255
     * @param int $Rs = NULL
     * @param int $Gs = NULL
     * @param int $Bs = NULL
     * @param int $Rt = 0
     * @param int $Gt = 0
     * @param int $Bt = 0
     * @param boolean $Border = TRUE
     */
    public function drawLegend($XPos, $YPos, $R = 255, $G = 255, $B = 255, $Rs = NULL, $Gs = NULL, $Bs = NULL, $Rt = 0, $Gt = 0, $Bt = 0, $Border = TRUE) {
        $this->validateDataDescription('drawLegend');

        if(!isset($this->DataDescription['Description'])) {
            return;
        }

        $C_TextColor = $this->AllocateColor($this->Picture, $Rt, $Gt, $Bt);

        list($MaxWidth, $MaxHeight) = $this->getLegendBoxSize();

        if(is_null($Rs)) {
            $Rs = max($R - 30, 0);
        }
        if(is_null($Gs)) {
            $Gs = max($G - 30, 0);
        }
        if(is_null($Bs)) {
            $Bs = max($B - 30, 0);
        }

        if($Border) {
            $this->drawFilledRoundedRectangle($XPos + 1, $YPos + 1, $XPos + $MaxWidth + 1, $YPos + $MaxHeight + 1, 5, $Rs, $Gs, $Bs);
            $this->drawFilledRoundedRectangle($XPos, $YPos, $XPos + $MaxWidth, $YPos + $MaxHeight, 5, $R, $G, $B);
        }

        $YOffset = 4 + $this->FontSize;
        $ID = 0;
        foreach($this->DataDescription['Description'] as $Key => $Value)
        {
            $this->drawFilledRoundedRectangle($XPos + 10, $YPos + $YOffset - 4, $XPos + 14, $YPos + $YOffset - 4, 2, $this->Palette[$ID]['R'], $this->Palette[$ID]['G'], $this->Palette[$ID]['B']);
            imagettftext($this->Picture, $this->FontSize, 0, $XPos+22, $YPos + $YOffset, $C_TextColor, $this->FontName, utf8_decode($Value));

            $Position   = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Value));
            $TextHeight = $Position[1]-$Position[7];

            $YOffset = $YOffset + $TextHeight + 4;
            ++$ID;
        }
    }

    /**
     * Draw the legend for a pie graph
     *
     * First RGB-tripel describes the color of the optional
     * border (defaults to white), the second its shadow (grey)
     * and the third the text color (black).
     *
     * @param int $XPos
     * @param int $YPos
     * @param int $R = 255
     * @param int $G = 255
     * @param int $B = 255
     * @param int $Rs = NULL
     * @param int $Gs = NULL
     * @param int $Bs = NULL
     * @param int $Rt = 0
     * @param int $Gt = 0
     * @param int $Bt = 0
     * @param boolean $Border = TRUE
     */
    public function drawPieLegend($XPos, $YPos, $R = 255, $G = 255, $B = 255, $Rs = NULL, $Gs = NULL, $Bs = NULL, $Rt = 0, $Gt = 0, $Bt = 0, $Border = TRUE) {
        $this->validateDataDescription('drawPieLegend', FALSE);
        $this->validateData('drawPieLegend');

        if(is_null($this->DataDescription['Position'])) {
            return;
        }

        $C_TextColor = $this->AllocateColor($this->Picture, $Rt, $Gt, $Bt);

        list($MaxWidth, $MaxHeight) = $this->getLegendBoxSize(TRUE);

        if(is_null($Rs)) {
            $Rs = max($R - 30, 0);
        }
        if(is_null($Gs)) {
            $Gs = max($G - 30, 0);
        }
        if(is_null($Bs)) {
            $Bs = max($B - 30, 0);
        }

        if($Border) {
            $this->drawFilledRoundedRectangle($XPos + 1, $YPos + 1, $XPos + $MaxWidth + 1, $YPos + $MaxHeight + 1, 5, $Rs, $Gs, $Bs);
            $this->drawFilledRoundedRectangle($XPos, $YPos, $XPos + $MaxWidth, $YPos + $MaxHeight, 5, $R, $G, $B);
        }

        $YOffset = 4 + $this->FontSize;
        $ID = 0;
        foreach($this->Data as $Key => $Value) {
            $Value2     = $Value[$this->DataDescription['Position']];
            $Position   = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Value2));
            $TextHeight = $Position[1] - $Position[7];
            $this->drawFilledRectangle($XPos + 10, $YPos + $YOffset - 6, $XPos + 14, $YPos + $YOffset - 2, $this->Palette[$ID]['R'], $this->Palette[$ID]['G'], $this->Palette[$ID]['B']);

            imagettftext($this->Picture, $this->FontSize, 0, $XPos + 22, $YPos + $YOffset, $C_TextColor, $this->FontName, utf8_decode($Value2));
            $YOffset = $YOffset + $TextHeight + 4;
            ++$ID;
        }
    }

    /**
     * Draw the graph title
     *
     * If you specify $XPos2 and $YPos2, then the text will
     * be centered in the middle of the box ($XPos, $YPos, $XPos2, $YPos2).
     *
     * Default text color is black
     *
     * @param int $XPos
     * @param int $YPos
     * @param string $Value
     * @param int $R = 0
     * @param int $G = 0
     * @param int $B = 0
     * @param $XPos2 = NULL
     * @param $YPos2 = NULL
     * @param $Shadow = FALSE
     */
    public function drawTitle($XPos, $YPos, $Value, $R = 0, $G = 0, $B = 0, $XPos2 = NULL, $YPos2 = NULL, $Shadow = FALSE) {
        $C_TextColor = $this->AllocateColor($this->Picture, $R, $G, $B);
        $Position = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Value));

        if(isset($XPos2)) {
            $TextWidth = $Position[2] - $Position[0];
            $XPos      = floor(($XPos2 - $XPos - $TextWidth) / 2) + $XPos;
        }

        if(isset($YPos2)) {
            $TextHeight = $Position[5] - $Position[3];
            $YPos       = floor(($YPos2 - $YPos - $TextHeight) / 2) + $YPos;
        }

        if($Shadow) {
            $C_ShadowColor = $this->AllocateColor($this->Picture, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor);
            imagettftext($this->Picture, $this->FontSize, 0, $XPos + $this->ShadowXDistance, $YPos + $this->ShadowYDistance, $C_ShadowColor, $this->FontName, utf8_decode($Value));
        }

        imagettftext($this->Picture, $this->FontSize, 0, $XPos, $YPos, $C_TextColor, $this->FontName, utf8_decode($Value));
    }

    /**
     * Draw a text box
     *
     * Text color defaults to white with shadows and no
     * background. If all background colors ($BgR, $BgG, $BgB)
     * are set, the text box will have this background color and
     * the given $Alpha transparency (Between 0 and 100).
     *
     * Possible values for $Align:
     *   - ALIGN_TOP_LEFT Use the box top left corner.
     *   - ALIGN_TOP_CENTER Use the box top center corner.
     *   - ALIGN_TOP_RIGHT Use the box top right corner.
     *   - ALIGN_LEFT Use the center left. (Default)
     *   - ALIGN_CENTER Use the center.
     *   - ALIGN_RIGHT Use the center right.
     *   - ALIGN_BOTTOM_LEFT Use the box bottom left corner.
     *   - ALIGN_BOTTOM_CENTER Use the box bottom center corner.
     *   - ALIGN_BOTTOM_RIGHT Use the box bottom right corner.
     *
     * @param int $X1
     * @param int $Y1
     * @param int $X2
     * @param int $Y2
     * @param string $Text
     * @param int $Angle = 0
     * @param int $R = 255
     * @param int $G = 255
     * @param int $B = 255
     * @param int $Align = ALIGN_LEFT
     * @param boolean $Shadow = TRUE
     * @param $BgR = NULL
     * @param $BgG = NULL
     * @param $BgB = NULL
     * @param int $Alpha = 100
     */
    public function drawTextBox($X1, $Y1, $X2, $Y2, $Text, $Angle = 0, $R = 255, $G = 255, $B = 255, $Align = ALIGN_LEFT, $Shadow = TRUE, $BgR = NULL, $BgG = NULL, $BgB = NULL, $Alpha = 100) {
        $Position   = imageftbbox($this->FontSize, $Angle, $this->FontName, utf8_decode($Text));
        $TextWidth  = $Position[2] - $Position[0];
        $TextHeight = $Position[5] - $Position[3];
        $AreaWidth  = $X2 - $X1;
        $AreaHeight = $Y2 - $Y1;

        if(isset($BgR, $BgG, $BgB)) {
            $this->drawFilledRectangle($X1, $Y1, $X2, $Y2, $BgR, $BgG, $BgB, FALSE, $Alpha);
        }

        switch($Align) {
            case ALIGN_TOP_LEFT:
                $X = $X1 + 1;
                $Y = $Y1 + $this->FontSize + 1;
                break;
            case ALIGN_TOP_CENTER:
                $X = $X1 + ($AreaWidth / 2) - ($TextWidth / 2);
                $Y = $Y1 + $this->FontSize + 1;
                break;
            case ALIGN_TOP_RIGHT:
                $X = $X2 - $TextWidth - 1;
                $Y = $Y1 + $this->FontSize + 1;
                break;
            default: // Break intentionally omitted
            case ALIGN_LEFT:
                $X = $X1 + 1;
                $Y = $Y1 + ($AreaHeight / 2) - ($TextHeight / 2);
                break;
            case ALIGN_CENTER:
                $X = $X1 + ($AreaWidth / 2) - ($TextWidth / 2);
                $Y = $Y1 + ($AreaHeight / 2) - ($TextHeight / 2);
                break;
            case ALIGN_RIGHT:
                $X = $X2 - $TextWidth - 1;
                $Y = $Y1 + ($AreaHeight / 2) - ($TextHeight / 2);
                break;
            case ALIGN_BOTTOM_LEFT:
                $X = $X1 + 1;
                $Y = $Y2 - 1;
                break;
            case ALIGN_BOTTOM_CENTER:
                $X = $X1 + ($AreaWidth / 2) - ($TextWidth / 2);
                $Y = $Y2 - 1;
                break;
            case ALIGN_BOTTOM_RIGHT:
                $X = $X2 - $TextWidth - 1;
                $Y = $Y2 - 1;
                break;
        }

        if($Shadow) {
            $C_ShadowColor = $this->AllocateColor($this->Picture, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor);
            imagettftext($this->Picture, $this->FontSize, $Angle, $X + $this->ShadowXDistance, $Y + $this->ShadowYDistance, $C_ShadowColor, $this->FontName, utf8_decode($Text));
        }

        $C_TextColor   = $this->AllocateColor($this->Picture, $R, $G, $B);
        imagettftext($this->Picture, $this->FontSize, $Angle, $X, $Y, $C_TextColor, $this->FontName, utf8_decode($Text));
    }

    /**
     * Draw a horizontal threshold line with optional text
     *
     * Line and text color defaults to reddish. To draw a
     * continuous line instead of a dotted one, set the
     * $TickWidth to 0.
     *
     * @param float $Value
     * @param int $R = 255
     * @param int $G = 80
     * @param int $B = 51
     * @param boolean $ShowLabel = FALSE
     * @param boolean $ShowOnRight = FALSE
     * @param int $TickWidth = 4
     * @param string $FreeText = NULL
     */
    public function drawTreshold($Value, $R = 255, $G = 80, $B = 51, $ShowLabel = FALSE, $ShowOnRight = FALSE, $TickWidth = 4, $FreeText = NULL) {
        $C_TextColor = $this->AllocateColor($this->Picture, $R, $G, $B);
        $Y = $this->GArea_Y2 - ($Value - $this->VMin) * $this->DivisionRatio;

        // Check if it falls into graph
        if($Y <= $this->GArea_Y1 || $Y >= $this->GArea_Y2) {
            return;
        }

        if($TickWidth == 0) {
            $this->drawLine($this->GArea_X1, $Y, $this->GArea_X2, $Y, $R, $G, $B);
        } else {
            $this->drawDottedLine($this->GArea_X1, $Y, $this->GArea_X2, $Y, $TickWidth, $R, $G, $B);
        }

        if($ShowLabel) {
            if(is_null($FreeText)) {
                $Label = $Value;
            } else {
                $Label = $FreeText;
            }

            if($ShowOnRight) {
                imagettftext($this->Picture, $this->FontSize, 0, $this->GArea_X2 + 2, $Y + ($this->FontSize / 2), $C_TextColor, $this->FontName, utf8_decode($Label));
            } else {
                imagettftext($this->Picture, $this->FontSize, 0, $this->GArea_X1 + 2, $Y - ($this->FontSize / 2), $C_TextColor, $this->FontName, utf8_decode($Label));
            }
        }
    }

    /**
     * Draw a vertical line with optional text
     *
     * Line and text color defaults to reddish. To draw a
     * continuous line instead of a dotted one, set the
     * $TickWidth to 0.
     *
     * An optional free text can be positioned at one of the following
     * places:
     *  - ALIGN_TOP_RIGHT (Default)
     *  - ALIGN_TOP_LEFT
     *  - ALIGN_BOTTOM_RIGHT
     *  - ALIGN_BOTTOM_LEFT
     *
     * @param float $ValueName
     * @param string $SerieName = 'Serie1'
     * @param int $R = 255
     * @param int $G = 80
     * @param int $B = 51
     * @param int $TickWidth = 4
     * @param string $FreeText = NULL
     * @param int $TextPosition = ALIGN_TOP_RIGHT
     */
    public function drawVerticalLine($ValueName, $SerieName = 'Serie1', $R = 255, $G = 80, $B = 51, $TickWidth = 4, $FreeText = NULL, $TextPosition = ALIGN_TOP_RIGHT) {
        $C_TextColor = $this->AllocateColor($this->Picture, $R, $G, $B);

        // Find value
        $Cp = 0;
        foreach($this->Data as $Key => $Value) {
            if($this->Data[$Key][$this->DataDescription['Position']] == $ValueName) {
                $NumericalValue = $this->Data[$Key][$SerieName];
                break;
            }
            ++$Cp;
        }

        $X = $this->GArea_X1 + $this->GAreaXOffset + ($this->DivisionWidth * $Cp);

        if($TickWidth == 0) {
            $this->drawLine($X, $this->GArea_Y1, $X, $this->GArea_Y2, $R, $G, $B);
        } else {
            $this->drawDottedLine($X, $this->GArea_Y1, $X, $this->GArea_Y2, $TickWidth, $R, $G, $B);
        }

        if(! is_null($FreeText)) {
            $Position   = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($FreeText));
            $TextHeight = $Position[3] - $Position[5] + 2;
            $TextWidth  = $Position[2] - $Position[0] + 2;

            switch($TextPosition) {
                default: // Break intentionally omitted
                case ALIGN_TOP_RIGHT:
                    $TX = $X + 1;
                    $TY = $this->GArea_Y1 + $TextHeight;
                    break;
                case ALIGN_TOP_LEFT:
                    $TX = $X - $TextWidth - 1;
                    $TY = $this->GArea_Y1 + $TextHeight;
                    break;
                case ALIGN_BOTTOM_LEFT:
                    $TX = $X - $TextWidth - 1;
                    $TY = $this->GArea_Y2 - $TextHeight;
                    break;
                case ALIGN_BOTTOM_RIGHT:
                    $TX = $X + 1;
                    $TY = $this->GArea_Y2 - $TextHeight;
                    break;
            }

            imagettftext($this->Picture, $this->FontSize, 0, $TX, $TY, $C_TextColor, $this->FontName, utf8_decode($FreeText));
        }
    }


    /**
     * Put a text label to a specific point
     *
     * Searches the given series for the value $ValueName
     * and puts a pointed label to its right side.
     *
     * Defaults to a grey background ($R, $G, $B) with
     * black text ($Rt, $Gt, $Bt) with an optional
     * shadow (activated by default).
     *
     * @param string $SerieName = 'Serie1'
     * @param float $ValueName
     * @param string $Caption
     * @param int $R = 210
     * @param int $G = 210
     * @param int $B = 210
     * @param int $Rt = 0
     * @param int $Gt = 0
     * @param int $Bt = 0
     * @param boolean $Shadow = TRUE
     */
    public function setLabel($SerieName = 'Serie1', $ValueName, $Caption, $R = 210, $G = 210, $B = 210, $Rt = 0, $Gt = 0, $Bt = 0, $Shadow = TRUE) {
        $this->validateDataDescription('setLabel');
        $this->validateData('setLabel');
        $C_Label      = $this->AllocateColor($this->Picture, $R, $G, $B);
        $C_TextColor  = $this->AllocateColor($this->Picture, $Rt, $Gt, $Bt);

        $Cp = 0;
        foreach($this->Data as $Key => $Value) {
            if($this->Data[$Key][$this->DataDescription['Position']] == $ValueName) {
                $NumericalValue = $this->Data[$Key][$SerieName];
                break;
            }
            ++$Cp;
        }

        $XPos = $this->GArea_X1 + $this->GAreaXOffset + ($this->DivisionWidth * $Cp) + 2;
        $YPos = $this->GArea_Y2 - ($NumericalValue - $this->VMin) * $this->DivisionRatio;

        $Position   = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Caption));
        $TextHeight = $Position[3] - $Position[5] + 2;
        $TextWidth  = $Position[2] - $Position[0] + 2;
        $TextOffset = floor($TextHeight / 2);

        // Draw Shadow
        if($Shadow) {
            $C_Shadow = $this->AllocateColor($this->Picture, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor);
            $Poly = array($XPos + 1, $YPos + 1, $XPos + 9, $YPos - $TextOffset, $XPos + 8, $YPos + $TextOffset + 2);
            imagefilledpolygon($this->Picture, $Poly, 3, $C_Shadow);
            $this->drawLine($XPos, $YPos + 1, $XPos + 9, $YPos - $TextOffset - 0.2, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor);
            $this->drawLine($XPos, $YPos + 1, $XPos + 9, $YPos + $TextOffset + 2.2, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor);
            $this->drawFilledRectangle($XPos + 9, $YPos - $TextOffset - 0.2, $XPos + 13 + $TextWidth, $YPos + $TextOffset + 2.2, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor);
        }

        // Draw Label background
        $Poly = array($XPos, $YPos, $XPos + 8, $YPos - $TextOffset - 1, $XPos + 8, $YPos + $TextOffset + 1);
        imagefilledpolygon($this->Picture, $Poly, 3, $C_Label);
        $this->drawLine($XPos-1, $YPos, $XPos + 8, $YPos - $TextOffset - 1.2, $R, $G, $B);
        $this->drawLine($XPos-1, $YPos, $XPos + 8, $YPos + $TextOffset + 1.2, $R, $G, $B);
        $this->drawFilledRectangle($XPos + 8, $YPos - $TextOffset - 1.2, $XPos + 12 + $TextWidth, $YPos + $TextOffset + 1.2, $R, $G, $B);

        imagettftext($this->Picture, $this->FontSize, 0, $XPos + 10, $YPos + $TextOffset, $C_TextColor, $this->FontName, utf8_decode($Caption));
    }

    /* This function draw a plot graph */
    /**
     * Draw a plot graph
     *
     * If the color triple $R2, $G2, $B2 isn't
     * NULL, all plot circles will be colored with
     * these parameters. Otherwise the color of the
     * lines and cirles is taken from the the current
     * color palette.
     *
     * @param int $BigRadius = 5
     * @param int $SmallRadius = 2
     * @param int $R2 = NULL
     * @param int $G2 = NULL
     * @param int $B2 = NULL
     * @param boolean $Shadow = FALSE
     */
    public function drawPlotGraph($BigRadius = 5, $SmallRadius = 2, $R2 = NULL, $G2 = NULL, $B2 = NULL, $Shadow = FALSE) {
        $this->validateDataDescription('drawPlotGraph');
        $this->validateData('drawPlotGraph');

        $GraphID = 0;
        $Ro = $R2;
        $Go = $G2;
        $Bo = $B2;

        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            $ID = 0;
            foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                if($keyI == $ColName) {
                    $ColorID = $ID;
                }
                ++$ID;
            }

            $R = $this->Palette[$ColorID]['R'];
            $G = $this->Palette[$ColorID]['G'];
            $B = $this->Palette[$ColorID]['B'];
            $R2 = $Ro;
            $G2 = $Go;
            $B2 = $Bo;

            // Load symbol image
            if(isset($this->DataDescription['Symbol'][$ColName])) {
                $Is_Alpha = ((ord (file_get_contents ($this->DataDescription['Symbol'][$ColName], false, null, 25, 1)) & 6) & 4) == 4;

                $Infos       = getimagesize($this->DataDescription['Symbol'][$ColName]);
                $ImageWidth  = $Infos[0];
                $ImageHeight = $Infos[1];
                $Symbol      = imagecreatefromgif($this->DataDescription['Symbol'][$ColName]);
            }

            $XPos  = $this->GArea_X1 + $this->GAreaXOffset;
            $Hsize = round($BigRadius / 2);
            $R3 = NULL;
            $G3 = NULL;
            $B3 = NULL;

            foreach($this->Data as $Key => $Values) {
                $Value = $this->Data[$Key][$ColName];
                $YPos  = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);

                // Save point to image map if required
                if($this->BuildMap) {
                    $this->addToImageMap($XPos - $Hsize, $YPos - $Hsize, $XPos + 1 + $Hsize, $YPos + $Hsize + 1, $this->DataDescription['Description'][$ColName], $this->Data[$Key][$ColName].$this->DataDescription['Unit']['Y'], 'Plot');
                }

                // Draw value point
                if(is_numeric($Value)) {
                    // Check for image as dot
                    if(@is_null($this->DataDescription['Symbol'][$ColName])) {
                        // Draw shadow
                        if($Shadow) {
                            if(isset($R3, $G3, $B3)) {
                                $this->drawFilledCircle($XPos+2, $YPos+2, $BigRadius, $R3, $G3, $B3);
                            } else {
                                $R3 = $this->Palette[$ColorID]['R']-20;
                                $G3 = $this->Palette[$ColorID]['G']-20;
                                $B3 = $this->Palette[$ColorID]['B']-20;
                                $this->drawFilledCircle($XPos+2, $YPos+2, $BigRadius, $R3, $G3, $B3);
                            }
                        }

                        // Draw dot
                        $this->drawFilledCircle($XPos+1, $YPos+1, $BigRadius, $R, $G, $B);

                        // Draw inner circle
                        if($SmallRadius > 0) {
                            if(! isset($R2, $G2, $B2)) {
                                $R2 = $this->Palette[$ColorID]['R'] - 15;
                                $G2 = $this->Palette[$ColorID]['G'] - 15;
                                $B2 = $this->Palette[$ColorID]['B'] - 15;
                            }

                            $this->drawFilledCircle($XPos + 1, $YPos + 1, $SmallRadius, $R2, $G2, $B2);
                        }
                    } else {
                        imagecopymerge($this->Picture, $Symbol, $XPos+1-$ImageWidth/2, $YPos+1-$ImageHeight/2, 0, 0, $ImageWidth, $ImageHeight,100);
                    }
                }

                $XPos += $this->DivisionWidth;
            }
            ++$GraphID;
        }
    }

    /* This function draw a plot graph in an X/Y space */
    /**
     * Draw an X/Y plot graph
     *
     * @param string $YSerieName
     * @param string $XSerieName
     * @param int $PaletteID = 0
     * @param int $BigRadius = 5
     * @param int $SmallRadius 2
     * @param int $R2 = NULL
     * @param int $G2 = NULL
     * @param int $B2 = NULL
     * @param boolean $Shadow = TRUE
     */
    public function drawXYPlotGraph($YSerieName, $XSerieName, $PaletteID = 0, $BigRadius = 5, $SmallRadius = 2, $R2 = NULL, $G2 = NULL, $B2 = NULL, $Shadow = TRUE) {
        $R = $this->Palette[$PaletteID]['R'];
        $G = $this->Palette[$PaletteID]['G'];
        $B = $this->Palette[$PaletteID]['B'];
        $R3 = NULL;
        $G3 = NULL;
        $B3 = NULL;

        foreach($this->Data as $Key => $Values) {
            if(isset($this->Data[$Key][$YSerieName], $this->Data[$Key][$XSerieName])) {
                $Y = $this->GArea_Y2 - (($Y-$this->VMin) * $this->DivisionRatio);
                $X = $this->GArea_X1 + (($X-$this->VXMin) * $this->XDivisionRatio);

                // Draw shadow
                if($Shadow) {
                    if(isset($R3, $G3, $B3)) {
                        $this->drawFilledCircle($X+2, $Y+2, $BigRadius, $R3, $G3, $B3);
                    } else {
                        $R3 = $this->Palette[$PaletteID]['R'] - 20;
                        $G3 = $this->Palette[$PaletteID]['G'] - 20;
                        $B3 = $this->Palette[$PaletteID]['B'] - 20;
                        $this->drawFilledCircle($X + 2, $Y + 2, $BigRadius, $R3, $G3, $B3);
                    }
                }

                // Draw circle
                $this->drawFilledCircle($X+1, $Y+1, $BigRadius, $R, $G, $B);

                // Draw inner circle
                if($SmallRadius > 0) {
                    if(isset($R2, $G2, $B2)) {
                        $this->drawFilledCircle($X + 1, $Y + 1, $SmallRadius, $R2, $G2, $B2);
                    } else {
                        $R2 = $this->Palette[$PaletteID]['R'] + 20;
                        $G2 = $this->Palette[$PaletteID]['G'] + 20;
                        $B2 = $this->Palette[$PaletteID]['B'] + 20;
                        $this->drawFilledCircle($X + 1, $Y + 1, $SmallRadius, $R2, $G2, $B2);
                    }
                }
            }
        }
    }

    /**
     * Draw the area between two series
     *
     * @param string $Serie1
     * @param string $Serie2
     * @param int $R
     * @param int $G
     * @param int $B
     * @param int $Alpha = 50
     */
    public function drawArea($Serie1, $Serie2, $R, $G, $B, $Alpha = 50) {
        $this->validateData('drawArea');

        $LayerWidth  = $this->GArea_X2 - $this->GArea_X1;
        $LayerHeight = $this->GArea_Y2 - $this->GArea_Y1;

        $this->Layers[0] = imagecreatetruecolor($LayerWidth, $LayerHeight);
        $C_White         = $this->AllocateColor($this->Layers[0], 255, 255, 255);
        imagefilledrectangle($this->Layers[0], 0, 0, $LayerWidth, $LayerHeight, $C_White);
        imagecolortransparent($this->Layers[0], $C_White);

        $C_Graph = $this->AllocateColor($this->Layers[0], $R, $G, $B);

        $XPos     = $this->GAreaXOffset;
        $LastXPos = NULL;
        foreach($this->Data as $Key => $Values) {
            $Value1 = $this->Data[$Key][$Serie1];
            $Value2 = $this->Data[$Key][$Serie2];
            $YPos1  = $LayerHeight - (($Value1-$this->VMin) * $this->DivisionRatio);
            $YPos2  = $LayerHeight - (($Value2-$this->VMin) * $this->DivisionRatio);

            if(isset($LastXPos)) {
                $Points   = array();
                $Points[] = $LastXPos;
                $Points[] = $LastYPos1;
                $Points[] = $LastXPos;
                $Points[] = $LastYPos2;
                $Points[] = $XPos;
                $Points[] = $YPos2;
                $Points[] = $XPos;
                $Points[] = $YPos1;

                imagefilledpolygon($this->Layers[0], $Points, 4, $C_Graph);
            }

            $LastYPos1 = $YPos1;
            $LastYPos2 = $YPos2;
            $LastXPos  = $XPos;

            $XPos += $this->DivisionWidth;
        }

        imagecopymerge($this->Picture, $this->Layers[0], $this->GArea_X1, $this->GArea_Y1, 0, 0, $LayerWidth, $LayerHeight, $Alpha);
        imagedestroy($this->Layers[0]);
    }


    /**
     * Write the values on top of the series
     *
     * Defaults to the first series
     *
     * @param string/array $Series = 'Serie1'
     */
    public function writeValues($Series = 'Serie1') {
        $this->validateDataDescription('writeValues');
        $this->validateData('writeValues');

        if(!is_array($Series)) {
            $Series = array($Series);
        }

        foreach($Series as $Key => $Serie) {
            $ID = 0;
            foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                if($keyI == $Serie) {
                    $ColorID = $ID;
                }
                ++$ID;
            }

            $XPos  = $this->GArea_X1 + $this->GAreaXOffset;
            foreach($this->Data as $Key => $Values) {
                if(isset($this->Data[$Key][$Serie]) && is_numeric($this->Data[$Key][$Serie])) {
                    $Value = $this->Data[$Key][$Serie];
                    $YPos = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);

                    $Positions = imagettfbbox($this->FontSize, 0, $this->FontName, utf8_decode($Value));
                    $Width  = $Positions[2] - $Positions[6];
                    $XOffset = $XPos - ($Width / 2);
                    $Height = $Positions[3] - $Positions[7];
                    $YOffset = $YPos - 4;

                    $C_TextColor = $this->AllocateColor($this->Picture, $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B']);
                    imagettftext($this->Picture, $this->FontSize, 0, $XOffset, $YOffset, $C_TextColor, $this->FontName, utf8_decode($Value));
                }
                $XPos += $this->DivisionWidth;
            }
        }
    }

    /**
     * Draw a line graph
     *
     * Either draws all registered series or just
     * the one specified with $SerieName.
     *
     * @param string $SerieName = NULL
     */
    public function drawLineGraph($SerieName = NULL) {
        $this->validateDataDescription('drawLineGraph');
        $this->validateData('drawLineGraph');

        $GraphID = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            $ID = 0;
            foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                if($keyI == $ColName) {
                    $ColorID = $ID;
                }
                ++$ID;
            }

            if(is_null($SerieName) || $SerieName == $ColName) {
                $XPos  = $this->GArea_X1 + $this->GAreaXOffset;
                $XLast = NULL;
                foreach($this->Data as $Key => $Values) {
                    if(isset($this->Data[$Key][$ColName])) {
                        $Value = $this->Data[$Key][$ColName];
                        $YPos = $this->GArea_Y2 - (($Value - $this->VMin) * $this->DivisionRatio);

                        // Save to image map
                        if($this->BuildMap) {
                            $this->addToImageMap($XPos - 3, $YPos - 3, $XPos + 3, $YPos + 3, $this->DataDescription['Description'][$ColName], $this->Data[$Key][$ColName].$this->DataDescription['Unit']['Y'], 'Line');
                        }

                        if(!is_numeric($Value)) {
                            $XLast = NULL;
                        }

                        if(isset($XLast)) {
                            $this->drawLine($XLast, $YLast, $XPos, $YPos, $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B'], TRUE);
                        }

                        $XLast = $XPos;
                        $YLast = $YPos;
                    }
                    $XPos += $this->DivisionWidth;
                }
                ++$GraphID;
            }
        }
    }

    /**
     * Draw a XY graph
     *
     * @param string $YSerieName
     * @param string $XSerieName
     * @param int $PaletteID = 0
     */
    public function drawXYGraph($YSerieName, $XSerieName, $PaletteID = 0) {
        $YLast = NULL;
        $XLast = NULL;

        foreach($this->Data as $Key => $Values) {
            if(isset($this->Data[$Key][$YSerieName], $this->Data[$Key][$XSerieName])) {
                $X = $this->Data[$Key][$XSerieName];
                $Y = $this->Data[$Key][$YSerieName];

                $Y = $this->GArea_Y2 - (($Y-$this->VMin) * $this->DivisionRatio);
                $X = $this->GArea_X1 + (($X-$this->VXMin) * $this->XDivisionRatio);

                if(isset($XLast, $YLast)) {
                    $this->drawLine($XLast, $YLast, $X, $Y, $this->Palette[$PaletteID]['R'], $this->Palette[$PaletteID]['G'], $this->Palette[$PaletteID]['B'], TRUE);
                }

                $XLast = $X;
                $YLast = $Y;
            }
        }
    }

    /**
     * Draw cubic curve graph
     *
     * Either draws all registered series or just
     * the one specified with $SerieName.
     *
     * @param float $Accuracy = 0.1
     * @param string $SerieName = NULL
     */
    public function drawCubicCurve($Accuracy = 0.1, $SerieName = NULL) {
        $this->validateDataDescription('drawCubicCurve');
        $this->validateData('drawCubicCurve');

        $GraphID = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            if(is_null($SerieName) || $SerieName == $ColName) {
                $XIn = array();
                $YIn = array();
                $Yt = array();
                $U = array();
                $XIn[0] = 0;
                $YIn[0] = 0;

                $ID = 0;
                foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                    if($keyI == $ColName) {
                        $ColorID = $ID;
                    }
                    ++$ID;
                }

                $Index = 1;
                $XLast = NULL;
                $Missing = array();
                foreach($this->Data as $Key => $Values) {
                    if(isset($this->Data[$Key][$ColName])) {
                        $Value = $this->Data[$Key][$ColName];
                        $XIn[$Index] = $Index;
                        $YIn[$Index] = $Value;
                        if(!is_numeric($Value)) {
                            $Missing[$Index] = TRUE;
                        }
                        ++$Index;
                    }
                }
                --$Index;

                $Yt[0] = 0;
                $Yt[1] = 0;
                $U[1]  = 0;
                for($i = 2; $i <= $Index - 1; ++$i) {
                    $Sig    = ($XIn[$i] - $XIn[$i-1]) / ($XIn[$i+1] - $XIn[$i-1]);
                    $p      = $Sig * $Yt[$i-1] + 2;
                    $Yt[$i] = ($Sig - 1) / $p;
                    $U[$i]  = ($YIn[$i+1] - $YIn[$i]) / ($XIn[$i+1] - $XIn[$i]) - ($YIn[$i] - $YIn[$i-1]) / ($XIn[$i] - $XIn[$i-1]);
                    $U[$i]  = (6 * $U[$i] / ($XIn[$i+1] - $XIn[$i-1]) - $Sig * $U[$i-1]) / $p;
                }

                $qn = 0;
                $un = 0;
                $Yt[$Index] = ($un - $qn * $U[$Index-1]) / ($qn * $Yt[$Index-1] + 1);

                for($k = $Index - 1; $k >= 1; --$k) {
                    $Yt[$k] = $Yt[$k] * $Yt[$k+1] + $U[$k];
                }

                $XPos  = $this->GArea_X1 + $this->GAreaXOffset;
                for($X = 1; $X <= $Index; $X += $Accuracy) {
                    $klo = 1;
                    $khi = $Index;
                    $k   = $khi - $klo;
                    while($k > 1) {
                        $k = $khi - $klo;
                        if($XIn[$k] >= $X) {
                            $khi = $k;
                        } else {
                            $klo = $k;
                        }
                    }
                    $klo = $khi - 1;

                    $h     = $XIn[$khi] - $XIn[$klo];
                    $a     = ($XIn[$khi] - $X) / $h;
                    $b     = ($X - $XIn[$klo]) / $h;
                    $Value = $a * $YIn[$klo] + $b * $YIn[$khi] + (($a*$a*$a - $a) * $Yt[$klo] + ($b*$b*$b - $b) * $Yt[$khi]) * ($h*$h) / 6;

                    $YPos = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);

                    if(isset($XLast) && !isset($Missing[floor($X)]) && !isset($Missing[floor($X+1)])) {
                        $this->drawLine($XLast, $YLast, $XPos, $YPos, $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B'], TRUE);
                    }

                    $XLast = $XPos;
                    $YLast = $YPos;
                    $XPos  = $XPos + $this->DivisionWidth * $Accuracy;
                }

                // Add potentialy missing values
                $XPos  = $XPos - $this->DivisionWidth * $Accuracy;
                if($XPos < ($this->GArea_X2 - $this->GAreaXOffset)) {
                    $YPos = $this->GArea_Y2 - (($YIn[$Index] - $this->VMin) * $this->DivisionRatio);
                    $this->drawLine($XLast, $YLast, $this->GArea_X2 - $this->GAreaXOffset, $YPos, $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B'], TRUE);
                }

                ++$GraphID;
            }
        }
    }

    /**
     * Draw a filled cubic curve
     *
     * @param float $Accuracy = 0.1
     * @param int $Alpha = 100
     * @param boolean $AroundZero = FALSE
     */
    public function drawFilledCubicCurve($Accuracy = 0.1, $Alpha = 100, $AroundZero = FALSE) {
        $this->validateDataDescription('drawFilledCubicCurve');
        $this->validateData('drawFilledCubicCurve');

        $LayerWidth  = $this->GArea_X2 - $this->GArea_X1;
        $LayerHeight = $this->GArea_Y2 - $this->GArea_Y1;
        $YZero = $LayerHeight - ((0 - $this->VMin) * $this->DivisionRatio);

        if($YZero > $LayerHeight) {
            $YZero = $LayerHeight;
        }

        $GraphID = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            $XIn = array();
            $Yin = array();
            $Yt = array();
            $U = array();
            $XIn[0] = 0;
            $YIn[0] = 0;

            $ID = 0;
            foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                if($keyI == $ColName) {
                    $ColorID = $ID;
                }
                ++$ID;
            }

            $Index = 1;
            $XLast = NULL;
            $Missing = array();

            foreach($this->Data as $Key => $Values) {
                $Value = $this->Data[$Key][$ColName];
                $XIn[$Index] = $Index;
                $YIn[$Index] = $Value;
                if(!is_numeric($Value)) {
                    $Missing[$Index] = TRUE;
                }
                ++$Index;
            }
            --$Index;

            $Yt[0] = 0;
            $Yt[1] = 0;
            $U[1]  = 0;
            for($i = 2; $i <= $Index - 1; ++$i) {
                $Sig    = ($XIn[$i] - $XIn[$i-1]) / ($XIn[$i+1] - $XIn[$i-1]);
                $p      = $Sig * $Yt[$i-1] + 2;
                $Yt[$i] = ($Sig - 1) / $p;
                $U[$i]  = ($YIn[$i+1] - $YIn[$i]) / ($XIn[$i+1] - $XIn[$i]) - ($YIn[$i] - $YIn[$i-1]) / ($XIn[$i] - $XIn[$i-1]);
                $U[$i]  = (6 * $U[$i] / ($XIn[$i+1] - $XIn[$i-1]) - $Sig * $U[$i-1]) / $p;
            }

            $qn = 0;
            $un = 0;
            $Yt[$Index] = ($un - $qn * $U[$Index-1]) / ($qn * $Yt[$Index-1] + 1);

            for($k = $Index - 1; $k >= 1; --$k) {
                $Yt[$k] = $Yt[$k] * $Yt[$k+1] + $U[$k];
            }

            $Points   = array();
            $Points[] = $this->GAreaXOffset;
            $Points[] = $LayerHeight;

            $this->Layers[0] = imagecreatetruecolor($LayerWidth, $LayerHeight);
            $C_White         = $this->AllocateColor($this->Layers[0], 255, 255, 255);
            imagefilledrectangle($this->Layers[0], 0, 0, $LayerWidth, $LayerHeight, $C_White);
            imagecolortransparent($this->Layers[0], $C_White);

            $YLast = NULL;
            $XPos  = $this->GAreaXOffset;
            $PointsCount = 2;
            for($X = 1; $X <= $Index; $X += $Accuracy) {
                $klo = 1;
                $khi = $Index;
                $k   = $khi - $klo;
                while($k > 1) {
                    $k = $khi - $klo;
                    if($XIn[$k] >= $X) {
                        $khi = $k;
                    } else {
                        $klo = $k;
                    }
                }
                $klo = $khi - 1;

                $h     = $XIn[$khi] - $XIn[$klo];
                $a     = ($XIn[$khi] - $X) / $h;
                $b     = ($X - $XIn[$klo]) / $h;
                $Value = $a * $YIn[$klo] + $b * $YIn[$khi] + (($a*$a*$a - $a) * $Yt[$klo] + ($b*$b*$b - $b) * $Yt[$khi]) * ($h*$h) / 6;

                $YPos = $LayerHeight - (($Value-$this->VMin) * $this->DivisionRatio);

                if(isset($YLast) && $AroundZero && !isset($Missing[floor($X)]) && !isset($Missing[floor($X+1)])) {
                    $aPoints   = array();
                    $aPoints[] = $XLast;
                    $aPoints[] = $YLast;
                    $aPoints[] = $XPos;
                    $aPoints[] = $YPos;
                    $aPoints[] = $XPos;
                    $aPoints[] = $YZero;
                    $aPoints[] = $XLast;
                    $aPoints[] = $YZero;

                    $C_Graph = $this->AllocateColor($this->Layers[0], $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B']);
                    imagefilledpolygon($this->Layers[0], $aPoints, 4, $C_Graph);
                }

                if(!isset($Missing[floor($X)]) || $YLast == NULL) {
                    ++$PointsCount;
                    $Points[] = $XPos;
                    $Points[] = $YPos;
                } else {
                    ++$PointsCount;
                    $Points[] = $XLast;
                    $Points[] = $LayerHeight;
                }

                $YLast = $YPos;
                $XLast = $XPos;
                $XPos  += $this->DivisionWidth * $Accuracy;
            }

            // Add potentialy missing values
            $XPos  -= $this->DivisionWidth * $Accuracy;
            if($XPos < ($LayerWidth-$this->GAreaXOffset)) {
                $YPos = $LayerHeight - (($YIn[$Index]-$this->VMin) * $this->DivisionRatio);

                if(isset($YLast) && $AroundZero) {
                    $aPoints   = array();
                    $aPoints[] = $XLast;
                    $aPoints[] = $YLast;
                    $aPoints[] = $LayerWidth - $this->GAreaXOffset;
                    $aPoints[] = $YPos;
                    $aPoints[] = $LayerWidth - $this->GAreaXOffset;
                    $aPoints[] = $YZero;
                    $aPoints[] = $XLast;
                    $aPoints[] = $YZero;

                    $C_Graph = $this->AllocateColor($this->Layers[0], $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B']);
                    imagefilledpolygon($this->Layers[0], $aPoints, 4, $C_Graph);
                }

                if(isset($YIn[$klo], $YIn[$khi]) || $YLast == NULL) {
                    ++$PointsCount;
                    $Points[] = $LayerWidth  - $this->GAreaXOffset;
                    $Points[] = $YPos;
                }
            }

            $Points[] = $LayerWidth - $this->GAreaXOffset;
            $Points[] = $LayerHeight;

            if(!$AroundZero) {
                $C_Graph = $this->AllocateColor($this->Layers[0], $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B']);
                imagefilledpolygon($this->Layers[0], $Points, $PointsCount, $C_Graph);
            }

            imagecopymerge($this->Picture, $this->Layers[0], $this->GArea_X1, $this->GArea_Y1, 0, 0, $LayerWidth, $LayerHeight, $Alpha);
            imagedestroy($this->Layers[0]);

            $this->drawCubicCurve($this->Data, $this->DataDescription, $Accuracy, $ColName);

            ++$GraphID;
        }
    }

    /**
     * Draw a filled line graph
     *
     * @param int $Alpha = 100
     * @param boolean $AroundZero = FALSE
     */
    public function drawFilledLineGraph($Alpha = 100, $AroundZero = FALSE) {
        $this->validateDataDescription('drawFilledLineGraph');
        $this->validateData('drawFilledLineGraph');

        $LayerWidth  = $this->GArea_X2-$this->GArea_X1;
        $LayerHeight = $this->GArea_Y2-$this->GArea_Y1;

        $GraphID = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            $ID = 0;
            foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                if($keyI == $ColName) {
                    $ColorID = $ID;
                }
                ++$ID;
            }

            $aPoints   = array();
            $aPoints[] = $this->GAreaXOffset;
            $aPoints[] = $LayerHeight;

            $this->Layers[0] = imagecreatetruecolor($LayerWidth, $LayerHeight);
            $C_White = $this->AllocateColor($this->Layers[0], 255, 255, 255);
            imagefilledrectangle($this->Layers[0], 0, 0, $LayerWidth, $LayerHeight, $C_White);
            imagecolortransparent($this->Layers[0], $C_White);

            $XPos  = $this->GAreaXOffset;
            $XLast = NULL;
            $PointsCount = 2;
            $YZero = $LayerHeight - ((0-$this->VMin) * $this->DivisionRatio);

            if($YZero > $LayerHeight) {
                $YZero = $LayerHeight;
            }

            $YLast = NULL;
            foreach($this->Data as $Key => $Values) {
                $Value = $this->Data[$Key][$ColName];
                $YPos = $LayerHeight - (($Value-$this->VMin) * $this->DivisionRatio);

                // Save point to image map if necessary
                if($this->BuildMap) {
                    $this->addToImageMap($XPos - 3, $YPos - 3, $XPos + 3, $YPos + 3, $this->DataDescription['Description'][$ColName], $this->Data[$Key][$ColName].$this->DataDescription['Unit']['Y'], 'FLine');
                }

                if(!is_numeric($Value)) {
                    ++$PointsCount;
                    $aPoints[] = $XLast;
                    $aPoints[] = $LayerHeight;

                    $YLast = NULL;
                } else {
                    ++$PointsCount;
                    if(isset($YLast)) {
                        $aPoints[] = $XPos;
                        $aPoints[] = $YPos;
                    } else {
                        ++$PointsCount;
                        $aPoints[] = $XPos;
                        $aPoints[] = $LayerHeight;
                        $aPoints[] = $XPos;
                        $aPoints[] = $YPos;
                    }

                    if(isset($YLast) && $AroundZero) {
                        $Points   = array();
                        $Points[] = $XLast;
                        $Points[] = $YLast;
                        $Points[] = $XPos;
                        $Points[] = $YPos;
                        $Points[] = $XPos;
                        $Points[] = $YZero;
                        $Points[] = $XLast;
                        $Points[] = $YZero;

                        $C_Graph = $this->AllocateColor($this->Layers[0], $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B']);
                        imagefilledpolygon($this->Layers[0], $Points, 4, $C_Graph);
                    }
                    $YLast = $YPos;
                }

                $XLast = $XPos;
                $XPos  = $XPos + $this->DivisionWidth;
            }

            $aPoints[] = $LayerWidth - $this->GAreaXOffset;
            $aPoints[] = $LayerHeight;

            if(! $AroundZero) {
                $C_Graph = $this->AllocateColor($this->Layers[0], $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B']);
                imagefilledpolygon($this->Layers[0], $aPoints, $PointsCount, $C_Graph);
            }

            imagecopymerge($this->Picture, $this->Layers[0], $this->GArea_X1, $this->GArea_Y1, 0, 0, $LayerWidth, $LayerHeight, $Alpha);
            imagedestroy($this->Layers[0]);
            ++$GraphID;
            $this->drawLineGraph($this->Data, $this->DataDescription, $ColName);
        }
    }

    /**
     * Draw an overlay bar graph
     *
     * @param int $Alpha = 50
     */
    public function drawOverlayBarGraph($Alpha = 50) {
        $this->validateDataDescription('drawOverlayBarGraph');
        $this->validateData('drawOverlayBarGraph');

        $LayerWidth  = $this->GArea_X2 - $this->GArea_X1;
        $LayerHeight = $this->GArea_Y2 - $this->GArea_Y1;

        $GraphID = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            $ID = 0;
            foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                if($keyI == $ColName) {
                    $ColorID = $ID;
                }
                ++$ID;
            }

            $this->Layers[$GraphID] = imagecreatetruecolor($LayerWidth, $LayerHeight);
            $C_White                = $this->AllocateColor($this->Layers[$GraphID], 255, 255, 255);
            $C_Graph                = $this->AllocateColor($this->Layers[$GraphID], $this->Palette[$GraphID]['R'], $this->Palette[$GraphID]['G'], $this->Palette[$GraphID]['B']);
            imagefilledrectangle($this->Layers[$GraphID], 0, 0, $LayerWidth, $LayerHeight, $C_White);
            imagecolortransparent($this->Layers[$GraphID], $C_White);

            $XWidth = $this->DivisionWidth / 4;
            $XPos   = $this->GAreaXOffset;
            $YZero  = $LayerHeight - ((0-$this->VMin) * $this->DivisionRatio);
            $XLast  = NULL;
            $PointsCount = 2;
            foreach($this->Data as $Key => $Values) {
                if(isset($this->Data[$Key][$ColName])) {
                    $Value = $this->Data[$Key][$ColName];
                    if(is_numeric($Value)) {
                        $YPos  = $LayerHeight - (($Value-$this->VMin) * $this->DivisionRatio);

                        imagefilledrectangle($this->Layers[$GraphID], $XPos - $XWidth, $YPos, $XPos + $XWidth, $YZero, $C_Graph);

                        $X1 = floor($XPos - $XWidth + $this->GArea_X1);
                        $Y1 = floor($YPos+$this->GArea_Y1) + 0.2;
                        $X2 = floor($XPos + $XWidth + $this->GArea_X1);
                        $Y2 = $this->GArea_Y2 - ((0 - $this->VMin) * $this->DivisionRatio);
                        if($X1 <= $this->GArea_X1) {
                            $X1 = $this->GArea_X1 + 1;
                        }
                        if($X2 >= $this->GArea_X2) {
                            $X2 = $this->GArea_X2 - 1;
                        }

                        // Save point to image map if necessary
                        if($this->BuildMap) {
                            $this->addToImageMap($X1, min($Y1, $Y2), $X2, max($Y1, $Y2), $this->DataDescription['Description'][$ColName], $this->Data[$Key][$ColName].$this->DataDescription['Unit']['Y'], 'oBar');
                        }

                        $this->drawLine($X1, $Y1, $X2, $Y1, $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B'], TRUE);
                    }
                }
                $XPos += $this->DivisionWidth;
            }

            ++$GraphID;
        }

        for($i = 0; $i <= ($GraphID - 1); ++$i) {
            imagecopymerge($this->Picture, $this->Layers[$i], $this->GArea_X1, $this->GArea_Y1, 0, 0, $LayerWidth, $LayerHeight, $Alpha);
            imagedestroy($this->Layers[$i]);
        }
    }

    /**
     * Draw a bar graph
     *
     * @param boolean $Shadow = FALSE
     * @param int $Alpha = 100
     */
    public function drawBarGraph($Shadow = FALSE, $Alpha = 100) {
        $this->validateDataDescription('drawBarGraph');
        $this->validateData('drawBarGraph');

        $GraphID      = 0;
        $Series       = count($this->DataDescription['Values']);
        $SeriesWidth  = $this->DivisionWidth / ($Series + 1);
        $SerieXOffset = $this->DivisionWidth / 2 - $SeriesWidth / 2;

        $YZero  = $this->GArea_Y2 - ((0-$this->VMin) * $this->DivisionRatio);
        if($YZero > $this->GArea_Y2) {
            $YZero = $this->GArea_Y2;
        }

        $SerieID = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            $ID = 0;
            foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                if($keyI == $ColName) {
                    $ColorID = $ID;
                }
                ++$ID;
            }

            $XPos  = $this->GArea_X1 + $this->GAreaXOffset - $SerieXOffset + $SeriesWidth * $SerieID;
            $XLast = NULL;
            foreach($this->Data as $Key => $Values) {
                if(isset($this->Data[$Key][$ColName])) {
                    if(is_numeric($this->Data[$Key][$ColName])) {
                        $Value = $this->Data[$Key][$ColName];
                        $YPos = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);

                        // Save point to image map if necessary
                        if($this->BuildMap)
                        {
                            $this->addToImageMap($XPos+1,min($YZero, $YPos), $XPos+$SeriesWidth-1,max($YZero, $YPos), $this->DataDescription['Description'][$ColName], $this->Data[$Key][$ColName].$this->DataDescription['Unit']['Y'],'Bar');
                        }

                        if($Shadow && $Alpha == 100) {
                            $this->drawRectangle($XPos + 1, $YZero, $XPos + $SeriesWidth - 1, $YPos, 25, 25, 25, TRUE, $Alpha);
                        }

                        $this->drawFilledRectangle($XPos + 1, $YZero, $XPos+$SeriesWidth - 1, $YPos, $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B'], TRUE, $Alpha);
                    }
                }
                $XPos += $this->DivisionWidth;
            }
            ++$SerieID;
        }
    }

    /**
     * Draw a stacked bar graph
     *
     * If the $Continuous flag is set, the bars
     * will be put against each other. If not, the
     * bars will only occupy 80% of the available space.
     *
     * @param int $Alpha = 50
     * @param boolean $Continuous = FALSE
     * @param int $widthPercentage = 80
     */
    public function drawStackedBarGraph($Alpha = 50, $Continuous = FALSE, $widthPercentage = 80) {
        $this->validateDataDescription('drawBarGraph');
        $this->validateData('drawBarGraph');

        $GraphID = 0;
        $Series = count($this->DataDescription['Values']);

        if($Continuous) {
            $SeriesWidth  = $this->DivisionWidth;
        } else {
            $SeriesWidth  = $this->DivisionWidth * ($widthPercentage / 100);
        }

        $YZero = $this->GArea_Y2 - ((0-$this->VMin) * $this->DivisionRatio);
        if($YZero > $this->GArea_Y2) {
            $YZero = $this->GArea_Y2;
        }

        $SerieID = 0;
        $LastValue = array();
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            $ID = 0;
            foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                if($keyI == $ColName) {
                    $ColorID = $ID;
                }
                ++$ID;
            }

            $XPos  = $this->GArea_X1 + $this->GAreaXOffset - $SeriesWidth / 2;
            $XLast = NULL;
            foreach($this->Data as $Key => $Values) {
                if(isset($this->Data[$Key][$ColName])) {
                    if(is_numeric($this->Data[$Key][$ColName])) {
                        $Value = $this->Data[$Key][$ColName];

                        if(isset($LastValue[$Key])) {
                            $YPos    = $this->GArea_Y2 - ((($Value + $LastValue[$Key]) - $this->VMin) * $this->DivisionRatio);
                            $YBottom = $this->GArea_Y2 - (($LastValue[$Key] - $this->VMin) * $this->DivisionRatio);
                            $LastValue[$Key] += $Value;
                        } else {
                            $YPos    = $this->GArea_Y2 - (($Value-$this->VMin) * $this->DivisionRatio);
                            $YBottom = $YZero;
                            $LastValue[$Key] = $Value;
                        }

                        // Save point to image map if necessary
                        if($this->BuildMap) {
                            $this->addToImageMap($XPos + 1, min($YBottom, $YPos), $XPos + $SeriesWidth - 1, max($YBottom, $YPos), $this->DataDescription['Description'][$ColName], $this->Data[$Key][$ColName].$this->DataDescription['Unit']['Y'], 'sBar');
                        }

                        $this->drawFilledRectangle($XPos + 1, $YBottom, $XPos + $SeriesWidth - 1, $YPos, $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B'], TRUE, $Alpha);
                    }
                }
                $XPos += $this->DivisionWidth;
            }
            ++$SerieID;
        }
    }

    /**
     * Draw a limits bar graph
     *
     * The vertical line color can be specified
     * by ($R, $G, $B), defaults to light grey.
     *
     * @param int $R = 180
     * @param int $G = 180
     * @param int $B = 180
     */
    public function drawLimitsGraph($R = 180, $G = 180, $B = 180) {
        $this->validateDataDescription('drawLimitsGraph');
        $this->validateData('drawLimitsGraph');

        $XWidth = $this->DivisionWidth / 4;
        $XPos   = $this->GArea_X1 + $this->GAreaXOffset;

        foreach($this->Data as $Key => $Values) {
            $Min     = $this->Data[$Key][$this->DataDescription['Values'][0]];
            $Max     = $this->Data[$Key][$this->DataDescription['Values'][0]];
            $GraphID = 0;
            $MaxID = 0;
            $MinID = 0;
            foreach($this->DataDescription['Values'] as $Key2 => $ColName)
            {
                if(isset($this->Data[$Key][$ColName]))
                {
                    if($this->Data[$Key][$ColName] > $Max && is_numeric($this->Data[$Key][$ColName])) {
                        $Max = $this->Data[$Key][$ColName];
                        $MaxID = $GraphID;
                    }
                }

                if(isset($this->Data[$Key][$ColName]) && is_numeric($this->Data[$Key][$ColName])) {
                    if($this->Data[$Key][$ColName] < $Min) {
                        $Min = $this->Data[$Key][$ColName];
                        $MinID = $GraphID;
                    }
                    ++$GraphID;
                }
            }

            $YPos = $this->GArea_Y2 - (($Max - $this->VMin) * $this->DivisionRatio);
            $X1 = floor($XPos - $XWidth);
            $Y1 = floor($YPos) - 0.2;
            $X2 = floor($XPos + $XWidth);
            if($X1 <= $this->GArea_X1) {
                $X1 = $this->GArea_X1 + 1;
            }
            if($X2 >= $this->GArea_X2) {
                $X2 = $this->GArea_X2 - 1;
            }

            $YPos = $this->GArea_Y2 - (($Min-$this->VMin) * $this->DivisionRatio);
            $Y2 = floor($YPos) + 0.2;

            $this->drawLine(floor($XPos) - 0.2, $Y1 + 1, floor($XPos) - 0.2, $Y2 - 1, $R, $G, $B, TRUE);
            $this->drawLine(floor($XPos) + 0.2, $Y1 + 1, floor($XPos) + 0.2, $Y2 - 1, $R, $G, $B, TRUE);
            $this->drawLine($X1, $Y1, $X2, $Y1, $this->Palette[$MaxID]['R'], $this->Palette[$MaxID]['G'], $this->Palette[$MaxID]['B'], FALSE);
            $this->drawLine($X1, $Y2, $X2, $Y2, $this->Palette[$MinID]['R'], $this->Palette[$MinID]['G'], $this->Palette[$MinID]['B'], FALSE);

            $XPos += $this->DivisionWidth;
        }
    }

    /**
     * Draw a radar axis centered on the graph area
     *
     * The first color triple ($A_R, $A_G, $A_B) will specify
     * the outer axis color (Default: dark grey) and the second
     * ($S_R, $S_G, $S_B) will specify the inner axis color
     * (Default: light grey).
     *
     * @param boolean $Mosaic = TRUE
     * @param int $BorderOffset = 10
     * @param int $A_R = 60
     * @param int $A_G = 60
     * @param int $A_B = 60
     * @param int $S_R = 200
     * @param int $S_G = 200
     * @param int $S_B = 200
     * @param float $MaxValue = NULL
     */
    public function drawRadarAxis($Mosaic = TRUE, $BorderOffset = 10, $A_R = 60, $A_G = 60, $A_B = 60, $S_R = 200, $S_G = 200, $S_B = 200, $MaxValue = NULL) {
        $this->validateDataDescription('drawRadarAxis');
        $this->validateData('drawRadarAxis');

        $C_TextColor = $this->AllocateColor($this->Picture, $A_R, $A_G, $A_B);

        // Draw the radar axis
        $Points  = count($this->Data);
        $Radius  = ($this->GArea_Y2 - $this->GArea_Y1) / 2 - $BorderOffset;
        $XCenter = ($this->GArea_X2 - $this->GArea_X1) / 2 + $this->GArea_X1;
        $YCenter = ($this->GArea_Y2 - $this->GArea_Y1) / 2 + $this->GArea_Y1;

        // Search for the maximum value
        if(is_null($MaxValue)) {
            $MaxValue = $this->MaximumValue($this->Data, $this->DataDescription);
        }

        // Draw mosaic
        if($Mosaic) {
            $RadiusScale = $Radius / $MaxValue;
            for($t = 1; $t <= $MaxValue - 1; $t++) {
                $TRadius  = $RadiusScale * $t;
                $LastX1   = NULL;

                for($i = 0; $i <= $Points; ++$i) {
                    $Angle = -90 + $i * 360 / $Points;
                    $X1 = cos($Angle * M_PI / 180) * $TRadius + $XCenter;
                    $Y1 = sin($Angle * M_PI / 180) * $TRadius + $YCenter;
                    $X2 = cos($Angle * M_PI / 180) * ($TRadius + $RadiusScale) + $XCenter;
                    $Y2 = sin($Angle * M_PI / 180) * ($TRadius + $RadiusScale) + $YCenter;

                    if($t % 2 == 1 && isset($LastX)) {
                        $Plots   = array();
                        $Plots[] = $X1;
                        $Plots[] = $Y1;
                        $Plots[] = $X2;
                        $Plots[] = $Y2;
                        $Plots[] = $LastX2;
                        $Plots[] = $LastY2;
                        $Plots[] = $LastX1;
                        $Plots[] = $LastY1;

                        $C_Graph = $this->AllocateColor($this->Picture, 250, 250, 250);
                        imagefilledpolygon($this->Picture, $Plots, (count($Plots) + 1) / 2, $C_Graph);
                    }

                    $LastX1 = $X1;
                    $LastY1= $Y1;
                    $LastX2 = $X2;
                    $LastY2= $Y2;
                }
            }
        }

        // Draw the spider web
        for($t = 1; $t <= $MaxValue; ++$t) {
            $TRadius = ($Radius / $MaxValue) * $t;
            $LastX = NULL;

            for($i = 0; $i <= $Points; ++$i) {
                $Angle = -90 + $i * 360/$Points;
                $X = cos($Angle * M_PI / 180) * $TRadius + $XCenter;
                $Y = sin($Angle * M_PI / 180) * $TRadius + $YCenter;

                if(isset($LastX)) {
                    $this->drawDottedLine($LastX, $LastY, $X, $Y, 4, $S_R, $S_G, $S_B);
                }

                $LastX = $X;
                $LastY= $Y;
            }
        }

        // Draw the axis
        for($i = 0; $i <= $Points; ++$i) {
            $Angle = -90 + $i * 360 / $Points;
            $X = cos($Angle * M_PI / 180) * $Radius + $XCenter;
            $Y = sin($Angle * M_PI / 180) * $Radius + $YCenter;

            $this->drawLine($XCenter, $YCenter, $X, $Y, $A_R, $A_G, $A_B);

            $XOffset = 0;
            $YOffset = 0;
            if(isset($this->Data[$i][$this->DataDescription['Position']])) {
                $Label = $this->Data[$i][$this->DataDescription['Position']];

                $Positions = imagettfbbox($this->FontSize, 0, $this->FontName, utf8_decode($Label));
                $Width  = $Positions[2] - $Positions[6];
                $Height = $Positions[3] - $Positions[7];

                if($Angle >= 0 && $Angle <= 90) {
                    $YOffset = $Height;
                }

                if($Angle > 90 && $Angle <= 180) {
                    $YOffset = $Height;
                    $XOffset = -$Width;
                }

                if($Angle > 180 && $Angle <= 270) {
                    $XOffset = -$Width;
                }

                imagettftext($this->Picture, $this->FontSize, 0, $X + $XOffset, $Y + $YOffset, $C_TextColor, $this->FontName, utf8_decode($Label));
            }
        }

        // Write the values
        for($t = 1; $t <= $MaxValue; ++$t) {
            $TRadius = ($Radius / $MaxValue) * $t;

            $Angle = -90 + 360 / $Points;
            $X1 = $XCenter;
            $Y1 = $YCenter - $TRadius;
            $X2 = cos($Angle * M_PI / 180) * $TRadius + $XCenter;
            $Y2 = sin($Angle * M_PI / 180) * $TRadius + $YCenter;

            $XPos = floor(($X2-$X1) / 2) + $X1;
            $YPos = floor(($Y2-$Y1) / 2) + $Y1;

            $Positions = imagettfbbox($this->FontSize, 0, $this->FontName, utf8_decode($t));
            $X = $XPos - ($X + $Positions[2] - $X + $Positions[6]) / 2;
            $Y = $YPos + $this->FontSize;

            $this->drawFilledRoundedRectangle($X + $Positions[6] - 2, $Y + $Positions[7] - 1, $X + $Positions[2] + 4, $Y + $Positions[3] + 1, 2, 240, 240, 240);
            $this->drawRoundedRectangle($X + $Positions[6] - 2, $Y + $Positions[7] - 1, $X + $Positions[2] + 4, $Y + $Positions[3] + 1, 2, 220, 220, 220);
            imagettftext($this->Picture, $this->FontSize, 0, $X, $Y, $C_TextColor, $this->FontName, utf8_decode($t));
        }
    }

    /**
     * Draw  a radar graph centered on the graph area
     *
     * @param int $BorderOffset = 10
     * @param float $MaxValue = NULL
     */
    public function drawRadar($BorderOffset = 10, $MaxValue = NULL) {
        $this->validateDataDescription('drawRadar');
        $this->validateData('drawRadar');

        $Points  = count($this->Data);
        $Radius  = ($this->GArea_Y2 - $this->GArea_Y1) / 2 - $BorderOffset;
        $XCenter = ($this->GArea_X2 - $this->GArea_X1) / 2 + $this->GArea_X1;
        $YCenter = ($this->GArea_Y2 - $this->GArea_Y1) / 2 + $this->GArea_Y1;

        // Search for the maximum value
        if(is_null($MaxValue)) {
            $MaxValue = $this->MaximumValue($this->Data, $this->DataDescription);
        }

        $GraphID = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            $ID = 0;
            foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                if($keyI == $ColName) {
                    $ColorID = $ID;
                }
                ++$ID;
            }

            $Angle = -90;
            $XLast = NULL;
            foreach($this->Data as $Key => $Values) {
                if(isset($this->Data[$Key][$ColName])) {
                    $Value    = $this->Data[$Key][$ColName];
                    $Strength = ($Radius / $MaxValue) * $Value;

                    $XPos = cos($Angle * M_PI / 180) * $Strength + $XCenter;
                    $YPos = sin($Angle * M_PI / 180) * $Strength + $YCenter;

                    if(isset($XLast)) {
                        $this->drawLine($XLast, $YLast, $XPos, $YPos, $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B']);
                    }

                    if(is_null($XLast)) {
                        $FirstX = $XPos;
                        $FirstY = $YPos;
                    }

                    $Angle = $Angle + (360 / $Points);
                    $XLast = $XPos;
                    $YLast = $YPos;
                }
            }

            $this->drawLine($XPos, $YPos, $FirstX, $FirstY, $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B']);
            ++$GraphID;
        }
    }

    /**
     * Draw a filled radar graph centered on the graph area
     *
     * @param int $Alpha = 50
     * @param int $BorderOffset = 10
     * @param float $MaxValue = NULL
     */
    public function drawFilledRadar($Alpha = 50, $BorderOffset = 10, $MaxValue = NULL) {
        $this->validateDataDescription('drawFilledRadar');
        $this->validateData('drawFilledRadar');

        $Points      = count($this->Data);
        $LayerWidth  = $this->GArea_X2 - $this->GArea_X1;
        $LayerHeight = $this->GArea_Y2 - $this->GArea_Y1;
        $Radius      = ($this->GArea_Y2 - $this->GArea_Y1) / 2 - $BorderOffset;
        $XCenter     = ($this->GArea_X2 - $this->GArea_X1) / 2;
        $YCenter     = ($this->GArea_Y2 - $this->GArea_Y1) / 2;

        // Search for the maximum value
        if(is_null($MaxValue)) {
            $MaxValue = $this->MaximumValue($this->Data, $this->DataDescription);
        }

        $GraphID = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            $ID = 0;
            foreach($this->DataDescription['Description'] as $keyI => $ValueI) {
                if($keyI == $ColName) {
                    $ColorID = $ID;
                }
                ++$ID;
            }

            $Angle = -90;
            $XLast = NULL;
            $Plots = array();

            foreach($this->Data as $Key => $Values) {
                if(isset($this->Data[$Key][$ColName])) {
                    $Value    = $this->Data[$Key][$ColName];
                    if(!is_numeric($Value)) {
                        $Value = 0;
                    }
                    $Strength = ($Radius / $MaxValue) * $Value;

                    $XPos = cos($Angle * M_PI / 180) * $Strength + $XCenter;
                    $YPos = sin($Angle * M_PI / 180) * $Strength + $YCenter;

                    $Plots[] = $XPos;
                    $Plots[] = $YPos;

                    $Angle = $Angle + (360 / $Points);
                    $XLast = $XPos;
                    $YLast = $YPos;
                }
            }

            if(isset($Plots[0])) {
                $Plots[] = $Plots[0];
                $Plots[] = $Plots[1];

                $this->Layers[0] = imagecreatetruecolor($LayerWidth, $LayerHeight);
                $C_White         = $this->AllocateColor($this->Layers[0], 255, 255, 255);
                imagefilledrectangle($this->Layers[0], 0, 0, $LayerWidth, $LayerHeight, $C_White);
                imagecolortransparent($this->Layers[0], $C_White);

                $C_Graph = $this->AllocateColor($this->Layers[0], $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B']);
                imagefilledpolygon($this->Layers[0], $Plots, (count($Plots) + 1) / 2, $C_Graph);

                imagecopymerge($this->Picture, $this->Layers[0], $this->GArea_X1, $this->GArea_Y1, 0, 0, $LayerWidth, $LayerHeight, $Alpha);
                imagedestroy($this->Layers[0]);

                for($i = 0; $i <= count($Plots) - 4; $i += 2) {
                    $this->drawLine($Plots[$i] + $this->GArea_X1, $Plots[$i+1] + $this->GArea_Y1, $Plots[$i+2] + $this->GArea_X1, $Plots[$i+3] + $this->GArea_Y1, $this->Palette[$ColorID]['R'], $this->Palette[$ColorID]['G'], $this->Palette[$ColorID]['B']);
                }
            }

            ++$GraphID;
        }
    }

    /**
     * Draw basic pie graph
     *
     * The difference between the basic pie graph and
     * an unexploded flat pie graph is this graphs border
     * around the slices.
     *
     * The default line color around the slices is
     * set to white.
     *
     * The posssible $DrawLabels values are:
     *  - PIE_NOLABEL: Show no lables (default)
     *  - PIE_PERCENTAGE: Show percentages
     *  - PIE_LABELS: Show normal labels
     *  - PIE_PERCENTAGE_LABEL: Show both
     *
     * Only one series to plot is accepted.
     *
     * @param int $XPos
     * @param int $YPos
     * @param int $Radius = 100
     * @param int $DrawLabels = PIE_NOLABEL
     * @param int $R = 255
     * @param int $G = 255
     * @param int $B = 255
     * @param int $Decimals = 0
     */
    public function drawBasicPieGraph($XPos, $YPos, $Radius = 100, $DrawLabels = PIE_NOLABEL, $R = 255, $G = 255, $B = 255, $Decimals = 0) {
        $this->validateDataDescription('drawBasicPieGraph', FALSE);
        $this->validateData('drawBasicPieGraph');

        // Calculate pie sum
        $Series = 0;
        $PieSum = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            if($ColName != $this->DataDescription['Position']) {
                ++$Series;
                foreach($this->Data as $Key => $Values) {
                    if(isset($this->Data[$Key][$ColName])) {
                        $PieSum += $this->Data[$Key][$ColName];
                        $iValues[] = $this->Data[$Key][$ColName];
                        $iLabels[] = $this->Data[$Key][$this->DataDescription['Position']];
                    }
                }
            }
        }

        // Validate number of $Series
        if($Series != 1) {
            $this->RaiseFatal('Pie chart can only accept one serie of data.');
        }

        $SpliceRatio = 360 / $PieSum;
        $SplicePercent = 100 / $PieSum;

        // Calculate all polygons
        $Angle = 0;
        $TopPlots = array();

        foreach($iValues as $Key => $Value) {
            $TopPlots[$Key][] = $XPos;
            $TopPlots[$Key][] = $YPos;

            // Process labels and sizes
            $Caption = '';

            switch($DrawLabels) {
                default: // Break intentionally omitted
                case PIE_NOLABEL:
                    break;
                case PIE_PERCENTAGE:
                    $Caption  = (round($Value * pow(10, $Decimals) * $SplicePercent) / pow(10, $Decimals)) . '%';
                    break;
                case PIE_LABELS:
                    $Caption  = $iLabels[$Key];
                    break;
                case PIE_PERCENTAGE_LABEL:
                    $Caption  = $iLabels[$Key] . "\n" . (round($Value * pow(10, $Decimals) * $SplicePercent) / pow(10, $Decimals)) . '%';
                    break;
            }

            if($Caption) {
                $TAngle     = $Angle + ($Value * $SpliceRatio / 2);
                $Position   = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Caption));
                $TextWidth  = $Position[2] - $Position[0];
                $TextHeight = abs($Position[1]) + abs($Position[3]);

                $TX = cos(($TAngle) * M_PI / 180) * ($Radius + 10) + $XPos;

                if($TAngle > 0 && $TAngle < 180) {
                    $TY = sin(($TAngle) * M_PI / 180) * ($Radius + 10) + $YPos + 4;
                } else {
                    $TY = sin(($TAngle) * M_PI / 180) * ($Radius + 4) + $YPos - ($TextHeight / 2);
                }

                if($TAngle > 90 && $TAngle < 270) {
                    $TX -= $TextWidth;
                }

                $C_TextColor = $this->AllocateColor($this->Picture, 70, 70, 70);
                imagettftext($this->Picture, $this->FontSize, 0, $TX, $TY, $C_TextColor, $this->FontName, utf8_decode($Caption));
            }

            // Process pie slices
            for($iAngle = $Angle; $iAngle <= $Angle + $Value * $SpliceRatio; $iAngle += 0.5) {
                $TopX = cos($iAngle * M_PI / 180) * $Radius + $XPos;
                $TopY = sin($iAngle * M_PI / 180) * $Radius + $YPos;

                $TopPlots[$Key][] = $TopX;
                $TopPlots[$Key][] = $TopY;
            }

            $TopPlots[$Key][] = $XPos;
            $TopPlots[$Key][] = $YPos;

            $Angle = $iAngle;
        }
        $PolyPlots = $TopPlots;

        // Set array values type to float --- PHP Bug with imagefilledpolygon casting to integer
        foreach($TopPlots as $Key => $Value) {
            foreach ($TopPlots[$Key] as $Key2 => $Value2) {
                settype($TopPlots[$Key][$Key2],'float');
            }
        }

        // Draw top polygons
        foreach($PolyPlots as $Key => $Value) {
            $C_GraphLo = $this->AllocateColor($this->Picture, $this->Palette[$Key]['R'], $this->Palette[$Key]['G'], $this->Palette[$Key]['B']);
            imagefilledpolygon($this->Picture, $PolyPlots[$Key], (count($PolyPlots[$Key]) + 1) / 2, $C_GraphLo);
        }

        $this->drawCircle($XPos - 0.5, $YPos - 0.5, $Radius, $R, $G, $B);
        $this->drawCircle($XPos - 0.5, $YPos - 0.5, $Radius + 0.5, $R, $G, $B);

        // Draw border lines
        foreach ($TopPlots as $Key => $Value) {
            for($j = 0; $j <= count($TopPlots[$Key]) - 4; $j += 2) {
                $this->drawLine($TopPlots[$Key][$j], $TopPlots[$Key][$j+1], $TopPlots[$Key][$j+2], $TopPlots[$Key][$j+3], $R, $G, $B);
            }
        }
    }

    /**
     * Draw flat pie graph with shadows
     *
     * Wrapper for drawFlatPieGraph, calling twice
     *
     * The posssible $DrawLabels values are:
     *  - PIE_NOLABEL: Show no lables (default)
     *  - PIE_PERCENTAGE: Show percentages
     *  - PIE_LABELS: Show normal labels
     *  - PIE_PERCENTAGE_LABEL: Show both
     *
     * Only one series to plot is accepted.
     *
     * @param int $XPos
     * @param int $YPos
     * @param int $Radius = 100
     * @param int $DrawLabels = PIE_NOLABEL
     * @param int $SpliceDistance = 0
     * @param int $Decimals = 0
     */
    public function drawFlatPieGraphWithShadow($XPos, $YPos, $Radius = 100, $DrawLabels = PIE_NOLABEL, $SpliceDistance = 0, $Decimals = 0) {
        $this->drawFlatPieGraph($XPos + $this->ShadowXDistance, $YPos + $this->ShadowYDistance, $Radius, PIE_NOLABEL, $SpliceDistance, $Decimals, TRUE);
        $this->drawFlatPieGraph($XPos, $YPos, $Radius, $DrawLabels, $SpliceDistance, $Decimals, FALSE);
    }

    /**
     * Draw a flat pie chart
     *
     * The posssible $DrawLabels values are:
     *  - PIE_NOLABEL: Show no lables (default)
     *  - PIE_PERCENTAGE: Show percentages
     *  - PIE_LABELS: Show normal labels
     *  - PIE_PERCENTAGE_LABEL: Show both
     *
     * Only one series to plot is accepted.
     *
     * The flag $AllBlack is used internally to draw
     * all black pies as shadows.
     *
     * @param int $XPos
     * @param int $YPos
     * @param int $Radius = 100
     * @param int $DrawLabels = PIE_NOLABEL
     * @param int $SpliceDistance = 0
     * @param int $Decimals = 0
     * @param boolean $AllBlack = FALSE
     */
    public function drawFlatPieGraph($XPos, $YPos, $Radius = 100, $DrawLabels = PIE_NOLABEL, $SpliceDistance = 0, $Decimals = 0, $AllBlack = FALSE) {
        $this->validateDataDescription('drawFlatPieGraph', FALSE);
        $this->validateData('drawFlatPieGraph');

        // Back up current shadow status
        $ShadowStatus = $this->ShadowActive ;
        $this->ShadowActive = FALSE;

        $Series = 0;
        $PieSum = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            if($ColName != $this->DataDescription['Position']) {
                ++$Series;
                foreach($this->Data as $Key => $Values) {
                    if(isset($this->Data[$Key][$ColName])) {
                        $PieSum += $this->Data[$Key][$ColName];
                        $iValues[] = $this->Data[$Key][$ColName];
                        $iLabels[] = $this->Data[$Key][$this->DataDescription['Position']];
                    }
                }
            }
        }

        // Validate number of $Series
        if($Series != 1) {
            $this->RaiseFatal('Pie chart can only accept one serie of data.');
        }

        $SpliceRatio = 360 / $PieSum;
        $SplicePercent = 100 / $PieSum;

        // Calculate all polygons
        $Angle = 0;
        $TopPlots = array();
        foreach($iValues as $Key => $Value) {
            $XOffset = cos(($Angle+($Value / 2 * $SpliceRatio)) * M_PI / 180) * $SpliceDistance;
            $YOffset = sin(($Angle+($Value / 2 * $SpliceRatio)) * M_PI / 180) * $SpliceDistance;

            $TopPlots[$Key][] = round($XPos + $XOffset);
            $TopPlots[$Key][] = round($YPos + $YOffset);

            if($AllBlack) {
                $Rc = $this->ShadowRColor;
                $Gc = $this->ShadowGColor;
                $Bc = $this->ShadowBColor;
            } else {
                $Rc = $this->Palette[$Key]['R'];
                $Gc = $this->Palette[$Key]['G'];
                $Bc = $this->Palette[$Key]['B'];
            }

            $XLineLast = array();
            $YLineLast = array();

            // Process labels and sizes
            $Caption = '';

            switch($DrawLabels) {
                default: // Break intentionally omitted
                case PIE_NOLABEL:
                    break;
                case PIE_PERCENTAGE:
                    $Caption  = (round($Value * pow(10, $Decimals) * $SplicePercent) / pow(10, $Decimals)) . '%';
                    break;
                case PIE_LABELS:
                    $Caption  = $iLabels[$Key];
                    break;
                case PIE_PERCENTAGE_LABEL:
                    $Caption  = $iLabels[$Key] . "\n" . (round($Value * pow(10, $Decimals) * $SplicePercent) / pow(10, $Decimals)) . '%';
                    break;
            }

            if($Caption) {
                $TAngle     = $Angle + ($Value * $SpliceRatio / 2);
                $Position   = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Caption));
                $TextWidth  = $Position[2] - $Position[0];
                $TextHeight = abs($Position[1]) + abs($Position[3]);

                $TX = cos(($TAngle) * M_PI / 180) * ($Radius + 10 + $SpliceDistance) + $XPos;

                if($TAngle > 0 && $TAngle < 180) {
                    $TY = sin(($TAngle) * M_PI / 180) * ($Radius + 10 + $SpliceDistance) + $YPos + 4;
                } else {
                    $TY = sin(($TAngle) * M_PI / 180) * ($Radius + 4 + $SpliceDistance) + $YPos - ($TextHeight / 2);
                }

                if($TAngle > 90 && $TAngle < 270) {
                    $TX -= $TextWidth;
                }

                $C_TextColor = $this->AllocateColor($this->Picture, 70, 70, 70);
                imagettftext($this->Picture, $this->FontSize, 0, $TX, $TY, $C_TextColor, $this->FontName, utf8_decode($Caption));
            }


            // Process pie slices
            $LineColor = $this->AllocateColor($this->Picture, $Rc, $Gc, $Bc);
            $XLineLast = NULL;
            $YLineLast = NULL;
            for($iAngle = $Angle; $iAngle <= $Angle + $Value * $SpliceRatio; $iAngle += 0.5) {
                $PosX = cos($iAngle * M_PI / 180) * $Radius + $XPos + $XOffset;
                $PosY = sin($iAngle * M_PI / 180) * $Radius + $YPos + $YOffset;

                $TopPlots[$Key][] = round($PosX);
                $TopPlots[$Key][] = round($PosY);

                if($iAngle == $Angle || $iAngle == $Angle + $Value * $SpliceRatio || $iAngle + 0.5 > $Angle + $Value * $SpliceRatio) {
                    $this->drawLine($XPos+$XOffset, $YPos+$YOffset, $PosX, $PosY, $Rc, $Gc, $Bc);
                }

                if(isset($XLineLast)) {
                    $this->drawLine($XLineLast, $YLineLast, $PosX, $PosY, $Rc, $Gc, $Bc);
                }

                $XLineLast = $PosX;
                $YLineLast = $PosY;
            }

            $TopPlots[$Key][] = round($XPos + $XOffset);
            $TopPlots[$Key][] = round($YPos + $YOffset);

            $Angle = $iAngle;
        }
        $PolyPlots = $TopPlots;

        // Draw top polygons
        foreach ($PolyPlots as $Key => $Value) {
            if(!$AllBlack) {
                $C_GraphLo = $this->AllocateColor($this->Picture, $this->Palette[$Key]['R'], $this->Palette[$Key]['G'], $this->Palette[$Key]['B']);
            } else {
                $C_GraphLo = $this->AllocateColor($this->Picture, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor);
            }

            imagefilledpolygon($this->Picture, $PolyPlots[$Key], (count($PolyPlots[$Key]) + 1) / 2, $C_GraphLo);
        }
        $this->ShadowActive = $ShadowStatus;
    }

    /**
     * Draw a pseudo-3D pie chart
     *
     * The posssible $DrawLabels values are:
     *  - PIE_NOLABEL: Show no lables (default)
     *  - PIE_PERCENTAGE: Show percentages
     *  - PIE_LABELS: Show normal labels
     *  - PIE_PERCENTAGE_LABEL: Show both
     *
     * Only one series to plot is accepted.
     *
     * @param int $XPos
     * @param int $YPos
     * @param int $Radius = 100
     * @param int $DrawLabels = PIE_NOLABEL
     * @param boolean $EnhanceColors = TRUE
     * @param int $Skew = 60
     * @param int $SpliceHeight = 20
     * @param int $SpliceDistance = 0
     * @param int $Decimals = 0
     */
    public function drawPieGraph($XPos, $YPos, $Radius = 100, $DrawLabels = PIE_NOLABEL, $EnhanceColors = TRUE, $Skew = 60, $SpliceHeight = 20, $SpliceDistance = 0, $Decimals = 0) {
        $this->validateDataDescription('drawPieGraph', FALSE);
        $this->validateData('drawPieGraph');

        // Calculate pie sum
        $Series = 0;
        $PieSum = 0;
        $rPieSum = 0;
        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            if($ColName != $this->DataDescription['Position']) {
                ++$Series;
                foreach($this->Data as $Key => $Values) {
                    if(isset($this->Data[$Key][$ColName])) {
                        if($this->Data[$Key][$ColName] == 0) {
                            $iValues[] = 0;
                            $rValues[] = 0;
                            $iLabels[] = $this->Data[$Key][$this->DataDescription['Position']];
                        }  else {
                            $PieSum += $this->Data[$Key][$ColName];
                            $iValues[] = $this->Data[$Key][$ColName];
                            $iLabels[] = $this->Data[$Key][$this->DataDescription['Position']];
                            $rValues[] = $this->Data[$Key][$ColName];
                            $rPieSum += $this->Data[$Key][$ColName];
                        }
                    }
                }
            }
        }

        // Validate number of series
        if($Series != 1) {
            $this->RaiseFatal('Pie chart can only accept one serie of data.');
        }

        $SpliceDistanceRatio = $SpliceDistance;
        $SkewHeight          = ($Radius * $Skew) / 100;
        $SpliceRatio         = (360 - $SpliceDistanceRatio * count($iValues)) / $PieSum;
        $SplicePercent       = 100 / $PieSum;
        $rSplicePercent      = 100 / $rPieSum;

        // Calculate all polygons
        $Angle = 0;
        $CDev = 5;
        $TopPlots = array();
        $BotPlots = array();
        $aTopPlots = array();
        $aBotPlots = array();
        foreach($iValues as $Key => $Value) {
            $XCenterPos = cos(($Angle-$CDev+($Value*$SpliceRatio+$SpliceDistanceRatio)/2) * M_PI / 180) * $SpliceDistance + $XPos;
            $YCenterPos = sin(($Angle-$CDev+($Value*$SpliceRatio+$SpliceDistanceRatio)/2) * M_PI / 180) * $SpliceDistance + $YPos;
            $XCenterPos2 = cos(($Angle+$CDev+($Value*$SpliceRatio+$SpliceDistanceRatio)/2) * M_PI / 180) * $SpliceDistance + $XPos;
            $YCenterPos2 = sin(($Angle+$CDev+($Value*$SpliceRatio+$SpliceDistanceRatio)/2) * M_PI / 180) * $SpliceDistance + $YPos;

            $TopPlots[$Key][] = round($XCenterPos);
            $BotPlots[$Key][] = round($XCenterPos);
            $TopPlots[$Key][] = round($YCenterPos);
            $BotPlots[$Key][] = round($YCenterPos + $SpliceHeight);
            $aTopPlots[$Key][] = $XCenterPos;
            $aBotPlots[$Key][] = $XCenterPos;
            $aTopPlots[$Key][] = $YCenterPos;
            $aBotPlots[$Key][] = $YCenterPos + $SpliceHeight;

            // Process labels and sizes
            $Caption = '';

            switch($DrawLabels) {
                default: // Break intentionally omitted
                case PIE_NOLABEL:
                    break;
                case PIE_PERCENTAGE:
                    $Caption  = (round($Value * pow(10, $Decimals) * $SplicePercent) / pow(10, $Decimals)) . '%';
                    break;
                case PIE_LABELS:
                    $Caption  = $iLabels[$Key];
                    break;
                case PIE_PERCENTAGE_LABEL:
                    $Caption  = $iLabels[$Key] . "\n" . (round($Value * pow(10, $Decimals) * $SplicePercent) / pow(10, $Decimals)) . '%';
                    break;
            }

            if($Caption) {
                $TAngle = $Angle + ($Value * $SpliceRatio / 2);
                $Position = imageftbbox($this->FontSize, 0, $this->FontName, utf8_decode($Caption));
                $TextWidth = $Position[2] - $Position[0];
                $TextHeight = abs($Position[1]) + abs($Position[3]);

                $TX = cos(($TAngle) * M_PI / 180) * ($Radius + 10) + $XPos;

                if($TAngle > 0 && $TAngle < 180) {
                    $TY = sin(($TAngle) * M_PI / 180) * ($SkewHeight + 10) + $YPos + $SpliceHeight + 4;
                } else {
                    $TY = sin(($TAngle) * M_PI / 180) * ($SkewHeight + 4) + $YPos - ($TextHeight/2);
                }

                if($TAngle > 90 && $TAngle < 270) {
                    $TX -= $TextWidth;
                }

                $C_TextColor = $this->AllocateColor($this->Picture, 70, 70, 70);
                imagettftext($this->Picture, $this->FontSize, 0, $TX, $TY, $C_TextColor, $this->FontName, utf8_decode($Caption));
            }

            // Process pie slices
            for($iAngle = $Angle; $iAngle <= $Angle + $Value * $SpliceRatio; $iAngle += 0.5) {
                $TopX = cos($iAngle * M_PI / 180) * $Radius + $XPos;
                $TopY = sin($iAngle * M_PI / 180) * $SkewHeight + $YPos;

                $TopPlots[$Key][] = round($TopX);
                $BotPlots[$Key][] = round($TopX);
                $TopPlots[$Key][] = round($TopY);
                $BotPlots[$Key][] = round($TopY + $SpliceHeight);
                $aTopPlots[$Key][] = $TopX;
                $aBotPlots[$Key][] = $TopX;
                $aTopPlots[$Key][] = $TopY;
                $aBotPlots[$Key][] = $TopY + $SpliceHeight;
            }

            $TopPlots[$Key][] = round($XCenterPos2);
            $BotPlots[$Key][] = round($XCenterPos2);
            $TopPlots[$Key][] = round($YCenterPos2);
            $BotPlots[$Key][] = round($YCenterPos2 + $SpliceHeight);
            $aTopPlots[$Key][] = $XCenterPos2;
            $aBotPlots[$Key][] = $XCenterPos2;
            $aTopPlots[$Key][] = $YCenterPos2;
            $aBotPlots[$Key][] = $YCenterPos2 + $SpliceHeight;

            $Angle = $iAngle + $SpliceDistanceRatio;
        }

        // Draw bottom polygons
        foreach($iValues as $Key => $Value) {
            $C_GraphLo = $this->AllocateColor($this->Picture, $this->Palette[$Key]['R'], $this->Palette[$Key]['G'], $this->Palette[$Key]['B'], -20);
            imagefilledpolygon($this->Picture, $BotPlots[$Key], (count($BotPlots[$Key]) + 1) / 2, $C_GraphLo);

            if($EnhanceColors) {
                $En = -10;
            } else {
                $En = 0;
            }

            for($j = 0; $j <= count($aBotPlots[$Key]) -4 ; $j += 2) {
                $this->drawLine($aBotPlots[$Key][$j], $aBotPlots[$Key][$j+1], $aBotPlots[$Key][$j+2], $aBotPlots[$Key][$j+3], $this->Palette[$Key]['R']+$En, $this->Palette[$Key]['G']+$En, $this->Palette[$Key]['B']+$En);
            }
        }

        // Draw pie layers
        if($EnhanceColors) {
            $ColorRatio = 30 / $SpliceHeight;
        } else {
            $ColorRatio = 25 / $SpliceHeight;
        }

        for($i = $SpliceHeight - 1; $i >= 1; --$i) {
            foreach($iValues as $Key => $Value) {
                $C_GraphLo = $this->AllocateColor($this->Picture, $this->Palette[$Key]['R'], $this->Palette[$Key]['G'], $this->Palette[$Key]['B'], -10);
                $Plots = array();
                $Plot = 0;
                foreach($TopPlots[$Key] as $Key2 => $Value2) {
                    ++$Plot;
                    if($Plot % 2 == 1) {
                        $Plots[] = $Value2;
                    } else {
                        $Plots[] = $Value2 + $i;
                    }
                }
                imagefilledpolygon($this->Picture, $Plots, (count($Plots)+1)/2, $C_GraphLo);

                $Index = count($Plots);
                if($EnhanceColors) {
                    $ColorFactor = -20 + ($SpliceHeight - $i) * $ColorRatio;
                } else {
                    $ColorFactor = 0;
                }

                $this->drawAntialiasPixel($Plots[0], $Plots[1], $this->Palette[$Key]['R']+$ColorFactor, $this->Palette[$Key]['G']+$ColorFactor, $this->Palette[$Key]['B']+$ColorFactor);
                $this->drawAntialiasPixel($Plots[2], $Plots[3], $this->Palette[$Key]['R']+$ColorFactor, $this->Palette[$Key]['G']+$ColorFactor, $this->Palette[$Key]['B']+$ColorFactor);
                $this->drawAntialiasPixel($Plots[$Index-4], $Plots[$Index-3], $this->Palette[$Key]['R']+$ColorFactor, $this->Palette[$Key]['G']+$ColorFactor, $this->Palette[$Key]['B']+$ColorFactor);
            }
        }

        // Draw top polygons
        for($Key = count($iValues) - 1; $Key >= 0; --$Key) {
            $C_GraphLo = $this->AllocateColor($this->Picture, $this->Palette[$Key]['R'], $this->Palette[$Key]['G'], $this->Palette[$Key]['B']);
            imagefilledpolygon($this->Picture, $TopPlots[$Key], (count($TopPlots[$Key]) + 1) / 2, $C_GraphLo);

            if($EnhanceColors) {
                $En = 10;
            } else {
                $En = 0;
            }

            for($j = 0; $j <= count($aTopPlots[$Key]) - 4; $j += 2) {
                $this->drawLine($aTopPlots[$Key][$j], $aTopPlots[$Key][$j+1], $aTopPlots[$Key][$j+2], $aTopPlots[$Key][$j+3], $this->Palette[$Key]['R']+$En, $this->Palette[$Key]['G']+$En, $this->Palette[$Key]['B']+$En);
            }
        }
    }

    /**
     * Draw background with given color
     *
     * The default color is white.
     *
     * @param int $R = 255
     * @param int $G = 255
     * @param int $B = 255
     */
    public function drawBackground($R = 255, $G = 255, $B = 255) {
        $C_Background = $this->AllocateColor($this->Picture, $R, $G, $B);
        imagefilledrectangle($this->Picture, 0, 0, $this->XSize, $this->YSize, $C_Background);
    }

    /**
     * Draw a gradient over a number of steps
     *
     * $Target accepts the following values:
     *   - TARGET_GRAPHAREA (Default)
     *   - TARGET_BACKGROUND
     *
     * $Direction accepts the following values:
     *   - GRADIENT_VERTICAL (Default)
     *   - GRADIENT_HORIZONTAL
     *
     * Gradient starts with the starting color ($R, $G, $B) and
     * will draw as many colors as specified in $Decay (Can be
     * positive or negative). If $Decay is zero, will draw a
     * unicolor background.
     *
     * Wrapper for drawGraphAreaGradientToColor
     *
     * @param int $R
     * @param int $G
     * @param int $B
     * @param int $Decay
     * @param int $Target = TARGET_GRAPHAREA
     * @param int $Direction = GRADIENT_VERTICAL
     */
    public function drawGraphAreaGradient($R, $G, $B, $Decay, $Target = TARGET_GRAPHAREA, $Direction = GRADIENT_VERTICAL) {
        $this->drawGraphAreaGradientToColor($R, $G, $B, $R - $Decay, $G - $Decay, $B - $Decay, $Target, $Direction);
    }

    /**
     * Draw a gradient from one color to another
     *
     * $Target accepts the following values:
     *   - TARGET_GRAPHAREA (Default)
     *   - TARGET_BACKGROUND
     *
     * $Direction accepts the following values:
     *   - GRADIENT_VERTICAL (Default)
     *   - GRADIENT_HORIZONTAL
     *
     * Gradient starts with the starting color ($Rs, $Gs, $Bs) and
     * ends with the target color ($Rt, $Gt, $Bt).
     *
     * @param int $Rs
     * @param int $Gs
     * @param int $Bs
     * @param int $Rt
     * @param int $Gt
     * @param int $Bt
     * @param int $Target = TARGET_GRAPHAREA
     * @param int $Direction = GRADIENT_VERTICAL
     */
    public function drawGraphAreaGradientToColor($Rs, $Gs, $Bs, $Rt, $Gt, $Bt, $Target = TARGET_GRAPHAREA, $Direction = GRADIENT_VERTICAL) {
        switch($Target) {
            default: // Break intentionally omitted
            case TARGET_GRAPHAREA:
                $X1 = $this->GArea_X1;
                $X2 = $this->GArea_X2;
                $Y1 = $this->GArea_Y1;
                $Y2 = $this->GArea_Y2;
                break;
            case TARGET_BACKGROUND:
                $X1 = 0;
                $X2 = $this->XSize - 1;
                $Y1 = 0;
                $Y2 = $this->YSize - 1;
                break;
        }

        if($Rs != $Rt || $Gs != $Gt || $Bs != $Bt) {
            // Save direction, includes some variable variables magic
            $C1 = NULL;
            $C2 = NULL;
            switch($Direction) {
                default: // Break intentionally omitted
                case GRADIENT_VERTICAL:
                    $C1 = 'Y1';
                    $C2 = 'Y2';
                    $XX2 = 'X2';
                    $YY2 = 'Y1';
                    break;
                case GRADIENT_HORIZONTAL:
                    $C1 = 'X1';
                    $C2 = 'X2';
                    $XX2 = 'X1';
                    $YY2 = 'Y2';
                    break;
            }

            $gradientLength = $$C2 - $$C1 + 1;
            $RDecay = ($Rt - $Rs) / $gradientLength;
            $GDecay = ($Gt - $Gs) / $gradientLength;
            $BDecay = ($Bt - $Bs) / $gradientLength;

            for($i = 0; $i < $gradientLength; ++$i) {
                $C_Background = $this->AllocateColor($this->Picture, $Rs + ($i * $RDecay), $Gs + ($i * $GDecay), $Bs +($i * $BDecay));
                imageline($this->Picture, $X1, $Y1, $$XX2, $$YY2, $C_Background);
                ++$$C1;
            }
        } else {
            // No gradient required
            $this->drawFilledRectangle($X1, $Y1, $X2, $Y2, $Rs, $Gs, $Bs, FALSE, 100);
        }
    }

    /**
     * Draw a rectangle with antialias
     *
     * @param int $X1
     * @param int $Y1
     * @param int $X2
     * @param int $Y2
     * @param int $R
     * @param int $G
     * @param int $B
     */
    public function drawRectangle($X1, $Y1, $X2, $Y2, $R, $G, $B) {
        if($X2 < $X1) { list($X1, $X2) = array($X2, $X1); }
        if($Y2 < $Y1) { list($Y1, $Y2) = array($Y2, $Y1); }

        $X1 -= 0.2;
        $Y1 -= 0.2;
        $X2 += 0.2;
        $Y2 += 0.2;
        $this->drawLine($X1, $Y1, $X2, $Y1, $R, $G, $B);
        $this->drawLine($X2, $Y1, $X2, $Y2, $R, $G, $B);
        $this->drawLine($X2, $Y2, $X1, $Y2, $R, $G, $B);
        $this->drawLine($X1, $Y2, $X1, $Y1, $R, $G, $B);
    }

    /**
     * Draw a filled rectangle with antialias
     *
     * Drops a shadows.
     *
     * The $NoFallBack flag is used internally.
     *
     * @param int $X1
     * @param int $Y1
     * @param int $X2
     * @param int $Y2
     * @param int $R
     * @param int $G
     * @param int $B
     * @param boolean $DrawBorder = TRUE
     * @param int $Alpha = 100
     * @param boolean $NoFallBack = FALSE
     */
    public function drawFilledRectangle($X1, $Y1, $X2, $Y2, $R, $G, $B, $DrawBorder = TRUE, $Alpha = 100, $NoFallBack = FALSE) {
        if($Alpha == 100) {
            // Solid rectangle, process shadows
            if($this->ShadowActive && !$NoFallBack) {
                $this->drawFilledRectangle($X1 + $this->ShadowXDistance, $Y1 + $this->ShadowYDistance, $X2 + $this->ShadowXDistance, $Y2 + $this->ShadowYDistance, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor, FALSE, $this->ShadowAlpha, TRUE);
                if($this->ShadowBlur != 0) {
                    $AlphaDecay = ($this->ShadowAlpha / $this->ShadowBlur);

                    for($i = 1; $i <= $this->ShadowBlur; ++$i) {
                        $this->drawFilledRectangle($X1 + $this->ShadowXDistance - $i / 2, $Y1 + $this->ShadowYDistance - $i / 2, $X2 + $this->ShadowXDistance - $i / 2 , $Y2 + $this->ShadowYDistance - $i / 2, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor, FALSE, $this->ShadowAlpha - $AlphaDecay * $i,TRUE);
                    }
                    for($i = 1; $i <= $this->ShadowBlur; ++$i) {
                        $this->drawFilledRectangle($X1 + $this->ShadowXDistance + $i / 2, $Y1 + $this->ShadowYDistance + $i / 2, $X2 + $this->ShadowXDistance + $i / 2, $Y2 + $this->ShadowYDistance + $i / 2, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor, FALSE, $this->ShadowAlpha - $AlphaDecay * $i, TRUE);
                    }
                }
            }

            $C_Rectangle = $this->AllocateColor($this->Picture, $R, $G, $B);
            imagefilledrectangle($this->Picture, round($X1), round($Y1), round($X2), round($Y2), $C_Rectangle);
        } else {
            // Transparent rectangle
            $LayerWidth  = abs($X2 - $X1) + 2;
            $LayerHeight = abs($Y2 - $Y1) + 2;

            $this->Layers[0] = imagecreatetruecolor($LayerWidth, $LayerHeight);
            $C_White         = $this->AllocateColor($this->Layers[0], 255, 255, 255);
            imagefilledrectangle($this->Layers[0], 0, 0, $LayerWidth, $LayerHeight, $C_White);
            imagecolortransparent($this->Layers[0], $C_White);

            $C_Rectangle = $this->AllocateColor($this->Layers[0], $R, $G, $B);
            imagefilledrectangle($this->Layers[0], 1, 1, round($LayerWidth - 1), round($LayerHeight - 1), $C_Rectangle);

            imagecopymerge($this->Picture, $this->Layers[0], round(min($X1, $X2) - 1), round(min($Y1, $Y2) - 1), 0, 0, $LayerWidth, $LayerHeight, $Alpha);
            imagedestroy($this->Layers[0]);
        }

        if($DrawBorder) {
            // Deactivate shadow and draw the border
            $ShadowSettings = $this->ShadowActive;
            $this->ShadowActive = FALSE;
            $this->drawRectangle($X1, $Y1, $X2, $Y2, $R, $G, $B);
            $this->ShadowActive = $ShadowSettings;
        }
    }


    /**
     * Draw a rounded rectangle
     *
     * Give a radius in pixels for the corners
     *
     * @param int $X1
     * @param int $Y1
     * @param int $X2
     * @param int $Y2
     * @param int $Radius = 5
     * @param int $R
     * @param int $G
     * @param int $B
     */
    public function drawRoundedRectangle($X1, $Y1, $X2, $Y2, $Radius = 5, $R, $G, $B) {
        if(! $Radius) {
            $Radius = 5;
        }

        // Draw corners first
        $Step = 90 / ((M_PI * $Radius)/2);
        for($i = 0; $i <= 90; $i += $Step) {
            $X = cos(($i+180) * M_PI / 180) * $Radius + $X1 + $Radius;
            $Y = sin(($i+180) * M_PI / 180) * $Radius + $Y1 + $Radius;
            $this->drawAntialiasPixel($X, $Y, $R, $G, $B);

            $X = cos(($i-90) * M_PI / 180) * $Radius + $X2 - $Radius;
            $Y = sin(($i-90) * M_PI / 180) * $Radius + $Y1 + $Radius;
            $this->drawAntialiasPixel($X, $Y, $R, $G, $B);

            $X = cos(($i) * M_PI / 180) * $Radius + $X2 - $Radius;
            $Y = sin(($i) * M_PI / 180) * $Radius + $Y2 - $Radius;
            $this->drawAntialiasPixel($X, $Y, $R, $G, $B);

            $X = cos(($i+90) * M_PI / 180) * $Radius + $X1 + $Radius;
            $Y = sin(($i+90) * M_PI / 180) * $Radius + $Y2 - $Radius;
            $this->drawAntialiasPixel($X, $Y, $R, $G, $B);
        }

        // Draw antialiased lines between the corners
        $X1 -= 0.2;
        $Y1 -= 0.2;
        $X2 += 0.2;
        $Y2 += 0.2;

        $this->drawLine($X1 + $Radius, $Y1, $X2 - $Radius, $Y1, $R, $G, $B);
        $this->drawLine($X2, $Y1 + $Radius, $X2, $Y2 - $Radius, $R, $G, $B);
        $this->drawLine($X2 - $Radius, $Y2, $X1 + $Radius, $Y2, $R, $G, $B);
        $this->drawLine($X1, $Y2 - $Radius, $X1, $Y1 + $Radius, $R, $G, $B);
    }

    /**
     * Draw a filled rounded rectangle
     *
     * Give a radius in pixels for the corners
     *
     * @param int $X1
     * @param int $Y1
     * @param int $X2
     * @param int $Y2
     * @param int $Radius = 5
     * @param int $R
     * @param int $G
     * @param int $B
     */
    public function drawFilledRoundedRectangle($X1, $Y1, $X2, $Y2, $Radius = 5, $R, $G, $B) {
        $C_Rectangle = $this->AllocateColor($this->Picture, $R, $G, $B);

        // Draw the corners first
        $Step = 90 / ((M_PI * $Radius)/2);

        for($i = 0; $i <= 90; $i += $Step) {
            $Xi1 = cos(($i+180) * M_PI / 180) * $Radius + $X1 + $Radius;
            $Yi1 = sin(($i+180) * M_PI / 180) * $Radius + $Y1 + $Radius;

            $Xi2 = cos(($i-90) * M_PI / 180) * $Radius + $X2 - $Radius;
            $Yi2 = sin(($i-90) * M_PI / 180) * $Radius + $Y1 + $Radius;

            $Xi3 = cos(($i) * M_PI / 180) * $Radius + $X2 - $Radius;
            $Yi3 = sin(($i) * M_PI / 180) * $Radius + $Y2 - $Radius;

            $Xi4 = cos(($i+90) * M_PI / 180) * $Radius + $X1 + $Radius;
            $Yi4 = sin(($i+90) * M_PI / 180) * $Radius + $Y2 - $Radius;

            imageline($this->Picture, $Xi1, $Yi1, $X1+$Radius, $Yi1, $C_Rectangle);
            imageline($this->Picture, $X2-$Radius, $Yi2, $Xi2, $Yi2, $C_Rectangle);
            imageline($this->Picture, $X2-$Radius, $Yi3, $Xi3, $Yi3, $C_Rectangle);
            imageline($this->Picture, $Xi4, $Yi4, $X1+$Radius, $Yi4, $C_Rectangle);

            $this->drawAntialiasPixel($Xi1, $Yi1, $R, $G, $B);
            $this->drawAntialiasPixel($Xi2, $Yi2, $R, $G, $B);
            $this->drawAntialiasPixel($Xi3, $Yi3, $R, $G, $B);
            $this->drawAntialiasPixel($Xi4, $Yi4, $R, $G, $B);
        }

        // Fill the rectangle
        imagefilledrectangle($this->Picture, $X1, $Y1+$Radius, $X2, $Y2-$Radius, $C_Rectangle);
        imagefilledrectangle($this->Picture, $X1+$Radius, $Y1, $X2-$Radius, $Y2, $C_Rectangle);

        // Draw antialiased lines between the corners
        $X1 -= 0.2;
        $Y1 -= 0.2;
        $X2 += 0.2;
        $Y2 += 0.2;

        $this->drawLine($X1 + $Radius, $Y1, $X2 - $Radius, $Y1, $R, $G, $B);
        $this->drawLine($X2, $Y1 + $Radius, $X2, $Y2 - $Radius, $R, $G, $B);
        $this->drawLine($X2 - $Radius, $Y2, $X1 + $Radius, $Y2, $R, $G, $B);
        $this->drawLine($X1, $Y2 - $Radius, $X1, $Y1 + $Radius, $R, $G, $B);
    }

    /**
     * Draw a circle/ellipse
     *
     * If no width is given, will draw a circle
     *
     * @param int $Xc
     * @param int $Yc
     * @param int $Height
     * @param int $R
     * @param int $G
     * @param int $B
     * @param int $Width = NULL
     */
    public function drawCircle($Xc, $Yc, $Height, $R, $G, $B, $Width = NULL) {
        if($Height == 0) {
            return;
        }

        if(is_null($Width)) {
            $Width = $Height;
        }

        $C_Circle = $this->AllocateColor($this->Picture, $R, $G, $B);
        $Step     = 360 / (2 * M_PI * max($Width, $Height));

        for($i = 0; $i <= 360; $i += $Step) {
            $X = cos($i * M_PI / 180) * $Height + $Xc;
            $Y = sin($i * M_PI / 180) * $Width + $Yc;
            $this->drawAntialiasPixel($X, $Y, $R, $G, $B);
        }
    }

    /**
     * Draw a filled circle/ellipse
     *
     * If no width is given, will draw a circle.
     *
     * @param int $Xc
     * @param int $Yc
     * @param int $Height
     * @param int $R
     * @param int $G
     * @param int $B
     * @param int $Width = NULL
     */
    public function drawFilledCircle($Xc, $Yc, $Height, $R, $G, $B, $Width = NULL) {
        if($Height == 0) {
            return;
        }

        if(is_null($Width)) {
            $Width = $Height;
        }

        $C_Circle = $this->AllocateColor($this->Picture, $R, $G, $B);
        $Step     = 360 / (2 * M_PI * max($Width, $Height));

        for($i = 90; $i <= 270; $i += $Step) {
            $X1 = cos($i * M_PI / 180) * $Height + $Xc;
            $Y1 = sin($i * M_PI / 180) * $Width + $Yc;
            $X2 = cos((180 - $i) * M_PI / 180) * $Height + $Xc;
            $Y2 = sin((180 - $i) * M_PI / 180) * $Width + $Yc;

            $this->drawAntialiasPixel($X1 - 1, $Y1 - 1, $R, $G, $B);
            $this->drawAntialiasPixel($X2 - 1, $Y2 - 1, $R, $G, $B);

            if(($Y1 - 1) > $Yc - max($Width, $Height)) {
                imageline($this->Picture, $X1, $Y1 - 1, $X2 - 1, $Y2 - 1, $C_Circle);
            }
        }
    }

    /**
     * Draw Ellipse
     *
     * Wrapper for drawCircle.
     *
     * @param int $Xc
     * @param int $Yc
     * @param int $Height
     * @param int $Width
     * @param int $R
     * @param int $G
     * @param int $B
     */
    public function drawEllipse($Xc, $Yc, $Height, $Width, $R, $G, $B) {
        $this->drawCircle($Xc, $Yc, $Height, $R, $G, $B, $Width);
    }

    /**
     * Draw fille ellipse
     *
     * Wrapper for drawFilledCircle
     *
     * @param int $Xc
     * @param int $Yc
     * @param int $Height
     * @param int $Width
     * @param int $R
     * @param int $G
     * @param int $B
     */
    public function drawFilledEllipse($Xc, $Yc, $Height, $Width, $R, $G, $B) {
        $this->drawFilledCircle($Xc, $Yc, $Height, $R, $G, $B, $Width);
    }

    /**
     * Draw a line
     *
     * You can set the style with setLineStyle().
     *
     * If LineDotSize is larger than 1, will draw a
     * dotted line.
     *
     * If $GraphFunction is set to TRUE, will only draw
     * within the graph area.
     *
     * @param int $X1
     * @param int $Y1
     * @param int $X2
     * @param int $Y2
     * @param int $R
     * @param int $G
     * @param int $B
     * @param boolean $GraphFunction = FALSE
     */
    public function drawLine($X1, $Y1, $X2, $Y2, $R, $G, $B, $GraphFunction = FALSE) {
        if($this->LineDotSize > 1) {
            $this->drawDottedLine($X1, $Y1, $X2, $Y2, $this->LineDotSize, $R, $G, $B, $GraphFunction);
            return;
        }

        $Distance = sqrt(($X2 - $X1) * ($X2 - $X1) + ($Y2 - $Y1) * ($Y2 - $Y1));
        if($Distance == 0) {
            return;
        }

        $XStep = ($X2 - $X1) / $Distance;
        $YStep = ($Y2 - $Y1) / $Distance;

        for($i = 0; $i <= $Distance; ++$i) {
            $X = $i * $XStep + $X1;
            $Y = $i * $YStep + $Y1;

            if(($X >= $this->GArea_X1 && $X <= $this->GArea_X2 && $Y >= $this->GArea_Y1 && $Y <= $this->GArea_Y2) || !$GraphFunction) {
                if($this->LineWidth == 1) {
                    $this->drawAntialiasPixel($X, $Y, $R, $G, $B);
                } else {
                    $StartOffset = - ($this->LineWidth / 2);
                    $EndOffset = $this->LineWidth / 2;

                    for($j = $StartOffset; $j <= $EndOffset; ++$j) {
                        $this->drawAntialiasPixel($X + $j, $Y + $j, $R, $G, $B);
                    }
                }
            }
        }
    }

    /**
     * Draw dotted line
     *
     * You can set the line style with
     * setLineStyle();
     *
     * If the flag $GraphFunction is set to
     * TRUE, will only draw within the graph area.
     *
     * @param int $X1
     * @param int $Y1
     * @param int $X2
     * @param int $Y2
     * @param int $DotSize
     * @param int $R
     * @param int $G
     * @param int $B
     * @param boolean $GraphFunction = FALSE
     */
    public function drawDottedLine($X1, $Y1, $X2, $Y2, $DotSize, $R, $G, $B, $GraphFunction = FALSE) {
        $Distance = sqrt(($X2 - $X1) * ($X2 - $X1) + ($Y2 - $Y1) * ($Y2 - $Y1));

        $XStep = ($X2 - $X1) / $Distance;
        $YStep = ($Y2 - $Y1) / $Distance;

        $DotIndex = 0;
        for($i = 0; $i <= $Distance; ++$i) {
            $X = $i * $XStep + $X1;
            $Y = $i * $YStep + $Y1;

            if($DotIndex <= $DotSize) {
                if(($X >= $this->GArea_X1 && $X <= $this->GArea_X2 && $Y >= $this->GArea_Y1 && $Y <= $this->GArea_Y2) || !$GraphFunction) {
                    if($this->LineWidth == 1) {
                        $this->drawAntialiasPixel($X, $Y, $R, $G, $B);
                    } else {
                        $StartOffset = - ($this->LineWidth / 2);
                        $EndOffset = $this->LineWidth / 2;

                        for($j = $StartOffset; $j <= $EndOffset; ++$j) {
                            $this->drawAntialiasPixel($X + $j, $Y + $j, $R, $G, $B);
                        }
                    }
                }
            }

            ++$DotIndex;
            if($DotIndex == $DotSize * 2) {
                $DotIndex = 0;
            }
        }
    }

    /**
     * Insert PNG image into chart
     *
     * Wrapper for drawFromPicture.
     *
     * @param string $FileName
     * @param int $X
     * @param int $Y
     * @param int $Alpha = 100
     * @return boolean $FileFound
     */
    public function drawFromPNG($FileName, $X, $Y, $Alpha = 100) {
        return $this->drawFromPicture(1, $FileName, $X, $Y, $Alpha);
    }

    /**
     * Insert GIF image into chart
     *
     * Wrapper for drawFromPicture.
     *
     * @param string $FileName
     * @param int $X
     * @param int $Y
     * @param int $Alpha = 100
     * @return boolean $FileFound
     */
    public function drawFromGIF($FileName, $X, $Y, $Alpha = 100) {
        return $this->drawFromPicture(2, $FileName, $X, $Y, $Alpha);
    }

    /**
     * Insert JPG image into chart
     *
     * Wrapper for drawFromPicture.
     *
     * @param string $FileName
     * @param int $X
     * @param int $Y
     * @param int $Alpha = 100
     * @return boolean $FileFound
     */
    public function drawFromJPG($FileName, $X, $Y, $Alpha = 100) {
        return $this->drawFromPicture(3, $FileName, $X, $Y, $Alpha);
    }

    /**
     * Generic image insert function
     *
     * Valid values for $PicType:
     * - 'png', 1
     * - 'gif', 2
     * - 'jpeg', 'jpg', 3
     *
     * @param string $PicType
     * @param string $FileName
     * @param int $X
     * @param int $Y
     * @param int $Alpha = 100
     * @return boolean $FileFound
     */
    public function drawFromPicture($PicType, $FileName, $X, $Y, $Alpha = 100) {
        if(file_exists($FileName)) {
            $Infos  = getimagesize($FileName);
            $Width  = $Infos[0];
            $Height = $Infos[1];
            switch(strtolower($PicType)) {
                default: // Break intentionally omitted
                case 1:
                case 'png':
                    $Raster = imagecreatefrompng($FileName);
                    break;
                case 2:
                case 'gif':
                    $Raster = imagecreatefromgif($FileName);
                    break;
                case 3:
                case 'jpeg':
                case 'jpg':
                    $Raster = imagecreatefromjpeg($FileName);
                    break;
            }

            imagecopymerge($this->Picture, $Raster, $X, $Y, 0, 0, $Width, $Height, $Alpha);
            imagedestroy($Raster);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Draw a single pixel with transparency onto the image
     *
     * @param int $X
     * @param int $Y
     * @param int $Alpha
     * @param int $R
     * @param int $G
     * @param int $B
     */
    public function drawAlphaPixel($X, $Y, $Alpha, $R, $G, $B) {
        if($X < 0 || $Y < 0 || $X >= $this->XSize || $Y >= $this->YSize) {
            return;
        }

        $RGB2 = imagecolorat($this->Picture, $X, $Y);
        $R2   = ($RGB2 >> 16) & 0xFF;
        $G2   = ($RGB2 >> 8) & 0xFF;
        $B2   = $RGB2 & 0xFF;

        $iAlpha = (100 - $Alpha)/100;
        $Alpha  = $Alpha / 100;

        $Ra   = floor($R * $Alpha + $R2 * $iAlpha);
        $Ga   = floor($G * $Alpha + $G2 * $iAlpha);
        $Ba   = floor($B * $Alpha + $B2 * $iAlpha);

        $C_Aliased = $this->AllocateColor($this->Picture, $Ra, $Ga, $Ba);
        imagesetpixel($this->Picture, $X, $Y, $C_Aliased);
    }

    /**
     * Allocate color
     *
     * Takes an optional $Factor which is added
     * to each color component.
     *
     * @param resource $Picture
     * @param int $R
     * @param int $G
     * @param int $B
     * @param int $Factor = 0
     * @return resource $color
     */
    private function AllocateColor($Picture, $R, $G, $B, $Factor = 0) {
        $R = round($R + $Factor);
        $G = round($G + $Factor);
        $B = round($B + $Factor);
        if($R < 0) { $R = 0; } if($R > 255) { $R = 255; }
        if($G < 0) { $G = 0; } if($G > 255) { $G = 255; }
        if($B < 0) { $B = 0; } if($B > 255) { $B = 255; }

        return imagecolorallocate($Picture, $R, $G, $B);
    }

    /**
     * Add a border around the picture
     *
     * Careful, this will change the images dimensions and should
     * be called before any plotting function.
     *
     * Default is a black border, 3px wide.
     *
     * @param int $Size = 3
     * @param int $R = 0
     * @param int $G = 0
     * @param int $B = 0
     */
    public function addBorder($Size = 3, $R = 0, $G = 0, $B = 0) {
        $Width  = $this->XSize + 2 * $Size;
        $Height = $this->YSize + 2 * $Size;

        $Resampled    = imagecreatetruecolor($Width, $Height);
        $C_Background = $this->AllocateColor($Resampled, $R, $G, $B);
        imagefilledrectangle($Resampled, 0, 0, $Width, $Height, $C_Background);

        imagecopy($Resampled, $this->Picture, $Size, $Size, 0, 0, $this->XSize, $this->YSize);
        imagedestroy($this->Picture);

        $this->XSize = $Width;
        $this->YSize = $Height;

        $this->Picture = imagecreatetruecolor($this->XSize, $this->YSize);
        $C_White = $this->AllocateColor($this->Picture, 255, 255, 255);
        imagefilledrectangle($this->Picture, 0, 0, $this->XSize, $this->YSize, $C_White);
        imagecolortransparent($this->Picture, $C_White);
        imagecopy($this->Picture, $Resampled, 0, 0, 0, 0, $this->XSize, $this->YSize);
    }

    /**
     * Set the interval for skipping labels and grid lines
     *
     * @param int $XInterval
     */
    public function setInterval($XInterval = 1) {
        $this->XInterval = $XInterval;
    }

    /**
     * Set the antialiasing quality
     *
     * The quality can range between 0 (default, best)
     * and 100 (worst).
     *
     * @param int $quality = 0
     */
    public function setAntialiasQuality($quality = 0) {
        $this->AntialiasQuality = min(100, max(0, (int) $quality));
    }

    /**
     * Set the minimum pixel height of a division
     *
     * @param int $pixels = 25
     */
    public function setMinDivHeight($pixels = 25) {
        $this->MinDivHeight = $pixels;
    }

    /**
     * Render the current picture to a file
     *
     * @param string $FileName
     * @return boolean $FileWriteSuccess
     */
    public function render($FileName) {
        // Spell out errors
        if($this->ErrorReporting) {
            $this->printErrors($this->ErrorInterface);
        }

        // Save image map
        if($this->BuildMap) {
            $this->SaveImageMap();
        }

        // Cache if necessary
        if($this->CacheEnabled) {
            $this->writeToCache();
        }


        // Save image
        $success = imagepng($this->Picture, $FileName);

        return $success;
    }

    /**
     * Render the current picture to browser
     *
     * @return boolean $PictureRenderSuccess
     */
    function stroke() {
        // Draw errors
        if($this->ErrorReporting) {
            $this->printErrors('GD');
        }

        // Save image map
        if($this->BuildMap) {
            $this->SaveImageMap();
        }

        // Cache if necessary
        if($this->CacheEnabled) {
            $this->writeToCache();
        }

        // Output picture
        header('Content-type: image/png');
        $success = imagepng($this->Picture);

        return $success;
    }

    /**
     * Draw a single antialiased pixel
     *
     * $NoFallBack flag is used internally
     *
     * @param int $X
     * @param int $Y
     * @param int $R
     * @param int $G
     * @param int $B
     * @param int $Alpha = 100
     * @param boolean $NoFallBack = FALSE
     */
    private function drawAntialiasPixel($X, $Y, $R, $G, $B, $Alpha = 100, $NoFallBack = FALSE) {
        // Shadow
        if($this->ShadowActive && !$NoFallBack) {
            $this->drawAntialiasPixel($X + $this->ShadowXDistance, $Y + $this->ShadowYDistance, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor, $this->ShadowAlpha, TRUE);
            if($this->ShadowBlur) {
                $AlphaDecay = ($this->ShadowAlpha / $this->ShadowBlur);

                for($i = 1; $i <= $this->ShadowBlur; ++$i) {
                    $this->drawAntialiasPixel($X + $this->ShadowXDistance - $i / 2, $Y + $this->ShadowYDistance - $i / 2, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor, $this->ShadowAlpha - $AlphaDecay * $i, TRUE);
                }
                for($i = 1; $i <= $this->ShadowBlur; ++$i) {
                    $this->drawAntialiasPixel($X + $this->ShadowXDistance + $i / 2, $Y + $this->ShadowYDistance + $i / 2, $this->ShadowRColor, $this->ShadowGColor, $this->ShadowBColor, $this->ShadowAlpha - $AlphaDecay * $i, TRUE);
                }
            }
        }

        $Plot = array();
        $Xi   = floor($X);
        $Yi   = floor($Y);

        if($Xi == $X && $Yi == $Y) {
            if($Alpha == 100) {
                $C_Aliased = $this->AllocateColor($this->Picture, $R, $G, $B);
                imagesetpixel($this->Picture, $X, $Y, $C_Aliased);
            } else {
                $this->drawAlphaPixel($X, $Y, $Alpha, $R, $G, $B);
            }
        } else {
            $Alpha1 = (((1 - ($X - floor($X))) * (1 - ($Y - floor($Y))) * 100) / 100) * $Alpha;
            if($Alpha1 > $this->AntialiasQuality) { $this->drawAlphaPixel($Xi, $Yi, $Alpha1, $R, $G, $B); }

            $Alpha2 = ((($X - floor($X)) * (1 - ($Y - floor($Y))) * 100) / 100) * $Alpha;
            if($Alpha2 > $this->AntialiasQuality) { $this->drawAlphaPixel($Xi+1, $Yi, $Alpha2, $R, $G, $B); }

            $Alpha3 = (((1 - ($X - floor($X))) * ($Y - floor($Y)) * 100) / 100) * $Alpha;
            if($Alpha3 > $this->AntialiasQuality) { $this->drawAlphaPixel($Xi, $Yi+1, $Alpha3, $R, $G, $B); }

            $Alpha4 = ((($X - floor($X)) * ($Y - floor($Y)) * 100) / 100) * $Alpha;
            if($Alpha4 > $this->AntialiasQuality) { $this->drawAlphaPixel($Xi+1, $Yi+1, $Alpha4, $R, $G, $B); }
        }
    }

    /**
     * Validate the data description array before plotting
     *
     * Adds warnings to the $Errors array and continues execution
     *
     * If the $DescriptionRequired flag is set, will also check if every
     * value has a description. If not, it will copy the all values to its
     * description.
     *
     * @param string $FunctionName
     * @param boolean $DescriptionRequired = TRUE
     */
    private function validateDataDescription($FunctionName, $DescriptionRequired= TRUE) {
        if(!isset($this->DataDescription['Position'])) {
            $this->Errors[] = '[Warning] '.$FunctionName.' - Y Labels are not set.';
            $this->DataDescription['Position'] = 'Name';
        }

        if($DescriptionRequired) {
            if(!isset($this->DataDescription['Description'])) {
                $this->Errors[] = '[Warning] '.$FunctionName.' - Series descriptions are not set.';
                foreach($this->DataDescription['Values'] as $key => $Value) {
                    $this->DataDescription['Description'][$Value] = $Value;
                }
            }

            if(count($this->DataDescription['Description']) < count($this->DataDescription['Values'])) {
                $this->Errors[] = '[Warning] '.$FunctionName.' - Some series descriptions are not set.';
                foreach($this->DataDescription['Values'] as $key => $Value) {
                    if(!isset($this->DataDescription['Description'][$Value]))
                    $this->DataDescription['Description'][$Value] = $Value;
                }
            }
        }
    }

    /**
     * Validate the data
     *
     * Checks if all values in a series are set.
     *
     * @param string $FunctionName
     */
    private function validateData($FunctionName) {
        $this->DataSummary = array();

        foreach($this->Data as $key => $Values) {
            foreach($Values as $key2 => $Value) {
                if(!isset($this->DataSummary[$key2])) {
                    $this->DataSummary[$key2] = 1;
                } else {
                    ++$this->DataSummary[$key2];
                }
            }
        }

        if(max($this->DataSummary) == 0) {
            $this->Errors[] = '[Warning] '.$FunctionName.' - No data set.';
        }

        foreach($this->DataSummary as $key => $Value) {
            if($Value < max($this->DataSummary)) {
                $this->Errors[] = '[Warning] '.$FunctionName.' - Missing data in serie '.$key.'.';
            }
        }
    }

    /**
     * Print out errors in current mode
     *
     * Possible values for $Mode are:
     *  - 'CLI' (Standard out, Default)
     *  - 'GD' (Graphical out, default for stroke)
     *
     * @param string $Mode = 'CLI'
     */
    private function printErrors($Mode = 'CLI') {
        // No errors found? Nothing to display, so exit.
        if(count($this->Errors) == 0) {
            return;
        }

        switch(strtoupper($Mode)) {
            default: // Break intentionally omitted
            case 'CLI':
                foreach($this->Errors as $key => $Value) {
                    echo $Value . "\n";
                }
                break;
            case 'GD':
                $this->setLineStyle(1);
                $MaxWidth = 0;
                $errorFont = realpath(dirname(__FILE__) . '/' . $this->ErrorFontName);
                foreach($this->Errors as $key => $Value) {
                    $Position  = imageftbbox($this->ErrorFontSize, 0, $errorFont, utf8_decode($Value));
                    $TextWidth = $Position[2] - $Position[0];
                    $MaxWidth = max($MaxWidth, $TextWidth);
                }

                $this->drawFilledRoundedRectangle($this->XSize - ($MaxWidth + 20), $this->YSize - (20 + (($this->ErrorFontSize + 4) * count($this->Errors))), $this->XSize - 10, $this->YSize - 10, 6, 233, 185, 185);
                $this->drawRoundedRectangle($this->XSize - ($MaxWidth + 20), $this->YSize - (20 + (($this->ErrorFontSize + 4) * count($this->Errors))), $this->XSize - 10, $this->YSize - 10, 6, 193, 145, 145);

                $C_TextColor = $this->AllocateColor($this->Picture, 133, 85, 85);
                $YPos        = $this->YSize - (18 + (count($this->Errors) - 1) * ($this->ErrorFontSize + 4));
                foreach($this->Errors as $key => $Value) {
                    imagettftext($this->Picture, $this->ErrorFontSize, 0, $this->XSize - ($MaxWidth + 15), $YPos, $C_TextColor, $errorFont, utf8_decode($Value));
                    $YPos += ($this->ErrorFontSize + 4);
                }
                break;
        }
    }

    /**
     * Enable building of the image map for the current chart
     *
     * @param boolean $BuildMap = TRUE
     * @param string $GraphID = 'MyGraph'
     */
    public function setImageMap($BuildMap = TRUE, $GraphID = 'MyGraph') {
        $this->BuildMap = $BuildMap;
        $this->MapID    = $GraphID;
    }

    /**
     * Add a box to the image map
     *
     * @param int $X1
     * @param int $Y1
     * @param int $X2
     * @param int $Y2
     * @param string $SerieName
     * @param float $Value
     * @param string $CallerFunction
     */
    private function addToImageMap($X1, $Y1, $X2, $Y2, $SerieName, $Value, $CallerFunction) {
        if(is_null($this->MapFunction) || $this->MapFunction == $CallerFunction) {
            $this->ImageMap[]  = round($X1).','.round($Y1).','.round($X2).','.round($Y2).','.$SerieName.','.$Value;
            $this->MapFunction = $CallerFunction;
        }
    }

    /**
     * Load an image map from disk and delete it
     *
     * If the map has been found, it will be echoed
     * immediatly, if not a 404 error is produced.
     * Then the script exits.
     *
     * This function is to be used for AJAX insertion
     * into HTML.
     *
     * @param string $MapName
     * @param boolean $Flush = TRUE
     */
    public function getImageMap($MapName, $Flush = TRUE) {
        // Strip HTML query strings
        $Values   = $this->tmpFolder . $MapName;
        $Value    = split('\?', $Values);
        $FileName = $Value[0];

        if(file_exists($FileName)) {
            readfile($FileName);
            if($Flush) {
                unlink($FileName);
            }
        } else {
            header('HTTP/1.0 404 Not Found');
        }
        exit();
    }

    /**
     * Save the image file to disk
     *
     * @return boolean $FileWriteSuccess
     */
    public function saveImageMap() {
        if(!$this->BuildMap) {
            return FALSE;
        }

        if(is_null($this->ImageMap)) {
            $this->Errors[] = '[Warning] SaveImageMap - Image map is empty.';
            return FALSE;
        }

        $Handle = fopen($this->tmpFolder.$this->MapID, 'w');

        if(!$Handle) {
            $this->Errors[] = '[Warning] SaveImageMap - Cannot save the image map.';
            return FALSE;
        } else {
            foreach($this->ImageMap as $Value) {
                fwrite($Handle, htmlentities($Value) . "\n");
            }
        }
        fclose ($Handle);
        return TRUE;
    }

    /**
     * Set temporary directory
     *
     * @param string $TempDir
     * @param return $DirectoryFoundAndWritable
     */
    public function setTempDir($TempDir) {
        if(is_writable($TempDir)) {
            $this->tmpFolder = realpath($TempDir) . DIRECTORY_SEPARATOR;
            return TRUE;
        } else {
            $this->Errors[] = '[Warning] SetTempDir - Directory ' . $TempDir . ' not writable.';
            return FALSE;
        }
    }

    /**
     * Convert timestamp to time string
     *
     * Uses $TimeFormat for formatting, default is
     * HH:MM:SS. Change it with SetTimeFormat().
     *
     * @param int $Value
     * @return string $TimeString
     */
    private function ToTime($Value) {
        $Value -= date('Z'); // Timezone correction
        return date($this->TimeFormat, $Value);
    }

    /**
     * Convert value to metric string
     *
     * Three possible suffixes: g, m, k
     *
     * @param $Value
     * @return string $MetricString
     */
    private function ToMetric($Value) {
        $Go = floor($Value/1000000000);
        $Mo = floor(($Value - $Go*1000000000)/1000000);
        $Ko = floor(($Value - $Go*1000000000 - $Mo*1000000)/1000);
        $o  = floor($Value - $Go*1000000000 - $Mo*1000000 - $Ko*1000);

        if($Go != 0) {
            return $Go.'.'.$Mo.'g';
        }
        if($Mo != 0) {
            return $Mo.'.'.$ko.'m';
        }
        if($Ko != 0)  {
            return $Ko.'.'.$o.'k';
        }
        return $o;
    }

    /**
     * Convert value to currency string
     *
     * @param $Value
     * @return string $CurrencyString
     */
    private function ToCurrency($Value) {
        $Go = floor($Value/1000000000);
        $Mo = floor(($Value - $Go*1000000000)/1000000);
        $Ko = floor(($Value - $Go*1000000000 - $Mo*1000000)/1000);
        $o  = floor($Value - $Go*1000000000 - $Mo*1000000 - $Ko*1000);

        if(strlen($o) == 1) { $o = '00'.$o; }
        if(strlen($o) == 2) { $o = '0'.$o; }

        $ResultString = $o;
        if($Ko != 0) { $ResultString = $Ko.'.'.$ResultString; }
        if($Mo != 0) { $ResultString = $Mo.'.'.$ResultString; }
        if($Go != 0) { $ResultString = $Go.'.'.$ResultString; }

        $ResultString = $this->Currency . $ResultString;
        return $ResultString;
    }

    /**
     * Convert timestamp to date string
     *
     * @param int $Value
     * @return string $DateString
     */
    private function ToDate($Value) {
        if($this->UseStrftime) {
            return(strftime($this->DateFormat, $Value));
        } else {
            return(date($this->DateFormat, $Value));
        }
    }

    /**
     * Set the current date format
     *
     * The default is 'd.m.Y'. See the page
     * http://www.php.net/date for all possible formatting strings.
     *
     * If the $useStrftime flag is set to true, mtChart will use
     * the strftime-function with the alternate syntax from the page
     * http://www.php.net/manual/en/function.strftime.php to format
     * the dates according to the current locale. You can set the
     * locale with setlocale(LC_TIME, ...).
     *
     * @param string $Format
     * @param boolean $UseStrftime
     */
    public function setDateFormat($Format, $UseStrftime = FALSE) {
        $this->DateFormat = $Format;
        $this->UseStrftime = $UseStrftime;
    }

    /**
     * Set the current time format
     *
     * Always uses the date-format convention, see
     * http://www.php.net/date for all possible formatting strings.
     *
     * Ignores the $useStrftime flag
     *
     * @param string $TimeFormat
     */
    public function setTimeFormat($TimeFormat) {
        $this->TimeFormat = $TimeFormat;
    }

    /**
     * Check if value is an integer
     *
     * @param float $Value
     * @return boolean $IsInteger
     */
    private function isRealInt($Value) {
        if($Value == floor($Value)) {
            return TRUE;
        } else {
            return(FALSE);
        }
    }

    /**
     * Raise fatal error and exit script
     *
     * @param string $Message
     */
    private function RaiseFatal($Message) {
        echo "[FATAL] $Message\n";
        exit();
    }

    /**
     * Search for the maximum value in the plotted data
     *
     * @return float $MaximumValue
     */
    public function maximumValue() {
        $MaximumValue = NULL;

        foreach($this->DataDescription['Values'] as $Key2 => $ColName) {
            foreach($this->Data as $Key => $Values) {
                if(isset($this->Data[$Key][$ColName])) {
                    $MaximumValue = max($MaximumValue, $this->Data[$Key][$ColName]);
                }
            }
        }

        return $MaximumValue;
    }

    /**
     * Data functions start here
     *
     * Adapted from pData
     */

    /**
     * Import CSV file into mtData object
     *
     * @param string $FileName
     * @param string $Delimiter = ';'
     * @param array $DataColumns = NULL
     * @param boolean $HasHeader = FALSE
     * @param string $DataName = NULL
     * @param int $MaxLineLength = 4096;
     * @return boolean $success
     */
    public function importFromCSV($FileName, $Delimiter = ';', $DataColumns = NULL, $HasHeader = FALSE, $DataName = NULL, $MaxLineLength = 4096) {
        $success = FALSE;
        $handle = @fopen($FileName, 'r');

        if($handle) {
            $success = TRUE;
            $HeaderParsed = FALSE;
            while(!feof($handle)) {
                $buffer = fgets($handle, $MaxLineLength);
                $Values = explode($Delimiter, trim($buffer));

                if($buffer) {
                    if($HasHeader == TRUE && $HeaderParsed == FALSE) {
                        // Parse header first
                        if(is_null($DataColumns)) {
                            $ID = 1;
                            foreach($Values as $Value) {
                                $this->SetSerieName($Value, 'Serie'.$ID);
                                ++$ID;
                            }
                        } else {
                            foreach($DataColumns as $Value) {
                                $this->SetSerieName($Values[$Value], 'Serie'.$Value);
                            }
                        }
                        $HeaderParsed = TRUE;
                    } else {
                        // Normal data row
                        if(is_null($DataColumns)) {
                            $ID = 1;
                            foreach($Values as $Value) {
                                $this->AddPoint(floatval($Value), 'Serie'.$ID);
                                ++$ID;
                            }
                        } else {
                            $SerieName = '';
                            if(isset($DataName)) {
                                $SerieName = $Values[$DataName];
                            }

                            foreach($DataColumns as $Value) {
                                $this->AddPoint(floatval($Values[$Value]), 'Serie'.$Value, $SerieName);
                            }
                        }
                    }
                }
            }
            fclose($handle);
        }
        return $success;
    }

    /**
     * Add point to data series
     *
     * By default adds it to the first series.
     *
     * @param float $Value
     * @param string $Serie = 'Serie1'
     * @param string $Description = NULL
     */
    public function addPoint($Value, $Serie = 'Serie1', $Description = NULL) {
        if(is_array($Value) && count($Value) == 1) {
            $Value = $Value[0];
        }

        $ID = 0;
        for($i=0; $i<=count($this->Data); ++$i) {
            if(isset($this->Data[$i][$Serie])) {
                $ID = $i+1;
            }
        }

        if(count($Value) == 1) {
            $this->Data[$ID][$Serie] = $Value;
            if(isset($Description)) {
                $this->Data[$ID]['Name'] = $Description;
            } else if(!isset($this->Data[$ID]['Name'])) {
                $this->Data[$ID]['Name'] = $ID;
            }
        } else {
            foreach($Value as $Val) {
                $this->Data[$ID][$Serie] = $Val;
                if(!isset($this->Data[$ID]['Name'])) {
                    $this->Data[$ID]['Name'] = $ID;
                }
                ++$ID;
            }
        }
    }

    /**
     * Add a single series to plotted series
     *
     * By default adds first series.
     *
     * @param string $SerieName = 'Serie1'
     */
    public function addSerie($SerieName = 'Serie1') {
        if(!isset($this->DataDescription['Values'])) {
            $this->DataDescription['Values'][] = $SerieName;
        } else {
            $Found = FALSE;
            foreach($this->DataDescription['Values'] as $Value) {
                if($Value == $SerieName) {
                    $Found = TRUE;
                    break;
                }
            }

            if(! $Found) {
                $this->DataDescription['Values'][] = $SerieName;
            }
        }
    }

    /**
     * Add all series to plotted series
     *
     */
    public function addAllSeries() {
        unset($this->DataDescription['Values']);

        if(isset($this->Data[0])) {
            foreach($this->Data[0] as $Key => $Value) {
                if($Key != 'Name') {
                    $this->DataDescription['Values'][] = $Key;
                }
            }
        }
    }

    /**
     * Remove series from plotted series
     *
     * By default removes first series.
     *
     * @param string $SerieName = 'Serie1'
     * @return boolean $Found
     */
    function removeSerie($SerieName = 'Serie1') {
        $Found = FALSE;

        if(is_null($this->DataDescription['Values'])) {
            return $Found;
        }

        foreach($this->DataDescription['Values'] as $key => $Value) {
            if($Value == $SerieName) {
                unset($this->DataDescription['Values'][$key]);
                $Found = TRUE;
                break;
            }
        }

        return $Found;
    }

    /**
     * Set the series which will be used as label on the abscise (x-axis)
     *
     * @param $SerieName = 'Name'
     */
    public function setAbsciseLabelSerie($SerieName = 'Name') {
        $this->DataDescription['Position'] = $SerieName;
    }


    /**
     * Set the name for a series
     *
     * Will be used in the legend for this series
     *
     * @param string $Name
     * @param string $SerieName = 'Serie1'
     */
    public function setSerieName($Name, $SerieName = 'Serie1') {
        $this->DataDescription['Description'][$SerieName] = $Name;
    }

    /**
     * Set the name of the x-axis
     *
     * @param string $Name = 'X Axis'
     */
    public function setXAxisName($Name='X Axis') {
        $this->DataDescription['Axis']['X'] = $Name;
    }

    /**
     * Set the name of the y-axis
     *
     * @param string $Name = 'Y Axis'
     */
    public function setYAxisName($Name = 'Y Axis') {
        $this->DataDescription['Axis']['Y'] = $Name;
    }

    /**
     * Set the format of the x-axis' values
     *
     * Currently supported are:
     *   - number (default)
     *   - time
     *   - date
     *   - metric
     *   - currency
     *
     * @param string $Format = 'number'
     */
    public function setXAxisFormat($Format = 'number') {
        $this->DataDescription['Format']['X'] = $Format;
    }

    /**
     * Set the format of the y-axis' values
     *
     * Currently supported are:
     *   - number (default)
     *   - time
     *   - date
     *   - metric
     *   - currency
     *
     * @param string $Format = 'number'
     */
    public function setYAxisFormat($Format = 'number') {
        $this->DataDescription['Format']['Y'] = $Format;
    }

    /**
     * Set the unit of the x-axis' values
     *
     * The unit will be appended directly to the values
     * on this axis (No space in between).
     *
     * @param string $Unit = NULL
     */
    public function setXAxisUnit($Unit = NULL) {
        $this->DataDescription['Unit']['X'] = $Unit;
    }

    /**
     * Set the unit of the y-axis' values
     *
     * The unit will be appended directly to the values
     * on this axis (No space in between).
     *
     * @param string $Unit = NULL
     */
    public function setYAxisUnit($Unit = NULL) {
        $this->DataDescription['Unit']['Y'] = $Unit;
    }

    /**
     * Set the symbol for a series
     *
     * @param string $Name
     * @param string $Symbol
     */
    public function setSerieSymbol($Name, $Symbol) {
        $this->DataDescription['Symbol'][$Name] = $Symbol;
    }

    /**
     * Remove series from plotted series
     *
     * @param string $SerieName
     */
    public function removeSerieName($SerieName) {
        if(isset($this->DataDescription['Description'][$SerieName])) {
            unset($this->DataDescription['Description'][$SerieName]);
        }
    }

    /**
     * Remove all series from plotted series
     *
     */
    public function removeAllSeries() {
        $this->DataDescription['Values'] = NULL;
    }

    /**
     * Get data array
     *
     * @return array $this->Data
     */
    public function getData() {
        return($this->Data);
    }

    /**
     * Get data describtion
     *
     * @return array $this->DataDescription
     */
    public function getDataDescription() {
        return($this->DataDescription);
    }

    /**
     * Set data array
     *
     * @param array $Data
     */
    public function setData($Data) {
        $this->Data = $Data;
    }

    /**
     * Set data description
     *
     * @param array $DataDescription
     */
    public function setDataDescription($DataDescription) {
        $this->DataDescription = $DataDescription;
    }



    /**
     * Caching functions start here
     *
     * Adapted from pCache
     */

    /**
     * Enable caching
     *
     * In order to prevent differents graphs with the same data
     * set to overwrite each other, a freely chosen $ScriptID can be
     * given.
     *
     * The method returns its success state depending on the write
     * privileges on the caching directory.
     *
     * @param string $ScriptID = NULL
     * @param string $CacheFolder = 'Cache/'
     * @return boolean $Success
     */
    public function enableCaching($ScriptID = NULL, $CacheFolder = 'Cache/') {
        $folderpath = realpath($CacheFolder);
        if(is_writable($folderpath)) {
            $this->CacheFolder = $folderpath . DIRECTORY_SEPARATOR;
            $this->ScriptID = $ScriptID;
            $this->CacheEnabled = TRUE;
        } else {
            $this->Errors[] = '[WARNING] Cache folder ' . $CacheFolder . ' not found or not writable. Caching remains disabled.';
        }

        return $this->CacheEnabled;
    }

    /**
     * Clear cache folder
     *
     * Removes any caching file from the cache directory
     *
     * @return bool $success
     */
    public function clearCache() {
        $success = FALSE;
        if($handle = opendir($this->CacheFolder)) {
            while ($file = readdir($handle)) {
                if(preg_match('/^mtChart\.[a-f0-9]{32}\.png$/', $file)) {
                    unlink($this->CacheFolder.$file);
                }
            }
            closedir($handle);
            $success = TRUE;
        }

        return $success;
    }

    /**
     * Check if this graph is already cached
     *
     * If the graph exists, will return the full filename.
     *
     * @return string $filename
     */
    public function isInCache() {
        $cached = FALSE;

        if(file_exists($filename = $this->getCacheFilename())) {
            $cached = $filename;
        }

        return $cached;
    }

    /**
     * Returns the full filename and path for the current hash
     *
     * @return sring $filename
     */
    public function getCacheFilename() {
        if(is_null($this->Hash)) {
            $this->getHash();
        }

        return $this->CacheFolder . 'mtChart.' . $this->Hash . '.png';
    }

    /**
     * Write current graph to cache
     *
     * @return boolean $success
     */
    function writeToCache() {
        $success = imagepng($this->Picture, $this->getCacheFilename());

        if(! $success) {
            $this->Errors[] = '[WARNING] Could not write current file to cache.';
        }

        return $success;
    }

    /**
     * Delete specific data from Cache
     *
     * @return boolean $success
     */
    public function deleteFromCache() {
        $sucess = TRUE;

        if($FileName = $this->isInCache()) {
            $success = unlink($FileName);
        }

        return $sucess;
    }

    /**
     * Gets image file from cache and exits script
     *
     * If the flag Exit is set to TRUE (default), this
     * method will exit the execution of the current script.
     *
     * @param Exit = TRUE
     * @return bool $success
     */
    public function getFromCache($Exit = TRUE) {
        $success = FALSE;

        if($FileName = $this->isInCache()) {

            header('Content-type: image/png');
            $success = @readfile($FileName);

            if($success && $Exit) {
                exit();
            }
        }

        return $success;
    }

    /**
     * Construct the hash for the current data set
     *
     * @return string $Hash
     */
    private function getHash() {
        if(is_null($this->Hash)) {
            $mKey = (string) $this->ScriptID;
            $mKey .= serialize($this->Data);
            $mKey .= serialize($this->DataDescription);

            $this->Hash = md5($mKey);
        }

        return $this->Hash;
    }
}
