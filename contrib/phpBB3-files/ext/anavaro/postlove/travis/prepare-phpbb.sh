#!/bin/bash
#
# This file is part of the phpBB Forum Software package.
#
# @copyright (c) phpBB Limited <https://www.phpbb.com>
# @license GNU General Public License, version 2 (GPL-2.0)
#
# For full copyright and license information, please see
# the docs/CREDITS.txt file.
#
set -e
set -x

EXTNAME=$1
BRANCH=$2
EXTPATH_TEMP=$3

# Copy extension to a temp folder
mkdir ../../tmp
cp -R . ../../tmp
cd ../../

# Clone phpBB
git clone --depth=1 "git://github.com/phpbb/phpbb.git" "phpBB3" --branch=$BRANCH
