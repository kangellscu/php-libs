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
 * $loader->add('A\B\C\MyClass', '/path/to/file');
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

    }


    /**
     * If a file exists, require it from the file system
     *
     * @param string $file the file to require
     * 
     * @return boolean True if the file exists, false if not
     */
    protected function requireFile($file) {
        if (file_exists($file)) {
            require $file;
            return true;
        }

        return false;
    }
}

/* End of file */
