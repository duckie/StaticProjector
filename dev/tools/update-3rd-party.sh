#!/bin/bash

# This script browses a config file
# and automatically update the needed third party libs
# you need and available internet conection

CALL_DIR=`pwd`
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="$( dirname "$( dirname "$SCRIPT_DIR" )" )"
TP_DIR="$ROOT_DIR/sp-includes/third-party"

list=`cat "$SCRIPT_DIR/repo-3rd-party.txt" | cut -d ';' -f1 | tr '\n' ' '`

for lib in $list;
do
	echo
	echo "Updating $lib..."
	dir="$TP_DIR/$lib"
	git_dir="$TP_DIR/$lib/.git"
	if [ -d "$dir" ];
	then
		cd "$TP_DIR"
		if [ -d "$git_dir" ];
		then
			cd "$TP_DIR/$lib"
			git pull
		else
			repo=`cat "$SCRIPT_DIR/repo-3rd-party.txt" | grep $lib | cut -d ';' -f2`
			git clone "$repo" "$lib"
			files=`cat "$SCRIPT_DIR/repo-3rd-party.txt" | grep $lib | cut -d ';' -f3`
			for file in $files;
			do
				echo "$file" >> "$git_dir/info/sparse-checkout"
			done;
			
			cd "$TP_DIR/$lib"
			git config core.sparsecheckout true
		fi;
		
		cd "$TP_DIR/$lib"
		git read-tree -m -u HEAD
		echo "$lib updated."
	else
		echo "no such third party directory."
	fi;
	
	cd "$CALL_DIR"
done

