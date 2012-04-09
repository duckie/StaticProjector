#!/bin/bash

NAME="StaticProjector"
VERSION="0.1"

# This script packages Static Projector
# in a copyable directory and a zip file

CALL_DIR=`pwd`
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="$( dirname "$( dirname "$SCRIPT_DIR" )" )"
BUILD_DIR="$ROOT_DIR/build"
TP_DIR="$BUILD_DIR/sp-includes/third-party"

# Cleaning

if [ -d "$BUILD_DIR" ];
then
	rm -rf "$BUILD_DIR"
fi;

mkdir "$BUILD_DIR"

# Copying files

echo "Copying the files..."
cp -R "$ROOT_DIR/sp-includes" "$BUILD_DIR/"
cp -R "$ROOT_DIR/index.php" "$BUILD_DIR/"
cp -R "$ROOT_DIR/dev/doc" "$BUILD_DIR/"
cp -R "$ROOT_DIR/README.md" "$BUILD_DIR/"
# cp -R "$ROOT_DIR/LICENSE" "$BUILD_DIR/"

# Cleaning trash files
rm -f "$BUILD_DIR/doc/todo.md"
find "$BUILD_DIR" -name "*~" -exec rm -f {} \;
find "$BUILD_DIR" -type d -name ".git" -exec rm -rf {} \;


#Step 3 - Zippin' the thing

echo "Creating the archives..."

cd "$BUILD_DIR"
archive="$NAME-$VERSION"
zip -r "$archive.zip" "doc/" "sp-includes/" "index.php" "README.md"
cd "$CALL_DIR"

# End
echo "Done."

