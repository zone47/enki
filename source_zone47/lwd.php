<?php
/* / */
include 'functions.php';
include 'data.php';

if (isset($_GET['ark'])){
	echo "<style>
.notice__actions #widgetwd{width:100% !important;padding:0.5em;margin-top:15px;background-color:#dce6ef !important;border:1px solid #C0C0C0}
.lienwd:link {text-decoration:underline;color:#666;}
.lienwd:visited  {text-decoration:underline;color:#444;}
.lienwd:link:active, .lienwd:visited:active  {text-decoration:underline;color:#808080;}
#widgetwd {font-size:0.9em;line-height:1.2em;opacity:1 !important;}
#widgetwd img{display:inline;vertical-align:bottom}
#widgetwd img.thumb{display:block;border:1px solid #C0C0C0}
#widgetwd p{margin-bottom:5px;}
#widgetwd ul{margin-top:-7px;display:block;}
#widgetwd li{line-height:1.4em;margin-top:1px}
#widgetwd strong{font-weight:400;color:#222;}
#widgetwd #blocimg{float:right;margin-left:5px;}
#widgetwd .clear{clear:both;height:4px}
#widgetwd #creas{margin-top:5px;}
.otherwp{font-size:80%}
#widgetwd #creas .dispstrong{color:#444;font-weight:400}
.idcrea {font-size:84%}
.lienscrea{margin-top:-5px}
.listwd{margin-bottom:10px;}
#wikidata{color:#999;font-variant-caps: all-small-caps;font-size:0.9em;font-weight:bold;letter-spacing:1px}
#item {line-height:1.4em;}
.lbwd {color:#444;font-weight:400}
.bloccol{column-count: 2;}
.listinline, .listinline li{display:inline}
.items{margin-bottom:10px;}
.idext{margin-top:2px;}
#trend_exp_bt{
	cursor:pointer;	
}
#widgetwd .panel {
  display:none;
  margin-top:0;
}
#widgetwd .expanded {
   display:block;
   margin-top:0;
}
#trend_exp_bt{
	text-align :center;
}
#more{
	font-size:90%;
	text-decoration:underline;
}
.deplie{
  color:#009cad;
}
</style>";
	
	$url_get=filter_var($_GET['ark'], FILTER_SANITIZE_STRING);
	if (isset($_GET['lang'])){
		$lg=filter_var($_GET['lang'], FILTER_SANITIZE_STRING);
	}
	if ($lg!="en"){
		$lg="fr";
	}
	preg_match('/.*([0-9]{9}).*/i', $url_get, $matches);
	$arkid='';
	$creas=array();
	if (count($matches)>1){
		$arkid=$matches[1];
		$url_coll="https://collections.louvre.fr/ark:/53355/cl".$arkid;
		// Créas
		$datajson=file_get_contents($url_coll.".json",true);
		$donnees_notice=json_decode($datajson,true);
		foreach ($donnees_notice['creator'] as $key => $value){
			if (($value["wikidata"]!='')&&($value["wikidata"]!='Q4233718')){
				$creas[]=$value["wikidata"];
			}
		}
	}
		
	$sparql="
	SELECT ?item
	WHERE
	{
	  ?item wdt:P9394 ?ark.
	  VALUES ?ark {\"".$arkid."\"}
	}";
	
	$res_sparql=get_sparql_res($sparql);
	if ($arkid!=''){
		$datawd="<p><span id=\"wikidata\">".$trads[$lg]["wikidata"]."</span></p>\n"; 	
		
		$bloc_crea="";
		if (count($creas)>0){
			$crea_done=array();
			$bloc_crea="<ul id=\"creas\" class=\"listwd\">\n";
			for ($i=0;$i<count($creas);$i++){
				if (!(in_array($creas[$i],$crea_done))){
					$bloc_crea.="<li>\n";
					$bloc_crea.=wp_link($creas[$i],$lg,"P170");
					$bloc_crea.=" <a href=\"https://www.wikidata.org/wiki/".$creas[$i]."?uselang=".$lg."\"><img src=\"https://zone47.com/crotos/img/wd_ico.png\" alt=\"\"></a>";
					
					$datafic = file_get_contents("https://www.wikidata.org/wiki/Special:EntityData/".$creas[$i].".json",true);
					$data = json_decode($datafic,true);
					$varlab=$data["entities"][$creas[$i]];
					$claims=$varlab["claims"];
					
					$lienscrea="";
					foreach($tab_url_creaids as $key=>$url){
						if ($claims[$key]){
							foreach ($claims[$key] as $value){
								$id_crea=$value["mainsnak"]["datavalue"]["value"];

								if ($lienscrea!=""){
									$lienscrea.=" ";
								}
								if ($key!="P7711"){
									$lienscrea.="<a href=\"".str_replace("$1",$id_crea,$url)."\" class=\"lienwd idcrea\">".$trads[$lg][$key]."</a>\n";
								}
								else{
									$lienscrea.="<a href=\"".lienPOP($id_crea)."\" class=\"lienwd idcrea\">".$trads[$lg][$key]."</a>\n";
								}
							}
						}
					}
					if ($lienscrea!=""){
						$bloc_crea.="<div class=\"lienscrea\">".$lienscrea."</div>";
					}
					
					
					$bloc_crea.="</li>\n";
					$crea_done[]=$creas[$i];
				}
			}
			$bloc_crea.="</ul>\n";
		}
		
		$crea=false;
		$cpt=0;
		foreach ($res_sparql["results"]["bindings"] as $key => $value){
			$qwd=str_replace("http://www.wikidata.org/entity/","",$value["item"]["value"]);
			$cpt++;
		}
		if ($cpt==0){
			echo $datawd;
			//echo $trads[$lg]["noitem"]." <a href='http://zone47.com/lw/nouvel/index.php?ark=".$arkid."' class='lienwd'>".$trads[$lg]["create"]."</a>"; 
			echo $trads[$lg]["noitem"]; 
			if (count($creas)>0){
				echo $bloc_crea;
			}
			
		}
		elseif ($cpt>1){
			echo $datawd;
			echo "<p>".$trads[$lg]["several"]."</p>"; 
			
			$sparql="
			SELECT DISTINCT ?item ?itemLabel  (GROUP_CONCAT(distinct ?ninv; separator=\" ; \") as ?inv) 
			WHERE
			{
			  ?item wdt:P9394 ?ark.
			  VALUES ?ark {\"".$arkid."\"}.
			  OPTIONAL{?item wdt:P217 ?ninv}
			  SERVICE wikibase:label { bd:serviceParam wikibase:language \"[AUTO_LANGUAGE],fr,en\". }
			} GROUP BY ?item ?itemLabel ";
			$res_sparql2=get_sparql_res($sparql);
			foreach ($res_sparql2["results"]["bindings"] as $key => $value){
				$qwd2=str_replace("http://www.wikidata.org/entity/","",$value["item"]["value"]);
				$lb2=$value["itemLabel"]["value"];
				$inv2=$value["inv"]["value"];
				echo "<p class=\"items\"><a href=\"https://www.wikidata.org/wiki/".$qwd2."?uselang=".$lg."\" class=\"lienwd\">".$qwd2."</a> <a href=\"https://www.wikidata.org/wiki/".$qwd2."?uselang=".$lg."\"><img src=\"https://zone47.com/crotos/img/wd_ico.png\" alt=\"\"></a><br/>"; 
				echo "<strong>".$lb2."</strong><br/>"; 
				echo $inv2."</p>"; 

			}
		}
		elseif ($cpt==1){
			$datafic = file_get_contents("https://www.wikidata.org/wiki/Special:EntityData/".$qwd.".json",true);
			$data = json_decode($datafic,true);
			$varlab=$data["entities"][$qwd];
			$claims=$varlab["claims"];
			
			// Libellé
			$lb=label($varlab["labels"],$lg);
			
			// Page WP
			$wp=pageWP($varlab["sitelinks"],$lg);
			if (!is_null($wp)){
				$lgwp=substr($wp,8,strpos($wp,".")-8);
			}
									
			foreach($tab_prop as $key=>$val){
				if ($claims[$key]){
					foreach ($claims[$key] as $value){
						if ($tab_prop[$key]=="")
						   $tab_prop[$key]=$value["mainsnak"]["datavalue"]["value"];
						else
							break;
					}
					if ($key!="P18")
						$tab_prop[$key]=esc_dblq($tab_prop[$key]);
				}
			}

			foreach($tab_prop_multi as $key=>$val){
				if ($claims[$key]){
					foreach ($claims[$key] as $value){
						if ($tab_prop_multi[$key]!=""){
							$tab_prop_multi[$key].=";";
						}
						if (isset($value["mainsnak"]["datavalue"]["value"]["id"])){
							$tab_prop_multi[$key].=$value["mainsnak"]["datavalue"]["value"]["id"];
						}
						else{
							$tab_prop_multi[$key].=$value["mainsnak"]["datavalue"]["value"];
						}
					}
				}
			}
			
			//Image
			if($tab_prop["P18"]!=""){
				$fic_img=$tab_prop["P18"];
				$fic_img_enc=urlencode($fic_img);
				$commonsfic = file_get_contents("https://commons.wikimedia.org/w/api.php?action=query&format=json&prop=imageinfo&iiprop=size&titles=File:".$fic_img_enc,true);
				$dataresimg = json_decode($commonsfic,true);
				$dimg=$dataresimg["query"]["pages"];
				$iidata=$dimg[array_key_first($dimg)]["imageinfo"];
				$imginfo=$iidata[array_key_first($iidata)];
				$w=$imginfo["width"];
				$h=$imginfo["height"];
				if ($w/$h >= $thumb_wmax/$thumb_hmax){
					$hw = $thumb_wmax;
				}else{
					$hw=round($thumb_hmax*$w/$h);
				}
				$img="<img src=\"https://commons.wikimedia.org/w/thumb.php?f=".$fic_img."&w=".$hw."\" alt=\"\" class=\"thumb\"/>";
				$linkimg="<a href=\"https://commons.wikimedia.org/wiki/File:".$fic_img."?uselang=".$lg."\" title=\"".$trads[$lg]["imgcommons"]."\">".$img."</a>\n";
			}
			
			// Géolocalisation
			$geoloc="";
			if($tab_prop["P625"]!=""){
				$lat=floatval($tab_prop["P625"]["latitude"]);
				$long=floatval($tab_prop["P625"]["longitude"]);
				$url_geoloc="https://geohack.toolforge.org/geohack.php?params=".$lat."_N_".$long."_E_scale:5000_globe:Earth_type:camera_heading:179.00&language=".$lg;
				$geoloc="<a href=\"".$url_geoloc."\" class=\"lienwd\">".$trads[$lg]["geoloc"]."</a> <a href=\"".$url_geoloc."\"><img src=\"https://zone47.com/crotos/img/map.png\" alt=\"\"/></a>\n";
			}

						
			// Lieux
			$tab_pcoord=array("P189"=>"");
			$keys = array_keys($tab_pcoord);
			foreach ($keys as $key) {
				if ($tab_prop_multi[$key]!=""){
					$refsgeo=explode(";",$tab_prop_multi[$key]);
					for ($j=0;$j<count($refsgeo);$j++){
						$refeo=coordgeo($refsgeo[$j],$lg,$key);
						if (!is_null($refeo)){
							if ($tab_pcoord[$key]!=""){
								$tab_pcoord[$key].=" - ";
							}
							$tab_pcoord[$key].=$refeo;
						}
						$pleaides=array();
						$pleaides=getclaimvalue($refsgeo[$j],"P1584");
						for ($i=0;$i<count($pleaides);$i++){
							if ($pleaides[$i]!=""){
								$tab_pcoord[$key].=" <a href=\"https://pleiades.stoa.org/places/".$pleaides[$i]."\"><img src=\"https://zone47.com/lw/img/pleiades.png\" alt=\"\"/></a>";
							}
						}
					}
				}
			}
			
			// ID externes
			$idext="";
			foreach($tab_url_ids as $key=>$val){
				foreach ($val as $prop=>$url){
					if ($tab_prop_multi[$prop]!=""){
						$valsprop=explode(";",$tab_prop_multi[$prop]);
						for ($i=0;$i<count($valsprop);$i++){
							$idext.="<li class=\"idext\"><a href=\"".str_replace("$1",$valsprop[$i],$url)."\" class=\"lienwd\">".$trads[$lg][$prop]."</a></li>\n";
						}
					}
				}
			}
			
			// Expositions
			$nbexhib=0;
			$exhib="";
			if($tab_prop_multi["P608"]!=""){
				$qwd_exhib=explode(";",$tab_prop_multi["P608"]);
				for ($i=0;$i<count($qwd_exhib);$i++){
					if (!(in_array($qwd_exhib[$i],$tabexcl_expo))){
						$lien=querywd($qwd_exhib[$i],$lg,"P608");
						$exhib.="<li><a href=\"".$lien."\" class=\"lienwd\">".labelqwd($qwd_exhib[$i], $lg)."</a> <a href=\"".$lien."\"><img src=\"https://zone47.com/crotos/img/wdsparql_ico.png\" alt=\"\"></a></li>\n";
					}
				}
				$nbexhib=count($qwd_exhib);
			}
			
			
			// Sujet - Dépeint
			$tab_rep=array("P921"=>"","P180"=>"");
			$p180plus="";
			$keys = array_keys($tab_rep);
			$qwd_P921=array();
			foreach ($keys as $key) {
				if ($tab_prop_multi[$key]!=""){
					$qwd_rep=explode(";",$tab_prop_multi[$key]);
					if ($key=="P921"){
						$qwd_P921=$qwd_rep;
					}
					$cptdep=0;
					for ($i=0;$i<count($qwd_rep);$i++){
						$rep=wp_link($qwd_rep[$i],$lg,$key);
						if (!is_null($rep)){
							if (!(($key=="P180")&&($cptdep>$nbdepicts-1))){
								if (!(($key=="P180")&&(in_array($qwd_rep[$i],$qwd_P921)))){
									$tab_rep[$key].="<li>".$rep."</li>\n";
									$cptdep++;
								}
							}
							else{
								$p180plus.="<li>".$rep."</li>\n";
								
							}
							
						}
					}
				}
			}
			
			echo "<div id=\"blocimg\">".$linkimg."</div>\n"; 	
			echo $datawd;
			echo "<p id=\"item\"><a href=\"https://www.wikidata.org/wiki/".$qwd."?uselang=".$lg."\" title=\"".$trads[$lg]["item"]."\"><img src=\"https://zone47.com/crotos/img/wd_ico.png\" alt=\"\"></a> <a href=\"https://www.wikidata.org/wiki/".$qwd."?uselang=".$lg."\" class=\"lienwd\">".$qwd."</a></p>\n"; 
			echo "<p>\n";
			if (!is_null($wp)){
				echo " <a href=\"".$wp."\" class=\"lienwd\"><strong>".$lb."</strong></a> <a href=\"".$wp."\" ><img src=\"https://zone47.com/crotos/img/wps_ico.png\" alt=\"\"/>";	
				if ($lgwp!=$lg){
					echo "<span clas=\"otherwp\">[".$lgwp."]";	
				}
				echo "</a>";
				
			}else{
				echo "<strong>".$lb."</strong>";
			}
			echo "</p>\n";

			echo "<div class=\"clear\"></div>\n";
			
			if (count($creas)>0){
				echo $bloc_crea;
			}
			
			if ($geoloc!=""){
				echo "<p id=\"geoloc\">".$geoloc."</p>\n";
			}
			
			if ($tab_pcoord["P189"]!=""){
				echo "<p><span class=\"lbwd\">".$trads[$lg]["P189"]."</span> ".$tab_pcoord["P189"]."</p>\n";
			}
			if ($idext!=""){
				echo "<p><span class=\"lbwd\">".$trads[$lg]["ress"]."</span></p> <ul class=\"bloccol listwd\">".$idext."</ul>\n";
			}
			
			if ($exhib!=""){
				$pluriel="";
				if ($nbexhib>1){
					$pluriel="s";
				}
				echo "<p><span class=\"lbwd\">".$trads[$lg]["exhib"].$pluriel."</span></p> <ul class=\"listwd\">".$exhib."</ul>\n";
			}
			
			if ($tab_rep["P921"]!=""){
				echo "<p><span class=\"lbwd\">".$trads[$lg]["P921"]."</span></p> <ul class=\"listinline listwd listhem\">".$tab_rep["P921"]."</ul>\n";
			}
		
			if ($tab_rep["P180"]!=""){
				echo "<p><span class=\"lbwd\">".$trads[$lg]["P180"]."</span></p> <ul class=\"bloccol\">".$tab_rep["P180"]."</ul>\n";
				
				if ($p180plus!=""){
					echo "<ul id=\"exp-panel\" class=\"bloccol panel\">".$p180plus."</ul>\n";
					echo "<div id=\"trend_exp_bt\" onclick=\"document.getElementById('exp-panel').classList.toggle('expanded');document.getElementById('trend_exp_bt').classList.toggle('deplie');document.getElementById('more').classList.toggle('less');\"><span id=\"more\">Plus</span></div>
</div>";
				}
			}
		}
	}
}
?>
