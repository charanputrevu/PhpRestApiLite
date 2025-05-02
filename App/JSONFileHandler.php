<?php
namespace Theincubator\PhpRestApiLite;

/**
 * 
 */
class JSONFileHandler
{
    /**
     * 
     * @var string
     */
    private $filePath;

    /**
     * 
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;

        // Ensure the file exists, or create it
        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([])); // Initialize as an empty JSON array
        }
    }

    /**
     * Read data from the JSON file.
     * 
     * @return mixed The data from the JSON file, or null on error.
     */
    public function read()
    {
        $data = file_get_contents($this->filePath);

        return json_decode($data, true); // Decode as an associative array
    }

    /**
     * Write data to the JSON file with file locking.
     * 
     * @param mixed $data The data to write to the file.
     * @return bool True on success, false on failure.
     */
    public function write($data)
    {
        $file = fopen($this->filePath, 'c+');

        if (!$file) {
            return false; // Unable to open the file
        }

        // Lock the file for writing
        if (flock($file, LOCK_EX)) {
            // Truncate the file and write new data
            ftruncate($file, 0);
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            fwrite($file, $jsonData);

            // Release the lock
            flock($file, LOCK_UN);
        } else {
            fclose($file);
            return false; // Unable to lock the file
        }

        fclose($file);
        return true;
    }

    /**
     * Append data to the JSON file with file locking.
     * 
     * @param mixed $newData The data to append to the file.
     * @return bool True on success, false on failure.
     */
    public function append($newData)
    {
        $file = fopen($this->filePath, 'c+');
        if (!$file) {
            error_log("Failed to open file: " . $this->filePath);
            return false;
        }

        if (flock($file, LOCK_EX)) {
            try {
                clearstatcache();
                $fileSize = filesize($this->filePath);
                if ($fileSize === false) {
                    throw new Exception("Cannot get file size");
                }

                $content = '';
                if ($fileSize > 0) {
                    rewind($file);
                    $content = stream_get_contents($file);
                    if ($content === false) {
                        throw new Exception("Failed to read file");
                    }
                    error_log("Read content length: " . strlen($content));
                }

                $existingData = [];
                if (!empty($content)) {
                    $existingData = json_decode($content, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        error_log("JSON decode error: " . json_last_error_msg() . "\nContent: " . $content);
                        $existingData = [];
                    }
                }

                if (!is_array($existingData)) {
                    $existingData = [];
                }

                $existingData[] = $newData;
                $jsonData = json_encode($existingData, JSON_PRETTY_PRINT);
                
                rewind($file);
                ftruncate($file, 0);
                fwrite($file, $jsonData);
                fflush($file);

            } catch (Exception $e) {
                error_log("Error in append: " . $e->getMessage());
                flock($file, LOCK_UN);
                fclose($file);
                return false;
            }

            flock($file, LOCK_UN);
        }

        fclose($file);
        return true;
    }
}
