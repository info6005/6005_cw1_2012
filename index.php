<?php
/*
	6005_cw1_2012 - the Simple PHP RSS Reader for INFO6005
	Author: University of Southamoton, Electronics and Computer Science
	Published: 30th Jan 2012 
	
	This is part of a coursework for Students on the INFO6005 course at the University of Southampton.
	In this course students are expected to package some code for distribution on the debian/ubuntu platform

	Usage:
		php index.php --help (for help)
	
	This library can either read from a config file, or from stdin. 

	Basic Coursework Specification:
		* Student MUST use an appropiate source code control repository for managing both the exemplar code and other related resources. 
		* Students MUST package this code to be installed on the latest available LTS version of ubuntu available from http://www.ubuntu.com/download/ubuntu/download
		* The package MUST install on the latest ubuntu LTS release with a dpkg -i command.
		* The LTS install used will be the desktop version with no optional software installed!
		* The code MUST be installed in an appriate place in the operating system, e.g. /usr/share/package_name
	
	Extensions to get more marks (Source Code Control):
		* Tags
		* Changelog both for code and package
	
	Extensions to get more marks (Package)
		* Man pages 
		* Central OS controlled config which is conpatible with core software upgrades, e.g. user is prompted by package installer before any config is overwritten
		* Examples
		* Web Front End
		* Individual user config files in users home directories
		* Other package based features...

	Candidates can also customise/fix and extend the code itself, this may help them fill the changelog with items. 

	No marks are awarded for any complex customisations unless these are directly related to making a better package.

	Candidates are encorages to record their changes (maybe as a changelog or in the package documentation) and submit these along with the completed package. 
		
*/

	# Read config from STDIN
	if ($argc > 1) {
		$config = parse_args($argv);
	}

	# Read config from file
	if (@!$config) {
		$config_file = 'feeds.conf';
		if (file_exists($config_file)) {
			$config = conf_from_file($config_file);
		}
	}

	if (@$config["help"]) {
		print_help();
		exit(1);
	}

	if (@count($config["feeds"]) < 1) {
		echo "No Feeds Specified\n";
		exit(1);
	}

	if (@count($config["items"]) > 0) {
		$items = $config["items"][0];
	} else {
		$items = 1;
	}
	
	process_request($config);

	function process_request($config) {
		# GOT THE FEEDS, PROCESS AND OUTPUT
		require_once('rss_php.php');

		$rss = new rss_php;

		# SET DEFAULT ITEM COUNT, IN CASE ONE NOT SPECIFIED
		$count = $config["items"][0];
		if (@!$count) {
			$count = 1;
		}

		$feeds = $config["feeds"];
		
		for ($i=0;$i<count($feeds);$i++) {
			
			$url = $feeds[$i];
			
			$rss->load($url);
		
			$items = $rss->getItems();
		
			echo "Items From $url \n";
			echo "===========";
			for ($j=0;$j<strlen($url);$j++) {
				echo "=";
			}
			echo "\n\n";
			
			if (!$items) {
				echo "FAILED TO LOAD ANY ITEMS\n\n";
			} else {
					
				for ($k=0;$k<$count;$k++) {
				
					if ($items[$k]) {
	
						echo " * " . $items[$k]["title"] . "\n";

					}			
		
				}
			echo "\n\n";		
			}
		}
	}

	function print_help() {
		echo "usage: command [options]\n";
		echo "\n";
		echo "Options\n";
		echo "=======\n";
		echo "\n";
		echo "  --feeds feed1,feed2,feed3\n";
		echo "     list of urls to read from\n";
		echo "\n";
		echo "  --items n\n";
		echo "     number of items to read\n";
		echo "\n";
	}

	function parse_args($args) {
		$current_tag = "";
		for ($i=1;$i<count($args);$i++) {
			$arg = $args[$i];
			if (substr($arg,0,2) == "--") {
				$current_tag = substr($arg,2,strlen($arg));
				$config[$current_tag] = "set";
			} else {
				$things = explode(",",$arg);
				$config[$current_tag] = $things;
			}
		}
		return $config;
	}

	function conf_from_file($config_file) {
		$handle = fopen($config_file,"r");
		
		if (!$handle) {
			return;
		}
	
		$current_tag = "";
		while (!feof($handle)) {
			$line = trim(fgets($handle));
	
			if (substr($line,0,1) == "<" and substr($line,strlen($line)-1,strlen($line)) == ">") {
				$tag = $line;
				$tag = str_replace("<","",$tag);
				$tag = str_replace(">","",$tag);
				if ($current_tag != "") {
					if ("/" . $current_tag == $tag) {
						$current_tag = "";
					} else {
						echo "Tag Mismatch in Config File\n";
						exit(0);
					}
				} else {
					$current_tag = $tag;
				}
			} elseif ($current_tag != "") {
				$conf[$current_tag][] = $line;
			}
		}
		return $conf;
	}

?>
