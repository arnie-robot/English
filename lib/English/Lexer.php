<?php
/**
* English
*
* by Chris Alexander
*/

/**
* Lexer for English language
*/
class English_Lexer
{
	/**
	* Database connection details
	*/
	protected $dbhost;
	protected $dbuser;
	protected $dbpass;
	protected $dbname;

	/**
	* Constructs the lexer
	*
	* @param string $dbhost 	the database host to connect to
	* @param string $dbuser		the database username
	* @param string $dbpass		the database user's password
	* @param string $dbname		the name of the database to connect to
	*
	* @return English_Lexer
	*/
	public function __construct($dbhost, $dbuser, $dbpass, $dbname)
	{
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpass = $dbpass;
		$this->dbname = $dbname;
	}
	
	/**
	* Lexes the string and returns an array of tokens found
	*
	* @param string $string		the string to lex
	*
	* @return array
	*/
	public function lex($string)
	{
		$string = $this->tokenise($string);
		$string = $this->start($string);
		$string = $this->end($string);
		return $this->objectify($string);
	}
	
	/**
	* Converts the nasty string/slash format into some parsable data structure
	*
	* @param string $string
	*
	* @return array
	*/
	protected function objectify($string)
	{
		$words = explode(' ', $string);
		$result = array();
		foreach ($words as $k=>$word) {
			$separated = explode('/', $word);
			if (strlen($separated[0]) < 1) {
				continue;
			}
			switch ($separated[1]) {
				case 'DT':
					$type = 'SingularQualifier';
					break;
				case 'NNS':
					$type = 'PluralNoun';
					break;
				case 'VB':
					$type = 'Verb';
					break;
				case 'VBD':
					$type = 'Verb_Past';
					break;
				case 'VBG':
					$type = 'Verb_PresentParticiple';
					break;
				case 'VBN':
					$type = 'Verb_PastParticiple';
					break;
				case 'TO':
					$type = 'InfinitiveMarker';
					break;
				case 'PRP$':
					$type = 'Pronoun_PosessivePersonal';
					break;
				case 'IN':
					$type = 'Preposition';
					break;
				case 'JJ':
					$type = 'Adjective';
					break;
				case 'NN':
					$type = 'Noun';
					break;
				case 'MD':
					$type = 'ModalAuxiliary';
					break;
				case 'PRP':
					$type = 'Pronoun_Personal';
					break;
				case ',':
					$type = 'Punctuation_Comma';
					break;
				case '.':
					$type = 'Punctuation_FullStop';
					break;
				default:
					echo $separated[0] . ' - ' . $separated[1] . '<br />';
					$type = 'Unknown';
			}
			$type = 'English_Token_' . $type;
			$result[] = new $type($separated[0]);
		}
		return $result;
	}
	
	/**
	* Replaces certain key tokens in the string
	*
	* @param string $string
	*
	* @return string
	*/
	protected function tokenise($string)
	{
		$string .= " ";
		for($i = 0; $i < 150; $i++){
			$string = str_replace($this->token_list[(2*$i)], $this->token_list[(2*$i)+1], $string);
		}
		return $string;
	}
	
	/**
	* Start lex
	*
	* @param string $string
	*
	* @return string
	*/
	protected function start($string)
	{
		$words = explode(" ", $string);
		
		mysql_connect($this->dbhost,$this->dbuser,$this->dbpass);
		@mysql_select_db($this->dbname);
		$max = sizeof($words);
		for($i=0;$i<count($words);$i++){
		
			$query = "SELECT pos FROM lexicon WHERE word='" . mysql_real_escape_string($words[$i]) . "'";
			$result = mysql_query($query);
			$row = mysql_fetch_row($result);
			if(!$row[0]){
				if(!$ntothash[$words[$i]]){
					$ntothash[$words[$i]] = "";
					$ntotkeys[$i] = $words[$i];
				}
				if($i != 0 && $i!= $max-1){
					$bigramspace = $words[$i]." ".$words[$i+1];
					$bigramhash[$bigramspace] = "";
					$bigramspace = $words[$i-1]." ".$words[$i];
					$bigramhash[$bigramspace] = "";
				}
				elseif($i != $max-1){
					$bigramspace = $words[$i]." ".$words[$i+1];
					$bigramhash[$bigramspace] = "";
				}
				elseif($i != 0){
					$bigramspace = $words[$i-1]." ".$words[$i];
					$bigramhash[$bigramspace] = "";
				}
			}
		}
				
		if($ntotkeys) $ntotkeys = array_values($ntotkeys);

		$noun = "NN";
		$proper = "NNP";
		$number = "CD";
		
		for($cnt=0;$cnt<sizeof($ntothash);++$cnt){
			if(ord($ntotkeys[$cnt]) > 47  && ord($ntotkeys[$cnt]) < 59)
				$ntothash[$ntotkeys[$cnt]] = $number;
			elseif(ord($ntotkeys[$cnt]) > 64 && ord($ntotkeys[$cnt]) < 91)
				$ntothash[$ntotkeys[$cnt]] = $proper;
			else
				$ntothash[$ntotkeys[$cnt]] = $noun;
		}
		
		//load lex rulebase
		$query = "SELECT rule FROM rulebase WHERE type='l';";
		$result = mysql_query($query);
		for($cnt=0;$cnt<mysql_num_rows($result);++$cnt)
			$lrules[$cnt] = mysql_result($result,$cnt);			
		for($cnt=0;$cnt<mysql_num_rows($result);++$cnt){
			$therule = explode(" ",$lrules[$cnt]);
			$rulesize = sizeof($therule) - 1;
			$ntotsize = sizeof($ntotkeys);
			
			if(strcmp($therule[1],"char") == 0){
				for($cnt2=0;$cnt<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]], $therule[$rulesize-1]) != 0){
						if(strpbrk($ntotkeys[$cnt2],$therule[0])){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}			
			}
			elseif(strcmp($therule[2],"fchar") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]], $therule[0]) == 0){
						if(strpbrk($ntotkeys[$cnt2],$therule[1])){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[1],"deletepref") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]], $therule[$rulesize-1]) != 0){
						$tempstr = $ntotkeys[$cnt2];
						for($cnt3=0;$cnt3<(int)$therule[2];++$cnt3){
							if(substr($tempstr,$cnt3,1) != substr($therule[0],$cnt3,1)) break;
						}
						if($cnt3 == (int)$therule[2]){
							$tempstr += (int)$therule[2];
							$query = "SELECT pos FROM lexicon WHERE word='" . mysql_real_escape_string($tempstr) . "'";
							$result = mysql_query($query);
							$row = mysql_fetch_row($result);
							if($row[0]){
								$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
							}
						}
					}
				}
			}
			elseif(strcmp($therule[2],"fdeletepref") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt]], $therule[0]) == 0){
						$tempstr = $ntotkeys[$cnt2];
						for($cnt3=0;$cnt3<(int)$therule[3];++$cnt){
							if(substr($tempstr,$cnt3,1) != substr($therule[1],$cnt3,1)) break;
						}
						if($cnt3 == (int)$therule[3]){
							$tempstr += (int)$therule[3];
							$query = "SELECT pos FROM lexicon WHERE word='" . mysql_real_escape_string($tempstr) . "'";
							$result = mysql_query($query);
							$row = mysql_fetch_row($result);
							if($row[0]){
								$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
							}				
						}
					}
				}
			}
			elseif(strcmp($therule[1],"haspref") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]], $therule[$rulesize-1]) != 0){
						$tempstr = $ntotkeys[$cnt2];
						for($cnt3=0;$cnt3<(int)$therule[2];++$cnt3){
							if(substr($tempstr,$cnt3,1) != substr($therule[0],$cnt3,1)) break;
						}
						if($cnt3 == (int)$therule[2]){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[2],"fhaspref") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]], $therule[0]) == 0){
						$tempstr = $ntotkeys[$cnt2];
						for($cnt3=0;$cnt3<(int)$therule[3];++$cnt3){
							if(substr($tempstr,$cnt3,1) != substr($therule[1],$cnt3,1)) break;						
						}
						if($cnt3 == (int)$therule[3]){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[1],"deletesuf") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt]],$therule[$rulesize-1]) != 0){
						$tempstr = $ntotkeys[$cnt2];
						$tempcount = strlen($tempstr) - (int)$therule[2];
						for($cnt3=$tempcount;$cnt3<strlen($tempstr);++$cnt3){
							if(substr($tempstr,$cnt3,1) != substr($therule[0],$cnt3-$tempcount,1)) break;
						}
						if($cnt3 == strlen($tempstr)){
							$query = "SELECT pos FROM lexicon WHERE word='" . mysql_real_escape_string($tempstr) . "'";
							$result = mysql_query($query);
							$row = mysql_fetch_row($result);
							if($row[0]){
								$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
							}
						}
					}
				}
			}
			elseif(strcmp($therule[2],"fdeletesuf") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]],$therule[0]) == 0){
						$tempstr = $ntotkeys[$cnt2];
						$tempcount = strlen($tempstr) - (int)$therule[3];
						for($cnt3=$tempcount;$cnt3<strlen($tempstr);++$cnt3){
							if(substr($tempstr,$cnt3,1) != substr($therule[1],$cnt3-$tempcount,1)) break;
						}
						if($cnt3 == strlen($tempstr)){
							$query = "SELECT pos FROM lexicon WHERE word='" . mysql_real_escape_string($tempstr) . "'";
							$result = mysql_query($query);
							$row = mysql_fetch_row($result);
							if($row[0]){
								$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
							}
						}
					}
				}
			}
			elseif(strcmp($therule[1],"hassuf") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]],$therule[$rulesize-1]) != 0){
						$tempstr = $ntotkeys[$cnt2];
						$tempcount = strlen($tempstr) - (int)$therule[2];
						for($cnt3=$tempcount;$cnt3<strlen($tempstr);++$cnt3){
							if(substr($tempstr,$cnt3,1) != substr($therule[0],$cnt3-$tempcount,1)) break;
						}
						if($cnt3 == strlen($tempstr)) {
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[2],"fhassuf") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]],$therule[0]) == 0){
						$tempstr = $ntotkeys[$cnt2];
						$tempcount = strlen($tempstr) - (int)$therule[3];
						for($cnt3=$tempcount;$cnt3<strlen($tempstr);++$cnt3){
							if(substr($tempstr,$cnt3,1) != substr($therule[1],$cnt3-$tempcount,1)) break;
						}
						if($cnt3 == strlen($tempstr)){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[1],"addpref") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]],$therule[$rulesize-1]) == 0){
						$tempstr_space = $therule[0].$ntotkeys[$cnt2];
						$query = "SELECT pos FROM lexicon WHERE word='" . mysql_real_escape_string($tempstr_space) . "'";
						$result = mysql_query($query);
						$row = mysql_fetch_row($result);
						if($row[0]){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[2],"faddpref") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]],$therule[$rulesize-1]) == 0){
						$tempstr_space = $therule[1].$ntotkeys[$cnt2];
						$query = "SELECT pos FROM lexicon WHERE word='" . mysql_real_escape_string($tempstr_space) . "'";
						$result = mysql_query($query);
						$row = mysql_fetch_row($result);
						if($row[0]){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[1],"addsuf") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]],$therule[$rulesize-1]) != 0){
						$tempstr_space = $ntotkeys[$cnt2].$therule[0];
						$query = "SELECT pos FROM lexicon WHERE word='" . mysql_real_escape_string($tempstr_space) . "'";
						$result = mysql_query($query);
						$row = mysql_fetch_row($result);
						if($row[0]){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[2],"faddsuf") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]],$therule[0]) == 0){
						$tempstr_space = $ntotkeys[$cnt2].$therule[1];
						$query = "SELECT pos FROM lexicon WHERE word='" . mysql_real_escape_string($tempstr_space) . "'";
						$result = mysql_query($query);
						$row = mysql_fetch_row($result);
						if($row[0]){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[1],"goodleft") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt]],$therule[$rulesize-1]) != 0){
						$bigram_space = $ntotkeys[$cnt2]." ".$therule[0];
						if($this->hash_get($bigramhash,$bigram_space)){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[2],"fgoodleft") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntotkeys[$cnt2],$therule[0]) == 0){
						$bigram_space = $ntotkeys[$cnt2]." ".$therule[1];
						if($this->hash_get($bigramhash,$bigram_space)){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[1],"goodright") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]],$therule[$rulesize-1]) != 0){
						$bigram_space = $therule[0]." ".$ntotkeys[$cnt2];
						if($this->hash_get($bigramhash,$bigram_space)){
							$ntothash[$ntotkeys[$cnt2]] = $therule[$rulesize-1];
						}
					}
				}
			}
			elseif(strcmp($therule[2],"fgoodright") == 0){
				for($cnt2=0;$cnt2<$ntotsize;++$cnt2){
					if(strcmp($ntothash[$ntotkeys[$cnt2]],$therule[0]) == 0){
						$bigram_space = $therule[1]." ".$ntotkeys[$cnt2];
						if($this->hash_get($bigramhash,$bigram_space)){
							$ntothash[$ntotkeys[$cnt]] = $therule[$rulesize-1];
						}
					}
				}
			}
	
		}
		$sent = explode(" ",$string);
		for($i=0;$i<sizeof($sent);$i++){
			$query = "SELECT pos FROM lexicon WHERE word='" . mysql_real_escape_string($sent[$i]) . "'";
			$result = mysql_query($query);
			$row = mysql_fetch_row($result);
			if($row[0]){
				$tags = explode(" ",$row[0],2);
				$sent[$i] = $sent[$i]."/".$tags[0];
			}
			else{
				$sent[$i] = $sent[$i]."/".$this->hash_get($ntothash,$sent[$i]);
			}
		}		
		mysql_close();
		$rettxt = $sent[0];
		for($i=1;$i<sizeof($sent);$i++) $rettxt = $rettxt." ".$sent[$i];
		return $rettxt;
	}
	
	/**
	* Final lex
	*
	* @param string $string
	*
	* @return string
	*/
	protected function end($string)
	{
		$arraysize = 2;
		$staart = "STAART";
		$restrictmove = 1;
		
		$wi = 0;
		$ti = 0;
		
		$string = $staart . "/" . $staart . " " . $staart . "/" . $staart . " " . $string;

		$wordsinline = explode(" ",$string);
		
		for($i=0;$i<sizeof($wordsinline);$i++){
			$tempstr = explode("/",$wordsinline[$i]);
			$wordcorpus[$i] = $tempstr[0];
			$tagcorpus[$i] = $tempstr[1];
		}

		// read in rule from cRuleArray, and process each rule
		$corpussize = sizeof($tagcorpus) - 1;
		//read crules from dbase
		mysql_connect($this->dbhost,$this->dbuser,$this->dbpass);
		mysql_select_db($this->dbname);
		$query = "SELECT rule FROM rulebase WHERE type='c';";
		$result = mysql_query($query);
		mysql_close();
		for($cnt=0;$cnt<mysql_num_rows($result);++$cnt)
			$crules[$cnt] = mysql_result($result,$cnt);			
		for($i=0;$i<sizeof($crules);$i++){
			$thiscrule = explode(" ",$crules[$i]);	
			$old = $thiscrule[0];
			$new = $thiscrule[1];
			$when = $thiscrule[2];
			
			if(strcmp($when, "NEXTTAG") == 0 || strcmp($when, "NEXT2TAG") == 0 || strcmp($when, "NEXT1OR2TAG") == 0 || strcmp($when, "NEXT1OR2OR3TAG") == 0 || strcmp($when, "PREVTAG") == 0 || strcmp($when, "PREV2TAG") == 0 || strcmp($when, "PREV1OR2TAG") == 0 || strcmp($when, "PREV1OR2OR3TAG") == 0) $tag = $thiscrule[3];
			elseif(strcmp($when, "NEXTWD") == 0 ||strcmp($when, "CURWD") == 0 ||strcmp($when, "NEXT2WD") == 0 ||strcmp($when, "NEXT1OR2WD") == 0 ||strcmp($when, "NEXT1OR2OR3WD") == 0 ||strcmp($when, "PREVWD") == 0 ||strcmp($when, "PREV2WD") == 0 ||strcmp($when, "PREV1OR2WD") == 0 || strcmp($when, "PREV1OR2OR3WD") == 0) $word = $thiscrule[3];
			elseif(strcmp($when, "SURROUNDTAG") == 0){
				$lft = $thiscrule[3];
				$rght = $thiscrule[4];
			}
			elseif(strcmp($when, "PREVBIGRAM") == 0){
				$prev1 = $thiscrule[3];
				$prev2 = $thiscrule[4];
			}
			elseif(strcmp($when, "NEXTBIGRAM") == 0){
				$next1 = $thiscrule[3];
				$next2 = $thiscrule[4];
			}
			elseif(strcmp($when,"LBIGRAM") == 0|| strcmp($when,"WDPREVTAG") == 0){
				$prev1 = $thiscrule[3];
				$word = $thiscrule[4];
			}
			elseif(strcmp($when,"RBIGRAM") == 0 || strcmp($when,"WDNEXTTAG") == 0){
				$word = $thiscrule[3];
				$next1 = $thiscrule[4];
			}
			elseif(strcmp($when,"WDAND2BFR")== 0 || strcmp($when,"WDAND2TAGBFR")== 0){
				$prev2 = $thiscrule[3];
				$word = $thiscrule[4];
			}
			elseif(strcmp($when,"WDAND2AFT")== 0 || strcmp($when,"WDAND2TAGAFT")== 0){
				$next2 = $thiscrule[4];
				$word = $thiscrule[3];
			}

			for ($cnt = 0; $cnt <= $corpussize; ++$cnt){
				$curtag = $tagcorpus[$cnt];
				if(strcmp($curtag, $old) == 0){
					$curwd = $wordcorpus[$cnt];
					$atempstr2 = $curwd." ".$new;
					if(strcmp($when, "SURROUNDTAG") == 0){
						if($cnt < $corpussize && $cnt > 0){
							if(strcmp($lft, $tagcorpus[$cnt - 1]) == 0 && strcmp($rght, $tagcorpus[$cnt + 1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "NEXTTAG") == 0){
						if($cnt < $corpussize){
							if(strcmp(tag,$tagcorpus[$cnt + 1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "CURWD") == 0){
						if(strcmp($word, $wordcorpus[$cnt]) == 0) $tagcorpus[$cnt] = $new;
					}
					elseif(strcmp($when, "NEXTWD") == 0){
						if($cnt < $corpussize){
							if(strcmp($word, $wordcorpus[$cnt + 1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "RBIGRAM") == 0){
						if($cnt < $corpussize){
							if(strcmp($word, $wordcorpus[$cnt]) == 0 && strcmp($next1, $wordcorpus[$cnt+1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "WDNEXTTAG") == 0){
						if($cnt < $corpussize){
							if(strcmp($word, $wordcorpus[$cnt]) == 0 && strcmp($next1, $tagcorpus[$cnt+1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}

					elseif(strcmp($when, "WDAND2AFT") == 0){
						if($cnt < $corpussize-1){
							if(strcmp($word, $wordcorpus[$cnt]) == 0 && strcmp($next2, $wordcorpus[$cnt+2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "WDAND2TAGAFT") == 0){
						if($cnt < $corpussize-1){
							if(strcmp($word, $wordcorpus[$cnt]) == 0 && strcmp($next2, $tagcorpus[$cnt+2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}

					elseif(strcmp($when, "NEXT2TAG") == 0){
						if($cnt < $corpussize - 1){
							if(strcmp($tag, $tagcorpus[$cnt + 2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "NEXT2WD") == 0){
						if($cnt < $corpussize - 1){
							if(strcmp($word, $wordcorpus[$cnt + 2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "NEXTBIGRAM") == 0){
						if($cnt < $corpussize - 1){
							if(strcmp($next1, $tagcorpus[$cnt + 1]) == 0 && strcmp($next2, $tagcorpus[$cnt + 2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "NEXT1OR2TAG") == 0){
						if($cnt < $corpussize){
							if($cnt < $corpussize-1) $tempcnt1 = $cnt+2;
							else $tempcnt1 = $cnt+1;
							if(strcmp($tag, $tagcorpus[$cnt + 1]) == 0 || strcmp($tag, $tagcorpus[$tempcnt1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "NEXT1OR2WD") == 0){
						if($cnt < $corpussize){
							if($cnt < $corpussize-1) $tempcnt1 = $cnt+2;
							else $tempcnt1 = $cnt+1;
							if (strcmp($word, $wordcorpus[$cnt + 1]) == 0 || strcmp($word, $wordcorpus[$tempcnt1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "NEXT1OR2OR3TAG") == 0){
						if($cnt < $corpussize){
							if($cnt < $corpussize -1) $tempcnt1 = $cnt+2;
							else $tempcnt1 = $cnt+1;
							if($cnt < $corpussize-2) $tempcnt2 = $cnt+3;
							else $tempcnt2 =$cnt+1;
							if(strcmp($tag, $tagcorpus[$cnt + 1]) == 0 || strcmp($tag, $tagcorpus[$tempcnt1]) == 0 || strcmp($tag, $tagcorpus[$tempcnt2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "NEXT1OR2OR3WD") == 0){
						if($cnt < $corpussize){
							if($cnt < $corpussize -1) $tempcnt1 = $cnt+2;
							else $tempcnt1 = $cnt+1;
							if($cnt < $corpussize-2) $tempcnt2 = $cnt+3;
							else $tempcnt2 =$cnt+1;
							if(strcmp($word, $wordcorpus[$cnt + 1]) == 0 || strcmp($word, $wordcorpus[$tempcnt1]) == 0 || strcmp($word, $wordcorpus[$tempcnt2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "PREVTAG") == 0){
						if($cnt > 0){
							if(strcmp($tag, $tagcorpus[$cnt - 1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "PREVWD") == 0){
						if($cnt > 0){
							if(strcmp($word, $wordcorpus[$cnt - 1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "LBIGRAM") == 0){
						if($cnt > 0){
							if(strcmp($word, $wordcorpus[$cnt]) == 0 && strcmp($prev1, $wordcorpus[$cnt-1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "WDPREVTAG") == 0){
						if($cnt > 0){
							if(strcmp($word, $wordcorpus[$cnt]) == 0 && strcmp($prev1, $tagcorpus[$cnt-1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "WDAND2BFR") == 0){
						if($cnt > 1){
							if(strcmp($word, $wordcorpus[$cnt]) == 0 && strcmp($prev2, $wordcorpus[$cnt-2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "WDAND2TAGBFR") == 0){
						if($cnt > 1){
							if(strcmp($word, $wordcorpus[$cnt]) == 0 && strcmp($prev2, $tagcorpus[$cnt-2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}

					elseif(strcmp($when, "PREV2TAG") == 0){
						if($cnt > 1){
							if(strcmp($tag, $tagcorpus[$cnt - 2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "PREV2WD") == 0){
						if($cnt > 1){
							if(strcmp($word, $wordcorpus[$cnt - 2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "PREV1OR2TAG") == 0){
						if($cnt > 0){
							if($cnt > 1) $tempcnt1 = $cnt-2;
							else $tempcnt1 = $cnt-1;
							if(strcmp($tag, $tagcorpus[$cnt - 1]) == 0 || strcmp($tag, $tagcorpus[$tempcnt1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "PREV1OR2WD") == 0){
						if($cnt > 0){
							if($cnt > 1) $tempcnt1 = $cnt-2;
							else $tempcnt1 = $cnt-1;
							if(strcmp($word, $wordcorpus[$cnt - 1]) == 0 || strcmp($word, $wordcorpus[$tempcnt1]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "PREV1OR2OR3TAG") == 0){
						if($cnt > 0){
							if($cnt>1) $tempcnt1 = $cnt-2;
							else $tempcnt1 = $cnt-1;
							if($cnt >2) $tempcnt2 = $cnt-3;
							else $tempcnt2 = $cnt-1;
							if(strcmp($tag, $tagcorpus[$cnt - 1]) == 0 || strcmp($tag, $tagcorpus[$tempcnt1]) == 0 || strcmp($tag, $tagcorpus[$tempcnt2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "PREV1OR2OR3WD") == 0){
						if($cnt > 0){
							if($cnt>1) $tempcnt1 = $cnt-2;
							else $tempcnt1 = $cnt-1;
							if($cnt >2) $tempcnt2 = $cnt-3;
							else $tempcnt2 = $cnt-1;
							if(strcmp($word, $wordcorpus[$cnt - 1]) == 0 || strcmp($word, $wordcorpus[$tempcnt1]) == 0 || strcmp($word, $wordcorpus[$tempcnt2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					elseif(strcmp($when, "PREVBIGRAM") == 0){
						if($cnt > 1){
							if(strcmp($prev2, $tagcorpus[$cnt - 1]) == 0 && strcmp($prev1, $tagcorpus[$cnt - 2]) == 0) $tagcorpus[$cnt] = $new;
						}
					}
					else throw new English_Exception("$when is not an allowable transform type");
				}
			}  
		}
		
		$rettxt = $wordcorpus[2]."/".$tagcorpus[2]." ";
		for($i=3;$i<=$corpussize;++$i){
			$rettxt = $rettxt.$wordcorpus[$i]."/".$tagcorpus[$i]." ";
		}
		return $rettxt;
	}
	
	/**
	* Returns some hash something??
	*/
	protected function hash_get($hash, $ky){
		if($hash[$ky]) return $hash[$ky];
		else return FALSE;
	}
	
	/**
	* A list of tokens to replace out
	*/
	protected $token_list = array("\"", " \" ",",", " , ",";", " ; ",":", " : ","? ", " ? ","! ", " ! ",
		". ", " .","[", " [ ","]", " ] "," (", " ( ",") ", " ) ",").", " ).","<", " < ",
		">", " > ","--", " -- ","'s ", " 's ","'S ", " 'S ","'m ", " 'm ","'M ", " 'M ",
		"'d ", " 'd ","'D ", " 'D ","'ll ", " 'll ","'re ", " 're ","'ve ", " 've ",
		" can't ", " can n't "," Can't ", " Can n't ","n't ", " n't ","'LL ", " 'LL ",
		"'RE ", " 'RE ","'VE ", " 'VE ","N'T ", " N'T "," Cannot ", " Can not ",
		" cannot ", " can not "," D'ye ", " D' ye "," d'ye ", " d' ye ",
		" Gimme ", " Gim me "," gimme ", " gim me "," Gonna ", " Gon na ",
		" gonna ", " gon na "," Gotta ", " Got ta "," gotta ", " got ta ",
		" Lemme ", " Lem me "," lemme ", " lem me "," More'n ", " More 'n ",
		" more'n ", " more 'n ","'Tis ", " 'T is ","'tis ", " 't is ","'Twas ", " 'T was ",
		"'twas ", " 't was "," Wanna ", " Wan na "," wanna ", " wanna ",

		"Let 's ", "Let's ",".. .", " ...","Adm .", "Adm.","Aug .", "Aug.","Ave .", "Ave.",
		"Brig .", "Brig.","Bros .", "Bros.","CO .", "CO.", "CORP .", "CORP.","COS .", "COS.", 
		"Capt .", "Capt.","Co .", "Co.","Col .", "Col.","Colo .", "Colo.","Corp .", "Corp.",
		"Cos .", "Cos.","Dec .", "Dec.","Del .", "Del.","Dept .", "Dept.","Dr .", "Dr.",
		"Drs .", "Drs.","Etc .", "Etc.","Feb .", "Feb.","Ft .", "Ft.","Ga .", "Ga.",
		"Gen .", "Gen.","Gov .", "Gov.","Hon .", "Hon.","INC .", "INC.","Inc .", "Inc.",
		"Ind .", "Ind.","Jan .", "Jan.","Jr .", "Jr.","Kan .", "Kan.","Ky .", "Ky.",
		"La .", "La.","Lt .", "Lt.","Ltd .", "Ltd.","Maj .", "Maj.","Md .", "Md.",
		"Messrs .", "Messrs.","Mfg .", "Mfg.","Miss .", "Miss.","Mo .", "Mo.",
		"Mr .", "Mr.","Mrs .", "Mrs.","Ms .", "Ms.","Nev .", "Nev.","No .", "No.",
		"Nos .", "Nos.","Nov .", "Nov.","Oct .", "Oct.","Ph .", "Ph.","Prof .", "Prof.",
		"Prop .", "Prop.","Pty .", "Pty.","Rep .", "Rep.","Reps .", "Reps.","Rev .", "Rev.",
		"S.p.A .", "S.p.A.","Sen .", "Sen.","Sens .", "Sens.","Sept .", "Sept.","Sgt .", "Sgt.",
		"Sr .", "Sr.","St .", "St.","Va .", "Va.","Vt .", "Vt.","U.S .", "U.S.","Wyo .", "Wyo.",
		"a.k.a .", "a.k.a.","a.m .", "a.m.","cap .", "cap.","e.g .", "e.g.","eg .", "eg.",
		"etc .", "etc.","ft .", "ft.","i.e .", "i.e.","p.m .", "p.m.","v .", "v.",
		"v.B .", "v.B.","v.w .", "v.w.","vs .", "vs.","__END__", "__END__");
}