#! /bin/bash
#
# clone the simpletest from SVN/SF -- I've been happy enough with git to never want to go back to SVN/CVS/RCS. Besides, github graphs are a boon by themselves. Enough reason for me to spend this extra effort!
#

git svn clone https://simpletest.svn.sourceforge.net/svnroot/simpletest/simpletest  -T trunk -b branches -t tags


