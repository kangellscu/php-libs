<?php
namespace Kangell\Autoload;


/**
 * ClassLoader implements a PSR-0 class loader 
 *
 * $loader = new \KTools\Autoload\ClassLoader(); 
 *
 * // register class with namespace (PSR-0)
 * $loader->add('A\B\C', __DIR__ . "/AAA");
 * $loader->add("A\B', __DIR__ . "/BBB");
 *
 * // register class file
 * $loader->addClassMap('A\B\C\MyClass', '/path/to/file');
 * 
 * // active the autoloader
 * $loader->register();
 * 
 * // to enable searching the include path (eg. for PEAR packages)
 * $loader->setUseIncludePath(true);
 *
 * In this example, if you try to use a class in the A\B\C 
 * namespace or one of its children (A\B\C\D for instance), 
 * the autoloader will first look for the class under the AAA/
 * directory, and it will then fallback to the BBB/ directory if not
 * found before giving up.                                             
 * 
 * Note:
 *      PSR-0: underscore "_" should be replaced by DIRECTORY_SEPARATOR
 *      PSR-4: underscore "_" has not special definistion
 */

class ClassLoader
{
    // PSR-4
    private $prefixesPSR4 = array();
    private $fallbackDirsPSR4 = array();


    // PSR-0
    private $prefixesPSR0 = array();
    private $fallbackDirsPSR0 = array();

    private $useIncludePath = false;
    private $classMap = array();


    public function getPrefixes() {
        return $this->prefixesPSR0;
    }


    public function getFallbackDirs() {
        return $this->fallbackDirs;
    }


    public function getPrefixesPSR4() {

    }


    public function getFallbackDirsPSR4() {

    }


    /**
     * Can be used to check if the autoloader uses the include path to check
     * for classes.
     *
     * @return boolean
     */
    public function getUseIncludePath() {
        return $this->useIncludePath;
    }


    /**
     * Turns on|off searching the include path for class files.
     *
     * @param boolean $useIncludePath
     */
    public function setUseIncludePath($useIncludePath) {
        $this->useIncludePath = $useIncludePath;
    }


    public function getClassMap() {

    }


    /**
     * @param array $classMap class to filename map
     *      eg:
     *          array(
     *              "A\B\C\MyClass" => "path/to/MyClass.php"
     *          )
     */
    public function addClassMap(array $classMap) {
        $this->classMap = array_merge($this->classMap, $classMap);
    }    


    /**
     * Add a set of PSR-0 directories for a given prefix.
     *
     * @param string $prefix the prefix
     * @param string|array $paths the PSR-0 root directories
     * @param boolean $prepend Whether to prepend the directories
     *
     * @return void
     */
    public function add($prefix, $paths, $prepend=false) {
        if ( ! $prefix) {
            if ($prepend) {
                $this->fallbackDirPSR0 = array_merge(
                    (array) $paths, $this->fallbackDirsPSR0
                );
            } else {
                $this->fallbackDirPSR0 = array_merge(
                    $this->fallbackDirsPSR0, (array) $paths
                );
            }    

            return;
        }

        $first = $prefix[0];
        if ( ! isset($this->prefixesPSR0[$first][$prefix])) {
            $this->prefixesPSR0[$first][$prefix] = $paths;
        }
        if ($prepend) {
            $this->prefixesPSR0[$first][$prefix] = array(
                (array) $paths, $this->prefixesPSR0[$first][$prefix]
            );
        } else {
            $this->prefixesPSR0[$first][$prefix] = array(
                $this->prefixesPSR0[$first][$prefix], (array) $paths
            );
        }
    }


    /**
     * Set a set of PSR-0 directories for a given prefix,
     * replacing any others previously set for this prefix.
     *
     * @param string $prefix the prefix
     * @param string|array $paths the PSR-0 root directories
     *
     * @return void
     */
    public function set($prefix, $paths) {
        if ( ! $prefix) {
            $this->fallbackDirsPSR0 = (array) $paths;
        } else {
            $this->prefixesPSR0[$prefix[0]][$prefix] = (array) $paths;
        }
    }


    public function addPsr4($prefix, $paths, $prepend=false) {

    }


    public function setPsr4($prefix, $paths) {

    }


    /**
     * Registers this instance as an autoloader. 
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend=false) {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }


    /**
     * Unregisters this instance as an autoloader.
     */
    public function unregister() {
        spl_autoload_unregister(array($this, 'loadClass'));
    }


    public function loadClass($class) {
        if ($file = $this->findFile($class)) {
            includeFile($file);

            return true;
        }

        return false;
    }


    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|false The path if found, false otherwise
     */
    public function findFile($class) {
        // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
        if ("\\" == $class[0]) {
            $class = substr($class, 1);
        }

        // class map lookup
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        $logicalClassPath = strtr($class, "\\", DIRECTORY_SEPARATOR);
        $first = $class[0];

        // PSR-4 lookup
        
        // PSR-4 fallback dirs


        // PSR-0 lookup
        if (false !== ($pos = strrpos($class, DIRECTORY_SEPARATOR))) {
            // namespaced class name 
            $logicalPathPSR0 = substr($logicalClassPath, 0, $pos + 1)
                . strtr(substr($logicalClassPath, $pos + 1), "_", DIRECTORY_SEPARATOR)
                . ".php";
        } else {
            // PEAR-like class name
            $logicalPathPSR0 = strtr($class, "_", DIRECTORY_SEPARATOR) . ".php";
        }

        if (isset($this->prefixesPSR0[$first])) {
            foreach ($this->prefixesPSR0[$first] as $prefix => $dirs) {
                if (0 !== strpos($class, $prefix)) {
                    continue;
                }
                foreach ($dirs as $dir) {
                    if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPSR0)) {
                        return $file;
                    }
                }
            }
        }
        
        // PSR-0 fallback dirs
        foreach ($this->fallbackDirsPSR0 as $dir) {
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPSR0)) {
                return $file;
            }
        }
        
        // PSR-0 include paths
        if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPSR0)) {
            return $file;
        }

        // Remember that this class does not exist
        return $this->classMap[$class] = false;
    }
}


/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 */
function includeFile($file)
{
    include $file;
}
/* End of file */
