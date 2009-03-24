<?php
require_once "UnfuddleBaseTask.php";
class UnfuddleAddRepoTask extends UnfuddleBaseTask {
    private $abbreviation = '';
    private $system = 'svn';

    protected $failMessageIntro = 'Unable to create the repository';
    protected $successMessageIntro = 'Repository successfully created';

    protected $apiURLAppend = 'repositories';

    public function setAbbreviation($abbreviation) {
        $this->abbreviation = $abbreviation;
    }

    public function setSystem($system) {
        $this->system = $system;
    }

    protected function validateProperties() {
        parent::validateProperties();
        if (!$this->abbreviation) {
            throw new BuildException("You must specify an abbreviation for the repository.");
        }
    }

    /**
     *  <repository>
            <abbreviation>mynewrepo</abbreviation>
            <title>My New Repo</title>
            <system>svn</system>
            <projects>
                <project id='30226'>
            </projects>
        </repository>
     * @return string XML
     */
    protected function getRequestBodyXml() {
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        
        $xmlWriter->startElement('repository');
        $xmlWriter->writeElement('abbreviation', $this->abbreviation);
        $xmlWriter->writeElement('title', $this->title);
        $xmlWriter->writeElement('description', $this->body);
        $xmlWriter->writeElement('system', $this->system);

        $xmlWriter->startElement('projects');
        $xmlWriter->startElement('project');
        $xmlWriter->writeAttribute('id', "{$this->projectId}");
        $xmlWriter->endElement();
        $xmlWriter->endElement();

        if ($this->categoryIds) {
            $xmlWriter->startElement('categories');
            foreach ($this->categoryIds as $categoryId) {
                $xmlWriter->startElement('category');
                $xmlWriter->writeAttribute('id', "$categoryId");
                $xmlWriter->endElement();
            }
            $xmlWriter->endElement();
        }
        $xmlWriter->endElement();
        return $xmlWriter->flush();
    }
}