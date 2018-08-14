#! /bin/bash

pushd ../examples/Source/src > /dev/null
cloc $*
popd > /dev/null