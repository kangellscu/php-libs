<?php
namespace KTools\libs;


/**
 * Usage:
 *      $locker = new FileLock($lock_file);
 *      if ($locker->lock()) {
 *          $data = $locker->get_data();
 *          .... business logic ....
 *          $locker->set_data($data);
 *          $locker->release();
 *      }
 *      $locker->close();
 */
class FileLocker
{
    private $fp;
    private $lock_file;

    public function __construct($lock_file) {
        $this->lock_file = $lock_file;
        $is_success = $this->fp = fopen($lock_file, 'a+');
        if ( ! $is_success) {
            throw new Exception("Open lock file: {$lock_file} failed");
        }
        fseek($this->fp, 0); 
    }


    /**
     * get file lock
     *
     * @return boolean $is_locked
     */
    public function lock() {
        return flock($this->fp, LOCK_EX | LOCK_NB);
    }


    /**
     * release lock
     */
    public function release($close_lock_file=true) {
        flock($this->fp, LOCK_UN);
        if ($close_lock_file) {
            fclose($this->fp);
        }
    }


    public function close() {
        fclose($this->fp);
    }


    /**
     * get data from lock file
     *
     * @return mixed $data return false if a error happend
     */
    public function get_data() {
	$read_size = filesize($this->lock_file) ?: 1;
        $data = fread($this->fp, $read_size);
        if ($data === false) {
            return false;
        }
        return json_decode($data, true);
    }


    /**
     * write data into lock file
     *
     * @param mixed $data
     *
     * @return false|int $write_number
     */
    public function set_data($data) {
        ftruncate($this->fp, 0);
        return fwrite($this->fp, json_encode($data));
    }
}


/* End of file */
