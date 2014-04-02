# Create simpletest.zip snapshot, which gets shipped in b2evo's CVS
# (it's an archive to work around CVS latency)
archive:
	git archive --prefix simpletest/ master | bzip2 > simpletest.tar.bz2
	mv simpletest.tar.bz2 ~/src/b2evo/b2evolution/tests
