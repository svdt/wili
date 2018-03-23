<?php
function DisplayConfig(){
  $status = new StatusMessages();
  include "functions.php";
  ini_set('auto_detect_line_endings',TRUE);
  $DATAPATH = WILI_PACKAGES."WL/wldata/";
  $settings = WILI_PACKAGES.'WL/settings.conf';
  $wlconf = WILI_PACKAGES.'WL/wlconf.conf';

  $HL = readCSV($DATAPATH.'wienerlinien-ogd-haltestellen.csv');
  $LL = readCSV($DATAPATH.'wienerlinien-ogd-linien.csv');
  $SL = readCSV($DATAPATH.'wienerlinien-ogd-steige.csv');
  $CL = readCSV($DATAPATH.'wienerlinien-ogd-farben.csv');

  $CList = json_decode(file_get_contents($wlconf), true);

  if(isset($_POST["update"])){
    exec(WILI_PACKAGES."WL/update_data.sh 2>&1", $result, $exitCode);
    if($exitCode == 0){
      $status->addMessage('Daten wurden aktualisiert', 'success');
    } else {
      $status->addMessage('Beim Aktualisieren der Daten ist ein Fehler aufgetreten. Überprüfe deine Internetverbindung.', 'danger');
    }
  }
  if(isset($_POST["sid"]) && array_key_exists($_POST["sid"], $HL)){
    $SID = $_POST["sid"];
  } else {
    $SID = false;
  }
  if(isset($_POST["lid"]) && array_key_exists($_POST["lid"], $SL)){
    $LID = $_POST["lid"];
  } else {
    $LID = false;
  }

  if($SID && $LID){
    if($SL[$LID]["FK_HALTESTELLEN_ID"] == $SID){
      $info = getLineInfo($SL,$HL,$LL,$LID);
      addToList($wlconf,$SL,$SID,$HL[$SID]["DIVA"],$LID,$info["RBL"], $HL[$SID]["NAME"], $info["NAME"], $info["DIRNAME"], $info["DIR"],$CL[$SL[$LID]["FK_LINIEN_ID"]]["FARBE"]);
      #addToList($wlconf,$SL,$_GET["station"],$_GET["line"],$SL[$_GET["line"]]["RBL_NUMMER"], $HL[$_GET["station"]]["NAME"], $info[0], $info[1]);
      $CList = json_decode(file_get_contents($wlconf), true);
    }
  }
  if(isset($_POST["method"]) && ($_POST["method"] == "bin" || $_POST["method"] == "dec")){
    $set = json_decode(file_get_contents($settings), true);
    $checked = ($_POST["method"] == "dec") ? 0 : 1;
    $set["method"] = ($checked) ? "bin" : "dec";
    file_put_contents($settings, json_encode($set));
  } else {
    $set = json_decode(file_get_contents($settings), true);
    $checked = ($set["method"] == "bin") ? 1 : 0;
  }

  if(isset($_POST["color"])){
    $keys = explode('-', $_POST["color"]);
    if(array_key_exists($keys[0], $CList) && array_key_exists($keys[1], $CList[$keys[0]]["LINES"]) && preg_match("/([a-f0-9]{3}){1,2}\b/i",$keys[2])){
      $CList = json_decode(file_get_contents($wlconf), true);
      $CList[$keys[0]]["LINES"][$keys[1]]["C"] = $keys[2];
      file_put_contents($wlconf, json_encode($CList));
    }
  }

  if(isset($_POST["del"])){
    deleteFromList($_POST["del"],$wlconf);
    $CList = json_decode(file_get_contents($wlconf), true);
  } elseif (isset($_POST["wegzeit"]) && intval($_POST["wegzeit"]) > -1 && isset($_POST["wegzeit-id"]) && array_key_exists($_POST["wegzeit-id"], $CList)){
    $CList = json_decode(file_get_contents($wlconf), true);
    $CList[$_POST["wegzeit-id"]]["WEG"] = strval(intval($_POST["wegzeit"]));
    file_put_contents($wlconf, json_encode($CList));
  }

  $HList = getHList($HL,$SID);

  if($SID){
    $match = array("FK_HALTESTELLEN_ID" => $SID);
    $LINESTOPS = searchInCSV($SL, $match, "ALL");
    $LSTRING = getLList($SL,$HL,$LL,$LINESTOPS,$SID);
  }
?>
<h3>Wiener Linien</h3>
<?php  "<p>".$status->showMessages()."</p>"; ?>
<div class="row">

      <!-- /.panel-heading -->
        <?php
          echo getWL($CList);
         ?>

  <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><div class="panel panel-default">
  <div class="panel-heading"><button class="btn btn-xs add" name="add"><i class="fa fa-plus" aria-hidden="true"></i></button></div>
  <form id="search" method="POST">
  <div class="panel-item">
  <select id="station" name="sid" data-placeholder="Wähle eine Haltestelle..." class="chosen-select-line" tabindex="10">
  <option value=""></option>
  <?php
    echo $HList;
  ?>
  </select></div>

  <?php
  if($SID){
    echo '<div class="panel-item">'.$LSTRING.'</div>';
  }
   ?>
  </form>
  </div>
</div>

<script type="text/javascript">
function formatLine (line) {
  if (!$(line.element).data('dir')) { return line.text; }
  var $line = $(
  '<span>' + line.text + '<br><small><b> Richtung</b> ' + $(line.element).data('dir') + '</small></span>'
  );
  return $line;
};

$('#station').select2();
$('#line').select2({templateResult: formatLine});
$('#station').change(function() {$("#search").submit();});
$('#line').change(function() {$("#search").submit();});

function changeBG(color, e) {
  var hexColor = "transparent";
  if(color) {
    hexColor = color;
  }
  $(e).val(escape(e.id+'-'+hexColor));
  $(e).closest("form.form-line").submit();
};
</script>
<div class="col-12">
<div class="form-group form-inline">
<form class="method" method="POST">

<div class="form-group mb-2">
<label class="switch my-2 mr-2" onclick="toggleSubmit(this)" name="method" value="<?php echo ($checked) ? "dec" : "bin"?>">
  <input type="checkbox" data-toggle="toggle" data-on="Binär" data-off="Dezimal" <?php echo ($checked) ?  "checked" : "";?>>
</label>
<input type="submit" class="btn btn-wili my-2 mr-2" value="Daten aktualisieren" name="update"/>
</div>
</form>
<!--<label>
  <span>Binär/Dezimaldarstellung</span>
</label>-->
</div>
</div>
</div>
<?php
}
?>
