<?php
namespace Ma27\Kernel\Controller;

/**
 * Controller container to load the controllers from
 * a given directory and provide them as iterator
 *
 * @package Ma27\Kernel\Controller
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
class Container implements \Countable, \Iterator
{
    /**
     * List of found files in the controller directory to scan
     * @var \string[]
     */
    private $fileList = array();

    /**
     * Current iteration position
     * @var int
     */
    private $position = 0;

    /**
     * Loads the controllers by its name and path and stores them in the file
     * list to provide data as iterator
     *
     * @param string $modulePath File path of the module which owns the controllers
     * @param string $subPath    Sub path in the module where the controllers are stored
     *
     * @throws \InvalidArgumentException If the given controller does not exist
     *
     * @api
     */
    public function __construct($modulePath, $subPath)
    {
        $controllerPath = (string)$modulePath . '/' . (string)$subPath;
        if (!file_exists($controllerPath)) {
            throw new \InvalidArgumentException(sprintf('Controller path (%s) does not exist!', $controllerPath));
        }

        $iteration = new \GlobIterator($controllerPath . '/*Controller.php', \FilesystemIterator::KEY_AS_FILENAME);
        if (count($iteration) !== 0) {
            $result = array();
            foreach ($iteration as $fileInfo) {
                $result[] = $fileInfo->getBasename('.php');
            }

            $this->fileList = $result;
        }
    }

    /**
     * Returns the number of founded controllers in the directory
     *
     * @return int
     *
     * @api
     */
    public function count()
    {
        return count($this->fileList);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return isset($this->fileList[$this->position]);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->fileList[$this->position];
    }
} 