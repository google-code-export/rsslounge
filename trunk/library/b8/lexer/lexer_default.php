<?php

#   Copyright (C) 2006-2009 Tobias Leupold <tobias.leupold@web.de>
#
#   This file is part of the b8 package
#
#   This program is free software; you can redistribute it and/or modify it
#   under the terms of the GNU Lesser General Public License as published by
#   the Free Software Foundation in version 2.1 of the License.
#
#   This program is distributed in the hope that it will be useful, but
#   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
#   or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public
#   License for more details.
#
#   You should have received a copy of the GNU Lesser General Public License
#   along with this program; if not, write to the Free Software Foundation,
#   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.

# Get the shared functions class file (if not already loaded)
require_once dirname(__FILE__) . "/../shared_functions.php";

# The default class to split a text into tokens

class lexer_default extends b8SharedFunctions
{

	# Constructor

	function lexer_default()
	{

		# Till now, everything's fine
		# Yes, I know that this is crap ;-)
		$this->constructed = TRUE;

		# Config parts we need
		$config[] = array("name" => "minSize",		"type" => "int",	"default" => 3);
		$config[] = array("name" => "maxSize",		"type" => "int",	"default" => 15);
		$config[] = array("name" => "allowNumbers",	"type" => "bool",	"default" => FALSE);

		# Get the configuration

		$configFile = "config_lexer";

		if(!$this->loadConfig($configFile, $config)) {
			$this->echoError("Failed initializing the configuration.");
			$this->constructed = FALSE;
		}

	}

	# Split the text up to tokens

	function getTokens($text)
	{

		# Check if we have a string here

		if(!is_string($text)) {
			$this->echoError("The given parameter is not a string (<kbd>" . gettype($text) . "</kbd>). Cannot lex it.");
			return FALSE;
		}

		$tokens = "";
/*
		# Get internet and IP addresses

		preg_match_all("/([A-Za-z0-9\_\-\.]+)/", $text, $raw_tokens);

		foreach($raw_tokens[1] as $word) {

			if(strpos($word, ".") === FALSE)
				continue;

			if(!$this->isValid($word))
				continue;

			if(!isset($tokens[$word]))
				$tokens[$word] = 1;
			else
				$tokens[$word]++;

			# Delete the processed parts
			$text = str_replace($word, "", $text);

			# Also process the parts of the urls

			$url_parts = preg_split("/[^A-Za-z0-9!?\$¤¥£'`ÄÖÜäöüßÉéÈèÊêÁáÀàÂâÓóÒòÔôÇç]/", $word);

			foreach($url_parts as $word) {

				if(!$this->isValid($word))
					continue;

				if(!isset($tokens[$word]))
					$tokens[$word] = 1;
				else
					$tokens[$word]++;

			}

		}
*/
		# Raw splitting of the remaining text
        $text = preg_replace("/[^a-zA-Z0-9\säöüßÄÖÜ\-]/", '', $text);
		$raw_tokens = preg_split("/[^A-Za-z0-9!?\$¤¥£'`ÄÖÜäöüßÉéÈèÊêÁáÀàÂâÓóÒòÔôÇç]/", $text);

        /* modification (tobias zeising), always get two words */
        $paired_raw_tokens = array();
        for($i = 0; $i<count($raw_tokens); $i++) {
            if($i!=0)
                $paired_raw_tokens[] = $raw_tokens[$i-1] . " " . $raw_tokens[$i];
        }
        $raw_tokens = $paired_raw_tokens;
        /* end modification (tobias zeising), always get two words */
		foreach($raw_tokens as $word) {

			if(!$this->isValid($word))
				continue;

			if(!isset($tokens[$word]))
				$tokens[$word] = 1;
			else
				$tokens[$word]++;

		}
/*
		# Get HTML

		preg_match_all("/(<.+?>)/", $text, $raw_tokens);

		foreach($raw_tokens[1] as $word) {

			if(!$this->isValid($word))
				continue;

			# If the text has parameters, just use the tag

			if(strpos($word, " ") !== FALSE) {
				preg_match("/(.+?)\s/", $word, $tmp);
				$word = "{$tmp[1]}...>";
			}

			if(!isset($tokens[$word]))
				$tokens[$word] = 1;
			else
				$tokens[$word]++;

		}
*/        
		# Return a list of all found tokens
		return($tokens);

	}

	# Check if a token is valid

	function isValid($token)
	{

		# Check for a proper length
		if(strlen($token) < $this->config['minSize'] or strlen($token) > $this->config['maxSize'])
			return FALSE;

		# If wanted, exclude pure numbers
		if($this->config['allowNumbers'] == FALSE) {
			if(preg_match("/^[0-9]+$/", $token))
				return FALSE;
		}

		# Otherwise, the token is okay
		return TRUE;

	}

}

?>
