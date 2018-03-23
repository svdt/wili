<?php

function readCSV($FILENAME){
  $array = array();
  $handle = fopen($FILENAME, "r");
  $i = 0;
  if ($handle) {
      $fields = explode(';',str_replace('"','',fgets($handle)));
      array_shift($fields);
      while ($line = fgets($handle)) {
        $tmp = explode(';',str_replace('"','',$line));
        $ID = $tmp[0];
        array_shift($tmp);
        $tmp[count($tmp)-1] = rtrim($tmp[count($tmp)-1]);
        $fields[count($fields)-1] = rtrim($fields[count($fields)-1]);
        $array[$ID] = array_combine($fields,$tmp);
      }
      fclose($handle);
  } else {
      // error opening the file.
  }
  return $array;
}

function searchInCSV($FILE, $match, $RETURN){
  $result = array();
  foreach ($FILE as $ID => $array) {
    $found = true;
    foreach ($match as $key => $value) {
      if($array[$key] != $value){
        $found = false;
      }
    }
    if($found){
      if($RETURN == "ID"){
        array_push($result, $ID);
      } elseif($RETURN == "ALL") {
        $result[$ID] = $array;
      } else {
        array_push($result, $array[$RETURN]);
      }
    }
  }
  switch (count($result)){
    case 0:
      return false;
    #case 1:
    #  return array_values($result)[0];
    default:
      return $result;
  }
}


function getLineInfo($SL,$HL,$LL,$LID){
  $line = $SL[$LID];
  $match = array("FK_LINIEN_ID" => $line["FK_LINIEN_ID"], "RICHTUNG" => $line["RICHTUNG"]);
  $LINESTOPSH = searchInCSV($SL, $match, "ALL");
  $e = end($LINESTOPSH);
  $ID = $e["FK_HALTESTELLEN_ID"];
  $e = reset($LINESTOPSH);
  $return = array();
  if(isset($HL[$ID]["NAME"])){
    $return["DIRNAME"] = $HL[$ID]["NAME"];
  } else {
    $return["DIRNAME"] = "";
  }
  $ID = $line["FK_LINIEN_ID"];
  $return["NAME"] = $LL[$ID]["BEZEICHNUNG"];
  $return["RBL"] = $line["RBL_NUMMER"];
  $return["DIR"] = $line["RICHTUNG"];

  return $return;
}


function getLList($SL,$HL,$LL,$LINESTOPSH,$LID){
  $S = '<select id="line" name="lid" data-placeholder="Wähle eine Linie..." class="chosen-select-station" tabindex="10">
  <option value=""></option>';
  foreach ($LINESTOPSH as $key => $line) {
    $return = getLineInfo($SL,$HL,$LL,$key);
    $DIRN = $return["DIRNAME"];
    $NAME = $return["NAME"];
    if($DIRN != $HL[$SL[$key]["FK_HALTESTELLEN_ID"]]["NAME"]) {
      if($key == $LID){
        $S .= '<option value="'.$key.'" data-dir="'.$DIRN.'" selected>'.$NAME.'</option>';
      } else {
        $S .= '<option value="'.$key.'" data-dir="'.$DIRN.'">'.$NAME.'</option>';
      }
    }
  }
  $S .= '</select>';
  return $S;
}

function getHList($HL, $HID){
  $HSTRING = "";
  foreach ($HL as $key => $value) {
    if($key == $HID){
      $HSTRING .= '<option value="'.$key.'" selected>'.$value["NAME"].'</option>';
    } else {
      $HSTRING .= '<option value="'.$key.'">'.$value["NAME"].'</option>';
    }
  }
  return $HSTRING;
}

function getWLList($ID,$e){
  if($e["WEG"] == "0"){
    $weg = 'placeholder="optional"';
  } else {
    $weg = 'value="'.$e["WEG"].'"';
  }
  $S = '<div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><div class="panel panel-default"><form method="POST">';
  $S .= '<div class="panel-heading"><h4>'.$e["NAME"].'</h4><button class="btn btn-xs del" type="submit" name="del" value="'.$ID.'"><i class="fa fa-times" aria-hidden="true"></i></button></div></form>';
  $S .= '<form method="POST"><div class="panel-item"><div class="input-group"><div class="input-group-prepend input-group-text">Wegzeit:</div><input type="hidden" name="wegzeit-id" value="'.$ID.'"><input type="number" name="wegzeit" class="form-control" '.$weg.' min="0" max="25"><button type="submit" class="input-group-addon btn btn-xs h-100">Ok</button></div></div></form>';
  foreach ($e["LINES"] as $key => $value) {
    $S .= '<form class="form-line" method="POST"><div class="panel-item">';
    $S .= '<input id="'.$ID.'-'.$key.'" type="hidden" value="'.$value["C"].'" name="color"><button type="submit" class="color h-100 jscolor" data-jscolor=\'{width:150,height:150, padding:0,shadow:false, valueElement: "'.$ID.'-'.$key.'",
    borderWidth:0, backgroundColor:"transparent", insetColor:"#61B296", mode:"HS", position:"bottom",closable:true, value:"'.$value["C"].'"}\' style="background-color:#'.$value["C"].'" data-color="'.$value["STDC"].'" data-color-new="'.$value["C"].'" data-value="'.$ID.'-'.$key.'"></button><span class="line h-100"><b>'.$value["LINE"].'</b> '.$value["DIRNAME"].'</span><button class="btn btn-xs del h-100" type="submit" name="del" value="'.$ID.'-'.$key.'"><i class="fa fa-times" aria-hidden="true"></i></button>';
    $S .= '</div></form>';
  }
  return $S.'</div></div>';
}

function getWL($CList){
  $S = '';
  foreach ($CList as $key => $e) {
    $S .= getWLList($key,$e);
  }
  return $S;
}


function deleteFromList($key,$wlconf){
  $CList = json_decode(file_get_contents($wlconf), true);
  $keys = explode('-', $key);
  if(count($keys) == 1 && array_key_exists($keys[0], $CList)) {
    unset($CList[$keys[0]]);
  } elseif (count($keys) == 2 && array_key_exists($keys[0], $CList) && array_key_exists($keys[1], $CList[$keys[0]]["LINES"])) {
    unset($CList[$keys[0]]["LINES"][$keys[1]]);
    if(count($CList[$keys[0]]["LINES"]) == 0) {
      unset($CList[$keys[0]]);
    }
  }
  if(count($CList) == 0){
    file_put_contents($wlconf, "{}");
  } else {
    file_put_contents($wlconf, json_encode($CList));
  }
}

function getStationRBLS($SL, $station){
  $rbls = array();
  $match = array("FK_HALTESTELLEN_ID" => $station);
  $LINESTOPSH = searchInCSV($SL, $match, "ALL");
  foreach ($LINESTOPSH as $key => $value) {
    array_push($rbls, $value["RBL_NUMMER"]);
  }
  return array_values(array_unique($rbls));
}

function addToList($wlconf, $SL,$station,$diva,$line, $rbl, $stationname, $linename, $dirname, $dir, $color){
  $CList = json_decode(file_get_contents($wlconf), true);
  $Stations = array_keys($CList);
  if(in_array($station, $Stations)){
    $Lines = array_keys($CList[$station]["LINES"]);
    if(in_array($line, $Lines)){
      return;
    } else {
      $CList[$station]["LINES"][$line] = ["RBL" => $rbl, "LINE" => $linename, "DIRNAME" => $dirname, "DIR" => $dir, "C" => $color, "STDC" => $color];
    }
  } else {
    $rbls = getStationRBLS($SL, $station);
    $CList[$station] = ["NAME" => $stationname, "DIVA" => $diva, "WEG" => "0", "RBLS" => $rbls, "LINES" => array()];
    $CList[$station]["LINES"][$line] = ["RBL" => $rbl, "LINE" => $linename, "DIRNAME" => $dirname, "DIR" => $dir, "C" => $color, "STDC" => $color];
  }
  file_put_contents($wlconf, json_encode($CList));
}
