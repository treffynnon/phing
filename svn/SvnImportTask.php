<?php
require_once 'phing/Task.php';
require_once 'phing/tasks/ext/svn/SvnBaseTask.php';

/**
 * Imports a directory into a repository
 * with authentication.
 *
 * This is based upon the work done in the
 * core SvnExport task of phing. 
 *
 * @author Simon Holywell 
 * @version $Id: SvnImportTask.php 
 * @package phing.tasks.ext.svn
 */
class SvnImportTask extends SvnBaseTask
{
    private $message = 'Completed initial import.';
    private $fromDir = '';
    /**
     * The main entry point
     *
     * @throws BuildException
     */
    function main()
    {
        $this->log("Importing SVN repository from '" . $this->fromDir . "' to '".$this->getRepositoryUrl()."'");
	$switches = array(
            'm' => $this->message
        );
	$args = array(
	    $this->fromDir,
	    $this->getRepositoryUrl()
        );
        //stop the base class from injection or annoying args!
	$this->setRepositoryUrl('');
	$this->setWorkingCopy('');
	$this->setup('import');
	$this->run($args, $switches);
	$this->log("Import completed!");
    }

    public function setMessage($message) {
        $this->message = $message;
    }
    public function setFromDir($fromDir) {
        $this->fromDir = $fromDir;
    }
}
