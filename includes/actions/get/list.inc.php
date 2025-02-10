<?php
define("API_PATH", dirname(dirname(__FILE__))."/");
function LoadFilesFrom($folder,$pattern="")
{
    $specialFolders=Array(".", "..","");
    $folder.=(!preg_match("/\/$/", $folder))?"/":"";
    $array=Array();
    if (is_dir($folder)) 
      foreach (scandir($folder) as $FTO) 
        if (!in_array($FTO, $specialFolders))
          if (is_dir($folder.$FTO)) 
            $array[$FTO] = LoadFilesFrom($folder.$FTO, $pattern);
          else 
            array_push($array, $folder.$FTO);
    return $array;
}
function array_flat($array) {
  return array_merge_recursive(...array_values($array));;
}
$files=array_flat(LoadFilesFrom(API_PATH,"*.inc.php"));
foreach ($files as $api) {
  $aux=explode(DIRECTORY_SEPARATOR, str_replace(".inc.php", "",str_replace(API_PATH, "", $api)));
  $json["api"][$aux[0]][]=$aux[1];
}

  