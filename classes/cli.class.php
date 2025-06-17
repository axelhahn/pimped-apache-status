<?php

namespace axelhahn;

define("CLIVALUE_REQUIRED", 1);
define("CLIVALUE_OPTIONAL", 2);
define("CLIVALUE_NONE", 3);

/**
 * C L I handler class
 * 
 * Class to handle command line argments. 
 * In a config array you define long and short parameters and pattern for 
 * needed values.
 * 
 * specialties:
 * - short parameter will be merged to long version value.
 * - parameters can be checked by given pattern
 * - a simple help is generated
 * - interactive input for a variable (with optional pattern check)
 * - colored text
 * 
 * @package cli
 * @version 1.09
 * @author Axel Hahn (https://www.axel-hahn.de/)
 * @license GNU GPL v 3.0
 * @link https://github.com/axelhahn/ahcli
 */
class cli
{

    // ----------------------------------------------------------------------
    // CONFIG
    // ----------------------------------------------------------------------
    /**
     * Current config array
     * @var array
     */
    protected $_aConfig = [];

    /**
     * Current variables and values from cli and interactive input
     * @var array
     */
    protected $_aValues = [];


    /**
     * Hash of foreground colors
     * @var array
     */
    protected $aFgColors = [
        'reset' => '0',
        'black' => '0;30',
        'dark gray' => '1;30',
        'blue' => '0;34',
        'light blue' => '1;34',
        'green' => '0;32',
        'light green' => '1;32',
        'cyan' => '0;36',
        'light cyan' => '1;36',
        'red' => '0;31',
        'light red' => '1;31',
        'purple' => '0;35',
        'light purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light gray' => '0;37',
        'white' => '1;37'
    ];

    /**
     * Hash of background colors
     * @var array
     */
    protected $aBgColors = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light gray' => '47',
    ];

    /**
     * Hash of themes with colors by type
     * @var array
     */
    protected $_aThemes = [
        'default' => [
            'reset' => ['reset', null],
            'head' => ['light blue', null],
            'input' => ['white', 'green'],
            'cli' => ['cyan', null],

            'ok' => ['light green', null],
            'info' => ['light cyan', null],
            'warning' => ['yellow', null],
            'error' => ['light red', null],
            // 'warning' => array('black', 'yellow'),
            // 'error' => array('black', 'red'),
        ]
    ];

    /**
     * Name of current color theme
     * @var string
     */
    public $sTheme = 'default';


    // ----------------------------------------------------------------------

    /**
     * Create cli helper object
     * 
     * @param array  $aArgs  config array
     */
    public function __construct(array $aArgs = [])
    {
        if (count($aArgs)) {
            $this->setargs($aArgs);
        }
    }

    // ----------------------------------------------------------------------
    // PRIVATE FUNCTIONS (helper)
    // ----------------------------------------------------------------------

    /**
     * Helper: check a variable ... if a pattern was defined return the result 
     * of match of value against pre defined pattern
     * 
     * @see read()
     * @see getopt()
     * @param string  $sVar     variable name (a key below 'params')
     * @param mixed   $sValue   value that will be verified
     * @return boolean
     */
    protected function _checkPattern(string $sVar, mixed $sValue): bool
    {

        $aData = $this->_aConfig['params'][$sVar];
        if (array_key_exists('pattern', $aData)) {

            // do not test optional params that exist but have no value
            if ($aData['value'] === CLIVALUE_OPTIONAL && !$sValue) {
                return true;
            }

            if ($sValue === false || preg_match($aData['pattern'], $sValue) < 1) {
                $this->color(
                    'error',
                    'ERROR: parameter "' . $sVar . '" (' . $aData['shortinfo'] . ') - it has a wrong value.' . "\n"
                    . '"' . $sValue . '" does not match ' . $aData['pattern'] . ' ' . "\n"
                );
                return false;
            }
        }

        return true;
    }

    /**
     * Helper: cli input to enter a value
     * 
     * @see read()
     * @param string $sPrefix  prefix/ login prompt
     * @param mixed  $default  default value if no value was given
     * @return bool|string
     */
    protected function _cliInput(string $sPrefix, mixed $default = false): bool|string
    {

        $this->color('input', $sPrefix ? $sPrefix : '>');
        echo ' ';

        if (PHP_OS == 'WINNT') {
            $sReturn = stream_get_line(STDIN, 1024, PHP_EOL);
        } else {
            $sReturn = readline('');
        }
        return $sReturn ? $sReturn : $default;
    }

    /**
     * Helper: generate the short and long option parameters for PHP getopts() 
     * function by given parameters.
     * 
     * @see getopts()
     * @return array
     */
    protected function _getGetoptParams(): array
    {
        $sShort = '';
        $aOptions = [];
        foreach ($this->_aConfig['params'] as $sParam => $aData) {
            foreach (['short', 'value', 'shortinfo'] as $sKey) {
                if (!array_key_exists($sKey, $aData)) {
                    die(__CLASS__ . ':: ERROR in cli config: missing key [params]->[' . $sParam . ']->[' . $sKey . '] in [array].');
                }
            }
            $sDots = ''
                . ($aData['value'] === CLIVALUE_REQUIRED
                    ? ':'
                    : ($aData['value'] === CLIVALUE_OPTIONAL ? '::' : '')
                )
            ;
            $sShort .= $aData['short'] . $sDots;
            $aOptions[] = $sParam . $sDots;
        }
        return [
            'short' => $sShort,
            'long' => $aOptions,
        ];
    }



    // ----------------------------------------------------------------------
    // SETTER
    // ----------------------------------------------------------------------

    /**
     * Force cli mode. The execution stops if php_sapi_name() does not return 
     * 'cli'
     * 
     * @return boolean
     */
    public function forceCli(): bool
    {
        if (php_sapi_name() !== "cli" && php_sapi_name() !== "cgi-fcgi") {
            die("ERROR: This script is for command line usage only.");
        }
        return true;
    }

    /**
     * Interactive action; read a value and stor as value; the variable must 
     * exist in config; if a pattern was given the input will be verified 
     * against it.
     * 
     * @param string  $sVar  variable name (a key below 'params')
     * @return mixed
     */
    public function read(string $sVar): mixed
    {
        $this->forceCli();
        if (!array_key_exists($sVar, $this->_aConfig['params'])) {
            die(__CLASS__ . ':: ERROR in cli config: missing key [params]->[' . $sVar . '] in [array].');
        }
        // remark ... check of this key was done in _getGetoptParams already
        echo $this->_aConfig['params'][$sVar]['shortinfo'] . "\n";

        $bOK = false;
        while (!$bOK) {
            if (array_key_exists('description', $this->_aConfig['params'][$sVar])) {
                echo $this->_aConfig['params'][$sVar]['description'] . "\n";
            }
            $sValue = $this->_cliInput("$sVar >");

            if ($this->_checkPattern($sVar, $sValue)) {
                // echo "Thank you.\n";
                $bOK = true;
            }
        }
        // put value to the value store too
        $this->setValue($sVar, $sValue);
        return $sValue;
    }

    /**
     * Apply a config; used by __constructor ... and can be called separately
     * 
     * @param array $aArgs
     * @return boolean
     */
    public function setargs(array $aArgs): bool
    {
        foreach (['label', 'params'] as $sKey) {
            if (!array_key_exists($sKey, $aArgs)) {
                die(__CLASS__ . ':: ERROR in cli config: missing key [' . $sKey . '] in [array].');
            }
        }
        $this->_aConfig = $aArgs;
        $this->_aValues = [];
        $this->getopt();
        return true;
    }

    /**
     * Set an variable and its value; this function is used internally
     * and can be used 
     * 
     * @param string  $sVar    variable name (a key below 'params')
     * @param mixed   $sValue  a value to set
     * @return boolean
     */
    public function setvalue(string $sVar, mixed $sValue): bool
    {
        if (!array_key_exists($sVar, $this->_aConfig['params'])) {
            die(__CLASS__ . ':: ERROR in cli config: missing key [params]->[' . $sVar . '] in [array].');
        }
        return $this->_aValues[$sVar] = $sValue;
    }

    // ----------------------------------------------------------------------
    // GETTER
    // ----------------------------------------------------------------------

    /**
     * Get label and descriptioon to display as header
     * 
     * @param boolean  $bLong  show description too (if available); default: false
     * @return string
     */
    public function getlabel(bool $bLong = false): string
    {
        return "\n" . '===== ' . $this->_aConfig['label'] . ' =====' . "\n\n"
            . (($bLong && array_key_exists('description', $this->_aConfig) && $this->_aConfig['description']) ? $this->_aConfig['description'] . "\n" : '')
        ;
    }

    /**
     * Get all params and values from cli parameters
     * 
     * @return array
     */
    public function getopt(): array
    {
        $this->forceCli();
        $aParamdef = $this->_getGetoptParams();
        $aOptions = getopt($aParamdef['short'], $aParamdef['long']);

        // echo __METHOD__ . " DEBUG \$aOptions = " . print_r($aOptions, 1);
        // echo __METHOD__ . " DEBUG \$aParamdef = " . print_r($aParamdef, 1);
        foreach ($aOptions as $sVar => $sValue) {
            foreach ($this->_aConfig['params'] as $sParam => $aData) {
                if ($sParam == $sVar || $aData['short'] == $sVar) {
                    if (!$this->_checkPattern($sParam, $sValue)) {
                        die();
                    }
                    $this->setValue(
                        $sParam,
                        ($sValue === false || $aData['value'] == CLIVALUE_NONE)
                        ? true
                        : $sValue
                    );
                }
            }
        }
        return $this->_aValues;
    }

    /**
     * Get the value based on variable
     * 
     * @param string  $sKey  name of variable
     * @return mixed
     */
    public function getvalue(string $sKey): mixed
    {
        if (!array_key_exists($sKey, $this->_aConfig['params'])) {
            $this->color('error');
            die(__CLASS__ . ':: ERROR in cli config: a parameter variable [' . $sKey . '] was not defined.' . "\n");
        }
        if (array_key_exists($sKey, $this->_aValues)) {
            return $this->_aValues[$sKey];
        }
        return false;
    }

    /**
     * Get all array with all current values
     * @return array
     */
    public function getAllValues(): array
    {
        return $this->_aValues;
    }

    /**
     * Get generated text for help to explain all valid parameters
     * 
     * @return string
     */
    public function showhelp(): string
    {
        $sReturn = "HELP:\n"
            . ($this->_aConfig['description'] ? $this->_aConfig['description'] . "\n" : '')
            . "\n"
            . "PARAMETERS:\n"
        ;
        foreach ($this->_aConfig['params'] as $sParam => $aData) {
            $sReturn .= (isset($aData['short']) && $aData['short'] ? '  -' . $aData['short'] . "\n" : "")
                . '  --' . $sParam
                . ($aData['value'] === CLIVALUE_REQUIRED ? ' [value] (value required)' : '')
                . ($aData['value'] === CLIVALUE_OPTIONAL ? ' [=value] (value is optional)' : '')
                . ($aData['value'] === CLIVALUE_NONE ? ' (without value)' : '')
                . "\n"
                . '    ' . $aData['shortinfo'] . "\n"
                . (isset($aData['description']) && $aData['description'] ? '    ' . $aData['description'] . "\n" : "")
                . (isset($aData['pattern']) && $aData['pattern'] ? '    If a value is given then it will be checked against regex ' . $aData['pattern'] . "\n" : "")
                . "\n"
            ;
        }
        return $sReturn;
    }

    // ----------------------------------------------------------------------
    // COLOR
    // ----------------------------------------------------------------------

    /**
     * Set color of a theme with echo; if a string was given, only the
     * string will be colored and then the color will be reset 
     * 
     * @param string $sType  type of color in the theme
     * @param string $sOutput
     * @return boolean
     */
    public function color(string $sType, string $sOutput = ''): bool
    {
        if (!array_key_exists($sType, $this->_aThemes[$this->sTheme])) {
            $sType = 'reset';
        }
        echo $this->getColor(
            $this->_aThemes[$this->sTheme][$sType][0],
            $this->_aThemes[$this->sTheme][$sType][1]
        );
        if ($sOutput) {
            echo $sOutput . $this->getColor();
        }
        return true;
    }

    /**
     * Get colorcode for console output
     * 
     * @param string  $sFgColor  foreground color
     * @param string  $sBgColor  background color
     * @return string
     */
    public function getColor(string|null $sFgColor = '', string|null $sBgColor = ''): string
    {

        $sReturn = '';
        if (!$sFgColor || !array_key_exists($sFgColor, $this->aFgColors)) {
            $sFgColor = 'reset';
        }
        $sReturn .= "\e[{$this->aFgColors[$sFgColor]}m";

        if ($sBgColor && !array_key_exists($sBgColor, $this->aBgColors)) {
            $sBgColor = null;
        }
        if ($sBgColor) {
            $sReturn .= "\e[{$this->aBgColors[$sBgColor]}m";
        }

        return $sReturn;
    }

    /**
     * Add a theme
     * 
     * @param array  $aColors  array with colorset - see $this->_aThemes
     * @param string $sTheme   name of the theme
     * @return boolean
     */
    public function addTheme(array $aColors, string $sTheme): bool
    {
        if (!is_array($aColors) || !count($aColors)) {
            echo "ERROR: colors must be an array\n";
            return false;
        }
        if (!is_string($sTheme) || !$sTheme || $sTheme !== preg_replace('/[^a-z0-9\ ]/i', '', $sTheme)) {
            echo "ERROR: name of theme must be a string and can copntain chars, numbers and spaces\n";
            return false;
        }
        foreach (array_keys($this->_aThemes['default']) as $sType) {
            if (!array_key_exists($sType, $aColors)) {
                echo "ERROR: colors must contain the keys " . implode("|", array_keys($this->_aThemes['default'])) . "\n";
                return false;
            }
        }
        $this->_aThemes[$sTheme] = $aColors;
        return $this->setTheme($sTheme);
    }

    /**
     * Set a new theme
     * 
     * @param string $sTheme  name of the theme
     * @return boolean
     */
    public function setTheme(string $sTheme): bool
    {

        if (!array_key_exists($sTheme, $this->_aThemes)) {
            return false;
        }
        $this->sTheme = $sTheme;
        return true;
    }

    /**
     * Write text to stderr
     * 
     * @param string $sText  text to write to stderr
     * @return void
     */
    public function stderr(string $sText): void
    {
        file_put_contents('php://stderr', $sText, FILE_APPEND);
    }

}
