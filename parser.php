<?php

echo("FF-link\tDate\tType\tBody\tFile Name\tFile Url\tAuthor\tTitle\t#Comments\t#Likes\n");
for($i=0; $i < 5560; $i=$i+150){
	#$u = "http://friendfeed-api.com/v2/feed/ffebookpaylasimalan?pretty=1&num=10&raw=1";
	$u = "file:///tmp/ff_file_ffebookpaylasimalan_$i.json";
	$c = file_get_contents($u);
	
	$ff = json_decode($c);
	
	foreach($ff->entries as $f){
		if(isset($f->files)){
			foreach($f->files as $file){
				echo($f->rawLink . "\t");
				echo(form_date($f->date) . "\t");
				echo("file" . "\t");
			
				echo(form($f->rawBody) . "\t");
				echo($file->name . "\t");
				//echo($file->type . "\t");
				echo($file->url . "\t");
				
				echo(form_info($file->name) . "\t");

				if(isset($f->comments)){
					echo(sizeof($f->comments)); //. " comment(s)");
				}
				echo("\t");
				if(isset($f->likes)){
					echo(sizeof($f->likes)); // . " like(s)");
				}
				echo("\t");
				
				echo("\n");
			}
		}
		else{
			echo($f->rawLink . "\t");
			echo(form_date($f->date) . "\t");
			echo("gen" . "\t");
			echo(form($f->rawBody) . "\n");
		}

		if(isset($f->comments)){
			foreach($f->comments as $c){
				if(stripos($c->rawBody, "://")){
					$body = form($c->rawBody);
					$pos = stripos($body, "://");
					echo($f->rawLink . "\t");
					echo(form_date($f->date) . "\t");
					echo("com-link" . "\t");
					echo($body . "\t");
					echo("\t"); // file-name
					
					$endOfUrl = strpos($body, " ", $pos);
					if(!$endOfUrl){
						$endOfUrl = strlen($body);
					}
					$urlType = substr($body, $pos-3, 3);
					
					if(strcmp($urlType,"tps") == 0){
						$startOfUrl = $pos-5;
					}
					elseif(strcmp($urlType,"ttp") == 0){
						$startOfUrl = $pos-4;
					}
					elseif(strcmp($urlType,"ftp") == 0){
						$startOfUrl = $pos-3;
					}
					
					echo(trim(substr($body, $startOfUrl, $endOfUrl-$startOfUrl)));
					echo("\t"); 
					
					if(stripos($body, "-")){
						$n = substr($body, 0, $startOfUrl);
						echo(form_info($n) . "\t");
					}
					echo("\n");
				}	
			}
		}
	}
}
	
function form($str){
	$trans = array("\n" => " ", "\r" => " ");
	return trim(strtr($str, $trans));
}

function form_date($str){
	$trans = array("T" => " ", "Z" => "");
	return trim(strtr(form($str), $trans));
}

function form_info($str){
	$trans = array("_" => " ", "—" => "-");
	$str = trim(strtr(form($str), $trans));
	$ext=explode("-", $str);
	$n=$ext[0];
	$t=explode(".", substr($str, strlen($ext[0])+1));
	return $n . "\t" . trim($t[0]);
}
