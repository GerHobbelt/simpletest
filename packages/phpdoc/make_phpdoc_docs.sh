#!/bin/bash
pushd .
cd $(echo $0 | sed -e 's=[^/]*$==');

#DEST_DIR=../../docs/pkg
if [ ! -d ../../tutorials ] 
then
	mkdir ../../tutorials
fi
if [ ! -d ../../tutorials/SimpleTest ]
then
	mkdir ../../tutorials/SimpleTest
fi
DEST_DIR=../../tutorials/SimpleTest

rm ${DEST_DIR}/*.pkg
#cp ../../docs/pkg/SimpleTest.pkg.ini ${DEST_DIR}

xalan -out ${DEST_DIR}/QuickStart.pkg -in ../../docs/source/en/simple_test.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/SimpleTest.pkg -in ../../docs/source/en/overview.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/UnitTestCase.pkg -in ../../docs/source/en/unit_test_documentation.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/GroupTests.pkg -in ../../docs/source/en/group_test_documentation.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/MockObjects.pkg -in ../../docs/source/en/mock_objects_documentation.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/PartialMock.pkg -in ../../docs/source/en/partial_mocks_documentation.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/Reporting.pkg -in ../../docs/source/en/reporter_documentation.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/Expectations.pkg -in ../../docs/source/en/expectation_documentation.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/WebTester.pkg -in ../../docs/source/en/web_tester_documentation.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/FormTesting.pkg -in ../../docs/source/en/form_testing_documentation.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/Authentication.pkg -in ../../docs/source/en/authentication_documentation.xml -xsl phpdoc_docs.xslt
xalan -out ${DEST_DIR}/Browser.pkg -in ../../docs/source/en/browser_documentation.xml -xsl phpdoc_docs.xslt

# some cleanup work
pushd .
cd $DEST_DIR

# remove XML declaration
for f in $(ls *.pkg --color=none)
do
	grep -v -e '^<?xml' $f > tmp.pkg
	mv tmp.pkg $f
done

# fix overview title
cat SimpleTest.pkg | sed -e 's/<refname>Overview/<refname>Simple Test PHP Unit Test Framework/g;s/<\([A-Za-z0-9]*\)\/>/<\1><\/\1>/g' > tmp.pkg
mv tmp.pkg SimpleTest.pkg

popd

rm -rf ../../docs/simpletest.org/api/

PhpDocumentor/phpdoc -c simpletest.ini 

popd

