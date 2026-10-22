<?php 
require("../clude/head.php");
$no=0;
$dpt=$_GET['id'];
?>
<style type="text/css">
  .table{   
    width: 100% !important;
    height: 100% !important;
    border: 1px solid #000 !important;
    border-collapse: collapse !important;
    line-height: 1.15;
  }

  @page {
    size: A4 portrait;
    margin: 5mm;
  }

  @media print {
    html, body {
      width: 100%;
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
    }

    #page-container,
    #content,
    .panel,
    .print {
      width: 100% !important;
      max-width: none !important;
      margin: 0 !important;
      padding: 0 !important;
      border: 0 !important;
      box-shadow: none !important;
    }

    #header, #sidebar, .sidebar, .navbar, .hidden-print {
      display: none !important;
    }

    .table-sheet {
      width: 100% !important;
      table-layout: fixed;
      font-size: 14px !important;
      line-height: 1.15 !important;
      color: #000 !important;
      border: 1px solid #000 !important;
      border-collapse: collapse !important;
      outline: 1px solid #000;
    }

    .table-sheet th,
    .table-sheet td {
      box-sizing: border-box;
    }

    .table-sheet th:nth-child(1),
    .table-sheet td:nth-child(1) {
      width: 8px !important;
    }

    .table-sheet th:nth-child(2),
    .table-sheet td:nth-child(2) {
      width: 200px !important;
    }

    .table-sheet th,
    .table-sheet td {
      border: 1px solid #000 !important;
      padding: 5px 4px !important;
      vertical-align: middle !important;
    }

    .table-sheet tbody td {
      font-size: 16px !important;
      font-weight: 600 !important;
    }

    .table-sheet tbody td:nth-child(2) {
      font-size: 18px !important;
      font-weight: 700 !important;
      text-align: left !important;
    }

    .table-sheet th {
      font-size: 14px !important;
      font-weight: 700 !important;
    }

    .table-sheet h2 {
      margin: 4px 0 !important;
      font-size: 18px !important;
    }

    .table-sheet thead tr:first-child td {
      font-size: 15px !important;
      padding: 7px 5px !important;
    }
  }
</style>

<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <?php require("../clude/header.php"); ?>
  <?php require("../clude/top_menu.php"); ?>
 
  <div id="content" class="content">
    <div class="panel panel-default" style="margin-bottom: 10px;">
      <span class="pull-right hidden-print" style="height: 50px; margin-top: 10px;">
        <a href="javascript:;" onclick="exportStockSheet()" class="btn btn-sm btn-primary m-b-10 hidden-print" >
        <i class="fa fa-file-excel-o m-r-5"></i> Export to Excel</a>
        <a href="javascript:;" onclick="window.print()" class="btn btn-sm btn-success m-b-10  hidden-print" >
        <i class="fa fa-print m-r-5"></i> Print</a>
      </span>
      <div class="print" > 
         <?php
        $noc=0;
        $get_cat = "SELECT * FROM kp_cat WHERE dpt='$dpt' ORDER BY name ASC";
        $get_cat_r = mysqli_query($connection,$get_cat) or die("Could not get the ite list");
        $item_count = mysqli_num_rows($get_cat_r);
        if ($item_count < 1) {
            echo "No food in the database";
        }else{
        ?>
        <table class="table table-sheet">
          <thead>
            <tr> 
              <td colspan="9" ><br> 
                <b>NAME:............................................................ 
                  DATE:............................................ 
                  COUNTER:.........................................
                </b>
              </td>
            </tr>
            <tr>
              <th>NO</th>
              <th>NAME</th>
              <th>OPENING</th>
              <th>ISSUE</th>
              <th>TOTAL</th>
              <th>CLOSING</th>
              <th>SALE</th>
              <th>S. PRICE</th>
              <th>AMOUNT</th> 
            </tr>
          </thead>
          <tbody>
            <?php
            while ($c = mysqli_fetch_assoc($get_cat_r)) {
              $cat_id = $c['cat_id']; 
              $catname = $c['name'];  
              $noc= $noc+1;
              ?>
              <tr> 
                <td colspan="9" ><br> 
                  <h2><?php echo $noc.". ".$catname; ?></h2>
                </td>
              </tr>
              <?php
              $get_items = "SELECT * FROM kp_items WHERE del_status=2 AND stock=1 AND dpt='$dpt' AND cat_id='$cat_id' ORDER BY name ASC";
              $get_items_r = mysqli_query($connection,$get_items) or die("Could not get the ite list");
              $item_count = mysqli_num_rows($get_items_r);
              if ($item_count < 1) {
                  echo "No food in the database";
              }else{
                $no=0;
                while ($i = mysqli_fetch_assoc($get_items_r)) {
                  $item_id = $i['item_id']; 
                  $name = $i['name']; 
                  $price = $i['price'];
                  $no= $no+1;
                  ?>
                  <tr>
                    <td><?php echo $no; ?></td>
                    <td><?php echo $name; ?></td> 
                    <td> </td>  
                    <td> </td>
                    <td> </td> 
                    <td> </td>  
                    <td> </td>
                    <td><?php echo $price; ?></td> 
                    <td> </td>    
                  </tr>
                  <?php
                }
              }
            }
            $get_items = "SELECT * FROM kp_items WHERE del_status=2 AND stock=1 AND dpt='$dpt' AND cat_id=0 ORDER BY name ASC";
              $get_items_r = mysqli_query($connection,$get_items) or die("Could not get the ite list");
              $item_count = mysqli_num_rows($get_items_r);
              if ($item_count < 1) {
                  echo "No food in the database";
              }else{
                $no=0;
                ?>
              <tr> 
                <td colspan="9" ><br> 
                  <h2><?php echo $noc.". NO CATEGORY ITEMS"; ?></h2>
                </td>
              </tr>
              <?php
                while ($i = mysqli_fetch_assoc($get_items_r)) {
                  $item_id = $i['item_id']; 
                  $name = $i['name']; 
                  $price = $i['price'];
                  $no= $no+1;
                  ?>
                  <tr>
                    <td><?php echo $no; ?></td>
                    <td><?php echo $name; ?></td> 
                    <td> </td>  
                    <td> </td>
                    <td> </td> 
                    <td> </td>  
                    <td> </td>
                    <td><?php echo $price; ?></td> 
                    <td> </td>    
                  </tr>
                  <?php
                }
              }
              for ($i=0; $i < 3; $i++) { 
                $no=$no+$i;
                ?>
                <tr>
                  <td><?php echo $no; ?></td>
                  <td> </td> 
                  <td> </td>  
                  <td> </td>
                  <td> </td> 
                  <td> </td>  
                  <td> </td>
                  <td> </td> 
                  <td> </td>    
                </tr>
                <?php
              } 
              for ($i=0; $i < 6; $i++) { 
                $no=$no+$i;
                ?>
                <tr>
                  <td><BR></td>
                  <td colspan="8"></td>
                </tr>
                <?php
              }
              ?>  
          </tbody>
        </table>   
        <?php
        }
        ?>  
      </div>
    </div>
  </div>
</div>
<!-- end #content -->
 
</div>
<!-- end page container -->
<!-- ================== BEGIN BASE JS ================== -->
<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
<script src="../../assets/js/theme/default.min.js"></script> 
<script src="../../assets/js/apps.min.js"></script>
<!-- ================== END BASE JS ================== -->
<script>
  function exportStockSheet() {
    var table = document.querySelector('.table-sheet');

    if (!table) {
      alert('There is no stock sheet to export.');
      return;
    }

    // Export the rendered table, including its category headings and blank rows.
    var tableCopy = table.cloneNode(true);
    var styles = '<style>' +
      'table{border-collapse:collapse;width:100%;}' +
      'th,td{border:1px solid #000;padding:5px 4px;vertical-align:middle;}' +
      'h2{margin:4px 0;font-size:18px;}' +
      '</style>';
    var excelDocument = '<html xmlns:o="urn:schemas-microsoft-com:office:office" ' +
      'xmlns:x="urn:schemas-microsoft-com:office:excel" ' +
      'xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8">' +
      styles + '</head><body>' + tableCopy.outerHTML + '</body></html>';
    var blob = new Blob(['\ufeff', excelDocument], { type: 'application/vnd.ms-excel' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    var date = new Date().toISOString().slice(0, 10);

    link.href = url;
    link.download = 'stock-sheet-' + date + '.xls';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  }

  $(document).ready(function() {
    App.init();
     
  }); 
</script> 
</body>
</html>
  
        
