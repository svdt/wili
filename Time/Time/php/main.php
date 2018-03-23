<?php
function DisplayConfig(){
  $status = new StatusMessages();

?>
<h3>Time</h3>
<?php  "<p>".$status->showMessages()."</p>"; ?>
<div class="row">
  <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><div class="panel panel-default">
  <div class="panel-heading"><button class="btn btn-xs add" name="add"><i class="fa fa-plus" aria-hidden="true"></i></button></div>
  <form id="search" method="POST">
  <div class="panel-item">Hi</div>
  </form>
  </div>
</div>

<div class="col-12">
<div class="form-group form-inline">
<!--<label>
  <span>Binär/Dezimaldarstellung</span>
</label>-->
</div>
</div>
</div>
<?php
}
?>
