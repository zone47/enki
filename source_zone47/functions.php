<?php
/* / */
function clean($data){
    $data = htmlspecialchars($data);
    $data = stripslashes($data);
    $data = trim($data);
    return $data;
}
function truncate($text, $chars =  56){
	if (strlen($text)>56)
		$trunk=true;
	else
		$trunk=false;
    $text = $text." ";
    $text = substr($text,0,$chars);
    $text = substr($text,0,strrpos($text,' '));
    if ($trunk)
		$text = $text."...";
    return $text;
}
function get_sparql_res($query){
	global $context;
	$sparqlurl=urlencode($query);
	$req="https://query.wikidata.org/sparql?format=json&query=".$sparqlurl;
	$res  = file_get_contents($req,true,$context);
	return json_decode($res,true);

}
function esc_dblq($text){
	return str_replace("\"","\\\"",$text);
}
function label($varlabels, $lg){
	global $tab_lg;
	if (isset($varlabels[$lg]["value"])){
		return $varlabels[$lg]["value"];
	}
	else{
		for ($i=0;$i<count($tab_lg);$i++){
			if (isset($varlabels[$tab_lg[$i]]["value"])){
				return $varlabels[$tab_lg[$i]]["value"];
			}
		}
	}
}
function labelqwd($qwd, $lg){
	$url_api="https://www.wikidata.org/w/api.php?action=wbgetentities&ids=".$qwd."&format=json&props=labels";
	$data  = json_decode(file_get_contents($url_api,true),true);
	return label($data["entities"][$qwd]["labels"],$lg);
}
function pageWP($varlinks, $lg){
	global $tab_lg;
	if (isset($varlinks[$lg."wiki"]["url"])){
		return $varlinks[$lg."wiki"]["url"];
	}
	else{
		for ($i=0;$i<count($tab_lg);$i++){
			if (isset($varlinks[$tab_lg[$i]."wiki"]["url"])){
				return $varlinks[$tab_lg[$i]."wiki"]["url"];
			}
		}
	}
}
function wp_link($qwd, $lg, $prop){
	global $tab_lg;
	$url_api="https://www.wikidata.org/w/api.php?action=wbgetentities&ids=".$qwd."&format=json&props=labels|sitelinks";
	$res  = json_decode(file_get_contents($url_api,true),true);
	$wplink=false;
	$classtrong="";
	if ($prop=="P170"){
		$classtrong=" dispstrong";
	}	
	$querylink=" <a href=\"".querywd($qwd,$lg,$prop)."\"><img src=\"https://zone47.com/crotos/img/wdsparql_ico.png\" alt=\"\"></a>";
	if (isset($res["entities"][$qwd]["sitelinks"][$lg."wiki"]["title"])){
		return "<a href=\"https://".$lg.".wikipedia.org/wiki/".$res["entities"][$qwd]["sitelinks"][$lg."wiki"]["title"]."\" class=\"lienwd".$classtrong."\">". label($res["entities"][$qwd]["labels"],$lg)."&nbsp;<img src=\"https://zone47.com/crotos/img/wps_ico.png\" alt=\"\"/></a>".$querylink;
	}
	else{
		for ($i=0;$i<count($tab_lg);$i++){
			if (isset($res["entities"][$qwd]["sitelinks"][$tab_lg[$i]."wiki"]["title"])){
				return "<a href=\"https://".$tab_lg[$i].".wikipedia.org/wiki/".$res["entities"][$qwd]["sitelinks"][$tab_lg[$i]."wiki"]["title"]."\" class=\"lienwd".$classtrong."\"\">".label($res["entities"][$qwd]["labels"],$lg)."&nbsp;<img src=\"https://zone47.com/crotos/img/wps_ico.png\" alt=\"\"/><span class=\"otherwp\">[".$tab_lg[$i]."]</span></a> ".$querylink;	
				$wplink=true;
			}
		}
		if (!$wplink){
			return "<span class=\"".$classtrong."\">".label($res["entities"][$qwd]["labels"],$lg)."</span>".$querylink;
		}
	}
}
function coordgeo($qwd,$lg,$prop){
	$discoplace="";
	if ($prop=="P189"){
		// test si le lieu est archéologique
		$sparql="SELECT (COUNT(?item) as ?nb)
		WHERE {
		  VALUES ?item {wd:".$qwd."}
		  ?item wdt:P31/wdt:P279* wd:Q839954.  
		}";
		$res_sparql=get_sparql_res($sparql);
		$nb=intval($res_sparql["results"]["bindings"][array_key_first($res_sparql["results"]["bindings"])]["nb"]["value"]);
		if ($nb>0){
			$wplace=wp_link($qwd,$lg,$prop);
			if (!is_null($wplace)){
				$discoplace=$wplace;
			}
		}
	}

	$url_api="https://www.wikidata.org/w/api.php?action=wbgetclaims&entity=".$qwd."&property=P625&format=json";
	$res  = json_decode(file_get_contents($url_api,true),true);
	if (isset($res["claims"]["P625"])){
		$refinfo=$res["claims"]["P625"][array_key_first($res["claims"]["P625"])];
		$lat=floatval($refinfo["mainsnak"]["datavalue"]["value"]["latitude"]);
		$long=floatval($refinfo["mainsnak"]["datavalue"]["value"]["longitude"]);
		if ($discoplace==""){
			$discoplace=labelqwd($qwd, $lg);
		}
		return $discoplace." <a href=\"https://geohack.toolforge.org/geohack.php?params=".$lat."_N_".$long."_E_scale:5000000_globe:Earth_type:camera_heading:179.00&language=".$lg."\"  class=\"lienwd\"\"><img src=\"https://zone47.com/crotos/img/map.png\" alt=\"\"/></a>";
	}
}
function getclaimvalue($qwd,$prop){
	$res=array();
	$url_api="https://www.wikidata.org/w/api.php?action=wbgetclaims&entity=".$qwd."&property=".$prop."&format=json";
	$res  = json_decode(file_get_contents($url_api,true),true);
	if (isset($res["claims"][$prop])){
		foreach($res["claims"][$prop] as $key=>$val){
			$res[]=$val["mainsnak"]["datavalue"]["value"];
		}
	}
	return $res;
}
function page_title($url) {
	$fp = file_get_contents($url);
	if (!$fp) 
		return null;
	$res = preg_match("/<title>(.*)<\/title>/siU", $fp, $title_matches);
	if (!$res) 
		return null; 
	$title = preg_replace('/\s+/', ' ', $title_matches[1]);
	$title = trim($title);
	return $title;
}

function lienPOP($idJOC){
	$titre=page_title("http://data.culture.fr/thesaurus/page/ark:/67717/".$idJOC."/");
	$tab_titre=explode("|",$titre);
	$chaine_recherche=urlencode(html_entity_decode(trim($tab_titre[0])));
	$lien ="https://www.pop.culture.gouv.fr/advanced-search/list/joconde?qb=%5B%7B%22field%22%3A%22AUTR.keyword%22%2C%22operator%22%3A%22%3D%3D%3D%2A%22%2C%22value%22%3A%22".$chaine_recherche."%22%2C%22combinator%22%3A%22AND%22%2C%22index%22%3A0%7D%5D";

	return $lien;

}
function querywd($qwd,$lg,$prop){
	if ($prop!="P921"){
		$searched="?item wdt:".$prop." ?searched.";
	}
	else{
		$searched="{?item wdt:P921 ?searched.}UNION{?item wdt:P180 ?searched.}";
	}
	return "https://query.wikidata.org/embed.html#".str_replace("+","%20",urlencode("#defaultView:ImageGrid
SELECT distinct
(SAMPLE(?url) as ?URL)
?item ?itemLabel
(GROUP_CONCAT(distinct ?crea_label; separator=\" - \") as ?crea)
(GROUP_CONCAT(distinct ?coll_label; separator=\" - \") as ?collection)
(GROUP_CONCAT(distinct ?date; separator=\" ; \") as ?dates)
(SAMPLE(?img) as ?image)

WHERE {
  BIND (wd:".$qwd." as ?searched) 
  ".$searched."
  # createur
  OPTIONAL { ?item wdt:P170 ?crea.
           OPTIONAL{?crea rdfs:label ?crea_label".$lg." filter (lang(?crea_label".$lg.") = \"".$lg."\")}
           OPTIONAL{?crea rdfs:label ?crea_labelen filter (lang(?crea_labelen) = \"en\")}
           BIND(COALESCE(?crea_label".$lg.",?crea_labelen) AS ?crea_label)
           }
  # collection
  OPTIONAL { ?item wdt:P195 ?coll.
           OPTIONAL{?coll rdfs:label ?coll_label".$lg." filter (lang(?coll_label".$lg.") = \"".$lg."\")}
           OPTIONAL{?coll rdfs:label ?coll_labelen filter (lang(?coll_labelen) = \"en\")}
           BIND(COALESCE(?coll_label".$lg.",?coll_labelen) AS ?coll_label)
           }
  # image
  OPTIONAL { ?item wdt:P18 ?img }
  # date de publication
  OPTIONAL { ?item wdt:P577 ?datepubli
             BIND (year(?datepubli) AS ?date)}
  # date unique
  OPTIONAL { ?item p:P571 ?declarationdate.
             MINUS {?declarationdate pq:P1319 ?pasavant }
             MINUS {?declarationdate pq:P1480 wd:Q5727902 }
             ?declarationdate ps:P571 ?dateunique .
             BIND (year(?dateunique) AS ?date)}
  # date unique environ
  OPTIONAL { ?item p:P571 ?declarationdate.
             MINUS {?declarationdate pq:P1319 ?pasavant }
             ?declarationdate pq:P1480 wd:Q5727902. 
             ?declarationdate ps:P571 ?dateunique . 
             BIND (CONCAT(\"c.\",STR(year(?dateunique))) AS ?date)}
  # période pas avant / pas apres
  OPTIONAL { ?item p:P571 ?declarationdate.
             ?declarationdate pq:P1319 ?pasavant . 
             ?declarationdate pq:P1326 ?pasapres.
             BIND (CONCAT(STR(year(?pasavant)),\" - \",STR(year(?pasapres))) AS ?date)}
  # URL vedette
  # Cas n°1, une URL via un identifiant spécifique à la collection
  OPTIONAL {
    ?item p:P195 ?declarationlcoll.
    ?declarationlcoll ps:P195 ?coll.
   
    {?Qprop wdt:P2378 ?coll.}
    UNION{
      ?coll  wdt:P361 ?Topcoll.
      ?Qprop wdt:P2378 ?Topcoll.
    }
    ?Qprop wdt:P31/wdt:P279* wd:Q18618644.
    ?Qprop wikibase:directClaim ?prop.        
    ?item ?prop ?id .
    ?Qprop wdt:P1630 ?formatterurl .  
    BIND(IRI(REPLACE(?id, '^(.+)$', ?formatterurl)) AS ?urlColl)
  }
  # Cas n°2, une URL via Handle
  OPTIONAL {
    ?item wdt:P1184 ?IDhandle.
    BIND(IRI(CONCAT(\"https://hdl.handle.net/\",STR(?IDhandle))) AS ?urlhandle)
  }
  # Cas n°3, une URL via la propriété P973/\"décrit à l'URL\"
  OPTIONAL {
    ?item wdt:P973 ?urlP973.
  }
  # Cas n°4, une URL via une propriété identifant d'œuvre d'art
  OPTIONAL {
    ?Qprop wikibase:directClaim ?prop.    
    ?Qprop wdt:P31/wdt:P279* wd:Q18618644.
    ?item ?prop ?id .
    ?Qprop wdt:P1630 ?formatterurl .
    FILTER NOT EXISTS {?item wdt:P1256 [] }
    FILTER NOT EXISTS {?item wdt:P1257 [] }
    #MINUS {?item wdt:P2378 []}
    BIND(IRI(REPLACE(?id, '^(.+)$', ?formatterurl)) AS ?urlID)
  }
  # cas n°1 l'emporte sur cas n°2 qui l'emporte sur cas n°3 qui l'emporte sur cas n°4
  BIND(COALESCE(COALESCE(COALESCE(?urlColl, ?urlhandle), ?urlP973),?urlID) AS ?url)  
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"[AUTO_LANGUAGE],".$lg.",en,fr,it,es,ca,de,nl,pt,pl,cs,da,sv,fi,nb,cy,el,tr,ru,uk,ja,zh,ko,fa,he,sw,vi,th,ar,pa,hi,bn,te,id,jv,la,eo\". }
} GROUP BY ?item ?itemLabel 
ORDER BY ?dates"));
}


?>
