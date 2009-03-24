# Phing Tasks
## Introduction
I have written two Phing tasks for easy repository management with Unfuddle.  When I get the latest build of a project it becomes a fork for a new websiteso I create a new repository and then import the new version.  UnfuddleAddRepo is based upon work by [Dave Winterbottom][daveblog].  I will merge his Unfuddle Message Task into this soon.
## Installation
Please see the [phing manual][phingmanual] for more information on installing phing and writing custom tasks for it.

* Find your phing tasks/ext directory mine is ``/usr/share/php/phing/tasks/ext/``
* Copy the supplied folders there
* In your phing build script you need to define the new tasks at the top of your project
    <taskdef name="svnimport" classname="phing.tasks.ext.svn.SvnImportTask" />
    <taskdef name="unfuddleaddrepo" classname="phing.tasks.ext.unfuddle.UnfuddleAddRepoTask" />
## Usage
To use SvnImport:
    <svnimport
            repositoryurl=""
            fromdir=""
            message="Commit message goes here"
            username="SVN Username"
            password="SVN Password"
            />

To use Unfuddle Add Repo:
    <unfuddleaddrepo
            subdomain="${unfuddleSubdomain}"
            projectid="${svnProjectId}"
            username="${svnUsername}"
            password="${svnPassword}"
            abbreviation="${svnAbbreviation}"
            title="${svnAbbreviation}"
            system="svn"
            />
[phingmanual]: http://phing.info/docs/guide/current/
[daveblog]: http://codeinthehole.com/archives/15-Phing-task-to-create-an-Unfuddle-message.html
